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

$vals = array();
if (array_key_exists('value', $_POST))
{
    $vals = $_POST['value'];
}
if (array_key_exists('oid', $_POST) && $_POST['oid'] != 0)
{
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
}
?>  

<div class="divSectionSummary sortableHandle" onmouseover="Test.uiToggleHover(<?=$_POST['counter']?>,true);" onmouseout="Test.uiToggleHover(<?=$_POST['counter']?>,false);">
    <table class="fullWidth tableSectionHeader">
        <tr>
            <!--<td class="tdSectionColumnIcon"></td>-->
            <td class="ui-widget-header tdSectionColumnCounter" id="tooltipSectionDetail_<?= $_POST['counter'] ?>" title=""><?= $_POST['counter'] ?></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= DS_TestSectionType::get_description_by_id(10) ?>"></span></td>
            <td class="tdSectionColumnIcon"><span id="spanExpandDetail_<?= $_POST['counter'] ?>" class="spanIcon ui-icon ui-icon-folder-<?= $_POST['detail'] == 1 ? "open" : "collapsed" ?> tooltip" title="<?= Language::string(390) ?>" onclick="Test.uiToggleDetails(<?= $_POST['counter'] ?>)"></span></td>
            <td class="tdSectionColumnType"><?= DS_TestSectionType::get_name_by_id(10) ?></td>
            <td class="tdSectionColumnAction">
                <?= Language::string(357) ?>
                <input type="text" class="ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[0], ENT_QUOTES) ?>" />
                <select class="ui-widget-content ui-corner-all controlValue<?= $_POST['counter'] ?>">
                    <option value="!=" <?= $vals[1] == "!=" ? "selected" : "" ?>><?= Language::string(221) ?></option>
                    <option value="==" <?= $vals[1] == "==" ? "selected" : "" ?>><?= Language::string(222) ?></option>
                    <option value=">" <?= $vals[1] == ">" ? "selected" : "" ?>><?= Language::string(223) ?></option>
                    <option value=">=" <?= $vals[1] == ">=" ? "selected" : "" ?>><?= Language::string(224) ?></option>
                    <option value="<" <?= $vals[1] == "<" ? "selected" : "" ?>><?= Language::string(225) ?></option>
                    <option value="<=" <?= $vals[1] == "<=" ? "selected" : "" ?>><?= Language::string(226) ?></option>
                </select> 
                <input type="text" class="ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[2], ENT_QUOTES) ?>" /><br/>
            </td>
            <td class="tdSectionColumnEnd"><table><tr><td></td></tr></table></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Test.uiRemoveSection(<?= $_POST['counter'] ?>)" title="<?= Language::string(59) ?>"></span></td>
            <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddLogicSection(0,<?=$_POST['counter']?>)" title="<?= Language::string(60) ?>"></span></td>
        </tr>
    </table>
</div>
<div class="divSectionDetail <?= $_POST['detail'] == 1 ? "" : "notVisible" ?>">
    <?php
    $i = 3;
    while (isset($vals[$i]))
    {
        ?>
        <select class="controlValue<?= $_POST['counter'] ?> controlValue<?= $_POST['counter'] ?>_link ui-widget-content ui-corner-all">
            <option value="&&" <?= isset($vals[$i]) && $vals[$i] == "&&" ? "selected" : "" ?>><?= Language::string(227) ?></option>
            <option value="||" <?= isset($vals[$i]) && $vals[$i] == "||" ? "selected" : "" ?>><?= Language::string(228) ?></option>
        </select> 
        <?php $i++; ?>
        <input type="text" class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all comboboxVars" value="<?= htmlspecialchars($vals[$i], ENT_QUOTES) ?>" />
        <?php $i++; ?>
        <select class="ui-widget-content ui-corner-all controlValue<?= $_POST['counter'] ?>">
            <option value="!=" <?= $vals[$i] == "!=" ? "selected" : "" ?>><?= Language::string(221) ?></option>
            <option value="==" <?= $vals[$i] == "==" ? "selected" : "" ?>><?= Language::string(222) ?></option>
            <option value=">" <?= $vals[$i] == ">" ? "selected" : "" ?>><?= Language::string(223) ?></option>
            <option value=">=" <?= $vals[$i] == ">=" ? "selected" : "" ?>><?= Language::string(224) ?></option>
            <option value="<" <?= $vals[$i] == "<" ? "selected" : "" ?>><?= Language::string(225) ?></option>
            <option value="<=" <?= $vals[$i] == "<=" ? "selected" : "" ?>><?= Language::string(226) ?></option>
        </select> 
        <?php $i++; ?>
        <input type="text" class="ui-widget-content ui-corner-all comboboxVars controlValue<?= $_POST['counter'] ?>" value="<?= htmlspecialchars($vals[$i], ENT_QUOTES) ?>" /><br/>
        <?php $i++; ?>
        <?php
    }
    ?>

    <table>
        <tr>
            <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddIfCond(<?= $_POST['counter'] ?>)" title="<?= Language::string(229) ?>"></span></td>
            <td><?php if (isset($vals[3]))
    { ?><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveIfCond(<?= $_POST['counter'] ?>)" title="<?= Language::string(230) ?>"></span><?php } ?></td>
        </tr>
    </table>

    <?= Language::string(231) ?>
</div>