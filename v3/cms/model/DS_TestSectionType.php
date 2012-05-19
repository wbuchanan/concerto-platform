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
    
    public static function get_name_by_id($id)
    {
        switch ($id)
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

    public function get_name()
    {
        return self::get_name_by_id($this->id);
    }

    public static function get_description_by_id($id)
    {
        switch ($id)
        {
            case self::CUSTOM: return Language::string(48);
            case self::END: return Language::string(46);
            case self::GO_TO: return Language::string(42);
            case self::IF_STATEMENT: return Language::string(43);
            case self::LOAD_HTML_TEMPLATE: return Language::string(41);
            case self::R_CODE: return Language::string(40);
            case self::SET_VARIABLE: return Language::string(44);
            case self::START: return Language::string(45);
            case self::TABLE_MOD: return Language::string(47);
        }
    }

    public function get_description()
    {
        return self::get_description_by_id($this->id);
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