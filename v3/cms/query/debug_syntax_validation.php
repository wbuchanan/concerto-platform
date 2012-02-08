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

$session = TestSession::start_new($_POST['Test_id']);
$test = $session->get_Test();

if ($test == null)
{
    echo json_encode(array("result" => -2));
    exit();
}

$sections = TestSection::from_property(array("Test_id"=>$test->id));

$result = array();
foreach ($sections as $section)
{
    $result["counter" . $section->counter] = $session->debug_syntax($section->id);
}
$session->mysql_delete();

echo json_encode(array("result" => 0, "data" => $result));
?>