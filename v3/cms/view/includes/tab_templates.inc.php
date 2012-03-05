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
if ($logged_user == null) die(Language::string(81));
?>

<table class="margin ui-widget-content ui-corner-all">
    <tr>
        <?php
        $class_name = "Template";
        $class_label = Language::string(87);
        if ($logged_user->is_module_accesible($class_name))
        {
            ?>
            <td colspan="2">
                <div class="fullWidth ui-widget-header" align="center" colspan="2">
                    <h3><?= $class_label ?></h3>
                </div>
            </td>
        </tr>
        <tr>
            <td class="padding" valign="top">
                <div align="center" id="div<?= $class_name ?>List"><?php include Ini::$path_internal . 'cms/view/list.php'; ?></div>
            </td>

            <td class="padding" valign="top">
                <?php if ($logged_user->is_module_writeable($class_name))
                { ?>
                    <div align="center" id="div<?= $class_name ?>Form"><?php include Ini::$path_internal . 'cms/view/' . $class_name . '_form.php'; ?></div><br />
                <?php } ?>
            </td>
        <?php } ?>
    </tr>
</table>

<div id="divTemplateDialogImport" class="notVisible">
    <div class="padding ui-widget-content ui-corner-all margin">
        <table>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(86) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(267) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="fileTemplateImport" type="file" name="files[]" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>