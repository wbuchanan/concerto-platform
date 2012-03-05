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

//////////
if (array_key_exists("class_name", $_POST)) $class_name = $_POST['class_name'];
if (array_key_exists("list_length", $_POST))
        $list_length = $_POST['list_length'];
else $list_length = 10;
$list_caption = Language::string(199);
$empty_caption = Language::string(200);
//////////

$sql = $logged_user->mysql_list_rights_filter($class_name, "`" . $class_name . "`.`id` ASC");
$num_rows = mysql_num_rows(mysql_query($sql));

$cols = $class_name::get_list_columns();
$s = $class_name::get_searchable_list_columns_indexes();
?>
<script>
    $(function(){
<?php
if ($num_rows > 0)
{
    ?>
                Methods.iniListTableExtensions("#table<?= $class_name ?>List",<?= $class_name ?>.listLength,[<?= $s ?>],true,function(){
                    if(<?= $class_name ?>.onAfterChangeListLength) <?= $class_name ?>.onAfterChangeListLength();
                });
                Methods.iniSortableTableHeaders();
                Methods.iniTooltips();
<?php } ?>
    })
</script>

<div class="ui-widget-content ui-corner-all margin padding">
    <?php
    if ($num_rows > 0)
    {
        ?>
        <div align="center">
            <table>
                <tr>
                    <td class="noWrap"><?= Language::string(252) ?>: <input type="text" id="table<?= $class_name ?>ListPagerFilter" name="table<?= $class_name ?>ListPagerFilter" class="ui-widget-content ui-corner-all" /></td>
                    <td><span class="spanIcon tooltip ui-icon ui-icon-cancel" id="table<?= $class_name ?>ListPagerFilterReset" title="<?= Language::string(201) ?>"></span></td>
                </tr>
            </table>
        </div>
    <?php } ?>
    <table id="table<?= $class_name ?>List">
        <caption class="ui-widget-header"><?= $list_caption ?></caption>
        <thead>
            <tr>
                <?php
                foreach ($cols as $col)
                {
                    ?>
                    <th class="ui-widget-header thSortable noWrap"><?= $col["name"] ?></th>
                    <?php
                }
                ?>
                <th class="noWrap ui-widget-header"><?= Language::string(202) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z))
            {
                $obj = $class_name::from_mysql_id($r[0]);
                $editable = $logged_user->is_object_editable($obj);
                ?>
                <tr class="row<?= $class_name ?>" id="row<?= $class_name . $obj->id ?>">
                    <?php
                    for ($i = 0; $i < count($cols); $i++)
                    {
                        ?>
                        <td class="noWrap ui-widget-content"><?= $obj->get_list_column_value($i); ?></td>
                        <?php
                    }
                    ?>

                    <td class="noWrap ui-widget-header">
                        <table>
                            <tr>
                                <td>
                                    <?php
                                    if ($editable)
                                    {
                                        ?><span class="spanIcon tooltip ui-icon ui-icon-pencil" onclick="<?= $class_name ?>.uiEdit(<?= $obj->id ?>)" title="<?= Language::string(203) ?>"></span>
                                    <?php } else
                                            echo '&nbsp;'; ?> 
                                </td>
                                <td>
                                    <?php
                                    if ($editable)
                                    {
                                        ?><span class="spanIcon tooltip ui-icon ui-icon-trash" onclick="<?= $class_name ?>.uiDelete(<?= $obj->id ?>)" title="<?= Language::string(204) ?>"></span>
                                    <?php } else
                                            echo '&nbsp;'; ?> 
                                </td>
                                <td>
                                    <?php
                                    if ($class_name::$exportable)
                                    {
                                        ?><span class="spanIcon tooltip ui-icon ui-icon-arrowthickstop-1-n" onclick="<?= $class_name ?>.uiExport(<?= $obj->id ?>)" title="<?= Language::string(265) ?>"></span>
                                    <?php } else
                                            echo '&nbsp;'; ?> 
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <table>
        <tr>
            <?php
            if ($logged_user->is_module_writeable($class_name))
            {
                ?>
                <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="<?= $class_name ?>.uiAdd()" title="<?= Language::string(205) ?>"></span></td>
                <?php
            }
            if ($class_name::$exportable)
            {
                ?><td><span class="spanIcon tooltip ui-icon ui-icon-arrowthickstop-1-s" onclick="<?= $class_name ?>.uiImport()" title="<?= Language::string(266) ?>"></span></td>
                <?php
            }
            ?>
        </tr>
    </table>
    <?php
    if ($num_rows == 0)
    {
        ?>
        <div class="ui-state-error padding margin" align="center"><?= $empty_caption ?></div>
        <?php
    }
    else
    {
        ?>
        <div class="pager" align="center" id="table<?= $class_name ?>ListPager">
            <table>
                <tr>
                    <td valign="middle"><span class="spanIcon tooltip ui-icon ui-icon-seek-first first" title="<?= Language::string(206) ?>"></span></td>
                    <td valign="middle"><span class="spanIcon tooltip ui-icon ui-icon-seek-prev prev" title="<?= Language::string(207) ?>"></span></td>
                    <td valign="middle"><input type="text" class="pagedisplay ui-widget-content ui-corner-all" readonly style="width: 50px;" /></td>
                    <td valign="middle">
                        <select id="selectPager<?= $class_name ?>" class="pagesize ui-widget-content ui-corner-all" onchange="<?= $class_name ?>.uiChangeListLength(this.value)">
                            <option value="10" <?= $list_length == 10 ? "selected" : "" ?>>10 <?= Language::string(210) ?></option>
                            <option value="25" <?= $list_length == 25 ? "selected" : "" ?>>25 <?= Language::string(210) ?></option>
                            <option value="50" <?= $list_length == 50 ? "selected" : "" ?>>50 <?= Language::string(210) ?></option>
                            <option value="100" <?= $list_length == 100 ? "selected" : "" ?>>100 <?= Language::string(210) ?></option>
                        </select>
                    </td>
                    <td valign="middle"><span class="spanIcon tooltip ui-icon ui-icon-seek-next next" title="<?= Language::string(208) ?>"></span></td>
                    <td valign="middle"><span class="spanIcon tooltip ui-icon ui-icon-seek-end last" title="<?= Language::string(209) ?>"></span></td>
                </tr>
            </table>
        </div>
    <?php } ?>
</div>