<?php

class Ini
{
    private static $error_reporting = true;
    public static $path_internal = "";
    public static $path_external = "";
    public static $path_internal_media = "";
    public static $path_external_media = "";
    public static $path_r_script = "";
    public static $path_temp = "";
    public static $path_mysql_home = "";
    public static $version = "3.1.0";

    function __construct($connect=true)
    {
        include __DIR__ . "/SETTINGS.php";

        @session_start();
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

        if (!isset($_GET['lng']))
        {
            if (!isset($_SESSION['lng'])) $_SESSION['lng'] = "en";
        }
        else $_SESSION['lng'] = $_GET['lng'];

        Language::load_dictionary();
    }

    private function load_settings()
    {
        include __DIR__ . "/SETTINGS.php";
        self::$path_external = $path_external;
        self::$path_external_media = self::$path_external . "media/";
        self::$path_internal = __DIR__ . "/";
        self::$path_internal_media = self::$path_internal . "media/";
        self::$path_r_script = $path_r_script;
        self::$path_temp = self::$path_internal . "temp/";
        self::$path_mysql_home = $path_mysql_home;
    }

    public function check_db_structure()
    {
        foreach (self::get_system_tables() as $table)
        {
            $sql = sprintf("SHOW TABLES LIKE '%s'", $table);
            $z = mysql_query($sql);
            if (mysql_num_rows($z) == 0) return false;
        }
        return true;
    }

