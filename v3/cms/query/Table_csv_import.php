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

$table = Table::from_mysql_id($_POST['oid']);
if (!$logged_user->is_object_editable($table))
{
    echo json_encode(array("result" => -2));
    exit();
}

$path = Ini::$path_internal . "cms/js/lib/fileupload/php/files/" . $_POST['file'];

if (!file_exists($path))
{
    echo json_encode(array("result" => -3));
    exit();
}

echo json_encode(array("result" => $table->import_from_csv($path, $_POST['delimeter'], $_POST['enclosure'], $_POST['header'] == 1)));
exit();
?>