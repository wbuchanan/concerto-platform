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
        $first_run = false;
        if ($counter == null) $first_run = true;
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

        $result = $this->RCall($code, false, $first_run);
        $values = $this->get_variables();

        $end = false;
        $halt_type = 0;

        foreach ($values as $k => $v)
        {
            if ($k == "CURRENT_SECTION_INDEX" && $v == 0) $end = true;
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

    public function debug_syntax($ts_id)
    {
        $ts = TestSection::from_mysql_id($ts_id);
        $result = $this->RCall($ts->get_RFunction(), true);
        return $result;
    }

    public function RCall($code, $debug_syntax = false, $first_run = false)
    {
        $command = "";
        if (!$debug_syntax && $first_run) $command = $this->get_ini_RCode();
        $command.=$code;

        $command_obj = json_encode(array(
            "session_id"=>$this->id,
            "code"=>$command
        ));
        
        if (!TestServer::is_running()) TestServer::start_process();
        $result = json_decode(TestServer::send($command_obj));

        return array("return" => $result->return, "output" => explode("\n", $result->output), "code" => $result->code);
    }

    public function mysql_delete()
    {
        $this->delete_object_links(TestSessionVariable::get_mysql_table());
        parent::mysql_delete();
    }

    public function get_ini_RCode()
    {
        $code = "
            TEMP_PATH <- '" . Ini::$path_temp . "'
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