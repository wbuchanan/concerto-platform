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
?>

<div class="divSectionContent">
    <div class="divSectionSummary sortableHandle">
        <table class="fullWidth tableSectionHeader">
            <tr>
                <td class="tdSectionColumnIcon"></td>
                <td class="ui-widget-header tdSectionColumnCounter"><?= $_POST['counter'] ?></td>
                <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= DS_TestSectionType::get_description_by_id(3) ?>"></span></td>
                <td class="tdSectionColumnIcon"><span class="spanIcon ui-icon ui-icon-folder-collapsed tooltip" title="<?= Language::string(390) ?>"></span></td>
                <td class="tdSectionColumnType"><?= DS_TestSectionType::get_name_by_id(3) ?></td>
                <td class="tdSectionColumnAction"></td>
                <td class="tdSectionColumnEnd"><table><tr><td></td></tr></table></td>
                <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Test.uiRemoveSection(<?= $_POST['counter'] ?>)" title="<?= Language::string(59) ?>"></span></td>
                <td class="tdSectionColumnIcon"><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddLogicSection(0)" title="<?= Language::string(60) ?>"></span></td>
            </tr>
        </table>
    </div>
    <div class="divSectionDetail notVisible">
        <b><?= Language::string(219) ?>:</b> 
        <select id="selectGoTo_<?= $_POST['counter'] ?>" class="fullWidth ui-widget-content ui-corner-all">
        </select>
    </div>
</div>
<div class="divSectionContainer">

</div>