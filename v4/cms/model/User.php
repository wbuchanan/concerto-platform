<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class User extends OModule {

    public $login = "incognito";
    public $firstname = "unknown";
    public $lastname = "";
    public $email = "";
    public $phone = "";
    public $password = "";
    public $last_login = "";
    public $UserInstitutionType_id = 0;
    public $institution_name = "";
    public $db_login = "";
    public $db_password = "";
    public $db_name = "";
    public $superuser = 0;
    public static $mysql_table_name = "User";
    public static $is_master_table = true;

    public function __construct($params = array()) {
        $this->login = Language::string(77);
        $this->firstname = Language::string(78);
        $this->lastname = Language::string(78);
        $this->email = Language::string(78);
        parent::__construct($params);
    }

    public function get_UserR() {
        return UserR::from_property(array("User_id" => $this->id), false);
    }

    public function get_full_description() {
        return $this->id . ". " . $this->firstname . " " . $this->lastname . " ( " . $this->institution_name . " )";
    }

    public function is_workspace_accessible($db) {
        if ($this->superuser == 1)
            return true;
        $shares = UserShare::from_property(array("invitee_id" => $this->id));
        foreach ($shares as $share) {
            $user = User::from_mysql_id($share->owner_id);
            if ($user->db_name == $db)
                return true;
        }
        return false;
    }

    public static function change_workspace($db) {
        $_SESSION['ptap_current_db'] = $db;
    }

    public static function is_simple_view() {
        if (array_key_exists("ptap_view", $_SESSION)) {
            return $_SESSION['ptap_view'] == 0;
        } else {
            $_SESSION['ptap_view'] = 0;
            return true;
        }
    }

    public static function view_class($reverse = false) {
        if ($reverse) {
            if (self::is_simple_view())
                return"viewReverslyDependant";
            else
                return "notVisible viewReverslyDependant";
        } else {
            if (self::is_simple_view())
                return"notVisible viewDependant";
            else
                return "viewDependant";
        }
    }

    public static function set_view($view) {
        $_SESSION['ptap_view'] = $view;
    }

    public static function recover_password($id, $hash) {
        $user = User::from_mysql_id($id);
        if ($user == null)
            return false;
        if ($hash != $user->calculate_password_recovery_hash())
            return false;
        $pass = User::get_temp_password();
        $user->password = $user->calculate_raw_password_hash($pass);
        $user->mysql_save();
        User::mail_utf8($user->email, "no-reply@concerto.e-psychometrics.com", Language::string(428), sprintf(Language::string(430), $pass));
        return true;
    }

    public function calculate_raw_password_hash($password) {
        $hash = $password;
        for ($i = 0; $i < 5000; $i++) {
            $hash = hash("sha512", $this->login . "-" . $hash);
        }
        return $this->calculate_password_hash($hash);
    }

    public function calculate_password_hash($hash) {
        for ($i = 0; $i < 5000; $i++) {
            $hash = hash("sha512", $hash . "-" . $this->id . "-" . $this->login . "-" . $this->last_login);
        }
        return $hash;
    }

    public function calculate_password_recovery_hash() {
        for ($i = 0; $i < 5000; $i++) {
            $hash = hash("sha512", $this->id . "-" . $this->login . "-" . $this->email . "-" . $this->last_login . "-" . $this->password);
        }
        return $hash;
    }

    public static function mail_utf8($to, $from_email, $subject = '(No subject)', $message = '') {
        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

        $headers = "From: $from_email\r\n" .
                "MIME-Version: 1.0" . "\r\n" .
                "Content-type: text/html; charset=UTF-8" . "\r\n";

        return mail($to, $subject, $message, $headers);
    }

    public static function get_temp_password() {
        return rand(1000, 9999);
    }

    public static function generate_password() {
        return md5(time() . rand(1000000000000000, 99999999999999999) . "ur");
    }

    public function get_UserInstitutionType() {
        return DS_UserInstitutionType::from_mysql_id($this->UserInstitutionType_id);
    }

    public function get_shares() {
        return UserShare::from_property(array("owner_id" => $this->id));
    }

    public static function get_logged_user() {
        if (isset($_SESSION['ptap_logged_login']) && isset($_SESSION['ptap_logged_password'])) {
            $user = self::from_property(array(
                        "login" => $_SESSION['ptap_logged_login'],
                        "password" => $_SESSION['ptap_logged_password']
                            ), false);
            if ($user != null)
                return $user;
        }
        return null;
    }

    public static function get_current_db() {
        $logged_user = @self::get_logged_user();

        if ($logged_user == null)
            return null;

        if (!$logged_user->is_workspace_accessible($_SESSION['ptap_current_db'])) {
            return $logged_user->db_name;
        }
        return $_SESSION['ptap_current_db'];
    }

    public static function get_all_db() {
        $result = array();
        $sql = sprintf("SELECT `db_name` FROM `%s`.`%s`", Ini::$db_master_name, User::get_mysql_table());
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            array_push($result, $r['db_name']);
        }
        return $result;
    }

    public static function log_in($login, $password) {
        $user = self::from_property(array(
                    "login" => $login
                        ), false);
        if ($user != null) {
            if ($user->calculate_password_hash($password) != $user->password)
                return null;

            $user->last_login = date("Y-m-d H:i:s");
            $hash = $user->calculate_password_hash($password);
            $user->password = $hash;
            $user->mysql_save();

            $_SESSION['ptap_logged_login'] = $login;
            $_SESSION['ptap_logged_password'] = $hash;
            $_SESSION['ptap_current_db'] = $user->db_name;
            $_SESSION['ptap_view'] = 0;
        }
        return $user;
    }

    public static function log_out() {
        unset($_SESSION['ptap_logged_login']);
        unset($_SESSION['ptap_logged_password']);
        unset($_SESSION['ptap_current_db']);
        unset($_SESSION['ptap_view']);
    }

    public function get_last_login() {
        $datetime = explode(" ", $this->last_login);
        if ($datetime[0] == "0000-00-00")
            $datetime[0] = "&lt;" . Language::string(73) . "&gt;";
        return $datetime[0];
    }

    public function get_full_name() {
        $name = $this->firstname . " " . $this->lastname;
        if (trim($name) == "")
            $name = "&lt;" . $this->email . "&gt;";
        return $name;
    }

    public function mysql_delete() {
        $this->remove_db_user();
        $this->remove_shares();
        $this->mysql_delete_object();
    }

    public function remove_shares() {
        $this->delete_object_links(UserShare::get_mysql_table(), "owner_id");
        $this->delete_object_links(UserShare::get_mysql_table(), "invitee_id");
    }

    public function mysql_save_from_post($post) {
        $is_new = $this->id == 0;
        if (array_key_exists("superuser", $post)) {
            $logged_user = User::get_logged_user();
            if ($logged_user == null || $logged_user->superuser == 0)
                $post['superuser'] = 0;
        }
        $post['oid'] = parent::mysql_save_from_post($post);
        $obj = User::from_mysql_id($post['oid']);
        if ($post['modify_password'] == 1) {
            $obj->password = $obj->calculate_password_hash($post['password_hash']);
            $obj->mysql_save();
        }

        if (array_key_exists("deleteShare", $post)) {
            $rows = json_decode($post["deleteShare"]);
            foreach ($rows as $row) {
                $sql = sprintf("DELETE FROM `%s`.`%s` WHERE id='%s' AND `owner_id`='%s'", Ini::$db_master_name, UserShare::get_mysql_table(), mysql_real_escape_string($row), mysql_real_escape_string($this->id));
                if (!mysql_query($sql))
                    return json_encode(array("result" => -6, "message" => mysql_error()));
            }
        }

        if (array_key_exists("updateShare", $post)) {
            $rows = json_decode($post["updateShare"], true);
            foreach ($rows as $row) {

                if ($row["id"] != 0) {
                    $sql = sprintf("UPDATE `%s`.`%s` SET `invitee_id`='%s' WHERE `id`=%s", Ini::$db_master_name, UserShare::get_mysql_table(), mysql_real_escape_string($row["invitee_id"]));
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                } else {
                    $sql = sprintf("INSERT INTO `%s`.`%s` SET `owner_id`='%s', `invitee_id`='%s'", Ini::$db_master_name, UserShare::get_mysql_table(), mysql_real_escape_string($this->id), mysql_real_escape_string($row['invitee_id']));
                    if (!mysql_query($sql))
                        return json_encode(array("result" => -6, "message" => mysql_error()));
                }
            }
        }

        if ($is_new) {
            $obj->create_db_user();
        }

        return $post['oid'];
    }

    public function create_db_user() {
        $user = Ini::$db_users_name_prefix . $this->id;
        $password = self::generate_password();
        $db_name = Ini::$db_users_db_name_prefix . $this->id;
        $sql = sprintf("CREATE USER '%s'@'localhost' IDENTIFIED BY '%s';", $user, $password);
        mysql_query($sql);
        $this->db_login = $user;
        $this->db_password = $password;
        $this->db_name = $db_name;
        $this->mysql_save();

        $sql = sprintf("CREATE DATABASE `%s` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", $db_name);
        mysql_query($sql);

        $sql = sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost'", $db_name, $user);
        mysql_query($sql);

        Ini::create_db_structure();
    }

    public function remove_db_user() {
        $sql = sprintf("DROP DATABASE `%s`", $this->db_name);
        mysql_query($sql);

        $sql = sprintf("DROP USER '%s'@'localhost'", $this->db_login);
        mysql_query($sql);
    }

    public function get_session_count() {
        $sql = sprintf("SELECT SUM(`%s`.`Test`.`session_count`) 
            FROM `%s`.`Test`", $this->db_name, $this->db_name);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            return $r[0];
        }
        return 0;
    }

    public static function get_list_columns() {
        $cols = parent::get_list_columns();

        array_push($cols, array(
            "name" => Language::string(173),
            "property" => "login",
            "searchable" => true,
            "sortable" => true,
            "type" => "string",
            "groupable" => false,
            "show" => true
        ));
        array_push($cols, array(
            "name" => Language::string(174),
            "property" => "email",
            "searchable" => true,
            "sortable" => true,
            "type" => "string",
            "groupable" => true,
            "show" => true
        ));
        array_push($cols, array(
            "name" => Language::string(175),
            "property" => "get_last_login",
            "searchable" => true,
            "sortable" => true,
            "type" => "string",
            "groupable" => false,
            "show" => true
        ));
        array_push($cols, array(
            "name" => Language::string(335),
            "property" => "get_session_count",
            "searchable" => true,
            "sortable" => true,
            "type" => "number",
            "groupable" => false,
            "width" => 120,
            "show" => true
        ));

        for ($i = 0; $i < count($cols); $i++) {
            if ($cols[$i]["property"] == "name") {
                array_splice($cols, $i, 1);
                $i--;
            }
        }

        return $cols;
    }

    public static function create_db($db = null) {
        if ($db == null)
            $db = Ini::$db_master_name;
        $sql = sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`User` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `login` text NOT NULL,
            `firstname` text NOT NULL,
            `lastname` text NOT NULL,
            `email` text NOT NULL,
            `phone` text NOT NULL,
            `password` text NOT NULL,
            `last_login` timestamp NOT NULL default '0000-00-00 00:00:00',
            `UserInstitutionType_id` int(11) NOT NULL,
            `institution_name` text NOT NULL,
            `db_login` text NOT NULL,
            `db_password` text NOT NULL,
            `db_name` text NOT NULL,
            `superuser` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ", $db);
        if (!mysql_query($sql))
            return false;

        $admin_data = array(
            "updated" => time(),
            "created" => time(),
            "login" => "admin",
            "firstname" => "unknown",
            "modify_password" => 0
        );

        $admin = new User();
        $lid = $admin->mysql_save_from_post($admin_data);

        $admin = User::from_mysql_id($lid);
        $admin->password = $admin->calculate_raw_password_hash("admin");
        $admin->superuser = 1;
        return $admin->mysql_save();
    }

}

?>