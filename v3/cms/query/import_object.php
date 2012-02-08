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

if(!$logged_user->is_module_writeable($_POST['class_name'])) 
{
    echo json_encode(array("result" => -2));
    exit();
}

$path = Ini::$path_internal . "cms/js/lib/fileupload/php/files/".$_POST['file'];

$obj = new $_POST['class_name']();
$obj->Owner_id=$logged_user->id;
$oid = $obj->import($path);
echo json_encode(array("result"=>(is_numeric($oid)?0:-3),"oid"=>$oid));