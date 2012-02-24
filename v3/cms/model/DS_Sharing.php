<?php

class DS_Sharing extends ODataSet
{
    public static $mysql_table_name = "DS_Sharing";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(100);
            case 2: return Language::string(101);
            case 3: return Language::string(102);
        }
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `DS_Sharing`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_Sharing` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `DS_Sharing` (`id`, `name`, `value`, `position`) VALUES
            (1, 'private', '1', 1),
            (2, 'group', '2', 2),
            (3, 'public', '3', 3);
            ";
        return mysql_query($sql);
    }

}

?>