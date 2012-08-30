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
    require_once'Ini.php';
    $ini = new Ini();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Przemyslaw Lis" />
        <meta http-equiv="Cache-Control" content="no-cache"/>
        <meta http-equiv="Expires" content="-1"/>
        <title>Concerto</title>

        <link rel="stylesheet" href="css/styles.css" />
        <link rel="stylesheet" href="css/QTI.css" />
        <link rel="stylesheet" href="css/jQueryUI/cupertino/jquery-ui-1.8.23.custom.css" />

        <script type="text/javascript" src="cms/js/lib/jquery-1.8.0.min.js"></script>
        <script type="text/javascript" src="cms/js/lib/jquery.json-2.3.min.js"></script>
        <script type="text/javascript" src="cms/js/lib/jquery-ui-1.8.23.custom.min.js"></script>

        <script type="text/javascript" src="js/lib/jquery.cookie.js"></script>
        <script type="text/javascript" src="js/ConcertoMethods.js"></script>
        <script type="text/javascript" src="js/Concerto.js"></script>
        <script type="text/javascript" src="js/QTI.js"></script>
        <script>
            $(function(){
                var values = new Array();
<?php
foreach ($_GET as $key => $value) {
    if ($key == "sid" || $key == "tid" || $key == "hash")
        continue;
    ?>
                values.push($.toJSON({
                    name:"<?= $key ?>",
                    value:"<?= $value ?>"
                }));
    <?php
}

if (array_key_exists("sid", $_GET) || array_key_exists("tid", $_GET)) {
    ?>
                test = new Concerto($("#divTestContainer"),<?= array_key_exists("hash", $_GET) ? "'" . $_GET['hash'] . "'" : "null" ?>,<?= array_key_exists("sid", $_GET) ? $_GET['sid'] : "null" ?>,<?= array_key_exists("tid", $_GET) ? $_GET['tid'] : "null" ?>);
                test.run(null,values);
<?php } ?>
    });
        </script>
    </head>

    <body>
        <div style="width:100%;" id="divTestContainer">
            <div align="center"><img src="cms/css/img/logo.png" /> v<?= Ini::$version ?></div>
            <div align="center">
                <div style="display: table;">
                    <fieldset class="ui-widget-content">
                        <legend>available tests</legend>
                        <select id="selectTest" class="ui-widget-content" onchange="Concerto.selectTest()">
                            <option value="0">&lt;none selected&gt;</option>
                            <?php
                            $z = mysql_query("SELECT * FROM `Test` WHERE `open`=1");
                            while ($r = mysql_fetch_array($z)) {
                                ?>
                                <option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </fieldset>
                </div>
            </div>
            <div id="divSessionResumeDialog" title="session resuming" style="display:none;">
                <p>This test has an ongoing session. Would you like to resume current test session?</p>
            </div>
        </div>
    </body>
</html>