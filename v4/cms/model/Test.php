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

class Test extends OModule {

    public $name = "unnamed test";
    public $description = "";
    public $session_count = 0;
    public $open = 0;
    public $loader_Template_id = 0;
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "Test";

    public function __construct($params = array()) {
        $this->name = Language::string(76);
        parent::__construct($params);
    }

    public function mysql_save_from_post($post) {
        $lid = parent::mysql_save_from_post($post);

        if ($this->id != 0) {
            $this->delete_object_links(TestVariable::get_mysql_table());
            $i = 0;
        }

        $i = 0;
        if (array_key_exists("parameters", $post)) {
            foreach ($post["parameters"] as $param) {
                $p = json_decode($param);
                $var = new TestVariable();
                $var->description = $p->description;
                $var->name = $p->name;
                $var->index = $i;
                $var->type = 0;
                $var->Test_id = $lid;
                $var->mysql_save();
                $i++;
            }
        }
        if (array_key_exists("returns", $post)) {
            foreach ($post["returns"] as $ret) {
                $r = json_decode($ret);
                $var = new TestVariable();
                $var->description = $r->description;
                $var->name = $r->name;
                $var->index = $i;
                $var->type = 1;
                $var->Test_id = $lid;
                $var->mysql_save();
                $i++;
            }
        }

        $obj = static::from_mysql_id($lid);
        if ($obj != null) {
            $xml_hash = $obj->calculate_xml_hash();
            $obj->xml_hash = $xml_hash;
            $obj->mysql_save();
        }

        return $lid;
    }

    public function verified_input_values($values) {
        $result = array();
        $params = $this->get_parameter_TestVariables();
        foreach ($values as $val) {
            $v = json_decode($val);
            foreach ($params as $param) {
                if ($param->name == $v->name)
                    array_push($result, $val);
                break;
            }
        }
        return $result;
    }

    public function get_loader_Template() {
        return Template::from_mysql_id($this->loader_Template_id);
    }

    public function mysql_delete() {
        $this->delete_sessions();
        $this->delete_object_links(TestVariable::get_mysql_table());
        parent::mysql_delete();
    }

    public function delete_sessions() {
        $sessions = TestSession::from_property(array("Test_id" => $this->id));
        foreach ($sessions as $session) {
            $session->remove();
        }
    }

    public function export($xml = null, $sub_test = false, $main_test = null) {
        if ($xml == null) {
            $xml = new DOMDocument('1.0', 'UTF-8');

            $export = $xml->createElement("export");
            $export->setAttribute("version", Ini::$version);
            $xml->appendChild($export);
            $xpath = new DOMXPath($xml);
        } else {
            $xpath = new DOMXPath($xml);
            $export = $xpath->query("/export");
            $export = $export->item(0);
        }

        //append subobjects of test
        $tests_ids = array();
        array_push($tests_ids, $this->id);
        $templates_ids = array();
        $custom_sections_ids = array();
        $tables_ids = array();
        $qtiai_ids = array();

        $loader = $this->get_loader_Template();
        if ($loader != null) {
            if (!in_array($loader->id, $templates_ids)) {
                $template = $loader;
                if ($template != null) {
                    $present_templates = $xpath->query("/export/Template");
                    $exists = false;
                    foreach ($present_templates as $obj) {
                        if ($template->xml_hash == $obj->getAttribute("xml_hash")) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {

                        $element = $template->to_XML();
                        $obj = $xml->importNode($element, true);
                        $export->appendChild($obj);
                        array_push($templates_ids, $loader->id);
                    }
                }
            }
        }

        //FILL

        if (!$sub_test) {
            $element = $this->to_XML();
            $obj = $xml->importNode($element, true);
            $export->appendChild($obj);
        }

        return ($sub_test ? $xml : $xml->saveXML());
    }

    public function import_XML($xml, $compare = null) {
        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);

        $elements = $xpath->query("/export");
        foreach ($elements as $element) {
            if (Ini::$version != $element->getAttribute("version"))
                return -5;
        }

        if ($compare == null) {
            $compare = array(
                "Template" => array(),
                "Table" => array(),
                "CustomSection" => array(),
                "Test" => array(),
                "QTIAssessmentItem" => array()
            );
        }

        //link templates
        $logged_user = User::get_logged_user();
        $elements = $xpath->query("/export/Template");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["Template"][$id] = Template::find_xml_hash($hash);
            if ($compare["Template"][$id] == 0) {
                $obj = new Template();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(Template::convert_to_XML_document($element));
                $compare["Template"][$id] = $lid;
            }
        }

