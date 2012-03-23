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

if (!$logged_user->is_module_writeable($class_name)) die(Language::string(81));
if (!$logged_user->is_object_editable($obj)) die(Language::string(81));
?>

<script>
    $(function(){
        Table.uiIniHTMLTooltips();
        Methods.iniIconButton("#btnTableStructureImportTable", "arrowthickstop-1-s");
        Methods.iniIconButton("#btnTableStructureImportCSV", "arrowthickstop-1-s");
        Methods.iniIconButton("#btnTableStructureExportCSV", "arrowthickstop-1-n");
        
        //table structure
        Table.uiIniStructureGrid();
        Table.uiIniDataGrid();
    });
</script>
<table>
    <tr>
        <td><button id="btnTableStructureImportTable" onclick="Table.uiImportTable()"><?= Language::string(125) ?></button></td>
        <td><button id="btnTableStructureImportCSV" onclick="Table.uiImportCSV()"><?= Language::string(126) ?></button></td>
        <td><button id="btnTableStructureExportCSV" onclick="Table.uiExportCSV()"><?= Language::string(127) ?></button></td>
    </tr>
</table>
<br/>

<!--grid magic starts here-->

<div id="div<?=$class_name?>GridStructure" align="left"></div>
<br/>
<div id="div<?=$class_name?>GridDataContainer" align="left">
</div>
<br/>

<table id="form<?= $class_name ?>Table">
    <caption class="ui-widget-header"><?= Language::string(128) ?></caption>
    <thead class="theadTable">
        <tr>
            <?php
            $cols = array();
            $types = array();
            if ($obj->has_table())
            {
                $cols = TableColumn::from_property(array("Table_id" => $obj->id));
                foreach ($cols as $col)
                {
                    $type = DS_TableColumnType::from_mysql_id($col->TableColumnType_id);
                    array_push($types, $type);
                    ?>
                    <th class="ui-widget-header thSortable noWrap" colname="<?= $col->name ?>" coloid="<?= $col->id ?>" coltype="<?= $col->TableColumnType_id ?>">
            <table class="fullWidth">
                <tbody>
                    <tr>
                        <td class="fullWidth"><?= $col->name ?> ( <?= $type->get_name() ?> )</td>
                        <td><span class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="Table.uiEditColumn($(this).parent().parent().parent().parent().parent())" title="<?= Language::string(19) ?>"></span></td>
                        <td><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Table.uiRemoveColumn($(this).parent().parent().parent().parent().parent())" title="<?= Language::string(20) ?>"></span></td>
                    </tr>
                </tbody>
            </table>
        </th>
        <?php
    }
}
?>
<th class="ui-widget-header" align="center"><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="<?= $class_name ?>.uiAddColumn()" title="<?= Language::string(129) ?>"></span></th>
</tr>
</thead>
<tbody class="tbodyTable">
    <?php
    if ($obj->has_table())
    {
        $sql = sprintf("SELECT * FROM `%s`", $obj->get_table_name());
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z))
        {
            ?>
            <tr>
                <?php
                foreach ($cols as $col)
                {
                    ?>
                    <td class='noWrap ui-widget-content'>
                        <?php
                        if ($col->TableColumnType_id == 1)
                        {
                            ?>
                            <div class="horizontalMargin"><input type="text" value="<?= htmlspecialchars($r[$col->name], ENT_QUOTES) ?>" class="fullWidth ui-widget-content ui-corner-all" /></div>
                            <?php
                        }
                        if ($col->TableColumnType_id == 2)
                        {
                            ?>
                            <div class="horizontalMargin"><input type="text" value="<?= htmlspecialchars($r[$col->name], ENT_QUOTES) ?>" class="fullWidth ui-widget-content ui-corner-all" /></div>
                            <?php
                        }
                        if ($col->TableColumnType_id == 3)
                        {
                            ?>
                            <div class="horizontalMargin" align="center">
                                <span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next())" title="<?= Language::string(130) ?>"></span>
                                <textarea class="notVisible"><?= $r[$col->name] ?></textarea>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </td>
                <td class='ui-widget-header' align='center' style='width:50px;'><span class='spanIcon tooltip ui-icon ui-icon-trash' onclick='Table.uiRemoveRow($(this).parent().parent())' title="<?= Language::string(11) ?>"></span></td>
            </tr>
            <?php
        }
    }
    ?>
</tbody>
</table>
<div class="margin" align="center"><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="<?= $class_name ?>.uiAddRow()" title="<?= Language::string(131) ?>"></span></div>
<div class="margin ui-widget-content ui-state-error" id="div<?= $class_name ?>EmptyTable"><?= Language::string(132) ?></div>