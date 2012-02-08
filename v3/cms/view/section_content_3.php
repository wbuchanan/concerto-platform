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
?>

<b><?=Language::string(219)?>:</b> 
<select id="selectGoTo_<?= $_POST['counter'] ?>" class="fullWidth ui-widget-content ui-corner-all">
</select>
<br/><br/>
<b><?=Language::string(113)?>:</b>
<div class="ui-widget-content ui-state-focus">
    <div>
        <table>
            <tr>
                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?=Language::string(220)?>"></span></td>
                <td>CURRENT_SECTION_INDEX</td>
            </tr>
        </table>
    </div>
    <div class="notVisible">
        <input class="inputReturnVar" type="hidden" value="CURRENT_SECTION_INDEX" />
    </div>
</div>