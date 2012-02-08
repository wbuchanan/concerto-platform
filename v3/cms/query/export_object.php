<?php

if (!isset($ini))
{
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) die(Language::string(81));

$obj = $_GET['class_name']::from_mysql_id($_GET['oid']);
if(!$logged_user->is_object_readable($obj)) die(Language::string(81));

header("Content-Type:text/xml");
header('Content-Disposition: attachment; filename="export_'.$_GET['class_name'].'_'.$_GET['oid'].'.xml"');

echo $obj->export();