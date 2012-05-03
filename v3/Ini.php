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

class Ini
{
    private static $error_reporting = true;
    public static $path_internal = "";
    public static $path_external = "";
    public static $path_internal_media = "";
    public static $path_external_media = "";
    public static $path_php_exe = "";
    public static $path_r_exe = "";
    public static $path_r_script = "";
    public static $path_temp = "";
    public static $path_mysql_home = "";
    public static $version = "3.4.3";
    public static $sock_host = "127.0.0.1";
    public static $sock_port = "8888";
    public static $path_unix_sock = "";
    public static $path_unix_sock_dir = "";
    public static $sock_type_used = 0;
    public static $r_instances_persistant = false;
    public static $r_instances_timeout = 1800;
    public static $r_server_timeout = 2100;
    public static $timer_tamper_prevention = true;
    public static $timer_tamper_prevention_tolerance = 3;

    function __construct($connect = true, $session = true)
    {
        //if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); 
        //else ob_start();
        include __DIR__ . "/SETTINGS.php";
        if ($session) @session_start();
        date_default_timezone_set($timezone);
        if (self::$error_reporting) error_reporting(E_ALL);
        else error_reporting(0);

        $this->load_settings();

        if ($connect)
        {
            if (!$this->initialize_db_connection())
                    die("Error initializing DB connection!");
        }
        $this->load_classes();

        if ($session)
        {
            if (!isset($_GET['lng']))
            {
                if (!isset($_SESSION['lng'])) $_SESSION['lng'] = "en";
            }
            else $_SESSION['lng'] = $_GET['lng'];

            Language::load_dictionary();
        }
    }

    private function load_settings()
    {
        include __DIR__ . "/SETTINGS.php";
        self::$path_external = $path_external;
        self::$path_external_media = self::$path_external . "media/";
        self::$path_internal = __DIR__ . "/";
        self::$path_internal_media = self::$path_internal . "media/";
        self::$path_r_script = $path_r_script;
        self::$path_r_exe = $path_r_exe;
        self::$path_php_exe = $path_php_exe;
        if ($path_temp != "") self::$path_temp = $path_temp;
        else self::$path_temp = self::$path_internal . "temp/";
        self::$path_mysql_home = $path_mysql_home;
        if ($path_sock != "") self::$path_unix_sock_dir = $path_sock;
        else self::$path_unix_sock_dir = self::$path_internal . "socks/";
        self::$path_unix_sock = self::$path_unix_sock_dir . "RConcerto.sock";
        self::$r_instances_persistant = $r_instances_persistant;
        self::$r_instances_timeout = $r_instances_persistant_instance_timeout;
        self::$r_server_timeout = $r_instances_persistant_server_timeout;
    }

    public static function does_patch_apply($patch_version, $previous_version)
    {
        $patch_elems = explode(".", $patch_version);
        $previous_elems = explode(".", $previous_version);

        if ($previous_elems[0] < $patch_elems[0]) return true;
        if ($previous_elems[0] == $patch_elems[0] && $previous_elems[1] < $patch_elems[1])
                return true;
        if ($previous_elems[0] == $patch_elems[0] && $previous_elems[1] == $patch_elems[1] && $previous_elems[2] < $patch_elems[2])
                return true;
        return false;
    }

    public function check_db_structure()
    {
        $version = Setting::get_setting("version");
        if ($version == null) $version = Ini::$version;

        foreach (self::get_system_tables() as $table)
        {
            $sql = sprintf("SHOW TABLES LIKE '%s'", $table);
            $z = mysql_query($sql);
            if (mysql_num_rows($z) == 0)
            {
                if (!$table::create_db()) return false;
            }
            else
            {
                if (!$table::update_db($version)) return false;
            }
        }
        Setting::set_setting("version", Ini::$version);
        return true;
    }

    public function reset_db()
    {
        CustomSection::create_db(true);
        CustomSectionVariable::create_db(true);
        DS_Module::create_db(true);
        DS_Right::create_db(true);
        DS_Sharing::create_db(true);
        DS_TableColumnType::create_db(true);
        DS_TestSectionType::create_db(true);
        Setting::create_db(true);
        Table::create_db(true);
        TableColumn::create_db(true);
        Template::create_db(true);
        Test::create_db(true);
        TestSection::create_db(true);
        TestSectionValue::create_db(true);
        TestSession::create_db(true);
        User::create_db(true);
        UserGroup::create_db(true);
        UserType::create_db(true);
        UserTypeRight::create_db(true);
    }

