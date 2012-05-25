<?php
/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

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
        $('#switcher').themeswitcher({
            loadTheme:"Cupertino",
            imgpath: "js/lib/themeswitcher/images/",
            onSelect:function(){
            }
        });
        
        Methods.iniIconButton(".btnLogout", "person");
        
        $("#selectLanguage").selectmenu({
            icons:[<?= $icons ?>],
            style:"dropdown",
            width:100,
            change:function(){
                Methods.modalLoading();
                location.href='index.php?lng='+$(this).val();
            }
        });
    });
</script>

<table>
    <tr>
        <td style="width:33%;" align="center" valign="middle">
            <?= Language::string(82) ?>: <b><?= $logged_user->login . "</b>, <b>" . $logged_user->get_full_name() ?></b>
            <button class="btnLogout" onclick="User.uiLogOut()"><?= Language::string(83) ?></button>
        </td>
        <td style="width:33%;" align="center" valign="middle">
            <table><tr><td align="center" valign="middle"><img src="css/img/logo.png" width="150" height="53" /></td><td align="center" valign="middle">v<?= Ini::$version ?></td></tr></table>
            <div id="divVersionCheck"></div>
        </td>
        <td style="width:33%;" align="center" valign="middle">
            <select id="selectLanguage" class="smallMargin">
                <?php
                foreach (Language::languages() as $lng_node)
                {
                    $attr = $lng_node->getAttributeNode("id")
                    ?>
                    <option class="flagIcon_<?= $attr->value ?>" value="<?= $attr->value ?>" <?= $_SESSION['lng'] == $attr->value ? "selected" : "" ?>><?= $lng_node->nodeValue ?></option>
                <?php } ?>
            </select>

            <div id="switcher" class="smallMargin"></div>
        </td>
    </tr>
</table>