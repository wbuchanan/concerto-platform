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
$class_name = "UserType";
$edit_caption = Language::string(157);
$new_caption = Language::string(158);
//////////

if (!$logged_user->is_module_writeable($class_name)) die(Language::string(81));

$oid = 0;
if (isset($_POST['oid']) && $_POST['oid'] != 0) $oid = $_POST['oid'];

$btn_cancel = "<button class='btnCancel' onclick='" . $class_name . ".uiEdit(0)'>" . Language::string(23) . "</button>";
$btn_delete = "<button class='btnDelete' onclick='" . $class_name . ".uiDelete($oid)'>" . Language::string(94) . "</button>";
$btn_save = "<button class='btnSave' onclick='" . $class_name . ".uiSave()'>" . Language::string(95) . "</button>";

$caption = "";
$buttons = "";
if ($oid > 0)
{
    $oid = $_POST['oid'];
    $obj = $class_name::from_mysql_id($oid);

    if (!$logged_user->is_object_editable($obj)) die(Language::string(81));

    $caption = $edit_caption . " #" . $oid;
    $buttons = $btn_cancel . $btn_save . $btn_delete;
}
else
{
    $obj = new $class_name();
    $caption = $new_caption;
    $buttons = "";
}

if ($oid != 0)
{
    ?>
    <script>
        $(function(){
            Methods.iniIconButton(".btnSave", "disk");
            Methods.iniIconButton(".btnDelete", "trash");
            Methods.iniIconButton(".btnCancel", "cancel");
            Methods.iniTooltips();
        });
    </script>

    <div class="padding ui-widget-content ui-corner-all margin">
        <table>
            <caption class="ui-widget-header"><?= $caption ?></caption>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(70) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(159) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="form<?= $class_name ?>InputName" value="<?= $obj->name ?>" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(72) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(160) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <select id="form<?= $class_name ?>SelectSharing" class="fullWidth ui-widget-content ui-corner-all">
                            <?php foreach (DS_Sharing::get_all() as $share)
                            { ?>
                                <option value="<?= $share->id ?>" <?= ($share->id == $obj->Sharing_id ? "selected" : "") ?>><?= $share->get_name() ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </td>
            </tr>
            <?php if ($oid > 0 && $logged_user->is_ownerhsip_changeable($obj))
            { ?>
                <tr>
                    <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(71) ?>:</td>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(161) ?>"></span></td>
                    <td class="fullWidth">
                        <div class="horizontalMargin">
                            <select id="form<?= $class_name ?>SelectOwner" class="fullWidth ui-widget-content ui-corner-all">
                                <option value="0" <?= (!$obj->has_Owner() ? "selected" : "") ?>>&lt;<?= Language::string(73) ?>&gt;</option>
                                <?php
                                $sql = $logged_user->mysql_list_rights_filter("User", "`User`.`lastname` ASC");
                                $z = mysql_query($sql);
                                while ($r = mysql_fetch_array($z))
                                {
                                    $owner = User::from_mysql_id($r[0]);
                                    ?>
                                    <option value="<?= $owner->id ?>" <?= ($obj->Owner_id == $owner->id ? "selected" : "") ?>><?= $owner->get_full_name() ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </td>
                </tr>
            <?php } ?>

            <tr>
                <td colspan="3"><hr/></td>
            </tr>
            <tr>
                <td colspan="3" id="form<?= $class_name ?>RightsList" align="center">
                    <table>
                        <caption class="ui-widget-header"><?= Language::string(162) ?></caption>
                        <thead>
                            <tr>
                                <th class="noWrap ui-widget-header"><?= Language::string(163) ?></th>
                                <th class="noWrap ui-widget-header"><?= Language::string(164) ?></th>
                                <th class="noWrap ui-widget-header"><?= Language::string(165) ?></th>
                                <th class="noWrap ui-widget-header"><?= Language::string(166) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $modules = DS_Module::get_all("`name` ASC");
                            foreach ($modules as $module)
                            {
                                ?>
                                <tr>
                                    <td class="noWrap ui-widget-content"><?= $module->name ?></td>
                                    <td class="noWrap ui-widget-content">
                                        <select class="fullWidth ui-widget-content ui-corner-all form<?= $class_name ?>ModuleRights" id="form<?= $class_name ?>ModuleRights_r_<?= $module->id ?>">
                                            <?php foreach (DS_Right::get_all() as $right)
                                            { ?>
                                                <option value="<?= $right->id ?>" <?= ($obj != null && $obj->get_rights_by_module($module->id)->read == $right->id ? "selected" : "") ?> ><?= $right->get_name() ?></option>
                                            <?php } ?>
                                        </select>
                                    </td> 
                                    <td class="noWrap ui-widget-content">
                                        <select class="fullWidth ui-widget-content ui-corner-all form<?= $class_name ?>ModuleRights" id="form<?= $class_name ?>ModuleRights_w_<?= $module->id ?>">
                                            <?php foreach (DS_Right::get_all() as $right)
                                            { ?>
                                                <option value="<?= $right->id ?>" <?= ($obj != null && $obj->get_rights_by_module($module->id)->write == $right->id ? "selected" : "") ?> ><?= $right->get_name() ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class="noWrap ui-widget-content">
                                        <input type="checkbox" class="form<?= $class_name ?>ModuleRights" id="form<?= $class_name ?>ModuleRights_o_<?= $module->id ?>" <?= $obj->get_rights_by_module($module->id)->ownership == 1 ? "checked" : "" ?>>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                    <?= $buttons ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
}
else
{
    ?>
    <div class="padding margin ui-state-error " align="center"><?= Language::string(123) ?></div>
    <?php
}
?>