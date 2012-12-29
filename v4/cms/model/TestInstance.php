<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class TestInstance {

    private $r = null;
    private $pipes;
    public $code_execution_halted = false;
    private $last_action_time;
    private $last_execution_time;
    public $TestSession_id = 0;
    public $User_id = 0;
    public $is_working = false;
    public $is_data_ready = false;
    public $response = "";
    public $error_response = "";
    public $code = "";
    public $is_serializing = false;
    public $is_serialized = false;
    public $is_finished = false;
    public $pending_variables = null;
    public $debug_code_appended = false;

    public function __construct($session_id = 0, $owner_id = 0) {
        $this->TestSession_id = $session_id;
        $this->User_id = $owner_id;
    }

    public function is_timedout() {
        if (time() - $this->last_action_time > Ini::$r_instances_timeout) {
            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->is_timedout() --- Test instance timedout");
            return true;
        }
        else
            return false;
    }

    public function is_execution_timedout() {
        TestSession::change_db($this->User_id);
        $session = $this->get_TestSession();

        if (time() - $this->last_execution_time > Ini::$r_max_execution_time && $session->status == TestSession::TEST_SESSION_STATUS_WORKING) {
            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->is_execution_timedout() --- Test instance execution timedout");
            return true;
        }
        else
            return false;
    }

    public function is_started() {
        if ($this->r == null)
            return false;
        if (is_resource($this->r)) {
            $status = proc_get_status($this->r);
            return $status["running"];
        }
        else
            return false;
    }

    public function start() {
        $env = array();
        if (Ini::$unix_locale != "") {
            $encoding = Ini::$unix_locale;
            $env = array(
                'LANG' => $encoding
            );
        }

        if (TestServer::$debug)
            TestServer::log_debug("TestInstance->start() --- Test instance starting");
        $this->last_action_time = time();
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $owner = $this->get_User();
        $userR = $owner->get_UserR();

        $this->r = proc_open("sudo -u " . $userR->login . " " . Ini::$path_r_exe . " --vanilla --quiet", $descriptorspec, $this->pipes, Ini::$path_temp, $env);
        if (is_resource($this->r)) {
            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->start() --- Test instance started");

            if (!stream_set_blocking($this->pipes[0], 0)) {
                if (TestServer::$debug) {
                    TestServer::log_debug("TestInstance->read() --- Error: (stream_set_blocking) #0");
                    return false;
                }
            }
            if (!stream_set_blocking($this->pipes[1], 0)) {
                if (TestServer::$debug) {
                    TestServer::log_debug("TestInstance->read() --- Error: (stream_set_blocking) #1");
                    return false;
                }
            }
            if (!stream_set_blocking($this->pipes[2], 0)) {
                if (TestServer::$debug) {
                    TestServer::log_debug("TestInstance->read() --- Error: (stream_set_blocking) #2");
                    return false;
                }
            }

            return true;
        } else {
            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->start() --- Test instance NOT started");
            return false;
        }
    }

    public function stop($terminate = false) {
        TestSession::change_db($this->User_id);
        $session = TestSession::from_mysql_id($this->TestSession_id);

        if ($this->is_started()) {
            if ($session->status == TestSession::TEST_SESSION_STATUS_TEMPLATE)
                $this->send_close_signal();

            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);

            if ($this->is_execution_timedout() || $terminate) {
                $this->terminate_processess();
            }
            $ret = proc_close($this->r);
            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->stop() --- Test instance closed with: " . $ret);
        }
        return null;
    }

    public function terminate_processess() {
        $status = proc_get_status($this->r);
        if ($status !== false) {
            $ppid = $status['pid'];

            TestInstance::kill_children($ppid);
        }
    }

    public static function kill_children($ppid) {
        if (TestServer::$debug)
            TestServer::log_debug("TestInstance->terminate_processess() --- killing children of pid:" . $ppid);

        $pids = preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`);
        foreach ($pids as $pid) {
            if (is_numeric($pid))
                TestInstance::kill_children($pid);
        }
        if (is_numeric($ppid)) {
            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->terminate_processess() --- killing " . $ppid);
            posix_kill($ppid, 9); //9 is the SIGKILL signal
        }
    }

    public function serialize() {
        TestSession::change_db($this->User_id);
        $session = $this->get_TestSession();

        if (TestServer::$debug)
            TestServer::log_debug("TestInstance->serialize() --- Serializing #" . $this->TestSession_id);

        $this->response = "";
        $this->error_response = "";
        $this->is_serializing = true;
        $this->is_serialized = false;
        $this->is_working = true;
        $fp = fopen($session->get_RSession_fifo_path(), "w");
        stream_set_blocking($fp, 0);
        fwrite($fp, "serialize");
        fclose($fp);

        if ($session->debug == 1) {
            $session->status = TestSession::TEST_SESSION_STATUS_SERIALIZED;
            $session->mysql_save();
        }
    }

    public function send_QTI_initialization() {
        TestSession::change_db($this->User_id);
        $session = $this->get_TestSession();

        $qti = QTIAssessmentItem::from_mysql_id($session->QTIAssessmentItem_id);
        $qti->validate();

        $code = $qti->get_QTI_ini_R_code();
        $json_code = json_encode(array("code" => $code));

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_QTI_initialization() --- sending code to session #" . $this->TestSession_id);
            if (TestServer::$debug_stream_data)
                TestServer::log_debug($code, true);
        }

        $this->is_working = true;

        $fp = fopen($session->get_RSession_fifo_path(), "w");
        stream_set_blocking($fp, 0);
        fwrite($fp, $json_code);
        fclose($fp);

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_QTI_initialization() --- finished sending code to session #" . $this->TestSession_id);
        }
    }

    public function send_QTI_response_processing() {
        TestSession::change_db($this->User_id);
        $session = $this->get_TestSession();

        $qti = QTIAssessmentItem::from_mysql_id($session->QTIAssessmentItem_id);
        $qti->validate();

        $code = $qti->get_response_processing_R_code();
        $json_code = json_encode(array("code" => $code));

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_QTI_response_processing() --- sending code to session #" . $this->TestSession_id);
            if (TestServer::$debug_stream_data)
                TestServer::log_debug($code, true);
        }

        $this->is_working = true;

        $fp = fopen($session->get_RSession_fifo_path(), "w");
        stream_set_blocking($fp, 0);
        fwrite($fp, $json_code);
        fclose($fp);

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_QTI_response_processing() --- finished sending code to session #" . $this->TestSession_id);
        }
    }

    public function send_variables($variables = null) {
        TestSession::change_db($this->User_id);
        $session = $this->get_TestSession();

        $variables = json_encode($variables);

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_variables() --- sending variables to session #" . $this->TestSession_id);
            if (TestServer::$debug_stream_data)
                TestServer::log_debug($variables, true);
        }


        $this->response = "";
        $this->error_response = "";
        $this->is_working = true;

        $fp = fopen($session->get_RSession_fifo_path(), "w");
        stream_set_blocking($fp, 0);
        fwrite($fp, $variables);
        fclose($fp);

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_variables() --- finished sending variables to session #" . $this->TestSession_id);
        }
    }

    public function send_close_signal() {
        TestSession::change_db($this->User_id);
        $session = $this->get_TestSession();

        if ($this->is_serialized)
            return;

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_close_signal() --- sending close signal to session #" . $this->TestSession_id);
        }


        $this->response = "";
        $this->error_response = "";
        $this->is_working = true;

        $fp = fopen($session->get_RSession_fifo_path(), "w");
        stream_set_blocking($fp, 0);
        fwrite($fp, "close");
        fclose($fp);

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_close_signal() --- finished sending close signal to session #" . $this->TestSession_id);
        }
    }

    public function read() {
        TestSession::change_db($this->User_id);

        $this->code_execution_halted = false;
        $this->last_action_time = time();

        $result = "";
        $error = "";
        while ($append = fread($this->pipes[1], 4096)) {
            $result.=$append;
        }

        $session = TestSession::from_mysql_id($this->TestSession_id);
        $change_status = false;

        //serialized
        if ($session->status == TestSession::TEST_SESSION_STATUS_SERIALIZED) {
            $this->is_serialized = true;
            $this->is_data_ready = true;

            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->read() --- Serialized instance recognized.");
        } else {

            //template
            if ($session->status == TestSession::TEST_SESSION_STATUS_TEMPLATE && !$this->is_serializing) {
                $this->is_data_ready = true;
                if (TestServer::$debug)
                    TestServer::log_debug("TestInstance->read() --- Template instance recognized.");
            }

            //QTI initialization
            if ($session->status == TestSession::TEST_SESSION_STATUS_QTI_INIT && !$this->is_serializing) {

                if (TestServer::$debug)
                    TestServer::log_debug("TestInstance->read() --- QTI initialization instance recognized.");

                $session->status = TestSession::TEST_SESSION_STATUS_WORKING;
                $change_status = true;
                $this->send_QTI_initialization();
            }

            //QTI response processing
            if ($session->status == TestSession::TEST_SESSION_STATUS_QTI_RP && !$this->is_serializing) {

                if (TestServer::$debug)
                    TestServer::log_debug("TestInstance->read() --- QTI response processing instance recognized.");

                $session->status = TestSession::TEST_SESSION_STATUS_WORKING;
                $change_status = true;
                $this->send_QTI_response_processing();
            }
        }

        $lines = explode("\n", $result);
        if (count($lines) > 0 && !$this->is_data_ready) {
            $last_line = $lines[count($lines) - 1];

            if ($last_line == "> ") {
                $this->is_data_ready = true;

                if ($session->status != TestSession::TEST_SESSION_STATUS_COMPLETED) {
                    $change_status = true;
                    $session->status = TestSession::TEST_SESSION_STATUS_WAITING;
                } else {
                    $this->is_finished = true;
                    if (TestServer::$debug)
                        TestServer::log_debug("TestInstance->read() --- Completed instance recognised.");
                }
            }
            if (strpos($last_line, "+ ") === 0 && $session->debug == 1) {
                $this->is_data_ready = true;

                $change_status = true;
                $session->status = TestSession::TEST_SESSION_STATUS_WAITING_CODE;
                if (TestServer::$debug)
                    TestServer::log_debug("TestInstance->read() --- Waiting for code instance recognised.");
            }
        }

        while ($append = fread($this->pipes[2], 4096)) {
            $error.=$append;
        }
        if (strpos($error, 'Execution halted') !== false || $this->is_execution_timedout()) {
            $this->code_execution_halted = true;
            $this->is_data_ready = true;

            $change_status = true;
            $session->status = TestSession::TEST_SESSION_STATUS_ERROR;

            if ($this->is_execution_timedout())
                $error.="
                TIMEOUT
                ";
        }

        $this->response.=$result;
        $this->error_response .= $error;

        if (strlen($this->response) > TestServer::$response_limit)
            $this->response = "( ... )
            " . substr($this->response, strlen($this->response) - TestServer::$response_limit);
        if (strlen($this->error_response) > TestServer::$response_limit)
            $this->error_response = "( ... )
            " . substr($this->error_response, strlen($this->error_response) - TestServer::$response_limit);

        if ($session->debug == 1 && $this->is_data_ready) {
            $session->output = $this->response;
            $session->error_output = $this->error_response;
            $change_status = true;
        }

        if ($change_status) {
            $session->mysql_save();
        }

        if ($session->status == TestSession::TEST_SESSION_STATUS_WORKING && $this->pending_variables != null) {
            $this->send_variables($this->pending_variables);
            $this->pending_variables = null;
        }

        if ($this->is_data_ready) {
            $this->last_action_time = time();
            return $this->response;
        }

        return null;
    }

    public function run($code, $variables = null, $reset_responses = true) {
        TestSession::change_db($this->User_id);

        $session = TestSession::from_mysql_id($this->TestSession_id);

        $this->pending_variables = $variables;
        $is_new = false;
        $send_code = "";

        $change_status = false;
        switch ($session->status) {
            case TestSession::TEST_SESSION_STATUS_NEW: {
                    $is_new = true;
                    $change_status = true;
                    $send_code .= $this->get_ini_code();
                    break;
                }
            case TestSession::TEST_SESSION_STATUS_SERIALIZED: {
                    $is_new = true;
                    $change_status = true;
                    $send_code .= $this->get_ini_code(true);
                    break;
                }
            case TestSession::TEST_SESSION_STATUS_TEMPLATE: {
                    $change_status = true;
                    break;
                }
        }

        if ($change_status) {
            $session->status = TestSession::TEST_SESSION_STATUS_WORKING;
            $session->mysql_save();
        }

        if ($code != null)
            $send_code .= $code;
        else {
            if ($is_new) {
                if ($session->debug == 0) {
                    $test = Test::from_mysql_id($session->Test_id);
                    if ($test != null) {
                        $send_code.= $test->code . $this->get_final_code();
                    }
                }
            }
        }

        if (TestServer::$debug)
            TestServer::log_debug("TestInstance->run() --- Sending " . strlen($send_code) . " data to test instance");
        $this->last_action_time = time();
        $this->last_execution_time = time();

        $lines = explode("\n", $send_code);
        $code = "";
        $i = -1;
        foreach ($lines as $line) {
            $i++;
            $line = trim($line);
            if ($line == "") {
                continue;
            }
            $code .= $line . "\n";
        }
        $this->code = $code;
        if ($reset_responses) {
            $this->response = "";
            $this->error_response = "";
        }

        $bytes = fwrite($this->pipes[0], $code);

        if (TestServer::$debug)
            TestServer::log_debug("TestInstance->run() --- " . $bytes . " written to test instance");

        $this->is_working = true;
        $this->is_data_ready = false;
    }

    public function get_ini_code($unserialize = false) {
        TestSession::change_db($this->User_id);

        $code = "";
        $session = $this->get_TestSession();
        if ($session == null)
            return($code . "stop('session #" . $this->TestSession_id . " does not exist!')
                ");
        $test = $this->get_Test();
        if ($test == null)
            return($code . "stop('test #" . $session->Test_id . " does not exist!')
                ");

        include Ini::$path_internal . 'SETTINGS.php';
        $path = Ini::$path_temp . $session->User_id;

        $user = $session->get_User();

        $code .= sprintf('
            CONCERTO_TEST_ID <- %d
            CONCERTO_TEST_SESSION_ID <- %d
            
            CONCERTO_DB_HOST <- "%s"
            CONCERTO_DB_PORT <- as.numeric(%d)
            CONCERTO_DB_LOGIN <- "%s"
            CONCERTO_DB_PASSWORD <- "%s"
            CONCERTO_DB_NAME <- "%s"
            CONCERTO_TEMP_PATH <- "%s"
            CONCERTO_DB_TIMEZONE <- "%s"
            CONCERTO_MEDIA_PATH <- "%s"
            source("' . Ini::$path_internal . 'lib/R/Concerto.R")
                
            concerto$initialize(CONCERTO_TEST_ID,CONCERTO_TEST_SESSION_ID,CONCERTO_DB_LOGIN,CONCERTO_DB_PASSWORD,CONCERTO_DB_NAME,CONCERTO_DB_HOST,CONCERTO_DB_PORT,CONCERTO_TEMP_PATH,CONCERTO_MEDIA_PATH,CONCERTO_DB_TIMEZONE,%s)
            %s
            
            rm(CONCERTO_TEST_ID)
            rm(CONCERTO_TEST_SESSION_ID)
            rm(CONCERTO_DB_HOST)
            rm(CONCERTO_DB_PORT)
            rm(CONCERTO_DB_LOGIN)
            rm(CONCERTO_DB_PASSWORD)
            rm(CONCERTO_DB_NAME)
            rm(CONCERTO_TEMP_PATH)
            rm(CONCERTO_DB_TIMEZONE)
            rm(CONCERTO_MEDIA_PATH)
            
            %s
            ', $test->id, $this->TestSession_id, $db_host, ($db_port != "" ? $db_port : "3306"), $user->db_login, $user->db_password, $user->db_name, $path, $mysql_timezone, Ini::$path_internal_media . $this->User_id, $unserialize ? "FALSE" : "TRUE", $unserialize ? '
                concerto$unserialize()
                concerto$db$connect(CONCERTO_DB_LOGIN,CONCERTO_DB_PASSWORD,CONCERTO_DB_NAME,CONCERTO_DB_HOST,CONCERTO_DB_PORT,CONCERTO_DB_TIMEZONE)' : "", $unserialize ? 'if(exists("onUnserialize")) do.call("onUnserialize",list(lastReturn=rjson::fromJSON("' . addcslashes(json_encode($this->pending_variables), '"') . '")),envir=.GlobalEnv);' : "");

        if ($unserialize)
            $this->pending_variables = null;
        return $code;
    }

    public function get_final_code() {
        $code = '
            concerto$finalize()
            ';
        return $code;
    }

    public function get_TestSession() {
        TestSession::change_db($this->User_id);

        return TestSession::from_mysql_id($this->TestSession_id);
    }

    public function get_Test() {
        TestSession::change_db($this->User_id);
        $session = $this->get_TestSession();
        if ($session == null)
            return null;
        return Test::from_mysql_id($session->Test_id);
    }

    public function get_User() {
        return User::from_mysql_id($this->User_id);
    }

}

?>
