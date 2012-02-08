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

$vals = array();
if (array_key_exists('value',$_POST))
{
    $vals = $_POST['value'];
}
if (array_key_exists('oid',$_POST) && $_POST['oid'] != 0)
{
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
}
?>

IF<br/>
<input type="text" class="ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[0], ENT_QUOTES) ?>" />
<select class="ui-widget-content ui-corner-all controlValue<?= $_POST['counter'] ?>">
    <option value="!=" <?= $vals[1] == "!=" ? "selected" : "" ?>><?=Language::string(221)?></option>
    <option value="==" <?= $vals[1] == "==" ? "selected" : "" ?>><?=Language::string(222)?></option>
    <option value=">" <?= $vals[1] == ">" ? "selected" : "" ?>><?=Language::string(223)?></option>
    <option value=">=" <?= $vals[1] == ">=" ? "selected" : "" ?>><?=Language::string(224)?></option>
    <option value="<" <?= $vals[1] == "<" ? "selected" : "" ?>><?=Language::string(225)?></option>
    <option value="<=" <?= $vals[1] == "<=" ? "selected" : "" ?>><?=Language::string(226)?></option>
</select> 
<input type="text" class="ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[2], ENT_QUOTES) ?>" /><br/>

<?php
$i = 3;
while (isset($vals[$i]))
{
    ?>
    <select class="controlValue<?= $_POST['counter'] ?> controlValue<?= $_POST['counter'] ?>_link ui-widget-content ui-corner-all">
        <option value="&&" <?= isset($vals[$i]) && $vals[$i] == "&&" ? "selected" : "" ?>><?=Language::string(227)?></option>
        <option value="||" <?= isset($vals[$i]) && $vals[$i] == "||" ? "selected" : "" ?>><?=Language::string(228)?></option>
    </select> 
    <?php $i++; ?>
    <input type="text" class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all comboboxVars" value="<?= htmlspecialchars($vals[$i], ENT_QUOTES) ?>" />
    <?php $i++; ?>
    <select class="ui-widget-content ui-corner-all controlValue<?= $_POST['counter'] ?>">
        <option value="!=" <?= $vals[$i] == "!=" ? "selected" : "" ?>><?=Language::string(221)?></option>
        <option value="==" <?= $vals[$i] == "==" ? "selected" : "" ?>><?=Language::string(222)?></option>
        <option value=">" <?= $vals[$i] == ">" ? "selected" : "" ?>><?=Language::string(223)?></option>
        <option value=">=" <?= $vals[$i] == ">=" ? "selected" : "" ?>><?=Language::string(224)?></option>
        <option value="<" <?= $vals[$i] == "<" ? "selected" : "" ?>><?=Language::string(225)?></option>
        <option value="<=" <?= $vals[$i] == "<=" ? "selected" : "" ?>><?=Language::string(226)?></option>
    </select> 
    <?php $i++; ?>
    <input type="text" class="ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[$i], ENT_QUOTES) ?>" /><br/>
    <?php $i++; ?>
    <?php
}
?>

<table>
    <tr>
        <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddIfCond(<?= $_POST['counter'] ?>)" title="<?=Language::string(229)?>"></span></td>
        <td><?php if (isset($vals[3]))
{ ?><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveIfCond(<?= $_POST['counter'] ?>)" title="<?=Language::string(230)?>"></span><?php } ?></td>
    </tr>
</table>

<?=Language::string(231)?>
<table>
    <tr>
        <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddLogicSession($('#divSectionSubContent_<?= $_POST['counter'] ?>'),true,<?= $_POST['counter'] ?>)"  title="<?=Language::string(232)?>"></span>
        </td>
    </tr>
</table>