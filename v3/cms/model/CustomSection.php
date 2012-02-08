<?php

class CustomSection extends OModule {

    public $name = "";
    public $description = "";
    public $code = "";
    
    public static $exportable = true;
    public static $mysql_table_name = "CustomSection";
    
    public function __construct($params = array())
    {
        $this->name = Language::string(68);
        parent::__construct($params);
    }

    public function mysql_delete() {
        $this->delete_object_links(CustomSectionVariable::get_mysql_table());
        $this->mysql_delete_object();
    }

    public function get_CustomSectionVariables() {
        return CustomSectionVariable::from_property(array("CustomSection_id" => $this->id));
    }

    public function get_parameter_CustomSectionVariables() {
        return CustomSectionVariable::from_property(array("CustomSection_id" => $this->id, "type" => 0));
    }

    public function get_return_CustomSectionVariables() {
        return CustomSectionVariable::from_property(array("CustomSection_id" => $this->id, "type" => 1));
    }

    public function mysql_save_from_post($post) {
        $lid = parent::mysql_save_from_post($post);

        if ($this->id != 0) {
            $this->delete_object_links(CustomSectionVariable::get_mysql_table());
            $i = 0;
            if (array_key_exists("parameters", $post)) {
                foreach ($post["parameters"] as $param) {
                    $p = json_decode($param);
                    $var = new CustomSectionVariable();
                    $var->description = $p->description;
                    $var->name = $p->name;
                    $var->index = $i;
                    $var->type = 0;
                    $var->CustomSection_id = $lid;
                    $var->mysql_save();
                    $i++;
                }
            }
            if (array_key_exists("returns", $post)) {
                foreach ($post["returns"] as $ret) {
                    $r = json_decode($ret);
                    $var = new CustomSectionVariable();
                    $var->description = $r->description;
                    $var->name = $r->name;
                    $var->index = $i;
                    $var->type = 1;
                    $var->CustomSection_id = $lid;
                    $var->mysql_save();
                    $i++;
                }
            }
        }
    }
    
    public function export()
    {
        $xml = new DOMDocument();

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $group = $xml->createElement("CustomSections");
        $export->appendChild($group);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $group->appendChild($obj);

        return $xml->saveXML();
    }

    public function import($path)
    {
        $xml = new DOMDocument();
        $xml->load($path);

        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);
        $elements = $xpath->query("/export/CustomSections/CustomSection");
        foreach ($elements as $element)
        {
            $children = $element->childNodes;
            foreach ($children as $child)
            {
                switch ($child->nodeName)
                {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "description": $this->description = $child->nodeValue;
                        break;
                    case "code": $this->code = $child->nodeValue;
                        break;
                }
            }
        }
        
        $lid = $this->mysql_save();
        
        $elements = $xpath->query("/export/CustomSections/CustomSection/CustomSectionVariables/CustomSectionVariable");
        foreach ($elements as $element)
        {
            $obj = new CustomSectionVariable();
            $obj->CustomSection_id=$lid;
            $children = $element->childNodes;
            foreach ($children as $child)
            {
                switch ($child->nodeName)
                {
                    case "name": $obj->name = $child->nodeValue;
                        break;
                    case "description": $obj->description = $child->nodeValue;
                        break;
                    case "index": $obj->index = $child->nodeValue;
                        break;
                    case "type": $obj->type = $child->nodeValue;
                        break;
                }
            }
            $obj->mysql_save();
        }
        return $lid;
    }

    public function to_XML()
    {
        $xml = new DOMDocument();

        $element = $xml->createElement("CustomSection");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES));
        $element->appendChild($id);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES));
        $element->appendChild($description);
        
        $code = $xml->createElement("code", htmlspecialchars($this->code, ENT_QUOTES));
        $element->appendChild($code);
        
        $csv = $xml->createElement("CustomSectionVariables");
        $element->appendChild($csv);
        
        $elems = $this->get_CustomSectionVariables();
        foreach($elems as $elem)
        {
            $e = $elem->to_XML();
            $e = $xml->importNode($e,true);
            
            $csv->appendChild($e);
        }

        return $element;
    }
}

?>