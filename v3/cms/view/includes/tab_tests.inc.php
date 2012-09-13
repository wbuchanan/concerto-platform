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
if ($logged_user == null)
    die(Language::string(81));

$class_name = "Test";
$class_label = Language::string(88);
$readable = $logged_user->is_module_accesible($class_name);
$writeable = $logged_user->is_module_writeable($class_name);

include Ini::$path_internal . "cms/view/includes/tab.inc.php";
?>

<div id="div<?= $class_name ?>DialogImport" class="notVisible">
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(267) ?>"></span></td>
                    <td class=""><b><?= Language::string(86) ?>:</b></td>
                </tr>
            </table>
        </legend>
        <input id="file<?= $class_name ?>Import" type="file" name="files[]" class="fullWidth ui-widget-content ui-corner-all" />
    </fieldset>
</div>

<div id="divTestVarsDialog" class="notVisible">
</div>

<div id="divTestDebugDialog" class="notVisible">
    <div id="divTestDebugAccordion" class="margin">
        <h3><a href="#"><?= Language::string(365) ?></a></h3>
        <div id="divTestDebugConsole"></div>
        <h3><a href="#"><?= Language::string(364) ?> <?= Language::string(363) ?></a></h3>
        <div id="divTestDebugTest"></div>
    </div>
</div>

<div id="div<?= $class_name ?>DialogDescription" class="notVisible">
    <fieldset class="padding ui-widget-content ui-corner-all margin">
        <legend>
            <table>
                <tr>
                    <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(254) ?>"></span></td>
                    <td class=""><b><?= Language::string(97) ?>:</b></td>
                </tr>
            </table>
        </legend>
        <textarea id="dialog<?= $class_name ?>TextareaDescription" name="dialog<?= $class_name ?>TextareaDescription" class="fullWidth ui-widget-content ui-corner-all">
        </textarea>
    </fieldset>
</div>