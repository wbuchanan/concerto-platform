<?php

class ODataSet extends OTable {

    public $name = "";
    public $value = "";
    public $position = 0;

    public static function get_all($sort="`position` ASC") {
        $res = array();
        $sql = sprintf("SELECT * FROM `%s` ORDER BY %s", static::get_mysql_table(), $sort);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            array_push($res, static::from_mysql_result($r));
        }
        return $res;
    }

    public static function from_value($value) {
        return self::from_property(array("value" => $value), false);
    }

}

?>