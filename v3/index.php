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

if (!isset($ini))
{
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
        
        <link rel="stylesheet" href="css/QTI.css" />

        <script type="text/javascript" src="cms/js/lib/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="cms/js/lib/jquery.json-2.3.min.js"></script>

        <script type="text/javascript" src="js/ConcertoMethods.js"></script>
        <script type="text/javascript" src="js/Concerto.js"></script>
        <script type="text/javascript" src="js/QTI.js"></script>
        <script>
            $(function(){
                var values = new Array();
<?php
    foreach($_GET as $key=>$value)
    {
        if($key=="sid" || $key=="tid" || $key=="hash") continue;
?>
        values.push($.toJSON({
            name:"<?=$key?>",
            value:"<?=$value?>"
        }));
<?php
    }
    
    if (array_key_exists("sid", $_GET) || array_key_exists("tid", $_GET))
    {
    ?>
                test = new Concerto($("#divTestContainer"),<?= array_key_exists("hash", $_GET) ? "'".$_GET['hash']."'" : "null" ?>,<?= array_key_exists("sid", $_GET) ? $_GET['sid'] : "null" ?>,<?= array_key_exists("tid", $_GET) ? $_GET['tid'] : "null" ?>);
                test.run(null,values);
<?php } ?>
    })
        </script>
    </head>

    <body>

        <div style="width:100%;" id="divTestContainer"></div>
    </body>
</html>