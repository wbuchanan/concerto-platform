<?php

if (!isset($ini)) {
    require_once'../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null)
    die(Language::string(81));
?>

CONCERTO v<?=Ini::$version?> &REG; 2011-2012