        //link QTI assessment items
        $logged_user = User::get_logged_user();
        $elements = $xpath->query("/export/QTIAssessmentItem");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["QTIAssessmentItem"][$id] = QTIAssessmentItem::find_xml_hash($hash);
            if ($compare["QTIAssessmentItem"][$id] == 0) {
                $obj = new QTIAssessmentItem();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(QTIAssessmentItem::convert_to_XML_document($element));
                $compare["QTIAssessmentItem"][$id] = $lid;
            }
        }

        //link tables
        $elements = $xpath->query("/export/Table");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["Table"][$id] = Table::find_xml_hash($hash);
            if ($compare["Table"][$id] == 0) {
                $obj = new Table();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(Table::convert_to_XML_document($element));
                $compare["Table"][$id] = $lid;
            }
        }

        //link custom sections
        $elements = $xpath->query("/export/CustomSection");
        foreach ($elements as $element) {
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            $compare["CustomSection"][$id] = CustomSection::find_xml_hash($hash);
            if ($compare["CustomSection"][$id] == 0) {
                $obj = new CustomSection();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(CustomSection::convert_to_XML_document($element));
                $compare["CustomSection"][$id] = $lid;
            }
        }

        //link tests
        $elements = $xpath->query("/export/Test");
        for ($i = 0; $i < $elements->length - 1; $i++) {
            $element = $elements->item($i);
            $id = $element->getAttribute("id");
            $hash = $element->getAttribute("xml_hash");
            if (!isset($compare["Test"][$id]))
                $compare["Test"][$id] = 0;
            if ($compare["Test"][$id] == 0) {
                $obj = new Test();
                $obj->Owner_id = $logged_user->id;
                $lid = $obj->import_XML(CustomSection::convert_to_XML_document($element), $compare);
                $compare["Test"][$id] = $lid;
            }
        }

        $elements = $xpath->query("/export/Test");
        $element = $elements->item($elements->length - 1);
        $this->xml_hash = $element->getAttribute("xml_hash");
        $element_id = $element->getAttribute("id");
        if (isset($compare["Test"][$element_id]) && $compare["Test"][$element_id] != 0)
            return $compare["Test"][$element_id];
        $children = $element->childNodes;
        foreach ($children as $child) {
            switch ($child->nodeName) {
                case "name": $this->name = $child->nodeValue;
                    break;
                case "description": $this->description = $child->nodeValue;
                    break;
                case "open": $this->open = $child->nodeValue;
                    break;
                case "loader_Template_id": $this->loader_Template_id = ($child->nodeValue == 0 ? 0 : $compare["Template"][$child->nodeValue]);
                    break;
            }
        }

        $this->id = $this->mysql_save();

        $post = array();

        $post["parameters"] = array();
        $elements = $xpath->query("/export/Test[@id='" . $element_id . "']/TestVariables/TestVariable");
        foreach ($elements as $element) {
            $tv = array();
            $tv["Test_id"] = $element_id;
            $children = $element->childNodes;
            $correct = true;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "index": $tv["index"] = $child->nodeValue;
                        break;
                    case "name": $tv["name"] = $child->nodeValue;
                        break;
                    case "description": $tv["description"] = $child->nodeValue;
                        break;
                    case "type": {
                            $tv["type"] = $child->nodeValue;
                            if ($tv["type"] != 0)
                                $correct = false;
                            break;
                        }
                }
            }
            if ($correct) {
                $tv = json_encode($tv);
                array_push($post['parameters'], $tv);
            }
        }

        $post["returns"] = array();
        $elements = $xpath->query("/export/Test[@id='" . $element_id . "']/TestVariables/TestVariable");
        foreach ($elements as $element) {
            $tv = array();
            $tv["Test_id"] = $element_id;
            $children = $element->childNodes;
            $correct = true;
            foreach ($children as $child) {
                switch ($child->nodeName) {
                    case "index": $tv["index"] = $child->nodeValue;
                        break;
                    case "name": $tv["name"] = $child->nodeValue;
                        break;
                    case "description": $tv["description"] = $child->nodeValue;
                        break;
                    case "type": {
                            $tv["type"] = $child->nodeValue;
                            if ($tv["type"] != 1)
                                $correct = false;
                            break;
                        }
                }
            }
            if ($correct) {
                $tv = json_encode($tv);
                array_push($post['returns'], $tv);
            }
        }
        
        //FILL

        return $this->mysql_save_from_post($post);
    }

    public function to_XML() {
        $xml = new DOMDocument();

        $element = $xml->createElement("Test");
        $element->setAttribute("id", $this->id);
        $element->setAttribute("xml_hash", $this->xml_hash);
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES, "UTF-8"));
        $element->appendChild($description);

        $open = $xml->createElement("open", htmlspecialchars($this->open, ENT_QUOTES, "UTF-8"));
        $element->appendChild($open);
        
        $loader_Template_id = $xml->createElement("loader_Template_id", htmlspecialchars($this->loader_Template_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($loader_Template_id);

        $test_variables = $xml->createElement("TestVariables");
        $element->appendChild($test_variables);

        $tv = $this->get_TestVariables();
        foreach ($tv as $var) {
            $elem = $var->to_XML();
            $elem = $xml->importNode($elem, true);
            $test_variables->appendChild($elem);
        }

        return $element;
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `Test`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Test` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `open` tinyint(1) NOT NULL,
            `session_count` bigint(20) NOT NULL,
            `loader_Template_id` bigint(20) NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

    public static function get_list_columns() {
        $cols = parent::get_list_columns();

        array_push($cols, array(
            "name" => Language::string(335),
            "property" => "session_count",
            "searchable" => true,
            "sortable" => true,
            "type" => "number",
            "groupable" => false,
            "width" => 120,
            "show" => true
        ));

        return $cols;
    }
    
    public function get_TestVariables() {
        return TestVariable::from_property(array("Test_id" => $this->id));
    }

    public function get_parameter_TestVariables() {
        return TestVariable::from_property(array("Test_id" => $this->id, "type" => 0));
    }

    public function get_return_TestVariables() {
        return TestVariable::from_property(array("Test_id" => $this->id, "type" => 1));
    }

}

?>