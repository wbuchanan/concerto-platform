<?php

class UserType extends OModule {

    public $name = "";
    public static $mysql_table_name = "UserType";
    
    public function __construct($params = array())
    {
        $this->name = Language::string(80);
        parent::__construct($params);
    }

    public function get_rights() {
        return UserTypeRight::from_property(array(
                    "UserType_id" => $this->id
                ));
    }

    public function mysql_delete() {
        $this->clear_object_links(User::get_mysql_table());

        $utr = UserTypeRight::from_property(array(
                    "UserType_id" => $this->id
                ));
        foreach ($utr as $obj)
            $obj->mysql_delete();

        $this->mysql_delete_object();
    }

    public function mysql_save_from_post($post) {
        $post['oid'] = parent::mysql_save_from_post($post);

        $obj = self::from_mysql_id($post['oid']);

        if (array_key_exists('ids',$post) && array_key_exists('values',$post) && array_key_exists('rws',$post)) {
            for ($i = 0; $i < count($post['ids']); $i++) {
                $id = $post['ids'][$i];
                $val = $post['values'][$i];
                $rw = $post['rws'][$i];

                if ($id != "" && $val != "" && $rw != "") {
                    $right = UserTypeRight::from_property(array(
                                "UserType_id" => $obj->id,
                                "Module_id" => $id
                                    ), false);
                    if ($right == null) {
                        $right = new UserTypeRight();
                        $right->UserType_id = $obj->id;
                        $right->Module_id = $id;
                    }

                    if ($rw == "r")
                        $right->read = $val;
                    if ($rw == "w")
                        $right->write = $val;
                    if ($rw == "o")
                        $right->ownership = $val;
                    $right->mysql_save();
                }
            }
        }
        return $post['oid'];
    }

    public function get_rights_by_module($Module_id) {
        foreach ($this->get_rights() as $right) {
            if ($right->Module_id == $Module_id)
                return $right;
        }

        $right = new UserTypeRight();
        $right->Module_id = $Module_id;
        $right->UserType_id = $this->id;
        return $right;
    }

    public function get_rights_by_module_table($table_name) {
        $Module_id = 0;
        $module = DS_Module::from_value($table_name);
        if ($module != null)
            $Module_id = $module->id;
        return $this->get_rights_by_module($Module_id);
    }
}

?>