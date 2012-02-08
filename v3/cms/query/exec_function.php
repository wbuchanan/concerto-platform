<?php

if (!isset($ini))
{
    require_once '../../Ini.php';
    $ini = new Ini();
}
if (User::get_logged_user() == null) die(Language::string(81));

echo json_encode(array("result" => $_POST['class_name']::$_POST['function']));
?>