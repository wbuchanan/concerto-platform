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

class TestSession extends OTable {

    public $Test_id = 0;
    public static $mysql_table_name = "TestSession";
    public $counter = 1;
    public $status = 0;
    public $time_limit = 0;
    public $HTML = "";
    public $Template_id = 0;
    public $time_tamper_prevention = 0;
    public $hash = "";
    public $r_type = "";

    const TEST_SESSION_STATUS_STARTED = 0;
    const TEST_SESSION_STATUS_LOADING = 1;
    const TEST_SESSION_STATUS_TEMPLATE = 2;
    const TEST_SESSION_STATUS_FINISHED = 3;
    const TEST_SESSION_STATUS_STOPPED = 4;
    const TEST_SESSION_STATUS_ERROR = 5;
    const TEST_SESSION_STATUS_TAMPERED = 6;
    const R_TYPE_RSCRIPT = 0;
    const R_TYPE_SOCKET_SERVER = 1;

    public function get_Test() {
        return Test::from_mysql_id($this->Test_id);
    }

    public static function start_new($test_id, $r_type) {
        $session = new TestSession();
        $session->Test_id = $test_id;
        $session->r_type = $r_type;
        $lid = $session->mysql_save();

        $sql = sprintf("UPDATE `%s` SET `session_count`=`session_count`+1 WHERE `%s`.`id`=%d", Test::get_mysql_table(), Test::get_mysql_table(), $test_id);
        mysql_query($sql);

        $session = TestSession::from_mysql_id($lid);
        return $session;
    }

    public function remove() {
        if ($this->r_type == TestSession::R_TYPE_SOCKET_SERVER) {
            if (TestServer::is_running())
                TestServer::send("close:" . $session->id);
        }
        $this->mysql_delete();
    }

    public function resume($values = array()) {
        return $this->run_Test($this->counter, $values);
    }

