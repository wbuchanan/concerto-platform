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

if (!isset($ini)) {
    require_once'../Ini.php';
    $ini = new Ini(false);
}

class Setup {

    public static function php_version_check() {
        $v = phpversion();
        $nums = explode(".", $v);
        if ($nums[0] < 5)
            return false;
        if ($nums[0] == 5 && $nums[1] < 3)
            return false;
        if ($nums[0] == 5 && $nums[1] >= 3)
            return true;
        if ($nums[0] > 5)
            return true;
    }

    public static function php_safe_mode_check() {
        return !ini_get("safe_mode");
    }

    public static function php_magic_quotes_check() {
        return !ini_get('magic_quotes_gpc');
    }

    public static function php_short_open_tag_check() {
        return ini_get("short_open_tag");
    }

    public static function file_paths_check($path) {
        if (file_exists($path) && is_file($path))
            return true;
        else
            return false;
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

    public static function rscript_check() {
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e 1+1', $array, $return);
        return ($return == 0);
    }

    public static function r_version_check($version) {
        $elems = explode(".", $version);
        if ($elems[0] > 2)
            return true;
        if ($elems[0] == 2) {
            if ($elems[1] >= 12)
                return true;
        }
        return false;
    }

    public static function get_r_version() {
        $output = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e version', $output, $return);
        $version = str_replace(" ", "", str_replace("major", "", $output[6])) .".". str_replace(" ", "", str_replace("minor", "", $output[7]));
        return $version;
    }

    public static function mysql_connection_check($host, $port, $login, $password) {
        if (@mysql_connect($host . ":" . $port, $login, $password))
            return true;
        else
            return false;
    }

    public static function mysql_select_db_check($db_name) {
        if (@mysql_select_db($db_name))
            return true;
        else
            return false;
    }

    public static function r_package_check($package) {
        $array = array();
        $return = 0;
        exec('"' . Ini::$path_r_script . '" -e "library(' . $package . ')"', $array, $return);
        return ($return == 0);
    }

}
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Concerto Platform - test page</title>
        <link rel="stylesheet" href="../cms/css/styles.css" />

        <script type="text/javascript" src="../cms/js/lib/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="../cms/js/lib/jquery-ui-1.8.18.custom.min.js"></script>
        <script type="text/javascript" src="../cms/js/Methods.js"></script>
        <script src="../cms/js/lib/themeswitcher/jquery.themeswitcher.min.js"></script>
        <script src="../cms/lib/jfeed/build/dist/jquery.jfeed.js"></script>

        <script>
            
            $(function(){
                $('#switcher').themeswitcher({
                    loadTheme:"Cupertino",
                    imgpath: "../cms/js/lib/themeswitcher/images/",
                    onSelect:function(){
                    }
                });
            })
        </script>
    </head>

    <body>
        <div id="switcher"></div>
        <div align="center" class="ui-widget-header ui-corner-all margin"><h2>Concerto platform - <?= Ini::$version != "" ? "v" . Ini::$version . " - " : "" ?>test page</h2></div>
        <br/>
        <div align="center">
            <table class="margin">
                <thead>
                    <tr>
                        <th class="ui-widget-header">test description</th>
                        <th class="ui-widget-header">test result</th>
                        <th class="ui-widget-header">recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ok = true;

