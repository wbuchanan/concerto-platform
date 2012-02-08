<?php
if (!isset($ini))
{
    require_once'../../Ini.php';
    $ini = new Ini();
}

$logged_user = User::get_logged_user();
if ($logged_user == null) die(Language::string(81));
?>

<style type="text/css">
<?php
$icons = "";
foreach (Language::languages() as $lng_node)
{
    $attr = $lng_node->getAttributeNode("id")->value;
    if ($icons != "") $icons.=",";
    $icons.="{find:'.flagIcon_" . $attr . "'} ";
    ?>
        .flagIcon_<?= $attr ?> .ui-selectmenu-item-icon { background: url(css/img/<?= $attr ?>.png) center no-repeat; }
<?php } ?>
</style>

<script type="text/javascript">
    $(function(){
        Methods.iniIconButton(".btnLogout", "person");
        
        $("#selectLanguage").selectmenu({
            icons:[<?= $icons ?>],
            style:"dropdown",
            width:100,
            change:function(){
                location.href='index.php?lng='+$(this).val();
            }
        });
    });
</script>

<table class="fullWidth">
    <tr>
        <td style="width:33%;" align="center" valign="middle">
            <?= Language::string(82) ?>: <b><?= $logged_user->login . "</b>, <b>" . $logged_user->get_full_name() ?></b>
            <button class="btnLogout" onclick="User.uiLogOut()"><?= Language::string(83) ?></button>
        </td>
        <td style="width:33%;" align="center" valign="middle">
            <h2>CONCERTO v<?=Ini::$version?></h2>
            <div id="divVersionCheck"></div>
        </td>
        <td style="width:33%;" align="center" valign="middle">
            <select id="selectLanguage">
                <?php
                foreach (Language::languages() as $lng_node)
                {
                    $attr = $lng_node->getAttributeNode("id")
                    ?>
                    <option class="flagIcon_<?= $attr->value ?>" value="<?= $attr->value ?>" <?= $_SESSION['lng'] == $attr->value ? "selected" : "" ?>><?= $lng_node->nodeValue ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
</table>