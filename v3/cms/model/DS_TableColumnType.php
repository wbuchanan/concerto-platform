<?php

class DS_TableColumnType extends ODataSet
{
    public static $mysql_table_name = "DS_TableColumnType";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(16);
            case 2: return Language::string(17);
            case 3: return Language::string(18);
        }
    }

}

?>