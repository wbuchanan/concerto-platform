<?php

class TestSessionVariable extends OTable
{
    public $name = "";
    public $value = "";
    public $TestSession_id = 0;
    public static $mysql_table_name = "TestSessionVariable";

    public function get_TestSession()
    {
        return TestSession::from_mysql_id($this->TestSession_id);
    }
}

?>