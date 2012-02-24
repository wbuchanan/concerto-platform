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

        $index = $xml->createElement("index", $this->index);
        $tsv->appendChild($index);

        $value = $xml->createElement("value", htmlspecialchars($this->value, ENT_QUOTES));
        $tsv->appendChild($value);

        return $tsv;
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSectionValue`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSectionValue` (
            `id` bigint(20) NOT NULL auto_increment,
            `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `updated` timestamp NOT NULL default '0000-00-00 00:00:00',
            `TestSection_id` bigint(20) NOT NULL,
            `index` int(11) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>