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
if ($obj == null)
{
    if (!$logged_user->is_module_writeable($_POST['class_name']))
    {
        echo json_encode(array("result" => -2));
        exit();
    }

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
else
{
    if (!$logged_user->is_object_editable($obj))
    {
        echo json_encode(array("result" => -2));
        exit();
    }
}

$_POST['oid'] = $obj->mysql_save_from_post($_POST);

echo json_encode(array("result" => 0, "oid" => $_POST['oid']));
?>