<?php
if (!isset($ini))
{
    require_once'Ini.php';
    $ini = new Ini(false,false);
}

$ts = new TestServer();
$ts->start();
?>