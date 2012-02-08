<?php

class OModule extends OTable
{
    public $Owner_id = 0;
    public $Sharing_id = 1;
    public static $exportable = false;

    public function get_Owner()
    {
        return User::from_mysql_id($this->Owner_id);
    }

    public function has_Owner()
    {
        if ($this->get_Owner() != null) return true;
        else return false;
    }

    public function get_Sharing()
    {
        return DS_Sharing::from_mysql_id($this->Sharing_id);
    }

    public function get_list_column_value($index)
    {
        $cols = static::get_list_columns();

        foreach (get_object_vars($this) as $k => $v)
        {
            if ($cols[$index]["property"] == $k)
            {
                return ($v != null ? $v : "&lt;" . Language::string(73) . "&gt;");
            }
        }

        foreach (get_class_methods($this) as $k)
        {
            if ($cols[$index]["property"] == $k)
            {
                $val = call_user_func_array(array($this, $k), array());
                return ($val != null ? $val : "&lt;" . Language::string(73) . "&gt;");
            }
        }

        return "&lt;" . Language::string(73) . "&gt;";
    }

    public static function get_searchable_list_columns_indexes()
    {
        $cols = static::get_list_columns();
        $searchables = array();
        for ($i = 0; $i < count($cols); $i++)
        {
            if ($cols[$i]["searchable"]) array_push($searchables, $i);
        }
        return implode(",", $searchables);
    }

    public function get_owner_full_name()
    {
        $owner = $this->get_Owner();
        if ($owner == null) return "&lt;" . Language::string(73) . "&gt;";
        return $owner->get_full_name();
    }

    public function get_sharing_name()
    {
        $share = $this->get_Sharing();
        if ($share == null) return null;
        return $share->get_name();
    }

    public function get_system_data()
    {
        return Language::string(69) . ": " . $this->id . ($this->has_Owner() ? ", " . Language::string(71) . ": " . $this->get_owner_full_name() : "");
    }

    public static function get_list_columns()
    {
        $cols = array();

        array_push($cols, array(
            "name" => Language::string(69),
            "property" => "id",
            "searchable" => true,
            "sortable" => true
        ));
        array_push($cols, array(
            "name" => Language::string(70),
            "property" => "name",
            "searchable" => true,
            "sortable" => true
        ));
        array_push($cols, array(
            "name" => Language::string(71),
            "property" => "get_owner_full_name",
            "searchable" => true,
            "sortable" => true
        ));
        array_push($cols, array(
            "name" => Language::string(72),
            "property" => "get_sharing_name",
            "searchable" => true,
            "sortable" => true
        ));

        return $cols;
    }

}

?>