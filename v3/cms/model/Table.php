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

class Table extends OModule
{
    public $name = "";
    public static $exportable = true;
    public static $mysql_table_name = "Table";

    public function __construct($params = array())
    {
        $this->name = Language::string(74);
        parent::__construct($params);
    }

    public function get_table_name()
    {
        return self::get_table_prefix() . "_" . $this->id;
    }

    public static function get_table_prefix()
    {
        return "c3tbl";
    }

    public function mysql_delete()
    {
        $this->mysql_delete_Table();

        parent::mysql_delete();
    }

    public static function get_mysql_optimal_numeric_type($rows, $name)
    {
        $max_int = 0;
        $int_length = 0;
        $dec_length = 0;
        $signed = false;

        foreach ($rows as $row)
        {
            foreach ($row as $col_name => $col_value)
            {
                $col_value = trim($col_value);
                if (substr($col_value, 0, 1) == "-")
                {
                    $signed = true;
                    $col_value = substr($col_value, 1);
                }
                if ($name == $col_name)
                {
                    $part = explode(".", $col_value);
                    $int = $part[0];
                    $max_int = max($int, $max_int);
                    $int_length = max(strlen($int), $int_length);
                    if (count($part) > 1)
                    {
                        $dec = $part[1];
                        $dec_length = max($dec_length, strlen($dec));
                    }
                    break;
                }
            }
        }

        $type = "";

        if ($dec_length > 0)
        {
            if ($dec_length > 0) $type = "FLOAT";
            if ($dec_length > 5) $type = "DOUBLE";

            return $type;
        }
        else
        {
            if ($max_int >= 0) $type = "TINYINT";
            if ($max_int >= 129) $type = "SMALLINT";
            if ($max_int >= 32769) $type = "MEDIUMINT";
            if ($max_int >= 8388609) $type = "INT";
            if ($max_int >= 2147483649) $type = "BIGINT";

            return $type;
        }
        return "INT";
    }

    public function mysql_save_from_post($post)
    {
        $lid = parent::mysql_save_from_post($post);
        $this->mysql_delete_TableColumn();

        if (array_key_exists("cols", $post))
        {
            $table_name = "`" . self::get_table_prefix() . "_" . $lid . "`";

            $sql = "DROP TABLE IF EXISTS " . $table_name . ";";
            mysql_query($sql);
            $sql = "CREATE TABLE  " . $table_name . " (";
            $i = 0;
            foreach ($post['cols'] as $col_json)
            {
                $col = json_decode($col_json);
                if ($i > 0) $sql.=",";
                $sql.="`" . $col->name . "` ";
                switch ($col->type)
                {
                    case 1:
                    case 3:
                        {
                            $sql.="TEXT NOT NULL";
                            break;
                        }
                    case 2:
                        {
                            if (array_key_exists("rows", $post) && $post['rows'] != null && is_array($post['rows']))
                            {
                                $sql.=Table::get_mysql_optimal_numeric_type($post['rows'], $col->name) . " NOT NULL";
                            }
                            else $sql.="INT NOT NULL";
                            break;
                        }
                }
                $i++;
            }
            $sql.=") ENGINE = INNODB;";
            mysql_query($sql);

            $pers_cols = TableColumn::from_property(array("Table_id" => $lid));
            foreach ($pers_cols as $oc)
            {
                $delete = true;
                foreach ($post['cols'] as $nc)
                {
                    $nc = json_decode($nc);
                    if ($oc->id == $nc->oid)
                    {
                        $delete = false;
                        break;
                    }
                }
                if ($delete) $oc->mysql_delete();
            }

            $sql = sprintf("INSERT INTO `%s` (`index`,`name`,`Table_id`,`TableColumnType_id`) VALUES ", TableColumn::get_mysql_table());
            $i = 0;
            foreach ($post['cols'] as $col_json)
            {
                $col = json_decode($col_json);
                //if ($col->oid == 0)
                //{
                if ($i > 0) $sql.=",";
                $sql.="(";
                $sql.= ($i + 1) . ",'" . mysql_real_escape_string($col->name) . "'," . $lid . "," . $col->type;
                $sql.=")";
                $i++;
                //}
            }
            mysql_query($sql);

            if (array_key_exists("rows", $post) && $post['rows'] != null && is_array($post['rows']))
            {
                $sql = "INSERT INTO " . $table_name . " (";
                $i = 0;
                foreach ($post['cols'] as $col_json)
                {
                    if ($i > 0) $sql.=",";
                    $col = json_decode($col_json);
                    $sql.="`" . $col->name . "`";
                    $i++;
                }
                $sql.=") VALUES ";

                for ($a = 0; $a < count($post['rows']); $a++)
                {
                    $row = $post['rows'][$a];
                    if ($a > 0) $sql.=",";
                    $sql.="(";
                    $i = 0;
                    foreach ($row as $col_name => $col_value)
                    {
                        $col = json_decode($post['cols'][$i]);
                        if ($i > 0) $sql.=",";
                        $sql.="'" . mysql_real_escape_string($col_value) . "'";
                        $i++;
                    }
                    $sql.=")";
                }
                mysql_query($sql);
            }
        }

        return $lid;
    }

