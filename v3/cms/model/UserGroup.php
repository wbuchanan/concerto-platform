<?php

class UserGroup extends OModule
{
    public $name = "";
    public static $mysql_table_name = "UserGroup";

    public function __construct($params = array())
    {
        $this->name = Language::string(79);
        parent::__construct($params);
    }

    public function mysql_delete()
    {
        $this->clear_object_links(User::get_mysql_table());
        $this->mysql_delete_object();
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `UserGroup`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `UserGroup` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>