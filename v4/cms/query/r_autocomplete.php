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

$path = Ini::$path_temp . session_id() . ".Rc";

$code = "
library(rjson)
result = list(names=c(),packages=c())
for(package in sort(.packages(T))){
    
    library(package,character.only=T)
    functions <- lsf.str(paste('package:',package,sep=''),pattern='^" . $_POST['string'] . "')
        
    for(func in functions){
        result".'$'."names = c(result".'$'."names,func)
        result".'$'."packages = c(result".'$'."packages,package)
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