    public function run_Test($counter = null, $values = array()) {
        $ini_code_required = false;
        if ($counter == null)
            $ini_code_required = true;
        $test = $this->get_Test();
        if ($counter == null) {
            $counter = $test->get_starting_counter();
        }
        $this->counter = $counter;
        $this->mysql_save();

        $code = "";
        $protected_vars = $test->get_TestProtectedVariables();
        foreach ($values as $v) {
            $val = json_decode($v);
            if (!property_exists($val, "name") || trim($val->name) == "" || strpos(trim($val->name), "CONCERTO_") === 0 || in_array(trim($val->name), $protected_vars))
                continue;

            if ($val->value == "NA") {
                $code.=sprintf("
                        %s <- NA
                        ", $val->name);
            } else {
                $code.=sprintf("
                    %s <- '%s'
                    if(suppressWarnings(!is.na(as.numeric(%s)))) %s <- as.numeric(%s)
                    ", $val->name, addslashes($val->value), $val->name, $val->name, $val->name);
            }
        }

        $section = $test->get_TestSection($counter);

        $code.=sprintf("
            CONCERTO_TEST_FLOW<-%d
            while(CONCERTO_TEST_FLOW > 0){
                CONCERTO_TEST_FLOW <- do.call(paste('CONCERTO_Test',CONCERTO_TEST_ID,'Section',CONCERTO_TEST_FLOW,sep=''),list())
            }
            ", $counter, $section->get_RFunctionName());

        return $this->RCall($code, $ini_code_required);
    }

    public function debug_syntax($ts_id, $close = false) {
        $ts = TestSection::from_mysql_id($ts_id);
        $result = $this->RCall($ts->get_RFunction(), false, $close, true);
        return $result;
    }

    public function does_RSession_file_exists() {
        if (file_exists($this->get_RSession_file_path()))
            return true;
        else
            return false;
    }

    public function RCall($code, $include_ini_code = false, $close = false, $debug_syntax = false) {
        $command = "";
        if (!$debug_syntax) {
            if ($include_ini_code)
                $command = $this->get_ini_RCode();
            else
                $command.=$this->get_next_ini_RCode();
        }
        else if ($this->r_type == TestSession::R_TYPE_RSCRIPT) {
            $command.="
            sink(stdout(), type='message')
            ";
        }

        $command.=$code;
        if (!$debug_syntax)
            $command.=$this->get_post_RCode();

        $output = array();
        $return = -999;

        if ($this->r_type == TestSession::R_TYPE_SOCKET_SERVER) {
            $command_obj = json_encode(array(
                "session_id" => $this->id,
                "code" => $command,
                "close" => 0
                    ));

            if (TestServer::$debug)
                TestServer::log_debug("TestSession->RCall --- checking for server");
            if (!TestServer::is_running())
                TestServer::start_process();
            if (TestServer::$debug)
                TestServer::log_debug("TestSession->RCall --- server found, trying to send");
            $response = TestServer::send($command_obj);
            $result = json_decode(trim($response));
            if (TestServer::$debug)
                TestServer::log_debug("TestSession->RCall --- sent and recieved response");

            $output = explode("\n", $result->output);
            $return = $result->return;
        }
        else {
            $this->write_RSource_file($command);

            include Ini::$path_internal . 'SETTINGS.php';
            exec("\"" . Ini::$path_r_script . "\" --vanilla \"" . $this->get_RSource_file_path() . "\" " . $db_host . " " . ($db_port != "" ? $db_port : "3306") . " " . $db_user . " " . $db_password . " " . $db_name . " " . $this->id . " " . (Ini::$path_mysql_home != "" ? "'" . Ini::$path_mysql_home . "'" : ""), $output, $return);
        }

        if (!$debug_syntax) {
            $thisSession = TestSession::from_mysql_id($this->id);

            if ($return != 0) {
                $thisSession->status = TestSession::TEST_SESSION_STATUS_ERROR;
            }

            $removed = false;
            if ($thisSession->status == TestSession::TEST_SESSION_STATUS_FINISHED ||
                    $thisSession->status == TestSession::TEST_SESSION_STATUS_ERROR ||
                    $thisSession->status == TestSession::TEST_SESSION_STATUS_STOPPED ||
                    $thisSession->status == TestSession::TEST_SESSION_STATUS_TAMPERED ||
                    $close) {
                $thisSession->remove();
                $removed = true;
            }
        }

        $test = Test::from_mysql_id($this->Test_id);
        $debug_mode = false;
        $logged_user = User::get_logged_user();
        if ($logged_user != null)
            $debug_mode = $logged_user->is_object_readable($test);

        if (!$debug_syntax) {
            $response = array(
                "data" => array(
                    "HASH" => $thisSession->hash,
                    "TIME_LIMIT" => $thisSession->time_limit,
                    "HTML" => $thisSession->HTML,
                    "TEST_ID" => $thisSession->Test_id,
                    "TEST_SESSION_ID" => $thisSession->id,
                    "STATUS" => $thisSession->status,
                    "TEMPLATE_ID" => $thisSession->Template_id
                )
            );
        }

        if ($debug_mode) {
            $command = htmlspecialchars($command, ENT_QUOTES);
            for ($i = 0; $i < count($output); $i++) {
                $output[$i] = htmlspecialchars($output[$i], ENT_QUOTES);
            }
            $response["debug"] = array(
                "code" => $command,
                "return" => $return,
                "output" => $output
            );
        }

        if (Ini::$timer_tamper_prevention && !$debug_syntax && !$removed) {
            $sql = sprintf("UPDATE `%s` SET `time_tamper_prevention`=%d WHERE `id`=%d", TestSession::get_mysql_table(), time(), $thisSession->id);
            mysql_query($sql);
        }

        return $response;
    }

    public function get_next_ini_RCode() {
        $code = "";
        if ($this->r_type == TestSession::R_TYPE_RSCRIPT) {
            $code = "
            sink(stdout(), type='message')
            library(session)
            restore.session('" . $this->get_RSession_file_path() . "')
                
            CONCERTO_ARGS <- commandArgs(T)
            CONCERTO_DB_HOST <- CONCERTO_ARGS[1]
            CONCERTO_DB_PORT <- as.numeric(CONCERTO_ARGS[2])
            CONCERTO_DB_LOGIN <- CONCERTO_ARGS[3]
            CONCERTO_DB_PASSWORD <- CONCERTO_ARGS[4]
            CONCERTO_DB_NAME <- CONCERTO_ARGS[5]

            CONCERTO_DRIVER <- dbDriver('MySQL')
            for(CONCERTO_DB_CONNECTION in dbListConnections(CONCERTO_DRIVER)) { dbDisconnect(CONCERTO_DB_CONNECTION) }
            CONCERTO_DB_CONNECTION <- dbConnect(CONCERTO_DRIVER, user = CONCERTO_DB_LOGIN, password = CONCERTO_DB_PASSWORD, dbname = CONCERTO_DB_NAME, host = CONCERTO_DB_HOST, port = CONCERTO_DB_PORT)
            
            rm(CONCERTO_DB_HOST)
            rm(CONCERTO_DB_PORT)
            rm(CONCERTO_DB_LOGIN)
            rm(CONCERTO_DB_PASSWORD)
            rm(CONCERTO_ARGS)
            ";
        }
        return $code;
    }

    public function get_post_RCode() {
        $code = "";
        if ($this->r_type == TestSession::R_TYPE_RSCRIPT) {
            $code = "
            save.session('" . $this->get_RSession_file_path() . "')
            ";
        }
        return $code;
    }

    public function write_RSource_file($code) {
        $file = fopen($this->get_RSource_file_path(), 'w');
        fwrite($file, $code);
        fclose($file);
    }

    public function get_RSource_file_path() {
        return Ini::$path_temp . $this->get_Test()->Owner_id . "/session_" . $this->id . ".R";
    }

    public function get_RSession_file_path() {
        return Ini::$path_temp . $this->get_Test()->Owner_id . "/session_" . $this->id . ".Rs";
    }

    public function mysql_delete() {
        if (file_exists($this->get_RSource_file_path()))
            unlink($this->get_RSource_file_path());
        if (file_exists($this->get_RSession_file_path()))
            unlink($this->get_RSession_file_path());
        parent::mysql_delete();
    }

    public function get_ini_RCode() {
        $path = Ini::$path_temp . $this->get_Test()->Owner_id;
        if (!is_dir($path))
            mkdir($path, 0777);
        $code = "";
        if ($this->r_type == TestSession::R_TYPE_RSCRIPT) {
            $code.="
            sink(stdout(), type='message')
            ";
        }
        $code .= "
            options(encoding='UTF-8')
            ";
        if ($this->r_type == TestSession::R_TYPE_RSCRIPT) {
            $code.="
            library(session)
            ";
        }
        $code .= sprintf("
            CONCERTO_TEST_ID <- %d
            CONCERTO_TEST_SESSION_ID <- %d
            ", $this->Test_id, $this->id);
        $code .= "CONCERTO_TEMP_PATH <- '" . $path . "'
            source('" . Ini::$path_internal . "lib/R/mainmethods.R" . "')
            ";
        $code .=$this->get_Test()->get_TestSections_RFunction_declaration();
        return $code;
    }

    public function mysql_save() {
        $new = false;
        if ($this->id == 0)
            $new = true;
        $lid = parent::mysql_save();
        if ($new) {
            $ts = TestSession::from_mysql_id($lid);
            $ts->hash = TestSession::generate_hash($lid);
            $ts->mysql_save();
        }
        return $lid;
    }

    public static function generate_hash($id) {
        return md5("cts" . $id . "." . rand(0, 100) . "." . time());
    }

    public static function authorized_session($id, $hash) {
        return TestSession::from_property(array("id" => $id, "hash" => $hash), false);
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSession`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSession` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Test_id` bigint(20) NOT NULL,
            `counter` int(11) NOT NULL,
            `status` tinyint(4) NOT NULL,
            `time_limit` int(11) NOT NULL,
            `HTML` text NOT NULL,
            `Template_id` bigint(20) NOT NULL,
            `time_tamper_prevention` INT NOT NULL,
            `hash` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

    public static function update_db($previous_version) {
        if (Ini::does_patch_apply("3.4.0", $previous_version)) {
            $sql = "ALTER TABLE `TestSession` ADD `status` tinyint(4) NOT NULL default '0';";
            if (!mysql_query($sql))
                return false;

            $sql = "ALTER TABLE `TestSession` ADD `time_limit` int(11) NOT NULL default '0';";
            if (!mysql_query($sql))
                return false;

            $sql = "ALTER TABLE `TestSession` ADD `HTML` text NOT NULL default '';";
            if (!mysql_query($sql))
                return false;

            $sql = "ALTER TABLE `TestSession` ADD `Template_id` bigint(20) NOT NULL default '0';";
            if (!mysql_query($sql))
                return false;

            $sql = "ALTER TABLE  `TestSession` ADD  `time_tamper_prevention` INT NOT NULL;";
            if (!mysql_query($sql))
                return false;

            $sql = "ALTER TABLE `TestSession` ADD `hash` text NOT NULL default '';";
            if (!mysql_query($sql))
                return false;

            $sql = "ALTER TABLE  `TestSession` ADD  `r_type` TINYINT( 1 ) NOT NULL;";
            if (!mysql_query($sql))
                return false;
        }
        return true;
    }

}

?>