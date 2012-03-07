<?php

if (!isset($ini))
{
    require_once'Ini.php';
    $ini = new Ini();
}

$ts = new TestServer();
$ts->start();

$errno = 0;
$errstr = "";
$sh = fsockopen("127.0.0.1", 9000, $errno, $errstr, 5);
if (!$sh)
{
    $ts->debug($errno . " - " . $errstr);
}
else
{
    fwrite($sh, "1+1");
    while (!feof($sh))
    {
        echo fgets($sh, 128);
    }
    fclose($sh);
}
?>
