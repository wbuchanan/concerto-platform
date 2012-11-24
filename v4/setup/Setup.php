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

class Setup {

    public static function get_db_update_steps_count() {
        return self::update_db(true);
    }

    public static function update_db_validate_column_names() {
        return self::update_db(false, false, true);
    }

    public static function create_db() {
        return self::update_db(false, false, false, true);
    }

    public static function update_db_recalculate_hash() {
        return self::update_db(false, true, true);
    }

    public static function php_version_check() {
        $v = phpversion();
        $nums = explode(".", $v);
        if ($nums[0] < 5)
            return json_encode(array("result" => 1, "param" => $v));
        if ($nums[0] == 5 && $nums[1] < 3)
            return json_encode(array("result" => 1, "param" => $v));
        if ($nums[0] == 5 && $nums[1] >= 3)
            return json_encode(array("result" => 0, "param" => $v));
        if ($nums[0] > 5)
            return json_encode(array("result" => 0, "param" => $v));
    }

    public static function php_safe_mode_check() {
        return json_encode(array("result" => !ini_get("safe_mode") ? 0 : 1, "param" => ini_get("safe_mode")));
    }

    public static function php_magic_quotes_check() {
        return json_encode(array("result" => !ini_get("magic_quotes_gpc") ? 0 : 1, "param" => ini_get("magic_quotes_gpc")));
    }

    public static function php_short_open_tag_check() {
        return json_encode(array("result" => ini_get("short_open_tag") ? 0 : 1, "param" => ini_get("magic_quotes_gpc")));
    }

    public static function php_exe_path_check() {
        require '../Ini.php';
        $ini = new Ini();
        return self::file_paths_check(Ini::$path_php_exe);
    }

    public static function R_exe_path_check() {
        require '../Ini.php';
        $ini = new Ini();
        return self::file_paths_check(Ini::$path_r_exe);
    }

    public static function file_paths_check($path) {
        if (file_exists($path) && is_file($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function directory_paths_check($path) {
        if (file_exists($path) && is_dir($path))
            return true;
        else
            return false;
    }

    public static function directory_writable_check($path) {
        if (self::directory_paths_check($path) && is_writable($path))
            return true;
        else
            return false;
    }

    public static function media_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        if (self::directory_writable_check(Ini::$path_internal_media))
            return json_encode(array("result" => 0, "param" => Ini::$path_internal_media));
        else
            return json_encode(array("result" => 1, "param" => Ini::$path_internal_media));
    }

    public static function socks_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        if (self::directory_writable_check(Ini::$path_unix_sock_dir))
            return json_encode(array("result" => 0, "param" => Ini::$path_unix_sock_dir));
        else
            return json_encode(array("result" => 1, "param" => Ini::$path_unix_sock_dir));
    }

    public static function temp_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        if (self::directory_writable_check(Ini::$path_temp))
            return json_encode(array("result" => 0, "param" => Ini::$path_temp));
        else
            return json_encode(array("result" => 1, "param" => Ini::$path_temp));
    }

