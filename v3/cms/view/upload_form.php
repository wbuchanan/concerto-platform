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

$obj = $_POST['class_name']::from_mysql_id($_POST['oid']);

if ($obj == null || !$logged_user->is_object_readable($obj))
    die(Language::string(81));
?>

<script>
    $(function(){
        Methods.iniTooltips();
    })
</script>

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(499) ?>"></span></td>
                <td class=""><b><?= Language::string(497) ?></b></td>
            </tr>
        </table>
    </legend>
    <table>
        <tr>
            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(70) ?>:</td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(376) ?>"></span></td>
            <td class="fullWidth">
                <div class="horizontalMargin" align="center">
                    <input id="inputDialogUploadName" type="text" class="ui-widget-content ui-corner-all fullWidth" value="<?= $obj->name ?>" />
                </div>
            </td>
        </tr>
        <tr>
            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(97) ?>:</td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(377) ?>"></span></td>
            <td class="fullWidth">
                <div class="horizontalMargin" align="center">
                    <textarea id="textareaDialogUploadDescription" name="textareaDialogUploadDescription" class="ui-widget-content ui-corner-all"><?= $obj->description ?></textarea>
                </div>
            </td>
        </tr>
        <tr>
            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(378) ?>:</td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(380) ?>"></span></td>
            <td class="fullWidth">
                <div class="horizontalMargin" align="center">
                    <input id="inputDialogUploadAuthor" type="text" class="ui-widget-content ui-corner-all fullWidth" value="<?= $obj->get_owner_full_name() ?>" />
                </div>
            </td>
        </tr>
        <tr>
            <td class="noWrap horizontalPadding tdFormLabel"><?= Language::string(379) ?>:</td>
            <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(381) ?>"></span></td>
            <td class="fullWidth">
                <div class="horizontalMargin" align="center">
                    <input id="inputDialogUploadRevision" type="text" class="ui-widget-content ui-corner-all fullWidth" value="1" />
                </div>
            </td>
        </tr>
    </table>
</fieldset>
