<?php
if (!isset($ini))
{
    require_once'../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) 
{
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}

$val = $_POST['value'][0];
$oid = 0;
if (array_key_exists('oid',$_POST) && $_POST['oid'] != 0)
{
    $oid = $_POST['oid'];
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
    $val = $vals[0];
}
?>

<textarea id="textareaCodeMirror_<?= $_POST['counter'] ?>" class="fullWidth ui-widget-content ui-corner-all textareaCode"><?= $val ?></textarea>