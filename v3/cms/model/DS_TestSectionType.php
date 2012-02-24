<?php

class DS_TestSectionType extends ODataSet
{
    public static $mysql_table_name = "DS_TestSectionType";
    const R_CODE = 1;
    const LOAD_HTML_TEMPLATE = 2;
    const GO_TO = 3;
    const IF_STATEMENT = 4;
    const SET_VARIABLE = 5;
    const START = 6;
    const END = 7;
    const TABLE_MOD = 8;
    const CUSTOM = 9;

    public static function get_all_selectable()
    {
        $result = array();
        $sql = sprintf("SELECT * FROM `%s` WHERE 
            `id`!=%d AND `id`!=%d AND `id`!='%d'
            ORDER BY `name` ASC", self::get_mysql_table(), self::START, self::END, self::CUSTOM);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z))
        {
            array_push($result, DS_TestSectionType::from_mysql_result($r));
        }
        return $result;
    }

    public function get_name()
    {
        switch ($this->id)
        {
            case self::CUSTOM: return Language::string(57);
            case self::END: return Language::string(55);
            case self::GO_TO: return Language::string(51);
            case self::IF_STATEMENT: return Language::string(52);
            case self::LOAD_HTML_TEMPLATE: return Language::string(50);
            case self::R_CODE: return Language::string(49);
            case self::SET_VARIABLE: return Language::string(53);
            case self::START: return Language::string(54);
            case self::TABLE_MOD: return Language::string(56);
        }
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `DS_TestSectionType`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_TestSectionType` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `DS_TestSectionType` (`id`, `name`, `value`, `position`) VALUES
            (1, 'R code', '1', 1),
            (2, 'load HTML template', '2', 2),
            (3, 'go to', '3', 3),
            (4, 'IF statement', '4', 4),
            (5, 'set variable', '5', 5),
            (6, 'start', '6', 6),
            (7, 'end', '7', 7),
            (8, 'table modification', '8', 8),
            (9, 'custom section', '9', 9);
            ";
        return mysql_query($sql);
    }

}

?>