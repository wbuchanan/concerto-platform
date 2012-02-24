<?php

class DS_Right extends ODataSet
{
    public static $mysql_table_name = "DS_Right";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(73);
            case 2: return Language::string(100);
            case 3: return Language::string(168);
            case 4: return Language::string(101);
            case 5: return Language::string(169);
        }
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `DS_Right`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_Right` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `DS_Right` (`id`, `name`, `value`, `position`) VALUES
            (1, 'none', '1', 1),
            (2, 'private', '2', 2),
            (3, 'standard', '3', 3),
            (4, 'group', '4', 4),
            (5, 'all', '5', 5);
            ";
        return mysql_query($sql);
    }

}

?>