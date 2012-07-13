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

class TestTemplate extends OTable {

    public $Test_id = 0;
    public $TestSection_id = 0;
    public $Template_id = 0;
    public $HTML = "";
    public static $mysql_table_name = "TestTemplate";

    public function to_XML() {
        $xml = new DOMDocument('1.0', 'UTF-8');

        $element = $xml->createElement("TestTemplate");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($id);

        $Test_id = $xml->createElement("Test_id", htmlspecialchars($this->Test_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($Test_id);

        $TestSection_id = $xml->createElement("TestSection_id", htmlspecialchars($this->TestSection_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($TestSection_id);

        $Template_id = $xml->createElement("Template_id", htmlspecialchars($this->Template_id, ENT_QUOTES, "UTF-8"));
        $element->appendChild($Template_id);

        $html = $xml->createElement("HTML", htmlspecialchars($this->HTML, ENT_QUOTES, "UTF-8"));
        $element->appendChild($html);

        return $element;
    }

    public static function repopulate_table() {
        $sql = "DELETE FROM `TestTemplate`";
        mysql_query($sql);
        $sql = "SELECT * FROM `TestSection`";
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            set_time_limit(0);
            if ($r['TestSectionType_id'] == DS_TestSectionType::LOAD_HTML_TEMPLATE) {
                $ts = TestSection::from_mysql_id($r['id']);
                $vals = $ts->get_values();
                $template = Template::from_mysql_id($vals[0]);
                if ($template != null) {
                    $html = Template::output_html($template->HTML, $vals, $template->get_outputs(), $template->get_inserts());

                    $test_template = new TestTemplate();
                    $test_template->Test_id = $r['Test_id'];
                    $test_template->TestSection_id = $r['id'];
                    $test_template->Template_id = $vals[0];
                    $test_template->HTML = $html;
                    $test_template->mysql_save();
                }
            }
        }
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `TestTemplate`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestTemplate` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Test_id` bigint(20) NOT NULL,
            `TestSection_id` bigint(20) NOT NULL,
            `Template_id` bigint(20) NOT NULL,
            `HTML` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>