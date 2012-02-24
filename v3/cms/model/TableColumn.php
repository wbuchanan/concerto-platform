<?php

class TableColumn extends OTable
{
    public $index = 0;
    public $name = "";
    public $Table_id = 0;
    public $TableColumnType_id = 0;
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

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `TableColumn`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TableColumn` (
            `id` bigint(20) NOT NULL auto_increment,
            `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `udpated` timestamp NOT NULL default '0000-00-00 00:00:00',
            `index` int(11) NOT NULL,
            `name` text NOT NULL,
            `Table_id` bigint(20) NOT NULL,
            `TableColumnType_id` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>