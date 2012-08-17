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
        
        <link rel="stylesheet" href="client/css/QTI.css" />
        <link rel="stylesheet" href="client/css/jQueryUI/cupertino/jquery-ui-1.8.22.custom.css" />

        <script type="text/javascript" src="client/jquery-1.8.0.min.js"></script>
        <script type="text/javascript" src="client/jquery.json-2.3.min.js"></script>

        <script type="text/javascript" src="client/ConcertoMethods.js"></script>
        <script type="text/javascript" src="client/Concerto.js"></script>
        <script type="text/javascript" src="client/concerto.jquery.js"></script>
        <script type="text/javascript" src="client/QTI.js"></script>

        <script>
            function start(){
                $("#divTestContainer").concerto({
                    testID:$("#testID").val(),
                    loadingImageSource:"client/css/img/ajax-loader.gif",
                    callback:function(sessionID,sessionHash, status, finished){
                        $("#log").html("<b>start</b> - sessionID: "+sessionID+", sessionHash: "+sessionHash+", status: "+status+", finished: "+finished);
                    }});
            }
            
            function resume(){
                $("#divTestContainer").concerto({
                    sessionID:$("#sessionID").val(),
                    sessionHash:$("#sessionHash").val(),
                    loadingImageSource:"client/css/img/ajax-loader.gif",
                    callback:function(sessionID,sessionHash, status, finished){
                        $("#log").html("<b>resume</b> - sessionID: "+sessionID+", sessionHash: "+sessionHash+", status: "+status+", finished: "+finished);
                    },
                    resumeFromLastTemplate:$("#resumeFromLastTemplate").is(":checked")});
            }
        </script>
    </head>

    <body>
        <div id="log">

        </div>
        <div>
            testID: <input type="text" id="testID" />, 
            sessionID: <input type="text" id="sessionID" />
            sessionHash: <input type="text" id="sessionHash" />
            resume from last template: <input type="checkbox" id="resumeFromLastTemplate" />
        </div>
        <div>
            <button onclick="start()">start new</button><button onclick="resume()">resume</button>
        </div>
        <div style="width:100%;" id="divTestContainer"></div>
    </body>
</html>