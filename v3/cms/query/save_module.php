<?php

if (!isset($ini))
{
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) die("Access denied!");

$obj = $_POST['class_name']::from_mysql_id($_POST['oid']);
if ($obj == null)
{
    $obj = new $_POST['class_name']();
    $vars = get_object_vars($obj);
    $is_ownable = false;
    foreach ($vars as $k => $v)
    {
        if ($k == "Owner_id")
        {
            $is_ownable = true;
            break;
        }
    }
    if ($is_ownable) $obj->Owner_id = $logged_user->id;
    if (isset($_POST['Sharing_id'])) $obj->Sharing_id = $_POST['Sharing_id'];
}

$_POST['oid'] = $obj->mysql_save_from_post($_POST);

echo json_encode(array("oid" => $_POST['oid']));
?>