    public static function get_system_tables()
    {
        return array(
            "CustomSection",
            "CustomSectionVariable",
            "DS_Module",
            "DS_Right",
            "DS_Sharing",
            "DS_TableColumnType",
            "DS_TestSectionType",
            "Setting",
            "Table",
            "TableColumn",
            "Template",
            "Test",
            "TestProtectedVariable",
            "TestSection",
            "TestSectionValue",
            "TestSession",
            "User",
            "UserGroup",
            "UserType",
            "UserTypeRight"
        );
    }

    private function initialize_db_connection()
    {
        include __DIR__ . "/SETTINGS.php";
        $h = mysql_connect($db_host . ($db_port != "" ? ":" . $db_port : ""), $db_user, $db_password);
        if (!$h) return false;
        mysql_set_charset('utf8', $h);
        if (mysql_select_db($db_name, $h)) return true;
        else return false;
    }

    private function load_classes()
    {
        require_once self::$path_internal . "cms/lib/simplehtmldom/simple_html_dom.php";
        require_once self::$path_internal . "cms/model/Language.php";
        require_once self::$path_internal . "cms/model/OTable.php";
        require_once self::$path_internal . "cms/model/Setting.php";
        require_once self::$path_internal . "cms/model/OModule.php";
        require_once self::$path_internal . "cms/model/UserGroup.php";
        require_once self::$path_internal . "cms/model/UserTypeRight.php";
        require_once self::$path_internal . "cms/model/UserType.php";
        require_once self::$path_internal . "cms/model/User.php";
        require_once self::$path_internal . "cms/model/Template.php";
        require_once self::$path_internal . "cms/model/Table.php";
        require_once self::$path_internal . "cms/model/TableColumn.php";
        require_once self::$path_internal . "cms/model/Test.php";
        require_once self::$path_internal . "cms/model/TestProtectedVariable.php";
        require_once self::$path_internal . "cms/model/TestServer.php";
        require_once self::$path_internal . "cms/model/TestInstance.php";
        require_once self::$path_internal . "cms/model/TestSection.php";
        require_once self::$path_internal . "cms/model/TestSectionValue.php";
        require_once self::$path_internal . "cms/model/TestSession.php";
        require_once self::$path_internal . "cms/model/CustomSection.php";
        require_once self::$path_internal . "cms/model/CustomSectionVariable.php";
        require_once self::$path_internal . "cms/model/ODataSet.php";
        require_once self::$path_internal . "cms/model/DS_Right.php";
        require_once self::$path_internal . "cms/model/DS_Sharing.php";
        require_once self::$path_internal . "cms/model/DS_TableColumnType.php";
        require_once self::$path_internal . "cms/model/DS_TestSectionType.php";
        require_once self::$path_internal . "cms/model/DS_Module.php";
    }

    public static function check_sent_data()
    {
        foreach ($_POST as $k => $v)
        {
            if (is_array($_POST[$k]))
            {
                for ($i = 0; $i < count($_POST[$k]); $i++)
                {
                    $_POST[$k][$i] = mysql_real_escape_string($_POST[$k][$i]);
                }
            }
            else $_POST[$k] = mysql_real_escape_string($_POST[$k]);
        }
        foreach ($_GET as $k => $v)
        {
            if (is_array($_GET[$k]))
            {
                for ($i = 0; $i < count($_GET[$k]); $i++)
                {
                    $_GET[$k][$i] = mysql_real_escape_string($_GET[$k][$i]);
                }
            }
            else $_GET[$k] = mysql_real_escape_string($_GET[$k]);
        }

        foreach ($_SESSION as $k => $v)
        {
            if (is_array($_SESSION[$k]))
            {
                for ($i = 0; $i < count($_SESSION[$k]); $i++)
                {
                    $_SESSION[$k][$i] = mysql_real_escape_string($_SESSION[$k][$i]);
                }
            }
            else $_SESSION[$k] = mysql_real_escape_string($_SESSION[$k]);
        }
    }

}

?>