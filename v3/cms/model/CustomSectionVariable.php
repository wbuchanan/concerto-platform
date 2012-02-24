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

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `CustomSectionVariable`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `CustomSectionVariable` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `index` int(11) NOT NULL,
            `CustomSection_id` bigint(20) NOT NULL,
            `name` text NOT NULL,
            `description` text NOT NULL,
            `type` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }
}

?>