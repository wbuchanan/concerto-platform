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

class DS_TableColumnType extends ODataSet
{
    public static $mysql_table_name = "DS_TableColumnType";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(16);
            case 2: return Language::string(354);
            case 3: return Language::string(355);
            case 4: return Language::string(18);
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
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;
            ";
        if (!mysql_query($sql)) return false;

        $sql = "
            INSERT INTO `DS_TableColumnType` (`id`, `name`, `value`, `position`) VALUES
            (1, 'string', 'string', 1),
            (2, 'integer', 'integer', 2),
            (3, 'float', 'float', 3),
            (4, 'HTML', 'HTML', 4);
            ";
        return mysql_query($sql);
    }

    public static function update_db($previous_version)
    {
        if (Ini::does_patch_apply("3.3.0", $previous_version))
        {
            $id_4_found = false;
            $sql = sprintf("SELECT * FROM `%s`", self::get_mysql_table());
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z))
            {
                switch ($r['id'])
                {
                    case 2:
                        {
                            if ($r['name'] != "integer")
                            {
                                $sql2 = sprintf("UPDATE `%s` SET `name`='%s', value='%s' WHERE `id`='%d'", self::get_mysql_table(), "integer", "integer", 2);
                                if(!mysql_query($sql2)) return false;
                            }
                            break;
                        }
                    case 3:
                        {
                            if ($r['name'] != "float")
                            {
                                $sql2 = sprintf("UPDATE `%s` SET `name`='%s', value='%s' WHERE `id`='%d'", self::get_mysql_table(), "float", "float", 3);
                                if(!mysql_query($sql2)) return false;
                            }
                            break;
                        }
                    case 4:
                        {
                            $id_4_found = true;
                            if ($r['name'] != "HTML")
                            {
                                $sql2 = sprintf("UPDATE `%s` SET `name`='%s', value='%s' WHERE `id`='%d'", self::get_mysql_table(), "HTML", "HTML", 4);
                                if(!mysql_query($sql2)) return false;
                            }
                            break;
                        }
                }
            }
            if (!$id_4_found)
            {
                $sql2 = sprintf("INSERT INTO `%s` SET `id`=4, `name`='%s', value='%s', `position`=4", self::get_mysql_table(), "HTML", "HTML");
                if(!mysql_query($sql2)) return false;
            }
        }
        return true;
    }

}

?>