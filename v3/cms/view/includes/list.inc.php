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
    $logged_user = User::get_logged_user();
}

if ($logged_user == null)
{
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}
$writeable = $logged_user->is_module_writeable($class_name);

?>
<script type="text/x-kendo-template" id="script<?= $class_name ?>ToolbarTemplate">
    <div class="toolbar">
        <?php
        if ($writeable)
        {
            ?>
            <span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-plus" onclick="<?= $class_name ?>.uiAdd()" title="<?= Language::string(205) ?>"></span>
            <?php
        }
        if ($class_name::$exportable)
        {
            ?><span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-arrowthickstop-1-s" onclick="<?= $class_name ?>.uiImport()" title="<?= Language::string(266) ?>"></span>
            <?php
        }
        ?>
    </div>
</script>
<?php

$cols = $class_name::get_list_columns();
$fields_schema = "";
foreach ($cols as $col)
{
    if ($fields_schema != "") $fields_schema.=",";
    else $fields_schema.="{";
    $fields_schema.=sprintf("'%s': { type: '%s'}", $col["property"], $col["type"]);
}
$fields_schema.="}";

$columns_def = "[";
foreach ($cols as $col)
{
    $columns_def.="{";
    if (array_key_exists("width", $col))
            $columns_def.=sprintf("width: %s,", $col["width"]);
    $columns_def.=sprintf("title: '%s',", $col["name"]);
    $columns_def.=sprintf("field: '%s',", $col["property"]);
    $columns_def.=sprintf("filterable: %s,", $col["searchable"] ? "true" : "false");
    $columns_def.=sprintf("sortable: %s,", $col["sortable"] ? "true" : "false");
    $columns_def.=sprintf("groupable: %s", $col["groupable"] ? "true" : "false");
    $columns_def.="}";
    $columns_def.=",";
}

$action_template='#if(editable) {#<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="' . $class_name . '.uiEdit(${ id })" title="' . Language::string(203) . '"></span>#}#';
$action_template.='#if(editable) {#<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-trash" onclick="' . $class_name . '.uiDelete(${ id })" title="' . Language::string(204) . '"></span>#}#';
if ($class_name::$exportable)
{
    $action_template.='<span style="display:inline-block;" class="spanIcon tooltip ui-icon ui-icon-arrowthickstop-1-n" onclick="' . $class_name . '.uiExport(${ id })" title="' . Language::string(265) . '"></span>';
}
$columns_def.=sprintf("{ title:'', width:80, field: 'actions', filterable: false, sortable: false, groupable: false, template: '%s'}", $action_template);
$columns_def.="]";
?>

<script>
    $(function(){
        $("#div<?= $class_name ?>Grid").kendoGrid({
            dataBound:function(e){
                Methods.iniTooltips();
            },
            toolbar: kendo.template($("#script<?= $class_name ?>ToolbarTemplate").html()),
            dataSource: {
                transport:{
                    read: {
                        url:"query/get_object_list.php?class_name=<?= $class_name ?>",
                        dataType:"json"
                    }
                },
                schema:{
                    model:{
                        fields:<?= $fields_schema ?>
                    }
                },
                pageSize:<?= $class_name ?>.listLength
            },
            //scrollable: {
            //    virtual: true
            //},
            height:500,
            filterable:true,
            sortable:true,
            pageable:true,
            groupable:true,
            columns:<?= $columns_def ?>
        });
    });
</script>

<div id="div<?= $class_name ?>Grid" style="min-width:800px;" align="left" class="margin">
</div>