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
    public $is_working = false;
    public $is_data_ready = false;
    public $response = "";
    public $error_response = "";
    public $code = "";
    public $is_serializing = false;
    public $is_serialized = false;
    public $is_finished = false;
    public $pending_variables = null;

    public function __construct($session_id = 0) {
        $this->TestSession_id = $session_id;
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

    public function is_execution_timedout($session = null) {
        if ($session == null)
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

        include Ini::$path_internal . 'SETTINGS.php';
        $this->r = proc_open("\"" . Ini::$path_r_exe . "\" --vanilla --quiet", $descriptorspec, $this->pipes, Ini::$path_temp, $env);
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

    public function stop() {

        if ($this->is_started()) {
            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);

            if ($this->is_execution_timedout()) {
                $ret = proc_terminate($this->r);
            } else {
                $ret = proc_close($this->r);
            }
            if (TestServer::$debug)
                TestServer::log_debug("TestInstance->stop() --- Test instance closed with: " . $ret);
        }
        return null;
    }

    public function serialize($session = null) {
        if ($session == null)
            $session = $this->get_TestSession();

        if (TestServer::$debug)
            TestServer::log_debug("TestInstance->serialize() --- Serializing #" . $this->TestSession_id);

        $this->response = "";
        $this->error_response = "";
        $this->is_serializing = true;
        $this->is_serialized = false;
        $this->is_working = true;

        $fp = fopen($session->get_RSession_fifo_path(), "w");
        fwrite($fp, "serialize");
        fclose($fp);
    }

    public function send_variables($session = null, $variables = null) {
        if ($session == null)
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
        fwrite($fp, $variables);
        fclose($fp);

        if (TestServer::$debug) {
            TestServer::log_debug("TestInstance->send_variables() --- finished sending variables to session #" . $this->TestSession_id);
        }
    }

    public function read() {
        $this->code_execution_halted = false;
        $this->last_action_time = time();

        $result = "";
        $error = "";
        while ($append = fread($this->pipes[1], 4096)) {
            $result.=$append;
        }

        $session = TestSession::from_mysql_id($this->TestSession_id);
        $change_status = false;

        $lines = explode("\n", $result);
        if (count($lines) > 0) {
            $last_line = $lines[count($lines) - 1];

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
                } else if ($last_line == "> ") {
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
            }
        }

        while ($append = fread($this->pipes[2], 4096)) {
            $error.=$append;
        }
        if (strpos($error, 'Execution halted') !== false || $this->is_execution_timedout($session)) {
            $this->code_execution_halted = true;
            $this->is_data_ready = true;

            $change_status = true;
            $session->status = TestSession::TEST_SESSION_STATUS_ERROR;

            if ($this->is_execution_timedout($session))
                $error.="
                TIMEOUT
                ";
        }

        $this->response.=$result;
        $this->error_response .= $error;

        if ($change_status) {
            $session->mysql_save();
        }

        if ($session->status == TestSession::TEST_SESSION_STATUS_WORKING && $this->pending_variables != null) {
            $this->send_variables($session, $this->pending_variables);
            $this->pending_variables = null;
        }

        if ($this->is_data_ready) {
            $this->last_action_time = time();
            return $this->response;
        }

        return null;
    }

    public function run($code, $variables = null) {
        $this->pending_variables = $variables;
        $is_new = false;
        $send_code = "";

        $session = TestSession::from_mysql_id($this->TestSession_id);
        $change_status = false;
        switch ($session->status) {
            case TestSession::TEST_SESSION_STATUS_NEW: {
                    $is_new = true;
                    $change_status = true;
                    $send_code .= $this->get_ini_code($session);
                    break;
                }
            case TestSession::TEST_SESSION_STATUS_SERIALIZED: {
                    $is_new = true;
                    $change_status = true;
                    $send_code .= $this->get_ini_code($session, null, true);
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
                $test = Test::from_mysql_id($session->Test_id);
                if ($test != null) {
                    $send_code.= $test->code . $this->get_final_code();
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
            $code .= $line . "
                ";
        }
        $this->code = $code;
        $this->response = "";
        $this->error_response = "";

        $bytes = fwrite($this->pipes[0], $code);

        if (TestServer::$debug)
            TestServer::log_debug("TestInstance->run() --- " . $bytes . " written to test instance");

        $this->is_working = true;
        $this->is_data_ready = false;
    }

    public function get_ini_code($session = null, $test = null, $unserialize = false) {
        $code = "";
        if ($session == null)
            $session = $this->get_TestSession();
        if ($session == null)
            return($code . "stop('session #" . $this->TestSession_id . " does not exist!')
                ");
        if ($test == null)
            $test = $this->get_Test($session);
        if ($test == null)
            return($code . "stop('test #" . $session->Test_id . " does not exist!')
                ");

        include Ini::$path_internal . 'SETTINGS.php';
        $path = Ini::$path_temp . $test->Owner_id;
        if (!is_dir($path))
            mkdir($path, 0777);
        $code .= sprintf('
            CONCERTO_TEST_ID <- %d
            CONCERTO_TEST_SESSION_ID <- %d
            
            CONCERTO_DB_HOST <- "%s"
            CONCERTO_DB_PORT <- as.numeric(%d)
            CONCERTO_DB_LOGIN <- "%s"
            CONCERTO_DB_PASSWORD <- "%s"
            CONCERTO_DB_NAME <- "%s"
            CONCERTO_TEMP_PATH <- "%s"
            CONCERTO_MYSQL_HOME <- "%s"
            CONCERTO_DB_TIMEZONE <- "%s"
            source("' . Ini::$path_internal . 'lib/R/Concerto.R")
                
            concerto$initialize(CONCERTO_TEST_ID,CONCERTO_TEST_SESSION_ID,CONCERTO_DB_LOGIN,CONCERTO_DB_PASSWORD,CONCERTO_DB_NAME,CONCERTO_DB_HOST,CONCERTO_DB_PORT,CONCERTO_MYSQL_HOME,CONCERTO_TEMP_PATH,CONCERTO_DB_TIMEZONE,%s)
            %s
            
            rm(CONCERTO_TEST_ID)
            rm(CONCERTO_TEST_SESSION_ID)
            rm(CONCERTO_DB_HOST)
            rm(CONCERTO_DB_PORT)
            rm(CONCERTO_DB_LOGIN)
            rm(CONCERTO_DB_PASSWORD)
            rm(CONCERTO_DB_NAME)
            rm(CONCERTO_TEMP_PATH)
            rm(CONCERTO_MYSQL_HOME)
            rm(CONCERTO_DB_TIMEZONE)
            
            %s
            ', $test->id, $this->TestSession_id, $db_host, ($db_port != "" ? $db_port : "3306"), $db_user, $db_password, $db_name, $path, $path_mysql_home, $mysql_timezone, $unserialize ? "FALSE" : "TRUE", $unserialize ? '
                concerto$unserialize()
                concerto$db$connect(CONCERTO_DB_LOGIN,CONCERTO_DB_PASSWORD,CONCERTO_DB_NAME,CONCERTO_DB_HOST,CONCERTO_DB_PORT,CONCERTO_MYSQL_HOME,CONCERTO_DB_TIMEZONE)' : "", $unserialize ? 'if(exists("onUnserialize")) do.call("onUnserialize",list(lastReturn=rjson::fromJSON("' . addcslashes(json_encode($this->pending_variables), '"') . '")));' : "");

        $returns = $test->get_return_TestVariables();
        if (count($returns) > 0) {
            $code .= '
            concerto$updateAllReturnVariables=function(){
                ';
            foreach ($returns as $ret) {
                $code.=sprintf('concerto$updateReturnVariable("%s")
                ', $ret->name);
            }
            $code.="
            }
            ";
        }
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
        return TestSession::from_mysql_id($this->TestSession_id);
    }

    public function get_Test($session = null) {
        if ($session == null)
            $session = $this->get_TestSession();
        if ($session == null)
            return null;
        return Test::from_mysql_id($session->Test_id);
    }

}

?>
