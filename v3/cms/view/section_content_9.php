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
if ($logged_user == null)
{
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}

//$vals[0] - csid

$vals = $_POST['value'];
$section = null;
if (array_key_exists('oid', $_POST) && $_POST['oid'] != 0)
{
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
}
$section = CustomSection::from_mysql_id($vals[0]);
$parameters = $section->get_parameter_CustomSectionVariables();
$returns = $section->get_return_CustomSectionVariables();
?>

<div class="divSectionSummary sortableHandle" onmouseover="Test.uiToggleHover(<?=$_POST['counter']?>,true);" onmouseout="Test.uiToggleHover(<?=$_POST['counter']?>,false);">
    <table class="fullWidth tableSectionHeader">
        <tr>
            <!--<td class="tdSectionColumnIcon"></td>-->
            <td class="ui-widget-header tdSectionColumnCounter" id="tooltipSectionDetail_<?= $_POST['counter'] ?>" title=""><?= $_POST['counter'] ?></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= DS_TestSectionType::get_description_by_id(9) ?>"></span></td>
            <td class="tdSectionColumnIcon"><span id="spanExpandDetail_<?=$_POST['counter']?>" class="spanIcon ui-icon ui-icon-folder-<?=$_POST['detail']==1?"open":"collapsed"?> tooltip" title="<?= Language::string(390) ?>" onclick="Test.uiToggleDetails(<?=$_POST['counter']?>)"></span></td>
            <td class="tdSectionColumnType"><?= DS_TestSectionType::get_name_by_id(9) ?></td>
            <td class="tdSectionColumnAction"></td>
            <td class="tdSectionColumnEnd"><table><tr><td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(369) ?>"></span></td><td><?= Language::string(55) ?></td><td><input type="checkbox" id="chkEndSection_<?= $_POST['counter'] ?>" class="chkEndSection" <?=$_POST['end']==1?"checked":""?> /></td></tr></table></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Test.uiRemoveSection(<?= $_POST['counter'] ?>)" title="<?= Language::string(59) ?>"></span></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddLogicSection(0,<?=$_POST['counter']?>)" title="<?= Language::string(60) ?>"></span></td>
        </tr>
    </table>
</div>
<div class="divSectionDetail <?=$_POST['detail']==1?"":"notVisible"?>">
    <div class="ui-widget-header" align="center">
        <table>
            <tr>
                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars(Template::strip_html($section->description), ENT_QUOTES) ?>"></span></td>
                <td><?= $section->name . " ( " . $section->get_system_data() . " )" ?></td>
            </tr>
        </table>
    </div>
    <br/>

    <input type="hidden" class="controlValue<?= $_POST['counter'] ?>" value="<?= $vals[0] ?>" />
    <?php
    $j = 1;
    if (count($parameters) > 0)
    {
        ?>
        <b><?= Language::string(106) ?>:</b>
        <div class="ui-widget-content ui-state-focus">
            <div>
                <table>
                    <?php
                    for ($i = 0; $i < count($parameters); $i++)
                    {
                        ?>
                        <tr>
                            <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars(Template::strip_html($parameters[$i]->description), ENT_QUOTES) ?>"></span></td>
                            <td><?= $parameters[$i]->name ?></td>
                            <td><- <input type="text" class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all comboboxVars" value="<?= htmlspecialchars(isset($vals[$j]) ? $vals[$j] : $parameters[$i]->name, ENT_QUOTES) ?>" /></td>
                        </tr>
                        <?php
                        $j++;
                    }
                    ?>
                </table>
            </div>
        </div>
        <br/>
        <?php
    }

    if (count($returns) > 0)
    {
        ?>
        <b><?= Language::string(113) ?>:</b>
        <div class="ui-widget-content ui-state-focus">
            <div>
                <table>
                    <?php
                    for ($i = 0; $i < count($returns); $i++)
                    {
                        ?>
                        <tr>
                            <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars(Template::strip_html($returns[$i]->description), ENT_QUOTES) ?>"></span></td>
                            <td><?= $returns[$i]->name ?></td>
                            <td>->> <input onchange="Test.uiSetVarNameChanged($(this))" type="text" class="ui-state-focus comboboxSetVars comboboxVars controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all" value="<?= htmlspecialchars(isset($vals[$j]) ? $vals[$j] : $returns[$i]->name, ENT_QUOTES) ?>" /></td>
                        </tr>
                        <?php
                        $j = $j + 3;
                    }
                    ?>
                </table>
            </div>
        </div>
        <?php
    }
    ?>
</div>