<?php

class CustomSectionVariable extends OTable
{
    public $CustomSection_id = 0;
    public $index = 0;
    public $name = "";
    public $description = "";
    public $type = 0;
    public static $mysql_table_name = "CustomSectionVariable";

    public function get_CustomSection()
    {
        return CustomSection::from_mysql_id($this->CustomSection_id);
    }

    public function to_XML()
    {
        $xml = new DOMDocument();

        $element = $xml->createElement("CustomSectionVariable");
        $xml->appendChild($element);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES));
        $element->appendChild($name);

        $description = $xml->createElement("description", htmlspecialchars($this->description, ENT_QUOTES));
        $element->appendChild($description);
        
        $index = $xml->createElement("index", htmlspecialchars($this->index, ENT_QUOTES));
        $element->appendChild($index);
        
        $type = $xml->createElement("type", htmlspecialchars($this->type, ENT_QUOTES));
        $element->appendChild($type);

        return $element;
    }
}

?>