    public function has_table()
    {
        $table_name = self::get_table_prefix() . "_" . $this->id;
        $sql = "SHOW TABLES LIKE '" . $table_name . "'";
        $z = mysql_query($sql);
        if (mysql_num_rows($z) > 0) return true;
        return false;
    }

    public function mysql_delete_Table()
    {
        $this->mysql_delete_TableColumn();

        $table_name = "`" . $this->get_table_name() . "`";
        $sql = "DROP TABLE IF EXISTS " . $table_name . ";";
        mysql_query($sql);
    }

    public function mysql_delete_TableColumn()
    {
        $this->delete_object_links(TableColumn::get_mysql_table());
    }

    public function get_TableColumns()
    {
        return TableColumn::from_property(array("Table_id" => $this->id));
    }

    public function import_from_mysql($table)
    {
        $this->mysql_delete_Table();

        $columns = array();
        $sql = sprintf("SHOW COLUMNS FROM `%s`", $table);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z))
        {
            array_push($columns, $r['Field']);
        }

        $sql = sprintf("SELECT * FROM `%s`", $table);
        $z = mysql_query($sql);
        $i = 0;
        while ($r = mysql_fetch_array($z))
        {
            if ($i == 0)
            {
                $sql = "CREATE TABLE  " . $this->get_table_name() . " (";
                $j = 0;
                foreach ($columns as $col)
                {
                    if ($j > 0) $sql.=",";
                    $sql.="`" . $col . "`  TEXT NOT NULL";

                    $sql2 = sprintf("INSERT INTO `%s` (`index`,`name`,`Table_id`,`TableColumnType_id`) VALUES (%d,'%s',%d,%d)", TableColumn::get_mysql_table(), ($j + 1), $col, $this->id, 1);
                    mysql_query($sql2);

                    $j++;
                }
                $sql.=") ENGINE = INNODB;";
                mysql_query($sql);
            }
            $cols = "";
            $vals = "";
            $j = 0;
            foreach ($columns as $col)
            {
                if ($j > 0)
                {
                    $cols.=",";
                    $vals.=",";
                }
                $cols.="`" . $col . "`";
                $vals.="'" . $r[$col] . "'";
                $j++;
            }
            $sql = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->get_table_name(), $cols, $vals);
            mysql_query($sql);
            $i++;
        }
        return 0;
    }

    public function import_from_csv($path, $delimeter = ",", $enclosure = '"', $header = false)
    {
        $this->mysql_delete_Table();

        $row = 1;
        $column_names = array();
        if (($handle = fopen($path, "r")) !== FALSE)
        {
            while (($data = fgetcsv($handle, 0, $delimeter, $enclosure)) !== FALSE)
            {
                if ($row == 1)
                {
                    $sql = "CREATE TABLE  " . $this->get_table_name() . " (";
                    for ($i = 1; $i <= count($data); $i++)
                    {
                        $column_name = "c" . $i;
                        if ($header) $column_name = $data[$i - 1];
                        if (trim($column_name) == "") continue;
                        array_push($column_names, $column_name);
                        if ($i > 1) $sql.=",";
                        $sql.="`" . $column_name . "`  TEXT NOT NULL";

                        $sql2 = sprintf("INSERT INTO `%s` (`index`,`name`,`Table_id`,`TableColumnType_id`) VALUES (%d,'%s',%d,%d)", TableColumn::get_mysql_table(), $i, $column_name, $this->id, 1);
                        if (!mysql_query($sql2)) return -4;
                    }
                    $sql.=") ENGINE = INNODB;";
                    if (!mysql_query($sql)) return -4;
                    if ($header)
                    {
                        $row++;
                        continue;
                    }
                }

                $sql = sprintf("INSERT INTO `%s` SET ", $this->get_table_name());
                for ($i = 1; $i <= count($column_names); $i++)
                {
                    if ($i > 1) $sql.=", ";
                    $sql.=sprintf("`%s`='%s'", $column_names[$i - 1], mysql_real_escape_string($data[$i - 1]));
                }
                if (!mysql_query($sql)) return -4;
                $row++;
            }
            fclose($handle);
        }
        return 0;
    }

    public function export()
    {
        $xml = new DOMDocument();

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $group = $xml->createElement("Tables");
        $export->appendChild($group);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $group->appendChild($obj);

        return $xml->saveXML();
    }

    public function import($path)
    {
        $xml = new DOMDocument();
        if (!$xml->load($path)) return -4;

        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);
        $elements = $xpath->query("/export/Tables/Table");
        foreach ($elements as $element)
        {
            $children = $element->childNodes;
            foreach ($children as $child)
            {
                switch ($child->nodeName)
                {
                    case "name": $this->name = $child->nodeValue;
                        break;
                }
            }
        }

        $post['cols'] = array();
        $elements = $xpath->query("/export/Tables/Table/TableColumns/TableColumn");
        foreach ($elements as $element)
        {
            $children = $element->childNodes;
            $col = array("oid" => 0);
            foreach ($children as $child)
            {
                switch ($child->nodeName)
                {
                    case "name": $col["name"] = $child->nodeValue;
                        break;
                    case "TableColumnType_id": $col["type"] = $child->nodeValue;
                        break;
                }
            }
            array_push($post['cols'], json_encode($col));
        }

        $post['rows'] = array();
        $elements = $xpath->query("/export/Tables/Table/rows/row");
        foreach ($elements as $element)
        {
            $children = $element->childNodes;
            $row = array();
            foreach ($children as $child)
            {
                $row[$child->nodeName] = $child->nodeValue;
            }
            array_push($post['rows'], $row);
        }

        return $this->mysql_save_from_post($post);
    }

    public function to_XML()
    {
        $xml = new DOMDocument();

        $element = $xml->createElement("Table");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES));
        $element->appendChild($id);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES));
        $element->appendChild($name);

        $columns = $xml->createElement("TableColumns");
        $element->appendChild($columns);

        $cols = $this->get_TableColumns();
        foreach ($cols as $col)
        {
            $column = $xml->createElement("TableColumn");
            $column = $xml->importNode($column, true);
            $columns->appendChild($column);

            $name = $xml->createElement("name", $col->name);
            $column->appendChild($name);
            $type = $xml->createElement("TableColumnType_id", $col->TableColumnType_id);
            $column->appendChild($type);
        }

        $rows = $xml->createElement("rows");
        $element->appendChild($rows);

        $sql = sprintf("SELECT * FROM `%s`", $this->get_table_name());
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z))
        {
            $row = $xml->createElement("row");

            foreach ($cols as $col)
            {
                $cell = $xml->createElement($col->name, $r[$col->name]);
                $row->appendChild($cell);
            }

            $rows->appendChild($row);
        }

        return $element;
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `Table`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Table` (
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