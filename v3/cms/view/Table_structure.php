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

if (!isset($ini)) {
    require_once'../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) {
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}

if (!$logged_user->is_module_writeable($class_name))
    die(Language::string(81));
if (!$logged_user->is_object_editable($obj))
    die(Language::string(81));
?>

<script>
    $(function(){
        Methods.iniIconButton("#btnExpand<?= $class_name ?>GridStructureContainer","arrowthick-1-n");
        Methods.iniIconButton("#btnExpand<?= $class_name ?>GridDataContainerExpandable","arrowthick-1-n");
        Table.uiIniHTMLTooltips();
        
        //table structure
        Table.uiIniStructureGrid();
        Table.uiIniDataGrid();
    });
</script>
<table>
    <tr>
        <td><button class="btnTableStructureImportTable" onclick="Table.uiImportTable()"><?= Language::string(125) ?></button></td>
        <td><button class="btnTableStructureImportCSV" onclick="Table.uiImportCSV()"><?= Language::string(126) ?></button></td>
        <td><button class="btnTableStructureExportCSV" onclick="Table.uiExportCSV()"><?= Language::string(127) ?></button></td>
    </tr>
</table>
<br/>

<!--grid magic starts here-->

<div class="margin" align="center"><button id="btnExpand<?= $class_name ?>GridStructureContainer" class="btnExpand fullWidth" onclick="Methods.toggleExpand('#div<?= $class_name ?>GridStructureContainer', this)"><?= Language::string(350) ?></button></div>
<div id="div<?= $class_name ?>GridStructureContainer" align="left" class="margin"></div>
<br/>
<div class="margin" align="center"><button id="btnExpand<?= $class_name ?>GridDataContainerExpandable" class="btnExpand fullWidth" onclick="Methods.toggleExpand('#div<?= $class_name ?>GridDataContainerExpandable', this)"><?= Language::string(351) ?></button></div>
<div id="div<?= $class_name ?>GridDataContainerExpandable">
    <div id="div<?= $class_name ?>DataStructureEmptyCaption" class="ui-state-error margin" style="display:none;"><?= Language::string(352) ?></div>
    <div id="div<?= $class_name ?>GridDataContainer" align="left" class="margin">
    </div>
</div>