<?php
/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!isset($ini)) {
    require_once 'model/Ini.php';
    $ini = new Ini();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Concerto Platform</title>
        <link rel="stylesheet" type="text/css" href="css/redmond/jquery-ui-1.8.16.custom.css" />
        <script type="text/javascript" src="js/lib/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/lib/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="admin/js/Methods.js"></script>
        <script type="text/javascript" src="js/Item.js"></script>
        <script type="text/javascript" src="js/Debug.js"></script>
        <link rel="stylesheet" href="lib/CodeMirror/lib/codemirror.css" />
        <link rel="stylesheet" href="lib/CodeMirror/theme/night.css" />
        <script src="lib/CodeMirror/lib/codemirror.js"></script>
        <script src="lib/CodeMirror/mode/r/r.js"></script>
    </head>
    <body>

        <?php
        Language::load_js_dictionary(true);
        $debug = false;
        if (isset($_GET['debug'])) {
            unset($_GET['debug']);
            if (User::get_logged_user() == null)
                die(Language::string(85));
            else
                $debug = true;
        }
        ?>

        <table style="width:100%;">
            <?php if ($debug) { ?>
                <tr>
                    <th id="thSessionHistory" class="ui-widget-header noWrap ui-corner-all" style="font-size: 9px; width:50%;" align="center">

                    </th>
                    <th id="thSessionItem" class="ui-widget-header noWrap ui-corner-all" align="center">

                    </th>
                </tr>
            <?php } ?>
            <tr>
                <?php if ($debug) { ?><td id="history" class="ui-widget-content ui-corner-all" valign="top"  style="font-size: 9px;"><?php } ?>

                </td>
                <td id="item" valign="top" class="<?= ($debug ? "ui-widget-content ui-corner-all" : "") ?>">

                </td>
            </tr>
        </table>

        <div id='hzn_progressDialog' title='submiting item' style="display: none; font-size: 9px;">
            <p id='hzn_progressDialogText' style="font-size: 9px;"></p>
            <div id='hzn_progressBar' style="font-size: 9px;"></div>
        </div>

        <?php if ($debug) { ?>
            <div id='hzn_sessionVariables' title='session variables' style="font-size: 9px; display:none;">
                No session variables available yet.
            </div>

            <div id='hzn_rVariables' title='R variables' style="font-size: 9px; display:none;">
                No R session variables available yet.
            </div>
        <?php } ?>
    </body>
    <script type="text/javascript">
<?php if ($debug) { ?>Debug.Session.createSession();<?php } ?>
<?php
$session_builder = "{";
if (count($_GET) > 0) {
    foreach ($_GET as $k => $v) {
        $session_builder.="'$k':'$v',";
    }
    $session_builder = substr($session_builder, 0, strlen($session_builder) - 1);
}
$session_builder.="}"
?>
    $(function(){
        $.post(
        "query/session_data.php", 
<?= $session_builder ?>, 
        function(data)
        {
<?php if ($debug) { ?>
                Debug.Session.sessionCreated(data.SessionID);
                for(var key in data)
                {
                    Debug.sessionVariableModified(key, data[key], Debug.Session.sessionContainer);
                }
                $("#hzn_sessionVariables").html("");
                for(var key in data)
                {
                    $("#hzn_sessionVariables").append("'<b>"+key+"</b>' = '"+data[key]+"'<br/>");
                }
<?php } ?>
            Item.Current = new Item(data,<?= ($debug ? 1 : 0) ?>);
        },
        "json"
    );
    });
    </script>
</html>