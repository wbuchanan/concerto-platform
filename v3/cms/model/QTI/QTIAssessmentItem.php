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

class QTIAssessmentItem extends OModule {

    public $root = null;
    public $name = "";
    public $XML = "";
    public $description = "";
    public $xml_hash = "";
    public static $exportable = true;
    public static $mysql_table_name = "QTIAssessmentItem";

    public function __construct($params = array()) {
        $this->name = Language::string(457);
        parent::__construct($params);
    }

    public function get_mapped_variables($TestSection_id) {
        $map = array();
        $ts = TestSection::from_mysql_id($TestSection_id);
        if ($ts == null || $ts->TestSectionType_id != DS_TestSectionType::QTI_INITIALIZATION)
            return null;
        $vals = $ts->get_values();
        for ($i = 1; $i < count($vals); $i = $i + 2) {
            $map[$vals[$i]] = $vals[$i + 1];
        }
        return $map;
    }

    public function validate($map = null) {
        $document = new DOMDocument('1.0', 'UTF-8');
        @$document->loadXML($this->XML);
        if (!$document) {
            return json_encode(array("result" => OQTIElement::VALIDATION_ERROR_TYPES_XML, "section" => "XML", "target" => "XML"));
        }
        $root = $document->getElementsByTagName("assessmentItem");
        if ($root->length == 0) {
            return json_encode(array("result" => OQTIElement::VALIDATION_ERROR_TYPES_CHILD_REQUIRED, "section" => "XML", "target" => "assessmentItem"));
        }
        $assessmentItem = new AssessmentItem($root->item(0));
        $this->root = $assessmentItem;
        return $this->root->validate($map);
    }

    public function get_outputs() {
        $result = array(array("name" => "QTI_HTML", "type" => "HTML"));
        foreach ($this->root->templateDeclaration as $var) {
            array_push($result, array("name" => $var->identifier, "type" => "template"));
        }
        foreach ($this->root->responseDeclaration as $var) {
            array_push($result, array("name" => $var->identifier, "type" => "response"));
        }
        foreach ($this->root->outcomeDeclaration as $var) {
            array_push($result, array("name" => $var->identifier, "type" => "outcome"));
        }
        return $result;
    }

    public function get_description() {
        return Template::strip_html($this->description);
    }

    public function get_variable_declaration_R_code() {
        //default outcome
        //default response

        $code = "";
        foreach ($this->root->responseDeclaration as $response) {
            if ($response->defaultValue != null) {
                $code.=sprintf("
                    %s <<- c()
                    ", $response->identifier);
                foreach ($response->defaultValue->value as $val) {
                    $code.=sprintf("
                        %s <<- c(%s,'%s')
                        ", $response->identifier, $response->identifier, $val->get_text());
                }
                $code.=sprintf("
                    %s <<- convertVariable(%s)
                    ", $response->identifier, $response->identifier);
            }
        }
        foreach ($this->root->outcomeDeclaration as $response) {
            if ($response->defaultValue != null) {
                $code.=sprintf("
                    %s <<- c()
                    ", $response->identifier);
                foreach ($response->defaultValue->value as $val) {
                    $code.=sprintf("
                        %s <<- c(%s,'%s')
                        ", $response->identifier, $response->identifier, $val->get_text());
                }
                $code.=sprintf("
                    %s <<- convertVariable(%s)
                    ", $response->identifier, $response->identifier);
            }
        }
        return $code;
    }

    public function get_template_processing_R_code($map = null) {
        //declare template variables
        //declare correct responses
        //modify default response
        //modify correct response
        //moidfy default outcome
        //HTML

        $code = "";
        //default value of template variables
        foreach ($this->root->templateDeclaration as $template) {
            if ($template->defaultValue != null) {
                $code.=sprintf("
                    %s <<- c()
                    ", $template->identifier);
                foreach ($template->defaultValue->value as $val) {
                    $code.=sprintf("
                        %s <<- c(%s,'%s')
                        ", $template->identifier, $template->identifier, $val->get_text());
                }
                $code.=sprintf("
                    %s <<- convertVariable(%s)
                    ", $template->identifier, $template->identifier);
            }
        }

        //declaration of correct responses
        foreach ($this->root->responseDeclaration as $response) {
            if ($response->correctResponse != null) {
                $code.=sprintf("
                    %s.correct <<- c()
                    ", $response->identifier);
                foreach ($response->correctResponse->value as $val) {
                    $code.=sprintf("
                        %s.correct <<- c(%s,'%s')
                        ", $response->identifier, $response->identifier, $val->get_text());
                }
                $code.=sprintf("
                    %s.correct <<- convertVariable(%s.correct)
                    ", $response->identifier, $response->identifier);
            }
        }

        //template processing
        if ($this->root->templateProcessing != null) {
            foreach ($this->root->templateProcessing->templateRule as $rule) {
                $code.=sprintf("
                    %s
                    ", $rule->get_R_code());
            }
        }

        //set HTML
        $html_result = $this->root->node->ownerDocument->saveXML($this->root->itemBody->node);
        if ($this->root->itemBody != null) {
            $xpath = new DOMXPath($this->root->node->ownerDocument);
            $xpath->registerNamespace("qti", "http://www.imsglobal.org/xsd/imsqti_v2p0");
            foreach (OQTIElement::$implemented_presentation_elements as $name) {
                $search = $xpath->query(".//qti:" . $name, $this->root->itemBody->node);
                foreach ($search as $elem) {
                    $name = ucfirst($name);
                    $obj = new $name($elem, $this->root->itemBody);
                    $obj->validate($map);
                    $html_result = str_ireplace($this->root->node->ownerDocument->saveXML($elem), $obj->get_HTML_code(), $html_result);
                }
            }
        }
        $code.=sprintf("
            %s <<- '%s'
            ", ($map != null && array_key_exists("QTI_HTML", $map) ? $map["QTI_HTML"] : "QTI_HTML"), addcslashes($html_result, "'"));

        return $code;
    }

    public function get_QTI_ini_R_code() {
        $code = $this->get_variable_declaration_R_code();
        $code.= $this->get_template_processing_R_code();
        return $code;
    }

    public function get_response_processing_R_code() {
        $code = "";
        //response processing
        if ($this->root->responseProcessing != null) {
            foreach ($this->root->responseProcessing->responseRule as $rule) {
                $code.=sprintf("
                    %s
                    ", $rule->get_R_code());
            }
        }
        return $code;
    }

    public function mysql_save() {
        $exclude = array("id", "updated", "root");
        if ($this->id == 0) {
            $this->created = date("Y-m-d H:i:s");
            $sql = "INSERT INTO ";
        }
        else
            $sql = "UPDATE ";
        $sql.=sprintf("`%s` SET ", self::get_mysql_table());

        $i = 0;
        foreach (get_object_vars($this) as $k => $v) {
            if (is_string($v))
                $v = mysql_real_escape_string($v);
            if (in_array($k, $exclude))
                continue;
            if ($i > 0)
                $sql.=", ";
            $sql.=sprintf("`%s`='%s' ", $k, $v);
            $i++;
        }

        if ($this->id != 0)
            $sql.=sprintf("WHERE `id`='%d'", $this->id);
        mysql_query($sql);
        if ($this->id != 0)
            return $this->id;
        else
            return mysql_insert_id();
    }

    public static function create_db($delete = false) {
        if ($delete) {
            if (!mysql_query("DROP TABLE IF EXISTS `QTIAssessmentItem`;"))
                return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `QTIAssessmentItem` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `XML` text NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>