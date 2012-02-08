<?php

class TableColumn extends OTable
{
    public $index = 0;
    public $name = "";
    public $Table_id = 0;
    public $TableColumnType_id=0;
    public static $mysql_table_name = "TableColumn";
    
    public function get_Table()
    {
        return Table::from_mysql_id($this->Table_id);
    }
    
    public function get_TableColumnType()
    {
        return DS_TableColumnType::from_mysql_id($this->TableColumnType_id);
    }

    public function to_XML()
    {
        $xml = new DOMDocument();

        $element = $xml->createElement("TableColumn");
        $xml->appendChild($element);

        $id = $xml->createElement("id", htmlspecialchars($this->id, ENT_QUOTES));
        $element->appendChild($id);
        
        $index = $xml->createElement("index", htmlspecialchars($this->index, ENT_QUOTES));
        $element->appendChild($index);

        $name = $xml->createElement("name", htmlspecialchars($this->name, ENT_QUOTES));
        $element->appendChild($name);
        
        $type = $xml->createElement("TableColumnType_id", htmlspecialchars($this->TableColumnType_id, ENT_QUOTES));
        $element->appendChild($type);

        return $element;
    }
}

?>