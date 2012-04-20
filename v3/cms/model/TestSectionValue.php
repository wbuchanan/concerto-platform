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

class TestSectionValue extends OTable
{
    public $TestSection_id = 0;
    public $index = 0;
    public $value = "";
    public static $mysql_table_name = "TestSectionValue";

    public function get_TestSection()
    {
        return TestSection::from_mysql_id($this->TestSection_id);
    }

    public function to_XML()
    {
        $xml = new DOMDocument('1.0', "UTF-8");

        $tsv = $xml->createElement("TestSectionValue");
        $xml->appendChild($tsv);

        $index = $xml->createElement("index", $this->index);
        $tsv->appendChild($index);

        $value = $xml->createElement("value", htmlspecialchars($this->value, ENT_QUOTES, "UTF-8"));
        $tsv->appendChild($value);

        return $tsv;
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSectionValue`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSectionValue` (
            `id` bigint(20) NOT NULL auto_increment,
            `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `updated` timestamp NOT NULL default '0000-00-00 00:00:00',
            `TestSection_id` bigint(20) NOT NULL,
            `index` int(11) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

    public static function update_db($previous_version)
    {
        if (Ini::does_patch_apply("3.4.0", $previous_version))
        {
            $sql = sprintf("SELECT `id` FROM `%s` WHERE `TestSectionType_id`=%d", TestSection::get_mysql_table(), DS_TestSectionType::LOAD_HTML_TEMPLATE);
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z))
            {
                $params_count = 0;
                $returns_count = 0;
                $sql2 = sprintf("SELECT `index`,`value` FROM `%s` WHERE `TestSection_id`=%d AND (`index`=1 OR `index`=2) ", TestSectionValue::get_mysql_table(), $r[0]);
                $z2 = mysql_query($sql2);
                while ($r2 = mysql_fetch_array($z2))
                {
                    if ($r2['index'] == 1) $params_count = $r2['value'];
                    if ($r2['index'] == 2) $returns_count = $r2['value'];
                }

                $delete_index = 3 + $params_count + 1;

                for ($i = 0; $i < $returns_count; $i++)
                {
                    $sql2 = sprintf("DELETE FROM `%s` WHERE `TestSection_id`=%d AND `index` IN (%d,%d)", TestSectionValue::get_mysql_table(), $r[0], $delete_index, $delete_index + 1);
                    if (!mysql_query($sql2)) return false;

                    $sql2 = sprintf("UPDATE `%s` SET `index`=`index`-2 WHERE `TestSection_id`=%d AND `index`>%d", TestSectionValue::get_mysql_table(), $r[0], $delete_index);
                    if (!mysql_query($sql2)) return false;

                    $delete_index++;
                }
            }

            $sql = sprintf("SELECT `id` FROM `%s` WHERE `TestSectionType_id`=%d", TestSection::get_mysql_table(), DS_TestSectionType::SET_VARIABLE);
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z))
            {
                $sql2 = sprintf("DELETE FROM `%s` WHERE `TestSection_id`=%d AND `index` IN (4,5)", TestSectionValue::get_mysql_table(), $r[0]);
                if (!mysql_query($sql2)) return false;

                $sql2 = sprintf("UPDATE `%s` SET `index`=`index`-2 WHERE `TestSection_id`=%d AND `index`>%d", TestSectionValue::get_mysql_table(), $r[0], 5);
                if (!mysql_query($sql2)) return false;
            }
            
            $sql = sprintf("SELECT `id` FROM `%s` WHERE `TestSectionType_id`=%d", TestSection::get_mysql_table(), DS_TestSectionType::CUSTOM);
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z))
            {
                $params_count = 0;
                $returns_count = 0;
                $csid = 0;
                
                $sql2 = sprintf("SELECT `value` FROM `%s` WHERE `TestSection_id`=%d AND `index`=0 ", TestSectionValue::get_mysql_table(), $r[0]);
                $z2 = mysql_query($sql2);
                $r2 = mysql_fetch_array($z2);
                $csid = $r2['value'];
                
                $sql2 = sprintf("SELECT * FROM `%s` WHERE `CustomSection_id`=%d AND `type`=0", CustomSectionVariable::get_mysql_table(),$csid);
                $params_count = mysql_num_rows(mysql_query($sql2));
                
                $sql2 = sprintf("SELECT * FROM `%s` WHERE `CustomSection_id`=%d AND `type`=1", CustomSectionVariable::get_mysql_table(),$csid);
                $returns_count = mysql_num_rows(mysql_query($sql2));

                $delete_index = 1 + $params_count + 1;

                for ($i = 0; $i < $returns_count; $i++)
                {
                    $sql2 = sprintf("DELETE FROM `%s` WHERE `TestSection_id`=%d AND `index` IN (%d,%d)", TestSectionValue::get_mysql_table(), $r[0], $delete_index, $delete_index + 1);
                    if (!mysql_query($sql2)) return false;

                    $sql2 = sprintf("UPDATE `%s` SET `index`=`index`-2 WHERE `TestSection_id`=%d AND `index`>%d", TestSectionValue::get_mysql_table(), $r[0], $delete_index);
                    if (!mysql_query($sql2)) return false;

                    $delete_index++;
                }
            }
        }
        return true;
    }

}

?>