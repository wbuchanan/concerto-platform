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

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSessionVariable`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSessionVariable` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `TestSession_id` bigint(20) NOT NULL,
            `name` varchar(40) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY `TestSession_id` (`TestSession_id`,`name`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>