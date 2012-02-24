<?php

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

                $code.=sprintf("
                %s <- '%s'
                ", $val->name, addslashes($val->value));

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

        $result = $this->RCall($code);
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

    public function RCall($code, $debug_syntax = false)
    {
        $command = "";
        if (!$debug_syntax)
        {
            if (!$this->does_RSession_file_exists())
                    $command = $this->get_first_ini_RCode();
            else $command = $this->get_next_ini_RCode();
            $command = $command . $this->get_common_ini_RCode() . $code;

            $command.=$this->get_post_RCode();
        }
        else $command = "sink(stdout(), type='message')\n" . $code;

        $this->write_RSource_file($command);

        $output = array();
        $return = -999;
        include Ini::$path_internal . 'SETTINGS.php';
        exec("\"" . Ini::$path_r_script . "\" --vanilla \"" . $this->get_RSource_file_path() . "\" --args " . $db_host . " " . ($db_port != "" ? substr($db_port, 1) : "3306") . " " . $db_user . " " . $db_password . " " . $db_name . " " . $this->id . " " . (Ini::$path_mysql_home != "" ? "'" . Ini::$path_mysql_home . "'" : ""), $output, $return);
        return array("return" => $return, "output" => $output, "code" => $command);
    }

    public function write_RSource_file($code)
    {
        $file = fopen($this->get_RSource_file_path(), 'w');
        fwrite($file, $code);
        fclose($file);
    }

    public function delete_RSource_file()
    {
        if (file_exists($this->get_RSource_file_path()))
                unlink($this->get_RSource_file_path());
    }

    public function mysql_delete()
    {
        $this->delete_RSource_file();
        $this->delete_object_links(TestSessionVariable::get_mysql_table());
        parent::mysql_delete();
    }

    public function get_RSource_file_path()
    {
        return Ini::$path_temp . "session_" . $this->id . ".R";
    }

    public function get_RSession_file_path()
    {
        return Ini::$path_temp . "session_" . $this->id . ".Rs";
    }

    public function does_RSession_file_exists()
    {
        if (file_exists($this->get_RSession_file_path())) return true;
        else return false;
    }

    public function get_first_ini_RCode()
    {
        $code = "
            sink(stdout(), type='message')
            library(session)
            TEMP_PATH <- '" . Ini::$path_temp . "'
            source('" . Ini::$path_internal . "lib/R/mainmethods.R" . "')
            ";
        $code .=$this->get_Test()->get_TestSections_RFunction_declaration();
        return $code;
    }

    public function get_set_section_index_RCode($counter)
    {
        $code = "
            CURRENT_SECTION_INDEX <<- " . $counter . "
            set.var('CURRENT_SECTION_INDEX'," . $counter . ")
            ";
        return $code;
    }

    public function get_next_ini_RCode()
    {
        $code = "
            sink(stdout(), type='message')
            library(session)
            restore.session('" . $this->get_RSession_file_path() . "')
            ";
        return $code;
    }

    public function get_post_RCode()
    {
        $code = "
            save.session('" . $this->get_RSession_file_path() . "')
            ";
        return $code;
    }

    public function get_common_ini_RCode()
    {
        $code = "
            drv <- dbDriver('MySQL')
            for(con in dbListConnections(drv)) { dbDisconnect(con) }
            con <- dbConnect(drv, user = DB_LOGIN, password = DB_PASSWORD, dbname = DB_NAME, host = DB_HOST, port = DB_PORT)
            ";
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