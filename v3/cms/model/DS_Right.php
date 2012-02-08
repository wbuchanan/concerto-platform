<?php

class DS_Right extends ODataSet
{
    public static $mysql_table_name = "DS_Right";

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(73);
            case 2: return Language::string(100);
            case 3: return Language::string(168);
            case 4: return Language::string(101);
            case 5: return Language::string(169);
        }
    }

}

?>