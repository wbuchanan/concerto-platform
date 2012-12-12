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

class Table extends OModule {

    public $name = "";
    public $description = "";
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "Table";

    public function __construct($params = array()) {
        $this->name = Language::string(74);
        parent::__construct($params);
    }

    public function mysql_delete() {
        $this->mysql_delete_Table();

        parent::mysql_delete();
    }

    private static $auto_increment_row_comparer_field = "";

    private static function auto_increment_row_comparer($a, $b) {
        $a = json_decode($a);
        $b = json_decode($b);

        $field = self::$auto_increment_row_comparer_field;
        if ($a->$field >= $b->$field && ($a->$field == null || $a->$field == ""))
            return 1;
        else
            return -1;
    }

    public function is_duplicate_table_name($name) {
        if ($this->id == 0 || $this->name != $name) {
            $sql = sprintf("SHOW TABLES LIKE '%s'", $name);
            $z = mysql_query($sql);
            if (mysql_num_rows($z) > 0)
                return true;
        }
        return false;
    }

    public static function create_new_mysql_table($name) {
        $sql = sprintf("CREATE TABLE  `%s` (
            `id` bigint(20) NOT NULL auto_increment,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", mysql_real_escape_string($name));
        return mysql_query($sql);
    }

    public function rename_mysql_table($name) {
        $sql = sprintf("RENAME TABLE  `%s` TO  `%s` ;", mysql_real_escape_string($this->name), mysql_real_escape_string($name));
        return mysql_query($sql);
    }

    public function get_columns() {
        $result = array();
        $sql = sprintf("SHOW COLUMNS IN `%s`", $this->name);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            array_push($result, TableColumn::from_mysql_result($r));
        }
        return $result;
    }

    public function get_indexes() {
        return TableIndex::from_mysql_table($this->name);
    }

