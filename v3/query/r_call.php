<?php

if (!isset($ini))
{
    require_once'../Ini.php';
    $ini = new Ini();
}

$session = null;
$result = array();
if (array_key_exists('sid', $_POST))
{
    $session = TestSession::from_mysql_id($_POST['sid']);
    if ($session != null)
    {
        if (!array_key_exists('values', $_POST)) $_POST['values'] = array();

        if (array_key_exists('btn_name', $_POST))
        {
            array_push($_POST['values'], json_encode(array(
                        "name" => "LAST_PRESSED_BUTTON_NAME",
                        "value" => $_POST['btn_name'],
                        "visibility" => 2,
                        "type" => 0
                    )));
        }

        $result = $session->resume($_POST['values']);
    }
}
else
{
    if (array_key_exists('tid', $_POST))
    {
        $session = TestSession::start_new($_POST['tid']);

        if (!array_key_exists('values', $_POST)) $_POST['values'] = array();
        if (array_key_exists('btn_name', $_POST))
        {
            array_push($_POST['values'], json_encode(array(
                        "name" => "LAST_PRESSED_BUTTON_NAME",
                        "value" => $_POST['btn_name'],
                        "visibility" => 2,
                        "type" => 0
                    )));
        }

        $result = $session->run_test(null, $_POST['values']);
    }
}

echo json_encode($result);
?>