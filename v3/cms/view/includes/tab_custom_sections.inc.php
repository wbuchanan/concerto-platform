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

$class_name = "CustomSection";
$class_label = Language::string(84);
$readable = $logged_user->is_module_accesible($class_name);
$writeable = $logged_user->is_module_writeable($class_name);

include Ini::$path_internal."cms/view/includes/tab.inc.php"; 
?>

<div id="divCustomSectionDialogImport" class="notVisible">
    <div class="padding ui-widget-content ui-corner-all margin">
        <table>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(86) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(267) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin" align="center">
                        <input id="fileCustomSectionImport" type="file" name="files[]" class="fullWidth ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div id="divCustomSectionDialogDescription" class="notVisible">
    <div class="padding ui-widget-content ui-corner-all margin">
        <table>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(97) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(254) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <textarea id="formDialogCustomSectionTextareaDescription" name="formDialogCustomSectionTextareaDescription" class="fullWidth ui-widget-content ui-corner-all">
                                                                                                
                        </textarea>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>