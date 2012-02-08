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
<b><?=Language::string(113)?>:</b><br/>
<div class="ui-widget-content ui-state-focus">
    <div>
        <table>
            <tr>
                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?=Language::string(245)?>"></span></td>
                <td>TEST_ID</td>
            </tr>
            <tr>
                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?=Language::string(246)?>"></span></td>
                <td>TEST_SESSION_ID</td>
            </tr>
        </table>
    </div>
    <div class="notVisible">
        <input class="inputReturnVar" type="hidden" value="TEST_ID" />
        <input class="inputReturnVar" type="hidden" value="TEST_SESSION_ID" />
    </div>
</div>