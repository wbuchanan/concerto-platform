<?php

class DS_Sharing extends ODataSet
{
    public static $mysql_table_name = "DS_Sharing";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(100);
            case 2: return Language::string(101);
            case 3: return Language::string(102);
        }
    }

}

?>