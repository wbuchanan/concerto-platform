<?php

class Setting extends OTable
{
    public static $mysql_table_name = "UserTypeRight";
    
    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `Setting`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `Setting` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` TEXT NOT NULL ,
            `value` TEXT NOT NULL
            ) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;
            ";
        if (!mysql_query($sql)) return false;
        $sql = "
            INSERT INTO `Setting` (`name`, `value`) VALUES ('version','" . Ini::$version . "');
            ";
        return mysql_query($sql);
    }

    public static function get_setting($name)
    {
        $sql = sprintf("SELECT `value` FROM `Setting` WHERE `name`='%s'", $name);
        $z = @mysql_query($sql);
        if(!$z) return null;
        while ($r = mysql_fetch_array($z)) return $r[0];
        return null;
    }

    public static function set_setting($name, $value)
    {
        $sql = sprintf("UPDATE `Setting` SET `value`='%s' WHERE `name`='%s'", $value, $name);
        if(!mysql_query($sql)) return false;
        return true;
    }

}
?>
