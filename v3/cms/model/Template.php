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

class Template extends OModule {

    public $name = "";
    public $HTML = "";
    public static $exportable = true;
    public static $mysql_table_name = "Template";

    public function __construct($params = array()) {
        $this->name = Language::string(75);
        parent::__construct($params);
    }

    public function get_inserts() {
        $inserts = array();
        $html = $this->HTML;
        while (strpos($html, "{{") !== false) {
            $html = substr($html, strpos($html, "{{") + 2);
            if (strpos($html, "}}") !== false) {
                $name = substr($html, 0, strpos($html, "}}"));
                if ($name == "TIME_LEFT")
                    continue;
                if (!in_array($name, $inserts))
                    array_push($inserts, $name);
            }
        }
        return $inserts;
    }

    public function get_insert_reference($name, $vals) {
        $inserts = $this->get_inserts();
        $j = 3;
        foreach ($inserts as $ins) {
            if ($ins == "TIME_LEFT")
                continue;

            if ($ins == $name)
                return $vals[$j];

            $j++;
        }
        return $name;
    }

    public function get_return_reference($name, $vals, $output = null) {
        if($output == null) $output = $this->get_outputs();
        $j = 3 + $vals[1];
        foreach ($output as $ret) {
            if ($ret["name"] == $name)
                return $vals[$j];

            $j++;
        }
        return $name;
    }

    public function get_html_with_return_properties($vals) {
        $html = str_get_html($this->HTML);
        $outputs = $this->get_outputs();

        foreach ($outputs as $out) {
            $elems = $html->find("[name='" . $out["name"] . "']");
            $reference = null;
            foreach ($elems as $elem) {
                if ($reference == null) {
                    $reference = $this->get_return_reference($out["name"], $vals, $outputs);
                }
                $elem->setAttribute("name", $reference);
            }
        }
        return $html->save();
    }

    public function get_outputs() {
        $names = array();
        $outputs = array();
        $html_string = $this->HTML;
        if (empty($html_string))
            $html_string = "<p></p>";
        $html = str_get_html($html_string);
        foreach ($html->find('input[type="text"], input[type="password"], input[type="checkbox"], input[type="radio"]') as $element) {
            if (!in_array($element->name, $names)) {
                array_push($outputs, array("name" => $element->name, "type" => $element->type));
                array_push($names, $element->name);
            }
        }

        foreach ($html->find('textarea, select') as $element) {
            if (!in_array($element->name, $names)) {
                array_push($outputs, array("name" => $element->name, "type" => $element->tag));
                array_push($names, $element->name);
            }
        }
        return $outputs;
    }

    public function export() {
        $xml = new DOMDocument('1.0',"UTF-8");
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $group = $xml->createElement("Templates");
        $export->appendChild($group);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $group->appendChild($obj);

        return trim($xml->saveXML());
    }

    public function import($path) {
        $xml = new DOMDocument('1.0', 'UTF-8');
        if (!$xml->load($path))
            return -4;

        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);
        $elements = $xpath->query("/export/Templates/Template");
        foreach ($elements as $element) {
            $children = $element->childNodes;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "HTML": $this->HTML = $child->nodeValue;
                        break;
                }
            }
        }
        return $this->mysql_save();
    }

    public function to_XML() {
        $xml = new DOMDocument('1.0', 'UTF-8');

        $element = $xml->createElement("Template");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES,"UTF-8"));
        $element->appendChild($id);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES,"UTF-8"));
        $element->appendChild($name);

        $HTML = $xml->createElement("HTML", htmlspecialchars($this->HTML, ENT_QUOTES,"UTF-8"));
        $element->appendChild($HTML);

        return $element;
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `Template`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Template` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `HTML` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

    public function get_preview_HTML()
    {
        $html = new simple_html_dom();
        $html->load($this->HTML);
        $elems = $html->find("style");
        foreach($elems as $elem)
        {
            $elem->outertext = "";
        }
        $elems = $html->find("link");
        foreach($elems as $elem)
        {
            $elem->outertext = "";
        }
        $elems = $html->find("script");
        foreach($elems as $elem)
        {
            $elem->outertext = "";
        }
        return $html->save();
    }
}

?>