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

class TestSession extends OTable
{
    public $Test_id = 0;
    public static $mysql_table_name = "TestSession";

    public function get_Test()
    {
        return Test::from_mysql_id($this->Test_id);
    }

    public static function start_new($test_id)
    {
        $session = new TestSession();
        $session->Test_id = $test_id;
        $lid = $session->mysql_save();

        $session = TestSession::from_mysql_id($lid);
        $session->replace_TestSessionVariable("TEST_SESSION_ID", $session->id);
        $session->replace_TestSessionVariable("TEST_ID", $test_id);
        return $session;
    }

    public function resume($values = array())
    {
        $vals = $this->get_variables();
        $counter = $vals["CURRENT_SECTION_INDEX"];
        return $this->run_Test($counter, $values);
    }

    public function run_Test($counter = null, $values = array())
    {
        $ini_code_required = false;
        if ($counter == null) $ini_code_required = true;
        $test = $this->get_Test();
        if ($counter == null)
        {
            $counter = $test->get_starting_counter();
        }

        $this->replace_TestSessionVariable("CURRENT_SECTION_INDEX", $counter);

        $code = "
            TEST_ID <<- get.var('TEST_ID')
            TEST_SESSION_ID <<- get.var('TEST_SESSION_ID')
            ";
        foreach ($values as $v)
        {
            $val = json_decode($v);
            if ($val->visibility == 1 || $val->visibility == 2)
                    $this->replace_TestSessionVariable(mysql_real_escape_string($val->name), mysql_real_escape_string($val->value));

            if ($val->visibility == 0 || $val->visibility == 2)
            {
                if ($val->type != 3)
                {
                    $code.=sprintf("
                    %s <- '%s'
                    ", $val->name, addslashes($val->value));
                }
                if ($val->type == 3)
                {
                    $code.=sprintf("
                        %s <- NA
                        ", $val->name);
                }

                if ($val->type == 2)
                {
                    $code.=sprintf("
                        %s <- as.numeric(%s)
                        ", $val->name, $val->name);
                }
            }
        }

        $section = $test->get_TestSection($counter);

        $code.=sprintf("
            CONCERTO_TEST_FLOW<-%d
            while(CONCERTO_TEST_FLOW > 0){
                CONCERTO_TEST_FLOW <- do.call(paste('Test',TEST_ID,'Section',CONCERTO_TEST_FLOW,sep=''),list())
            }
            ", $counter, $section->get_RFunctionName());

        $result = $this->RCall($code, $ini_code_required);
        $values = $this->get_variables();

        $end = false;
        $halt_type = 0;

        foreach ($values as $k => $v)
        {
            if ($k == "CURRENT_SECTION_INDEX" && $v == 0)
            {
                $end = true;
                if (TestServer::is_running())
                        TestServer::send("close:" . $this->id);
            }
        }

        if (!$end)
        {
            foreach ($values as $k => $v)
            {
                if ($k == "HALT_TYPE") $halt_type = $v;
            }
        }

        return array(
            "result" => $result,
            "values" => $this->get_variables(),
            "control" => array("end" => $end, "halt_type" => $halt_type)
        );
    }

    public function debug_syntax($ts_id, $close = false)
    {
        $ts = TestSection::from_mysql_id($ts_id);
        $result = $this->RCall($ts->get_RFunction(), false, $close, true);
        return $result;
    }

    public function does_RSession_file_exists()
    {
        if (file_exists($this->get_RSession_file_path())) return true;
        else return false;
    }

    public function RCall($code, $include_ini_code = false, $close = false, $debug_syntax = false)
    {
        $command = "";
        if (!$debug_syntax)
        {
            if ($include_ini_code) $command = $this->get_ini_RCode();
            else $command.=$this->get_next_ini_RCode();
        }
        else if (!Ini::$r_instances_persistant)
        {
            $command.="
            sink(stdout(), type='message')
            ";
        }

        $command.=$code;
        if (!$debug_syntax) $command.=$this->get_post_RCode();

        if (Ini::$r_instances_persistant)
        {
            $command_obj = json_encode(array(
                "session_id" => $this->id,
                "code" => $command,
                "close" => $close ? 1 : 0
                    ));

            if (TestServer::$debug)
                    TestServer::log_debug("TestSession->RCall --- checking for server");
            if (!TestServer::is_running()) TestServer::start_process();
            if (TestServer::$debug)
                    TestServer::log_debug("TestSession->RCall --- server found, trying to send");
            $response = TestServer::send($command_obj);
            $result = json_decode(trim($response));
            if (TestServer::$debug)
                    TestServer::log_debug("TestSession->RCall --- sent and recieved response");
            return array("return" => $result->return, "output" => explode("\n", $result->output), "code" => $result->code);
        }
        else
        {
            $this->write_RSource_file($command);

            $output = array();
            $return = -999;
            include Ini::$path_internal . 'SETTINGS.php';
            exec("\"" . Ini::$path_r_script . "\" --vanilla \"" . $this->get_RSource_file_path() . "\" " . $db_host . " " . ($db_port != "" ? $db_port : "3306") . " " . $db_user . " " . $db_password . " " . $db_name . " " . $this->id . " " . (Ini::$path_mysql_home != "" ? "'" . Ini::$path_mysql_home . "'" : ""), $output, $return);
            return array("return" => $return, "output" => $output, "code" => $command);
        }
    }

    public function get_next_ini_RCode()
    {
        $code = "";
        if (!Ini::$r_instances_persistant)
        {
            $code = "
            sink(stdout(), type='message')
            library(session)
            restore.session('" . $this->get_RSession_file_path() . "')
            drv <- dbDriver('MySQL')
            for(con in dbListConnections(drv)) { dbDisconnect(con) }
            con <- dbConnect(drv, user = DB_LOGIN, password = DB_PASSWORD, dbname = DB_NAME, host = DB_HOST, port = DB_PORT)
            ";
        }
        return $code;
    }

    public function get_post_RCode()
    {
        $code = "";
        if (!Ini::$r_instances_persistant)
        {
            $code = "
            save.session('" . $this->get_RSession_file_path() . "')
            ";
        }
        return $code;
    }

    public function write_RSource_file($code)
    {
        $file = fopen($this->get_RSource_file_path(), 'w');
        fwrite($file, $code);
        fclose($file);
    }

    public function get_RSource_file_path()
    {
        return Ini::$path_temp . "session_" . $this->id . ".R";
    }

    public function get_RSession_file_path()
    {
        return Ini::$path_temp . "session_" . $this->id . ".Rs";
    }

    public function mysql_delete()
    {
        if (file_exists($this->get_RSource_file_path()))
                unlink($this->get_RSource_file_path());
        if (file_exists($this->get_RSession_file_path()))
                unlink($this->get_RSession_file_path());

        $this->delete_object_links(TestSessionVariable::get_mysql_table());
        parent::mysql_delete();
    }

    public function get_ini_RCode()
    {
        $path = Ini::$path_temp . $this->get_Test()->Owner_id;
        if (!is_dir($path)) mkdir($path, 0777);
        $code = "";
        if (!Ini::$r_instances_persistant)
        {
            $code.="
            sink(stdout(), type='message')
            ";
        }
        $code .= "
            options(encoding='UTF-8')
            ";
        if (!Ini::$r_instances_persistant)
        {
            $code.="
            library(session)
            ";
        }
        $code .= "TEMP_PATH <- '" . $path . "'
            source('" . Ini::$path_internal . "lib/R/mainmethods.R" . "')
            ";
        $code .=$this->get_Test()->get_TestSections_RFunction_declaration();
        return $code;
    }

    public function replace_TestSessionVariable($name, $value)
    {
        $sql = sprintf("REPLACE INTO `%s` SET `name`='%s',`value`='%s',`TestSession_id`=%d", TestSessionVariable::get_mysql_table(), $name, $value, $this->id);
        mysql_query($sql);
    }

    public function get_variables()
    {
        $v = array();
        $vars = TestSessionVariable::from_property(array("TestSession_id" => $this->id));
        foreach ($vars as $var)
        {
            $v[$var->name] = $var->value;
        }
        return $v;
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
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
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>