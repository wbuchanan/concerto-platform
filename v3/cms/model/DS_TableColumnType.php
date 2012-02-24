<?php

class DS_TableColumnType extends ODataSet
{
    public static $mysql_table_name = "DS_TableColumnType";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(16);
            case 2: return Language::string(17);
            case 3: return Language::string(18);
        }
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `DS_TableColumnType`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_TableColumnType` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `DS_TableColumnType` (`id`, `name`, `value`, `position`) VALUES
            (1, 'string', 'string', 1),
            (2, 'numeric', 'numeric', 2),
            (3, 'HTML', 'HTML', 3);
            ";
        return mysql_query($sql);
    }

}

?>