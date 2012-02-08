<?php

if (!isset($ini))
{
    require_once '../../Ini.php';
    $ini = new Ini();
}

$user = User::log_in($_POST['login'], $_POST['password']);
if ($user == null)
{
    echo json_encode(array(
        "success" => 0
    ));
}
else
{
    echo json_encode(array(
        "success" => 1
    ));
}
?>