    public function update_db_structure()
    {
        mysql_query("DROP TABLE IF EXISTS `CustomSection`;");
        $sql = "
            CREATE TABLE `CustomSection` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `description` text NOT NULL,
            `code` text NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `CustomSectionVariable`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `CustomSectionVariable` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `index` int(11) NOT NULL,
            `CustomSection_id` bigint(20) NOT NULL,
            `name` text NOT NULL,
            `description` text NOT NULL,
            `type` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `DS_Module`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_Module` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `DS_Module` (`id`, `name`, `value`, `position`) VALUES
            (1, 'HTML templates', 'Template', 1),
            (2, 'tables', 'Table', 2),
            (3, 'users', 'User', 3),
            (4, 'user groups', 'UserGroup', 4),
            (5, 'user types', 'UserType', 5),
            (6, 'tests', 'Test', 6),
            (7, 'custom test section', 'CustomSection', 7);
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `DS_Right`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_Right` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `DS_Right` (`id`, `name`, `value`, `position`) VALUES
            (1, 'none', '1', 1),
            (2, 'private', '2', 2),
            (3, 'standard', '3', 3),
            (4, 'group', '4', 4),
            (5, 'all', '5', 5);
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `DS_Sharing`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_Sharing` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `DS_Sharing` (`id`, `name`, `value`, `position`) VALUES
            (1, 'private', '1', 1),
            (2, 'group', '2', 2),
            (3, 'public', '3', 3);
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `DS_TableColumnType`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_TableColumnType` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `DS_TableColumnType` (`id`, `name`, `value`, `position`) VALUES
            (1, 'string', 'string', 1),
            (2, 'numeric', 'numeric', 2),
            (3, 'HTML', 'HTML', 3);
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `DS_TestSectionType`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `DS_TestSectionType` (
            `id` int(11) NOT NULL auto_increment,
            `name` text NOT NULL,
            `value` text NOT NULL,
            `position` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `DS_TestSectionType` (`id`, `name`, `value`, `position`) VALUES
            (1, 'R code', '1', 1),
            (2, 'load HTML template', '2', 2),
            (3, 'go to', '3', 3),
            (4, 'IF statement', '4', 4),
            (5, 'set variable', '5', 5),
            (6, 'start', '6', 6),
            (7, 'end', '7', 7),
            (8, 'table modification', '8', 8),
            (9, 'custom section', '9', 9);
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `Table`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `Table` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `TableColumn`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `TableColumn` (
            `id` bigint(20) NOT NULL auto_increment,
            `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `udpated` timestamp NOT NULL default '0000-00-00 00:00:00',
            `index` int(11) NOT NULL,
            `name` text NOT NULL,
            `Table_id` bigint(20) NOT NULL,
            `TableColumnType_id` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `Template`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `Template` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `HTML` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `Test`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `Test` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `TestSection`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSection` (
            `id` bigint(20) NOT NULL auto_increment,
            `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `updated` timestamp NOT NULL default '0000-00-00 00:00:00',
            `counter` int(11) NOT NULL,
            `TestSectionType_id` int(11) NOT NULL,
            `Test_id` bigint(20) NOT NULL,
            `parent_counter` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `TestSectionValue`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSectionValue` (
            `id` bigint(20) NOT NULL auto_increment,
            `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `updated` timestamp NOT NULL default '0000-00-00 00:00:00',
            `TestSection_id` bigint(20) NOT NULL,
            `index` int(11) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `TestSession`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSession` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Test_id` bigint(20) NOT NULL,
            `counter` int(11) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `TestSessionVariable`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSessionVariable` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `TestSession_id` bigint(20) NOT NULL,
            `name` varchar(40) NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`),
            UNIQUE KEY `TestSession_id` (`TestSession_id`,`name`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `User`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `User` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `login` text NOT NULL,
            `firstname` text NOT NULL,
            `lastname` text NOT NULL,
            `email` text NOT NULL,
            `phone` text NOT NULL,
            `md5_password` text NOT NULL,
            `UserType_id` bigint(20) NOT NULL,
            `UserGroup_id` bigint(20) NOT NULL,
            `last_activity` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `User` (`id`, `updated`, `created`, `login`, `firstname`, `lastname`, `email`, `phone`, `md5_password`, `UserType_id`, `UserGroup_id`, `last_activity`, `Sharing_id`, `Owner_id`) VALUES (NULL, CURRENT_TIMESTAMP, NOW(), 'admin', 'unknown', '', '', '', MD5('admin'), '1', '', '0000-00-00 00:00:00', '1', '1');
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `UserGroup`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `UserGroup` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `UserType`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `UserType` (
            `id` int(11) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `UserType` (`id`, `updated`, `created`, `name`, `Sharing_id`, `Owner_id`) VALUES
            (1, '2012-01-13 20:07:27', '2011-12-05 13:46:52', 'super admin', 1, 1),
            (4, '2012-01-11 14:24:56', '2012-01-11 14:24:56', 'standard', 1, 1);
            ";
        mysql_query($sql);

        mysql_query("DROP TABLE IF EXISTS `UserTypeRight`;");
        $sql = "
            CREATE TABLE IF NOT EXISTS `UserTypeRight` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `Module_id` int(11) NOT NULL,
            `UserType_id` bigint(20) NOT NULL,
            `read` int(11) NOT NULL,
            `write` int(11) NOT NULL,
            `ownership` tinyint(1) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;
            ";
        mysql_query($sql);

        $sql = "
            INSERT INTO `UserTypeRight` (`id`, `updated`, `created`, `Module_id`, `UserType_id`, `read`, `write`, `ownership`) VALUES
            (1, '2012-01-13 19:57:12', '0000-00-00 00:00:00', 1, 1, 5, 5, 1),
            (2, '2012-01-13 19:57:12', '0000-00-00 00:00:00', 2, 1, 5, 5, 1),
            (3, '2012-01-13 19:57:20', '0000-00-00 00:00:00', 3, 1, 5, 5, 1),
            (4, '2012-01-13 19:57:20', '0000-00-00 00:00:00', 4, 1, 5, 5, 1),
            (5, '2012-01-13 20:16:12', '0000-00-00 00:00:00', 5, 1, 5, 5, 1),
            (6, '2012-01-13 19:57:20', '0000-00-00 00:00:00', 6, 1, 5, 5, 1),
            (7, '2012-01-11 15:05:29', '2012-01-11 15:04:58', 1, 4, 3, 3, 0),
            (8, '2012-01-11 15:05:56', '2012-01-11 15:04:58', 2, 4, 3, 3, 0),
            (9, '2012-01-11 15:05:56', '2012-01-11 15:04:58', 6, 4, 3, 3, 0),
            (10, '2012-01-11 15:08:29', '2012-01-11 15:04:58', 4, 4, 1, 1, 0),
            (11, '2012-01-11 15:08:29', '2012-01-11 15:04:58', 5, 4, 1, 1, 0),
            (12, '2012-01-11 15:08:29', '2012-01-11 15:04:58', 3, 4, 1, 1, 0),
            (19, '2012-01-18 18:59:34', '2012-01-18 18:59:34', 7, 1, 5, 5, 1);
            ";
        mysql_query($sql);
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
            "Table",
            "TableColumn",
            "Template",
            "Test",
            "TestSection",
            "TestSectionValue",
            "TestSession",
            "TestSessionVariable",
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
        require_once self::$path_internal . "cms/model/OModule.php";
        require_once self::$path_internal . "cms/model/UserGroup.php";
        require_once self::$path_internal . "cms/model/UserTypeRight.php";
        require_once self::$path_internal . "cms/model/UserType.php";
        require_once self::$path_internal . "cms/model/User.php";
        require_once self::$path_internal . "cms/model/Template.php";
        require_once self::$path_internal . "cms/model/Table.php";
        require_once self::$path_internal . "cms/model/TableColumn.php";
        require_once self::$path_internal . "cms/model/Test.php";
        require_once self::$path_internal . "cms/model/TestSection.php";
        require_once self::$path_internal . "cms/model/TestSectionValue.php";
        require_once self::$path_internal . "cms/model/TestSession.php";
        require_once self::$path_internal . "cms/model/TestSessionVariable.php";
        require_once self::$path_internal . "cms/model/CustomSection.php";
        require_once self::$path_internal . "cms/model/CustomSectionVariable.php";
        require_once self::$path_internal . "cms/model/ODataSet.php";
        require_once self::$path_internal . "cms/model/DS_Right.php";
        require_once self::$path_internal . "cms/model/DS_Sharing.php";
        require_once self::$path_internal . "cms/model/DS_TableColumnType.php";
        require_once self::$path_internal . "cms/model/DS_TestSectionType.php";
        require_once self::$path_internal . "cms/model/DS_Module.php";
    }

    public static function get_setting($name)
    {
        $sql = sprintf("SELECT `value` FROM `Setting` WHERE `name`='%s'", $name);
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) return $r[0];
        return null;
    }

    public static function set_setting($name, $value)
    {
        $sql = sprintf("UPDATE `Setting` SET `value`='%s' WHERE `name`='%s'", $value, $name);
        mysql_query($sql);
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