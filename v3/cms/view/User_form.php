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

//////////
$class_name = "User";
$edit_caption = Language::string(170);
$new_caption = Language::string(171);
//////////

if(!$logged_user->is_module_writeable($class_name)) die(Language::string(81));

$oid = 0;
if (isset($_POST['oid']) && $_POST['oid'] != 0) $oid = $_POST['oid'];

$btn_cancel = "<button class='btnCancel' onclick='" . $class_name . ".uiEdit(0)'>".Language::string(23)."</button>";
$btn_delete = "<button class='btnDelete' onclick='" . $class_name . ".uiDelete($oid)'>".Language::string(94)."</button>";
$btn_save = "<button class='btnSave' onclick='" . $class_name . ".uiSave()'>".Language::string(95)."</button>";

$caption = "";
$buttons = "";
if ($oid > 0)
{
    $oid = $_POST['oid'];
    $obj = $class_name::from_mysql_id($oid);
    
    if(!$logged_user->is_object_editable($obj)) die(Language::string(81));

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
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(173)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(178)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="form<?= $class_name ?>InputLogin" value="<?= $obj->login ?>" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><input class="tooltip" type="checkbox" id="form<?= $class_name ?>CheckboxPassword" title="<?=Language::string(180)?>" /><?=Language::string(179)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(181)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="password" id="form<?= $class_name ?>InputPassword" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>

            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(182)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(183)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="password" id="form<?= $class_name ?>InputPasswordConf" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(184)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(186)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="form<?= $class_name ?>InputFirstname" value="<?= $obj->firstname ?>" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(185)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(187)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="form<?= $class_name ?>InputLastname" value="<?= $obj->lastname ?>" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(174)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(188)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="form<?= $class_name ?>InputEmail" value="<?= $obj->email ?>" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(189)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(190)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="form<?= $class_name ?>InputPhone" value="<?= $obj->phone ?>" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(176)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(191)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <select id="form<?= $class_name ?>SelectUserGroup" class="fullWidth ui-widget-content ui-corner-all">
                            <option value="0" <?= (!$obj->has_UserGroup() ? "selected" : "") ?>>&lt;<?=Language::string(73)?>&gt;</option>
                            <?php
                            $sql = $logged_user->mysql_list_rights_filter("UserGroup", "`name` ASC");
                            $z = mysql_query($sql);
                            while ($r = mysql_fetch_array($z))
                            {
                                $group = UserGroup::from_mysql_id($r[0]);
                                ?>
                                <option value="<?= $group->id ?>" <?= ($obj->UserGroup_id == $group->id ? "selected" : "") ?>><?= $group->name ?> ( <?= $group->get_system_data() ?> )</option>
                            <?php } ?>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(177)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?=Language::string(192)?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <select id="form<?= $class_name ?>SelectUserType" class="fullWidth ui-widget-content ui-corner-all">
                            <option value="0" <?= (!$obj->has_UserType() ? "selected" : "") ?>>&lt;<?=Language::string(73)?>&gt;</option>
                            <?php
                            $sql = $logged_user->mysql_list_rights_filter("UserType", "`name` ASC");
                            $z = mysql_query($sql);
                            while ($r = mysql_fetch_array($z))
                            {
                                $type = UserType::from_mysql_id($r[0]);
                                ?>
                                <option value="<?= $type->id ?>" <?= ($obj->UserType_id == $type->id ? "selected" : "") ?>><?= $type->name ?> ( <?= $type->get_system_data() ?> )</option>
                            <?php } ?>
                        </select>
                    </div>
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
    <div class="padding margin ui-state-error " align="center"><?=Language::string(123)?></div>
    <?php
}
?>