    public function mysql_save_from_post($post) {
        $is_new = $this->id == 0;

        if ($is_new) {
            if (!Table::create_new_mysql_table($post['name']))
                return json_encode(array("result" => -6, "message" => mysql_error()));
        } else {
            if ($this->name != $post['name']) {
                if (!$this->rename_mysql_table($post['name']))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        $lid = parent::mysql_save_from_post($post);
        $obj = static::from_mysql_id($lid);

        if (array_key_exists("deleteData", $post)) {
            if ($post["deleteData"] == "*") {
                $sql = sprintf("DELETE * FROM `%s`", mysql_real_escape_string($obj->name));
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            } else {
                $rows = json_decode($post["deleteData"]);
                foreach ($rows as $row) {
                    $sql = sprintf("DELETE * FROM `%s` WHERE id='%s'", mysql_real_escape_string($obj->name), mysql_real_escape_string($row->id));
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                }
            }
        }

        if (array_key_exists("deleteIndexes", $post)) {
            $indexes = json_decode($post["deleteIndexes"]);
            foreach ($indexes as $index) {
                $sql = sprintf("DROP INDEX `%s` ON `%s`", mysql_real_escape_string($index->id), mysql_real_escape_string($obj->name));
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        if (array_key_exists("deleteColumns", $post)) {
            $columns = json_decode($post["deleteColumns"]);
            foreach ($columns as $column) {
                $sql = sprintf("ALTER TABLE `%s` DROP COLUMN `%s`", mysql_real_escape_string($obj->name), mysql_real_escape_string($column->id));
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        if (array_key_exists("updateColumns", $post)) {
            $columns = json_decode($post["updateColumns"]);
            foreach ($columns as $column) {
                $col = TableColumn::from_ui($column);
                if ($column->id != "") {
                    $sql = sprintf("ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` `%s`", mysql_real_escape_string($obj->name), mysql_real_escape_string($column->id), mysql_real_escape_string($column->name), mysql_real_escape_string($col->get_definition()));
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                } else {
                    //continue here
                }
            }
        }

        //hash
        if ($obj != null) {
            $xml_hash = $obj->calculate_xml_hash();
            $obj->xml_hash = $xml_hash;
            $obj->mysql_save();
        }

        return $lid;
    }

    public function mysql_delete_Table() {
        $sql = "DROP TABLE IF EXISTS " . $this->name . ";";
        mysql_query($sql);
    }

    public function import_from_mysql($table) {
        
    }

    public function import_from_csv($path, $delimeter = ",", $enclosure = '"', $header = false) {
        $this->mysql_delete_Table();

        $row = 1;
        $column_names = array();

        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, $delimeter, $enclosure)) !== FALSE) {
                if ($row == 1) {
                    $sql = "CREATE TABLE  " . $this->get_table_name() . " (";
                    for ($i = 1; $i <= count($data); $i++) {
                        $column_name = "c" . $i;
                        if ($header)
                            $column_name = Table::format_column_name($data[$i - 1]);
                        if (trim($column_name) == "")
                            continue;
                        array_push($column_names, $column_name);
                        if ($i > 1)
                            $sql.=",";
                        $sql.="`" . $column_name . "`  TEXT NOT NULL";

                        $sql2 = sprintf("INSERT INTO `%s` (`index`,`name`,`Table_id`,`type`) VALUES (%d,'%s',%d,%s)", TableColumn::get_mysql_table(), $i, $column_name, $this->id, "text");
                        if (!mysql_query($sql2))
                            return -4;
                    }
                    $sql.=") ENGINE = INNODB DEFAULT CHARSET=utf8;";
                    if (!mysql_query($sql))
                        return -4;
                    if ($header) {
                        $row++;
                        continue;
                    }
                }

                $sql = sprintf("INSERT INTO `%s` SET ", $this->get_table_name());
                for ($i = 1; $i <= count($column_names); $i++) {
                    if ($i > 1)
                        $sql.=", ";
                    $sql.=sprintf("`%s`='%s'", $column_names[$i - 1], mysql_real_escape_string($data[$i - 1]));
                }
                if (!mysql_query($sql))
                    return -4;
                $row++;
            }
        }
        return 0;
    }

    public static function format_column_name($name) {
        $name = preg_replace("/[^A-Z^a-z^0-9^_]/i", "", $name);
        $name = preg_replace("/^([^A-Z^a-z]{1,})*/i", "", $name);
        $name = preg_replace("/([^A-Z^a-z^0-9]{1,})$/i", "", $name);
        return $name;
    }

    public function export() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $export->appendChild($obj);

        return $xml->saveXML();
    }

    public function import_XML($xml) {
        $xpath = new DOMXPath($xml);

        $elements = $xpath->query("/export");
        foreach ($elements as $element) {
            if (Ini::$version != $element->getAttribute("version"))
                return -5;
        }

        $last_result = 0;
        $elements = $xpath->query("/export/Table");
        foreach ($elements as $element) {
            $this->xml_hash = $element->getAttribute("xml_hash");
            $children = $element->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "description": $this->description = $child->nodeValue;
                        break;
                }
            }

            $post['cols'] = array();
            $elements_tc = $xpath->query("./TableColumns/TableColumn", $element);
            foreach ($elements_tc as $element_tc) {
                $children = $element_tc->childNodes;
                $col = array("oid" => 0);
                foreach ($children as $child) {
                    switch ($child->nodeName) {
                        case "name": $col["name"] = $child->nodeValue;
                            break;
                        case "type": $col["type"] = $child->nodeValue;
                            break;
                        case "length": $col["lengthValues"] = $child->nodeValue;
                            break;
                        case "default_value": $col["defaultValue"] = $child->nodeValue;
                            break;
                        case "attributes": $col["attributes"] = $child->nodeValue;
                            break;
                        case "null": $col["nullable"] = $child->nodeValue;
                            break;
                        case "auto_increment": $col["auto_increment"] = $child->nodeValue;
                            break;
                    }
                }
                array_push($post['cols'], json_encode($col));
            }

            $post['indexes'] = array();
            $elements_ti = $xpath->query("./TableIndexes/TableIndex", $element);
            foreach ($elements_ti as $element_ti) {
                $children = $element_ti->childNodes;
                $index = array("oid" => 0);
                foreach ($children as $child) {
                    switch ($child->nodeName) {
                        case "type": $index["type"] = $child->nodeValue;
                            break;
                        case "columns": $index["columns"] = $child->nodeValue;
                            break;
                    }
                }
                array_push($post['indexes'], json_encode($index));
            }

            $post['rows'] = array();
            $elements_r = $xpath->query("./rows/row", $element);
            foreach ($elements_r as $element_r) {
                $children = $element_r->childNodes;
                $row = array();
                foreach ($children as $child) {
                    $row[$child->nodeName] = $child->nodeValue;
                }
                array_push($post['rows'], json_encode($row));
            }

            $last_result = $this->mysql_save_from_post($post);
        }
        return $last_result;
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', "UTF-8");

        $element = $xml->createElement("Table");
        $element->setAttribute("id", $this->id);
        $element->setAttribute("xml_hash", $this->xml_hash);
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES, "UTF-8"));
        $element->appendChild($description);

        $columns = $xml->createElement("TableColumns");
        $element->appendChild($columns);

        $cols = $this->get_TableColumns();
        foreach ($cols as $col) {
            $elem = $col->to_XML();
            $elem = $xml->importNode($elem, true);
            $columns->appendChild($elem);
        }

        $indexes = $xml->createElement("TableIndexes");
        $element->appendChild($indexes);

        $indx = $this->get_TableIndexes();
        foreach ($indx as $index) {
            $elem = $index->to_XML();
            $elem = $xml->importNode($elem, true);
            $indexes->appendChild($elem);
        }

        $rows = $xml->createElement("rows");
        $element->appendChild($rows);

        if ($this->has_table()) {
            $sql = sprintf("SELECT * FROM `%s`", $this->get_table_name());
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z)) {
                $row = $xml->createElement("row");

                foreach ($cols as $col) {
                    $cell = $xml->createElement($col->name, htmlspecialchars($r[$col->name], ENT_QUOTES, "UTF-8"));
                    $row->appendChild($cell);
                }

                $rows->appendChild($row);
            }
        }
        return $element;
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`Table` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        return mysql_query($sql);
    }

}

?>