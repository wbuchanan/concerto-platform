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

if(!$logged_user->is_module_writeable($class_name)) die(Language::string(81));
if(!$logged_user->is_object_editable($obj)) die(Language::string(81));
?>

<script>
    $(function(){
        Table.uiIniHTMLTooltips();
    });
</script>
<table>
    <tr>
        <td><span class="spanIcon tooltip ui-icon ui-icon-extlink" onclick="Table.uiImportTable()" title="<?=Language::string(125)?>"></span></td>
        <td><span class="spanIcon tooltip ui-icon ui-icon-arrowreturn-1-s" onclick="Table.uiImportCSV()" title="<?=Language::string(126)?>"></span></td>
        <td><span class="spanIcon tooltip ui-icon ui-icon-arrowreturn-1-n" onclick="Table.uiExportCSV()" title="<?=Language::string(127)?>"></span></td>
    </tr>
</table>
<table id="form<?= $class_name ?>Table">
    <caption class="ui-widget-header"><?=Language::string(128)?></caption>
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
                        <td><span class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="Table.uiEditColumn($(this).parent().parent().parent().parent().parent())" title="<?=Language::string(19)?>"></span></td>
                        <td><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="Table.uiRemoveColumn($(this).parent().parent().parent().parent().parent())" title="<?=Language::string(20)?>"></span></td>
                    </tr>
                </tbody>
            </table>
        </th>
        <?php
    }
}
?>
<th class="ui-widget-header" align="center"><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="<?= $class_name ?>.uiAddColumn()" title="<?=Language::string(129)?>"></span></th>
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
                                <span class="spanIcon tooltipTableStructure ui-icon ui-icon-document-b" onclick="Table.uiChangeHTML($(this).next())" title="<?=Language::string(130)?>"></span>
                                <textarea class="notVisible"><?= $r[$col->name] ?></textarea>
                            </div>
                    <?php
                }
            }
            ?>
                </td>
                <td class='ui-widget-header' align='center' style='width:50px;'><span class='spanIcon tooltip ui-icon ui-icon-trash' onclick='Table.uiRemoveRow($(this).parent().parent())' title="<?=Language::string(11)?>"></span></td>
            </tr>
        <?php
    }
}
?>
</tbody>
</table>
<div class="margin" align="center"><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="<?= $class_name ?>.uiAddRow()" title="<?=Language::string(131)?>"></span></div>
<div class="margin ui-widget-content ui-state-error" id="div<?= $class_name ?>EmptyTable"><?=Language::string(132)?></div>