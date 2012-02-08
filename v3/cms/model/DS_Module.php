<?php

class DS_Module extends ODataSet
{
    public static $mysql_table_name = "DS_Module";

    public function mysql_delete()
    {
        $utr = UserTypeRight::from_property(array(
                    "Module_id" => $this->id
                ));
        foreach ($utr as $obj) $obj->mysql_delete();

        $this->mysql_delete_object();
    }

    public function get_name()
    {
        switch ($this->id)
        {
            case 1: return Language::string(167);
            case 2: return Language::string(85);
            case 3: return Language::string(89);
            case 4: return Language::string(91);
            case 5: return Language::string(90);
            case 6: return Language::string(88);
            case 7: return Language::string(84);
        }
    }

}

?>