    public static function files_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        $path = Ini::$path_internal . "cms/js/lib/fileupload/php/files";
        if (self::directory_writable_check($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function thumbnails_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        $path = Ini::$path_internal . "cms/js/lib/fileupload/php/thumbnails";
        if (self::directory_writable_check($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function cache_directory_writable_check() {
        require '../Ini.php';
        $ini = new Ini();
        $path = Ini::$path_internal . "cms/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache";
        if (self::directory_writable_check($path))
            return json_encode(array("result" => 0, "param" => $path));
        else
            return json_encode(array("result" => 1, "param" => $path));
    }

    public static function rscript_check() {
        require '../Ini.php';
        $ini = new Ini();
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e 1+1', $array, $return);
        return json_encode(array("result" => $return, "param" => Ini::$path_r_script));
    }

    public static function r_version_check() {
        $version = Setup::get_r_version();
        $elems = explode(".", $version);
        if ($elems[0] > 2)
            return json_encode(array("result" => 0, "param" => $version));
        if ($elems[0] == 2) {
            if ($elems[1] >= 15)
                return json_encode(array("result" => 0, "param" => $version));
        }
        return json_encode(array("result" => 1, "param" => $version));
    }

    public static function get_r_version() {
        require '../Ini.php';
        $ini = new Ini();
        $output = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e version', $output, $return);
        $version = str_replace(" ", "", str_replace("major", "", $output[6])) . "." . str_replace(" ", "", str_replace("minor", "", $output[7]));
        return $version;
    }

    public static function mysql_connection_check() {
        include'../SETTINGS.php';
        if (@mysql_connect($db_host . ":" . $db_port, $db_user, $db_password))
            return json_encode(array("result" => 0, "param" => "Host: <b>$db_host</b>, Port: <b>$db_port</b>, Login: <b>$db_user</b>"));
        else
            return json_encode(array("result" => 1, "param" => "Host: <b>$db_host</b>, Port: <b>$db_port</b>, Login: <b>$db_user</b>"));;
    }

    public static function mysql_select_db_check() {
        include'../SETTINGS.php';
        Setup::mysql_connection_check();
        if (@mysql_select_db($db_name))
            return json_encode(array("result" => 0, "param" => $db_name));
        else
            return json_encode(array("result" => 1, "param" => $db_name));
    }

    public static function r_package_check($package) {
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e "library(' . $package . ')"', $array, $return);
        return ($return == 0);
    }

    public static function catR_r_package_check() {
        require '../Ini.php';
        $ini = new Ini();
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e "library(catR)"', $array, $return);
        return json_encode(array("result" => $return, "param" => "catR"));
    }

    public static function RMySQL_r_package_check() {
        require '../Ini.php';
        $ini = new Ini();
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e "library(RMySQL)"', $array, $return);
        return json_encode(array("result" => $return, "param" => "RMySQL"));
    }

    public static function session_r_package_check() {
        require '../Ini.php';
        $ini = new Ini();
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e "library(session)"', $array, $return);
        return json_encode(array("result" => $return, "param" => "session"));
    }

    public static function create_db_structure($simulate = false) {
        foreach (Ini::get_system_tables() as $table) {
            $sql = sprintf("SHOW TABLES LIKE '%s'", $table);
            $z = mysql_query($sql);
            if (mysql_num_rows($z) == 0) {
                if ($simulate) {
                    return true;
                } else {
                    if (!$table::create_db())
                        return json_encode(array("result" => 1, "param" => $table));
                }
            }
        }
        if ($simulate) {
            return false;
        }
        return json_encode(array("result" => 0));
    }

    public static function reset_db() {
        CustomSection::create_db(true);
        CustomSectionVariable::create_db(true);
        DS_Module::create_db(true);
        DS_Right::create_db(true);
        DS_Sharing::create_db(true);
        DS_UserInstitutionType::create_db(true);
        Setting::create_db(true);
        Table::create_db(true);
        TableColumn::create_db(true);
        Template::create_db(true);
        Test::create_db(true);
        TestSession::create_db(true);
        TestSessionReturn::create_db(true);
        TestVariable::create_db(true);
        User::create_db(true);
        UserGroup::create_db(true);
        UserType::create_db(true);
        UserTypeRight::create_db(true);
    }

    public static function update_db($simulate = false, $only_recalculate_hash = false, $only_validate_column_names = false, $only_create_db = false) {
        require '../Ini.php';
        $ini = new Ini();

        if ($only_create_db) {
            return self::create_db_structure();
        }

        if ($only_recalculate_hash) {
            OModule::calculate_all_xml_hashes();
            return json_encode(array("result" => 0));
        }

        if ($only_validate_column_names) {
            $result = TableColumn::validate_columns_name();
            return json_encode(array("result" => $result ? 0 : 1));
        }

        $versions_to_update = array();

        $previous_version = Setting::get_setting("version");
        if ($previous_version == null)
            $previous_version = Ini::$version;

        $recalculate_hash = false;
        $validate_column_names = false;

        /*
          if (Ini::does_patch_apply("3.3.0", $previous_version)) {
          if ($simulate) {
          array_push($versions_to_update, "3.3.0");
          } else {

          ///COMPATIBILITY FIX FOR V3.0.0 START
          $sql = "SHOW COLUMNS FROM `User` WHERE `Field`='last_activity'";
          $z = mysql_query($sql);
          if (mysql_num_rows($z) > 0) {
          $sql = "ALTER TABLE `User` CHANGE `last_activity` `last_login` timestamp NOT NULL default '0000-00-00 00:00:00';";
          if (!mysql_query($sql))
          return json_encode(array("result" => 1, "param" => $sql));
          }

          Setting::set_setting("version", "3.3.0");
          return json_encode(array("result" => 0, "param" => "3.3.0"));
          }
          }
         */

        if ($simulate)
            return json_encode(array("versions" => $versions_to_update, "validate_column_names" => $validate_column_names, "recalculate_hash" => $recalculate_hash, "create_db" => self::create_db_structure(true)));
        return json_encode(array("result" => 2));
    }

}

echo Setup::$_POST['check']();
?>