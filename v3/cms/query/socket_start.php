<?php
if (!isset($ini))
{
    require_once'Ini.php';
    $ini = new Ini();
}

$ts = new TestServer();
$ts->start();
?>