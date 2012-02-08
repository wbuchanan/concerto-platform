<?php

class TestSectionValue extends OTable
{
    public $TestSection_id = 0;
    public $index = 0;
    public $value = "";
    public static $mysql_table_name = "TestSectionValue";

    public function get_TestSection()
    {
        return TestSection::from_mysql_id($this->TestSection_id);
    }

    public function to_XML()
    {
        $xml = new DOMDocument();
        
        $tsv = $xml->createElement("TestSectionValue");
        $xml->appendChild($tsv);
        
        $index = $xml->createElement("index",$this->index);
        $tsv->appendChild($index);
        
        $value = $xml->createElement("value",htmlspecialchars($this->value, ENT_QUOTES));
        $tsv->appendChild($value);
        
        return $tsv;
    }
}

?>