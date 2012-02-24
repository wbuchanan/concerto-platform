<?php

class Template extends OModule
{
    public $name = "";
    public $HTML = "";
    public static $exportable = true;
    public static $mysql_table_name = "Template";

    public function __construct($params = array())
    {
        $this->name = Language::string(75);
        parent::__construct($params);
    }

    public function get_inserts()
    {
        $inserts = array();
        $html = $this->HTML;
        while (strpos($html, "{{") !== false)
        {
            $html = substr($html, strpos($html, "{{") + 2);
            if (strpos($html, "}}") !== false)
            {
                $name = substr($html, 0, strpos($html, "}}"));
                if($name=="TIME_LEFT") continue;
                if (!in_array($name, $inserts)) array_push($inserts, $name);
            }
        }
        return $inserts;
    }

    public function get_insert_reference($name, $vals)
    {
        $inserts = $this->get_inserts();
        $j = 3;
        foreach ($inserts as $ins)
        {
            if ($ins == "TIME_LEFT") continue;

            if ($ins == $name) return $vals[$j];

            $j++;
        }
        return $name;
    }

    public function get_return_reference($name, $vals)
    {
        $returns = $this->get_outputs();
        $j = 3 + $vals[1];
        foreach ($returns as $ret)
        {
            if ($ret["name"] == $name) return $vals[$j];

            $j = $j + 3;
        }
        return $name;
    }

    public function get_return_visibility($name, $vals)
    {
        $returns = $this->get_outputs();
        $j = 3 + $vals[1];
        foreach ($returns as $ret)
        {
            if ($ret["name"] == $name) return $vals[$j + 1];

            $j = $j + 3;
        }
        return 2;
    }

    public function get_return_type($name, $vals)
    {
        $returns = $this->get_outputs();
        $j = 3 + $vals[1];
        foreach ($returns as $ret)
        {
            if ($ret["name"] == $name) return $vals[$j + 2];

            $j = $j + 3;
        }
        return 0;
    }

    public function get_html_with_return_properties($vals)
    {
        $html = str_get_html($this->HTML);
        $outputs = $this->get_outputs();

        foreach ($outputs as $out)
        {
            $elems = $html->find("[name='" . $out["name"] . "']");
            foreach ($elems as $elem)
            {
                $elem->setAttribute("returnvisibility", $this->get_return_visibility($elem->getAttribute("name"), $vals));
                $elem->setAttribute("returntype", $this->get_return_type($elem->getAttribute("name"), $vals));
                $elem->setAttribute("name", $this->get_return_reference($elem->getAttribute("name"), $vals));
            }
        }
        return $html->save();
    }

    public function get_outputs()
    {
        $names = array();
        $outputs = array();
        $html_string = $this->HTML;
        if (empty($html_string)) $html_string = "<p></p>";
        $html = str_get_html($html_string);
        foreach ($html->find('input[type="text"]') as $element)
        {
            if (!in_array($element->name, $names))
            {
                array_push($outputs, array("name" => $element->name, "type" => $element->type));
                array_push($names, $element->name);
            }
        }
        foreach ($html->find('input[type="password"]') as $element)
        {
            if (!in_array($element->name, $names))
            {
                array_push($outputs, array("name" => $element->name, "type" => $element->type));
                array_push($names, $element->name);
            }
        }
        foreach ($html->find('input[type="checkbox"]') as $element)
        {
            if (!in_array($element->name, $names))
            {
                array_push($outputs, array("name" => $element->name, "type" => $element->type));
                array_push($names, $element->name);
            }
        }
        foreach ($html->find('input[type="radio"]') as $element)
        {
            $exists = false;
            foreach ($outputs as $elem)
            {
                if ($elem["name"] == $element->name && $elem["type"] == $element->type)
                {
                    $exists = true;
                    break;
                }
            }
            if (!$exists)
            {
                if (!in_array($element->name, $names))
                {
                    array_push($outputs, array("name" => $element->name, "type" => $element->type));
                    array_push($names, $element->name);
                }
            }
        }
        foreach ($html->find('textarea') as $element)
        {
            if (!in_array($element->name, $names))
            {
                array_push($outputs, array("name" => $element->name, "type" => $element->tag));
                array_push($names, $element->name);
            }
        }
        foreach ($html->find('select') as $element)
        {
            if (!in_array($element->name, $names))
            {
                array_push($outputs, array("name" => $element->name, "type" => $element->tag));
                array_push($names, $element->name);
            }
        }
        return $outputs;
    }

    public function export()
    {
        $xml = new DOMDocument();

        $export = $xml->createElement("export");
        $export->setAttribute("version", Ini::$version);
        $xml->appendChild($export);

        $group = $xml->createElement("Templates");
        $export->appendChild($group);

        $element = $this->to_XML();
        $obj = $xml->importNode($element, true);
        $group->appendChild($obj);

        return $xml->saveXML();
    }

    public function import($path)
    {
        $xml = new DOMDocument();
        if(!$xml->load($path)) return -4;

        $this->Sharing_id = 1;

        $xpath = new DOMXPath($xml);
        $elements = $xpath->query("/export/Templates/Template");
        foreach ($elements as $element)
        {
            $children = $element->childNodes;
            foreach ($children as $child)
            {
                switch ($child->nodeName)
                {
                    case "name": $this->name = $child->nodeValue;
                        break;
                    case "HTML": $this->HTML = $child->nodeValue;
                        break;
                }
            }
        }
        return $this->mysql_save();
    }

    public function to_XML()
    {
        $xml = new DOMDocument();

        $element = $xml->createElement("Template");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES));
        $element->appendChild($id);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES));
        $element->appendChild($name);

        $HTML = $xml->createElement("HTML", htmlspecialchars($this->HTML, ENT_QUOTES));
        $element->appendChild($HTML);

        return $element;
    }

}

?>