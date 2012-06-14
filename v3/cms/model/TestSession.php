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
    public $Template_TestSection_id = 0;
    public $debug = 0;
    public $release = 0;
    public $serialized = 0;

    const TEST_SESSION_STATUS_CREATED = 0;
    const TEST_SESSION_STATUS_WORKING = 1;
    const TEST_SESSION_STATUS_TEMPLATE = 2;
    const TEST_SESSION_STATUS_COMPLETED = 3;
    const TEST_SESSION_STATUS_ERROR = 4;
    const TEST_SESSION_STATUS_TAMPERED = 5;
    const R_TYPE_RSCRIPT = 0;
    const R_TYPE_SOCKET_SERVER = 1;

    public function get_Test() {
        return Test::from_mysql_id($this->Test_id);
    }

    public function register() {
        if (array_key_exists("sids", $_SESSION)) {
            if (array_key_exists(session_id(), $_SESSION['sids'])) {
                TestSession::unregister($_SESSION['sids'][session_id()]);
                $_SESSION['sids'][session_id()] = $this->id;
            }
            else
                $_SESSION['sids'][session_id()] = $this->id;
        }
        else {
            $_SESSION['sids'] = array();
            $_SESSION['sids'][session_id()] = $this->id;
        }
    }

    public static function unregister($id) {
        $obj = TestSession::from_mysql_id($id);
        if ($obj != null)
            $obj->remove();
        unset($_SESSION['sids'][session_id()]);
    }

    public static function start_new($test_id, $r_type, $debug = false) {
        $session = new TestSession();
        $session->Test_id = $test_id;
        $session->r_type = $r_type;
        $session->debug = ($debug ? 1 : 0);
        $lid = $session->mysql_save();

        $sql = sprintf("UPDATE `%s` SET `session_count`=`session_count`+1 WHERE `%s`.`id`=%d", Test::get_mysql_table(), Test::get_mysql_table(), $test_id);
        mysql_query($sql);

        $session = TestSession::from_mysql_id($lid);
        if ($debug)
            $session->register();
        return $session;
    }

    public function remove($sockets = true) {
        $this->close($sockets);
        $this->mysql_delete();
    }

    public function mysql_delete() {
        parent::mysql_delete();
        $this->remove_returns();
    }

    public function remove_returns() {
        $sql = sprintf("DELETE FROM `%s` WHERE `TestSession_id`=%d", TestSessionReturn::get_mysql_table(), $this->id);
        mysql_query($sql);
    }

    public function close($sockets = true) {
        if ($this->r_type == TestSession::R_TYPE_SOCKET_SERVER) {
            if ($sockets && TestServer::is_running())
                TestServer::send("close:" . $this->id);
        }
        $this->remove_files();
    }

    public function serialize() {
        if ($this->r_type == TestSession::R_TYPE_SOCKET_SERVER) {
            if (TestServer::is_running())
                TestServer::send("serialize:" . $this->id);
        }
    }

    public function remove_files() {
        if (file_exists($this->get_RSource_file_path()))
            unlink($this->get_RSource_file_path());
        if (file_exists($this->get_RSession_file_path()))
            unlink($this->get_RSession_file_path());
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
        $this->status = TestSession::TEST_SESSION_STATUS_WORKING;
        $this->mysql_save();

        $code = "";
        $protected_vars = $test->get_TestProtectedVariables_name();
        foreach ($values as $v) {
            $val = json_decode($v);
            if (!property_exists($val, "name") || trim($val->name) == "" || strpos(trim($val->name), "CONCERTO_") === 0 || in_array(trim($val->name), $protected_vars))
                continue;

            if ($val->value === "NA") {
                $code.=sprintf("
                        %s <- NA
                        ", $val->name);
            } else {
                $code.=sprintf("
                    %s <- '%s'
                    if(!is.null(%s) && !is.na(%s) && is.character(%s) && suppressWarnings(!is.na(as.numeric(%s)))) %s <<- as.numeric(%s)
                    ", $val->name, addslashes($val->value), $val->name, $val->name, $val->name, $val->name, $val->name, $val->name);
            }
        }

        $section = $test->get_TestSection($counter);

        $code.=sprintf("
            CONCERTO_TEST_FLOW<-%d
            evalWithTimeout({
            while(CONCERTO_TEST_FLOW > 0){
                CONCERTO_TEST_FLOW <- do.call(paste('CONCERTO_Test',CONCERTO_TEST_ID,'Section',CONCERTO_TEST_FLOW,sep=''),list())
            }
            CONCERTO_FLOW_LOOP_FINISHED <- TRUE
            },timeout=%s,onTimeout='error')
            if(CONCERTO_TEST_FLOW==-2) update.session.release(1)
            ", $counter, Ini::$r_max_execution_time);

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
            exec("LANG=\"en_US.UTF8\" \"" . Ini::$path_r_script . "\" --vanilla \"" . $this->get_RSource_file_path() . "\" " . $db_host . " " . ($db_port != "" ? $db_port : "3306") . " " . $db_user . " " . $db_password . " " . $db_name . " " . $this->id . " " . (Ini::$path_mysql_home != "" ? "'" . Ini::$path_mysql_home . "'" : ""), $output, $return);
        }

        $thisSession = null;
        $status = TestSession::TEST_SESSION_STATUS_ERROR;
        $removed = false;
        $release = 0;
        $html = "";
        $head = "";
        $Template_id = 0;
        $debug = 0;
        $hash = "";
        $time_limit = 0;
        $Test_id = 0;
        if (!$debug_syntax) {
            $thisSession = TestSession::from_mysql_id($this->id);
            if ($thisSession != null) {
                $status = $thisSession->status;
                $release = $thisSession->release;
                $html = $thisSession->HTML;
                $Template_id = $thisSession->Template_id;
                $debug = $thisSession->debug;
                $hash = $thisSession->hash;
                $time_limit = $thisSession->time_limit;
                $Test_id = $thisSession->Test_id;
                if ($return != 0) {
                    $status = TestSession::TEST_SESSION_STATUS_ERROR;
                }

                if ($status == TestSession::TEST_SESSION_STATUS_WORKING && $release == 1 || $close)
                    $status = TestSession::TEST_SESSION_STATUS_COMPLETED;

                $thisSession->status = $status;
                $thisSession->mysql_save();

                switch ($status) {
                    case TestSession::TEST_SESSION_STATUS_COMPLETED: {
                            if ($debug) {
                                TestSession::unregister($thisSession->id);
                                $removed = true;
                            }
                            else
                                $thisSession->serialize();
                            break;
                        }
                    case TestSession::TEST_SESSION_STATUS_ERROR:
                    case TestSession::TEST_SESSION_STATUS_TAMPERED: {
                            if ($debug) {
                                TestSession::unregister($thisSession->id);
                                $removed = true;
                            }
                            else
                                $thisSession->close();
                            break;
                        }
                    case TestSession::TEST_SESSION_STATUS_TEMPLATE: {
                            if ($debug) {
                                $html = Template::strip_html($html);
                                if ($release)
                                    TestSession::unregister($thisSession->id);
                            }
                            else {
                                $head = Template::from_mysql_id($Template_id)->head;
                                if ($release)
                                    $thisSession->serialize();
                            }
                            break;
                        }
                }
            }
            else
                $removed = true;
        }

        $test = Test::from_mysql_id($this->Test_id);
        $debug_data = false;
        $logged_user = User::get_logged_user();
        if ($logged_user != null)
            $debug_data = $logged_user->is_object_readable($test);

        if (!$debug_syntax) {
            $response = array(
                "data" => array(
                    "HEAD" => $head,
                    "HASH" => $hash,
                    "TIME_LIMIT" => $time_limit,
                    "HTML" => $html,
                    "TEST_ID" => $Test_id,
                    "TEST_SESSION_ID" => $this->id,
                    "STATUS" => $status,
                    "TEMPLATE_ID" => $Template_id
                )
            );
        }

        if ($debug_data) {
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
            $sql = sprintf("UPDATE `%s` SET `time_tamper_prevention`=%d WHERE `id`=%d", TestSession::get_mysql_table(), time(), $this->id);
            mysql_query($sql);
        }

        return $response;
    }

    public function get_next_ini_RCode() {
        $code = "";
        if ($this->r_type == TestSession::R_TYPE_RSCRIPT) {
            $code = "
            sink(stdout(), type='message')
            options(encoding='UTF-8')
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
            dbSendQuery(CONCERTO_DB_CONNECTION,statement = \"SET NAMES 'utf8';\")
            
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

        $test = $this->get_Test();
        $returns = $test->get_return_TestVariables();

        foreach ($returns as $ret) {
            $code.=sprintf("update.session.return('%s')
                ", $ret->name);
        }

        if ($this->r_type == TestSession::R_TYPE_RSCRIPT) {
            $code .= "
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
        $code .= sprintf("
            options(encoding='UTF-8')
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
        $session = TestSession::from_property(array("id" => $id, "hash" => $hash), false);
        if ($session == null)
            return null;
        switch ($session->status) {
            case TestSession::TEST_SESSION_STATUS_ERROR: return null;
            case TestSession::TEST_SESSION_STATUS_TAMPERED: return null;
            case TestSession::TEST_SESSION_STATUS_COMPLETED: return null;
        }
        return $session;
    }

    public static function forward($tid, $sid, $hash, $values, $btn_name, $debug, $time) {
        $session = null;
        $result = array();
        if ($sid != null && $hash != null) {
            $session = TestSession::authorized_session($sid, $hash);

            if ($session != null) {
                if ($values == null)
                    $values = array();

                if ($btn_name!=null) {
                    array_push($values, json_encode(array(
                                "name" => "LAST_PRESSED_BUTTON_NAME",
                                "value" => $btn_name
                            )));
                }

                if (Ini::$timer_tamper_prevention && $session->time_limit > 0 && $time - $session->time_tamper_prevention - Ini::$timer_tamper_prevention_tolerance > $session->time_limit) {
                    if ($session->debug == 1)
                        TestSession::unregister($session->id);
                    else
                        $session->close();

                    $result = array(
                        "data" => array(
                            "HASH" => "",
                            "TIME_LIMIT" => 0,
                            "HTML" => "",
                            "TEST_ID" => 0,
                            "TEST_SESSION_ID" => 0,
                            "STATUS" => TestSession::TEST_SESSION_STATUS_TAMPERED,
                            "TEMPLATE_ID" => 0
                        )
                    );
                    if ($session->debug == 1) {
                        $result["debug"] = array(
                            "code" => 0,
                            "return" => "",
                            "output" => ""
                        );
                    }
                }
                else
                    $result = $session->resume($values);
            }
            else {
                $result = array(
                    "data" => array(
                        "HASH" => "",
                        "TIME_LIMIT" => 0,
                        "HTML" => "",
                        "TEST_ID" => 0,
                        "TEST_SESSION_ID" => 0,
                        "STATUS" => TestSession::TEST_SESSION_STATUS_TAMPERED,
                        "TEMPLATE_ID" => 0
                    ),
                    "debug" => array(
                        "code" => 0,
                        "return" => "",
                        "output" => ""
                    )
                );
            }
        } else {
            if ($tid!=null) {
                $r_type = Ini::$r_instances_persistant ? TestSession::R_TYPE_SOCKET_SERVER : TestSession::R_TYPE_RSCRIPT;
                if ($debug == 1) $debug = true;
                else $debug = false;
                $session = TestSession::start_new($tid, $r_type, $debug);

                if ($values==null)
                    $values = array();

                $test = $session->get_Test();
                if ($test != null) {
                    $values = $test->verified_input_values($values);
                }

                $result = $session->run_test(null, $values);
            }
        }
        return $result;
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
            `r_type` tinyint( 1 ) NOT NULL,
            `Template_TestSection_id` bigint(20) NOT NULL,
            `debug` tinyint(1) NOT NULL,
            `release` tinyint(1) NOT NULL,
            `serialized` tinyint(1) NOT NULL,
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
        if (Ini::does_patch_apply("3.4.1", $previous_version)) {
            $sql = "ALTER TABLE `TestSession` ADD `Template_TestSection_id` bigint(20) NOT NULL default '0';";
            if (!mysql_query($sql))
                return false;
        }

        if (Ini::does_patch_apply("3.4.3", $previous_version)) {
            $sql = "ALTER TABLE `TestSession` ADD `debug` tinyint(1) NOT NULL default '0';";
            if (!mysql_query($sql))
                return false;

            $sql = "ALTER TABLE `TestSession` ADD `release` tinyint(1) NOT NULL default '0';";
            if (!mysql_query($sql))
                return false;
        }

        if (Ini::does_patch_apply("3.5.2", $previous_version)) {
            $sql = "SHOW COLUMNS FROM `TestSession` WHERE `Field`='r_typ'";
            $z = mysql_query($sql);
            if (mysql_num_rows($z) > 0) {
                $sql = "ALTER TABLE `TestSession` CHANGE `r_typ` `r_type` tinyint(1) NOT NULL default '0';";
                if (!mysql_query($sql))
                    return false;
            }
        }
        if (Ini::does_patch_apply("3.6.0", $previous_version)) {
            $sql = "SHOW COLUMNS FROM `TestSession` WHERE `Field`='serialized'";
            $z = mysql_query($sql);
            if (mysql_num_rows($z) == 0) {
                $sql = "ALTER TABLE `TestSession` ADD `serialized` tinyint(1) NOT NULL default '0';";
                if (!mysql_query($sql))
                    return false;
            }
        }
        return true;
    }

}

?>