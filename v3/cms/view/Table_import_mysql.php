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

<script>
    $(function(){
        Methods.iniTooltips();
    })
</script>

<div class="padding ui-widget-content ui-corner-all margin">
    <table>
        <tr>
            <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(124) ?>:</td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(255) ?>"></span></td>
            <td class="fullWidth">
                <div class="horizontalMargin">
                    <select id="formTableSelectMySQLTable" class="fullWidth ui-widget-content ui-corner-all">
                        <option value="0">&lt;<?= Language::string(73) ?>&gt;</option>
                        <?php
                        $sql = "SHOW TABLES";
                        $z = mysql_query($sql);
                        while ($r = mysql_fetch_array($z))
                        {
                            if (in_array($r[0], Ini::get_system_tables()) || (strpos($r[0], Table::get_table_prefix()) !== false && strpos($r[0], Table::get_table_prefix()) == 0))
                                    continue;
                            ?>
                            <option value="<?= $r[0] ?>"><?= $r[0] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</div>