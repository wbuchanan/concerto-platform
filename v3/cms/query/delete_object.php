<?php

if (!isset($ini))
{
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) 
{
    echo json_encode(array("result" => -1));
    exit();
}

$obj = $_POST['class_name']::from_mysql_id($_POST['oid']);
if(!$logged_user->is_object_editable($obj)) 
{
    echo json_encode(array("result" => -2));
    exit();
}

$obj->mysql_delete();
echo json_encode(array("result" => 0));
?>