                    if ($ok) {
                        ?>
                    <script>
                        $(function(){
                            Methods.currentVersion = "<?= Ini::$version ?>";
                            Methods.checkLatestVersion(function(isNewerVersion,version){
                                if(isNewerVersion==1) 
                                {
                                    $("#tdVersionCheckResult").removeClass("ui-state-highlight");
                                    $("#tdVersionCheckResult").addClass("ui-state-error");
                                    $("#tdVersionCheckResult").html("newer version is available: <b>v"+version+"</b>. Your current version <b>v<?= Ini::$version ?></b> <b style='color:red;'>IS OUTDATED</b>");
                                    $("#tdVersionCheckReccomendations").html("You can find the latest version at the link below:<br/><a href='http://code.google.com/p/concerto-platform'>http://code.google.com/p/concerto-platform</a>");
                                }
                                else
                                {
                                    $("#tdVersionCheckResult").html("your current version: <b>v<?= Ini::$version ?></b> <b style='color:green;'>IS UP TO DATE</b>");
                                }
                            },"../cms/lib/jfeed/proxy.php");
                        });
                    </script>
                    <tr>
                        <td class="ui-widget-content">Check for the latest <b>Concerto Platform</b> version</td>
                        <td id="tdVersionCheckResult" class="ui-state-highlight">...checking the latest version...</td>
                        <td id="tdVersionCheckReccomendation"class="ui-widget-content" align="center">-</td>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::php_version_check();
                        ?>
                        <td class="ui-widget-content">PHP version at least <b>v5.3</b></td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your PHP version: <b><?= phpversion() ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Update your PHP to v5.3 or higher") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::php_safe_mode_check();
                        ?>
                        <td class="ui-widget-content">PHP <b>'safe mode'</b> must be turned <b>OFF</b></td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your PHP <b>'safe mode'</b> is turned <b><?= ($test ? "OFF" : "ON") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Ask your server administrator to turn PHP 'safe mode' OFF") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::php_magic_quotes_check();
                        ?>
                        <td class="ui-widget-content">PHP <b>'magic quotes'</b> must be turned <b>OFF</b></td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your PHP <b>'magic quotes'</b> is turned <b><?= ($test ? "OFF" : "ON") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Ask your server administrator to turn PHP 'magic quotes' OFF") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::php_short_open_tag_check();
                        ?>
                        <td class="ui-widget-content">PHP <b>'short open tag'</b> must be turned <b>ON</b></td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your PHP <b>'short open tag'</b> is turned <b><?= ($test ? "ON" : "OFF") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Ask your server administrator to turn PHP 'short open tag' ON") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        include'../SETTINGS.php';
                        $test = Setup::mysql_connection_check($db_host, $db_port, $db_user, $db_password);
                        ?>
                        <td class="ui-widget-content"><b>MySQL</b> connection test</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">Host: <b><?= $db_host ?></b>, Port: <b><?= $db_port ?></b>, Login: <b><?= $db_user ?></b> <b><?= ($test ? "CONNECTED" : "CAN'T CONNECT") ?></b> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>db_host, db_port, db_user, db_password</b> in /SETTINGS.php file.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::mysql_select_db_check($db_name);
                        ?>
                        <td class="ui-widget-content"><b>MySQL</b> database connection test</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>MySQL</b> database <b><?= $db_name ?></b> <b><?= ($test ? "IS CONNECTABLE" : "IS NOT CONNECTABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>db_name</b> in <b>/SETTINGS.php</b> file. Check if database name is correct and if it is - check if MySQL user has required permissions to access this database.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    $ini = new Ini(true, true, false);
                    ?>
                    <tr>
                        <?php
                        $test = $ini->check_db_structure();
                        ?>
                        <td class="ui-widget-content"><b>MySQL</b> database tables structure test</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>MySQL</b> database <b><?= $db_name ?></b> tables structure <b><?= ($test ? "IS CORRECT" : "IS NOT CORRECT") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Setup application was unable to create valid database structure.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::rscript_check();
                        ?>
                        <td class="ui-widget-content"><b>Rscript</b> file path must be set.</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>Rscript</b> file path: <b><?= Ini::$path_r_script ?></b> <b><?= ($test ? "EXISTS" : "DOESN'T EXISTS") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center">
                            <?php
                            if ($test)
                                echo"-";
                            else {
                                ?>
                                Rscript file path not set, set incorrectly or unaccesible to PHP.<br/>
                                Usually the Rscript file path is <b>/usr/bin/Rscript</b>. Set your Rscript path in <b>/SETTINGS.php</b> file.
                            <?php } ?>
                        </td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::r_version_check(Setup::get_r_version());
                        ?>
                        <td class="ui-widget-content">R version installed must be at least <b>v2.12</b> .</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>R</b> version is: <b>v<?= Setup::get_r_version() ?></b> <b><?= ($test ? "CORRECT" : "INCORRECT") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center">
                            <?php
                            if ($test)
                                echo"-";
                            else {
                                ?>
                                Please update your R installation to version <b>v2.12</b> at least.
                            <?php } ?>
                        </td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::file_paths_check(Ini::$path_php_exe);
                        ?>
                        <td class="ui-widget-content"><b>PHP</b> executable file path must be set.</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>PHP</b> executable file path: <b><?= Ini::$path_php_exe ?></b> <b><?= ($test ? "EXISTS" : "DOESN'T EXISTS") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center">
                            <?php
                            if ($test)
                                echo"-";
                            else {
                                ?>
                                PHP executable file path not set, set incorrectly or unaccesible to PHP.<br/>
                                Usually the PHP executable file path is <b>/usr/bin/php</b>. Set your PHP executable path in <b>/SETTINGS.php</b> file.
                            <?php } ?>
                        </td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::file_paths_check(Ini::$path_r_exe);
                        ?>
                        <td class="ui-widget-content"><b>R</b> executable file path must be set.</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>R</b> executable file path: <b><?= Ini::$path_r_exe ?></b> <b><?= ($test ? "EXISTS" : "DOESN'T EXISTS") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center">
                            <?php
                            if ($test)
                                echo"-";
                            else {
                                ?>
                                R executable file path not set, set incorrectly or unaccesible to PHP.<br/>
                                Usually the R executable file path is <b>/usr/bin/R</b>. Set your R executable path in <b>/SETTINGS.php</b> file.
                            <?php } ?>
                        </td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::directory_writable_check(Ini::$path_temp);
                        ?>
                        <td class="ui-widget-content"><b>temp</b> directory path must be writable</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>temp</b> directory: <b><?= Ini::$path_temp ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>" . Ini::$path_temp . "</b> directory rigths to 0777.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $path = Ini::$path_internal . "cms/js/lib/fileupload/php/files";
                        $test = Setup::directory_writable_check($path);
                        ?>
                        <td class="ui-widget-content"><b>/cms/js/lib/fileupload/php/files</b> directory path must be writable</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>/cms/js/lib/fileupload/php/files</b> directory: <b><?= $path ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>/cms/js/lib/fileupload/php/files</b> directory rigths to 0777.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::directory_writable_check(Ini::$path_internal_media);
                        ?>
                        <td class="ui-widget-content"><b>/media</b> directory path must be writable</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>/media</b> directory: <b><?= Ini::$path_internal_media ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>/media</b> directory rigths to 0777.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::directory_writable_check(Ini::$path_internal . "cms/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache");
                        ?>
                        <td class="ui-widget-content"><b>/cms/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache</b> directory path must be writable</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>/cms/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache</b> directory: <b><?= Ini::$path_internal . "cms/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache" ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>/cms/lib/ckeditor/plugins/pgrfilemanager/PGRThumb/cache</b> directory rigths to 0777.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::directory_writable_check(Ini::$path_unix_sock_dir);
                        ?>
                        <td class="ui-widget-content"><b>UNIX sock</b> directory path must be writable</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>">your <b>UNIX sock</b> directory: <b><?= Ini::$path_unix_sock_dir ?></b> <b><?= ($test ? "IS WRITABLE" : "IS NOT WRITABLE") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Set <b>" . Ini::$path_unix_sock_dir . "</b> directory rigths to 0777.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>    

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::r_package_check("RMySQL");
                        ?>
                        <td class="ui-widget-content"><b>RMySQL</b> R package must be installed.</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>RMySQL</b> package <b><?= ($test ? "IS INSTALLED" : "IS NOT INSTALLED") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Install <b>RMySQL</b> package to main R library directory.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::r_package_check("catR");
                        ?>
                        <td class="ui-widget-content"><b>catR</b> R package must be installed.</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>catR</b> package <b><?= ($test ? "IS INSTALLED" : "IS NOT INSTALLED") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Install <b>catR</b> package to main R library directory.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>

                <?php
                if ($ok) {
                    ?>
                    <tr>
                        <?php
                        $test = Setup::r_package_check("session");
                        ?>
                        <td class="ui-widget-content"><b>session</b> R package must be installed.</td>
                        <td class="<?= ($test ? "ui-state-highlight" : "ui-state-error") ?>"><b>session</b> package <b><?= ($test ? "IS INSTALLED" : "IS NOT INSTALLED") ?> - <b style="color:<?= ($test ? "green" : "red") ?>"><?= ($test ? "PASSED" : "FAILED") ?></b></td>
                        <td class="ui-widget-content" align="center"><?= ($test ? "-" : "Install <b>session</b> package to main R library directory.") ?></td>
                        <?php $ok = $ok && $test; ?>
                    </tr>
                <?php } ?>    

                </tbody>
            </table>
        </div>
        <br/>
        <?php
        if (!$ok) {
            ?>
            <h1 class="ui-state-error" align="center">Please correct your problems using recommendations and run the test again.</h1>
            <?php
        } else {
            ?>
            <h1 class="" align="center" style="color:green;">Test completed. Every item passed correctly.</h1>
            <h1 class="ui-state-highlight" align="center" style="color:blue;">IT IS STRONGLY RECOMMENDED TO DELETE THIS <b>/setup</b> DIRECTORY NOW ALONG WITH ALL IT'S CONTENTS FOR SECURITY REASONS!</h1>
            <h2 class="" align="center"><a href="<?= Ini::$path_external . "cms/index.php" ?>">click here to launch Concerto Platform panel</a> - if this is fresh installation of Concerto then default admin account is <b>login:admin/password:admin</b></h2>
        <?php } ?>
        <div style="display:none;" id="divGeneralDialog">
        </div>
    </body>
</html>