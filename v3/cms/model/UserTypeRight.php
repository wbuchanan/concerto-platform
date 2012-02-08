<?php

class UserTypeRight extends OTable {

    public $UserType_id = 0;
    public $Module_id = 0;
    public $read = 1;
    public $write = 1;
    public $ownership = 1;
    public static $mysql_table_name = "UserTypeRight";

    public function get_UserType() {
        return UserType::from_mysql_id($this->UserType_id);
    }

}

?>