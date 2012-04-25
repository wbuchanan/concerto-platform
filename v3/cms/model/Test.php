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

class Test extends OModule
{
    public $name = "unnamed test";
    public $session_count = 0;
    public static $exportable = true;
    public static $mysql_table_name = "Test";

    public function __construct($params = array())
    {
        $this->name = Language::string(76);
        parent::__construct($params);
    }

    public function mysql_save_from_post($post)
    {
        $lid = parent::mysql_save_from_post($post);

        if ($this->id != 0) $this->delete_sections();
        else
        {
            $start_section = new TestSection();
            $start_section->TestSectionType_id = DS_TestSectionType::START;
            $start_section->Test_id = $lid;
            $start_section->counter = 1;
            $start_section->mysql_save();

            $end_section = new TestSection();
            $end_section->TestSectionType_id = DS_TestSectionType::END;
            $end_section->Test_id = $lid;
            $end_section->counter = 2;
            $end_section->mysql_save();
        }

        if (isset($post['sections']))
        {
            foreach ($post['sections'] as $section)
            {
                $s = new TestSection();
                $s->counter = $section['counter'];
                $s->TestSectionType_id = $section['type'];
                $s->Test_id = $lid;

                $s->parent_counter = $section['parent'];

                $slid = $s->mysql_save();

                $vals = $section['value'];
                $vals = json_decode($vals);

                foreach (get_object_vars($vals) as $k => $v)
                {
                    $index = substr($k, 1);
                    $value = $v;

                    $sv = new TestSectionValue();
                    $sv->TestSection_id = $slid;
                    $sv->index = $index;
                    $sv->value = $value;
                    $sv->mysql_save();
                }
            }
        }

        $sql = sprintf("DELETE FROM `%s` WHERE `Test_id`=%d", TestProtectedVariable::get_mysql_table(), $lid);
        mysql_query($sql);
        if (array_key_exists("protected", $post))
        {
            foreach ($post['protected'] as $var)
            {
                $var = json_decode($var);
                $s = new TestProtectedVariable();
                $s->name = $var->name;
                $s->Test_id = $lid;
                $slid = $s->mysql_save();
            }
        }
        return $lid;
    }

    public function mysql_delete()
    {
        $this->delete_sections();
        $this->delete_sessions();
        parent::mysql_delete();
    }

    public function delete_sections()
    {
        $sections = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($sections as $section)
        {
            $section->mysql_delete();
        }
    }

    public function delete_sessions()
    {
        $sessions = TestSession::from_property(array("Test_id" => $this->id));
        foreach ($sessions as $session)
        {
            $session->mysql_delete();
        }
    }

    public function get_max_counter()
    {
        $max = 0;
        $sections = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($sections as $section)
        {
            $max = max(array($max, $section->counter));
        }
        return $max;
    }

    public function get_starting_counter()
    {
        return $this->get_TestSection()->counter;
    }

    public function get_TestSection($counter = null)
    {
        $section = null;
        if ($counter == null)
                $section = TestSection::from_property(array("Test_id" => $this->id), false);
        else
                $section = TestSection::from_property(array("Test_id" => $this->id, "counter" => $counter), false);
        return $section;
    }

    public function get_TestSections_RFunction_declaration()
    {
        $code = "";
        $sections = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($sections as $s)
        {
            $code.=$s->get_RFunction();
        }
        return $code;
    }

    public function export()
    {
        $xml = new DOMDocument('1.0', 'UTF-8');

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $group = $xml->createElement("Tests");
        $export->appendChild($group);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $group->appendChild($obj);

        return $xml->saveXML();
    }

    public function import($path)
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        if (!@$xml->load($path)) return -4;

        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);
        $elements = $xpath->query("/export/Tests/Test");
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

        $this->id = $this->mysql_save();

        $post = array();
        $post["sections"] = array();

        $elements = $xpath->query("/export/Tests/Test/TestSections/TestSection");
        foreach ($elements as $element)
        {
            $test_section = array();
            $test_section["value"] = array();

            $children = $element->childNodes;
            foreach ($children as $child)
            {
                switch ($child->nodeName)
                {
                    case "counter": $test_section["counter"] = $child->nodeValue;
                        break;
                    case "TestSectionType_id": $test_section["type"] = $child->nodeValue;
                        break;
                    case "parent_counter": $test_section["parent"] = $child->nodeValue;
                        break;
                    case "TestSectionValues":
                        {
                            $ts_child_list = $child->childNodes;
                            foreach ($ts_child_list as $ts_child)
                            {
                                $index = -1;
                                $value = "";

                                $tsv_vars = $ts_child->childNodes;
                                foreach ($tsv_vars as $tsv_child)
                                {
                                    switch ($tsv_child->nodeName)
                                    {
                                        case "index": $index = $tsv_child->nodeValue;
                                            break;
                                        case "value": $value = $tsv_child->nodeValue;
                                            break;
                                    }
                                }
                                if ($index != -1)
                                        $test_section["value"]["v" . $index] = $value;
                            }
                            if (count($test_section["value"]) == 0)
                                    $test_section['value'] = "{}";
                            else
                                    $test_section['value'] = json_encode($test_section['value']);
                            break;
                        }
                }
            }
            array_push($post["sections"], $test_section);
        }

        return $this->mysql_save_from_post($post);
    }

    public function to_XML()
    {
        $xml = new DOMDocument();

        $element = $xml->createElement("Test");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($id);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES, "UTF-8"));
        $element->appendChild($name);

        $sections = $xml->createElement("TestSections");
        $element->appendChild($sections);

        $ts = TestSection::from_property(array("Test_id" => $this->id));
        foreach ($ts as $s)
        {
            $elem = $s->to_XML();
            $elem = $xml->importNode($elem, true);
            $sections->appendChild($elem);
        }

        return $element;
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `Test`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Test` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `session_count` bigint(20) NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

    public static function get_list_columns()
    {
        $cols = parent::get_list_columns();

        array_push($cols, array(
            "name" => Language::string(335),
            "property" => "session_count",
            "searchable" => true,
            "sortable" => true,
            "type" => "number",
            "groupable" => false,
            "width" => 100
        ));

        return $cols;
    }
    
    public function get_TestProtectedVariables()
    {
        $result = array(
            "TEST_ID",
            "TEST_SESSION_ID",
            "HASH"
        );
        
        $tpv = TestProtectedVariable::from_property(array("Test_id"=>$this->id));
        foreach($tpv as $v)
        {
            array_push($result, $v->name);
        }
        return $result;
    }

    public static function update_db($previous_version)
    {
        if (Ini::does_patch_apply("3.4.0", $previous_version))
        {
            $sql = "ALTER TABLE `Test` ADD `session_count` bigint(20) NOT NULL;";
            if (!mysql_query($sql)) return false;
        }
        return true;
    }
}

?>