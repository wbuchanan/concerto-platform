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
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) {
    echo json_encode(array());
    exit();
}

$path = Ini::$path_temp . session_id() . ".Rcd";

$code = "
library(tools)
library(rjson)
result = list(title=NULL,usage=NULL)

db <- Rd_db('".$_POST['pack']."')
for(doc in db){
    aliases <- tools:::.Rd_get_metadata(x=doc,kind='alias')
    if('".$_POST['func']."' %in% aliases) {
        result".'$'."title <- tools:::.Rd_get_metadata(x=doc,kind='title')
        result".'$'."usage <- paste(tools:::.Rd_get_metadata(doc,'usage'),collapse='<br/>')
        break
    }
}

result <- toJSON(result)

fileConn<-file('$path')
writeLines(result, fileConn)
close(fileConn)
";

$fh = fopen($path, "w");
fwrite($fh, $code);
fclose($fh);

$rscript_path = Ini::$path_r_script;

`$rscript_path $path`;

$result = file_get_contents($path);

if (file_exists($path))
    unlink($path);

echo $result;
?>