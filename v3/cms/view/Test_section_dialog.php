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

<span><?= Language::string(146) ?>:</span>
<div class="padding ui-widget-content ui-corner-all margin">
    <table>
        <tr>
            <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(122) ?></td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(253) ?>"></span></td>
            <td class="fullWidth">
                <select id="formTestSelectSectionType" class="fullWidth ui-widget-content ui-corner-all">
                    <optgroup label="<?= Language::string(147) ?>">
                        <?php
                        foreach (DS_TestSectionType::get_all_selectable() as $section)
                        {
                            ?>
                            <option id="optionSectionType<?= $section->id ?>" value="<?= $section->id ?>" ><?= $section->get_name() ?></option>
                        <?php } ?>
                    </optgroup>
                    <?php
                    $sql = $logged_user->mysql_list_rights_filter("CustomSection", "`name` ASC");
                    $z = mysql_query($sql);
                    if (mysql_num_rows($z) > 0)
                    {
                        ?>
                        <optgroup label="<?= Language::string(148) ?>">
                            <?php
                            while ($r = mysql_fetch_array($z))
                            {
                                $cs = CustomSection::from_mysql_id($r[0]);
                                ?>
                                <option id="optionSectionType<?= DS_TestSectionType::CUSTOM ?>" value="<?= DS_TestSectionType::CUSTOM ?>:<?= $cs->id ?>" ><?= $cs->name ?> ( <?= $cs->get_system_data() ?> )</option>
                                <?php
                            }
                            ?>
                        </optgroup>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
</div>