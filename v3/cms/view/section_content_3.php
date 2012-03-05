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
?>

<b><?= Language::string(219) ?>:</b> 
<select id="selectGoTo_<?= $_POST['counter'] ?>" class="fullWidth ui-widget-content ui-corner-all">
</select>
<br/><br/>
<b><?= Language::string(113) ?>:</b>
<div class="ui-widget-content ui-state-focus">
    <div>
        <table>
            <tr>
                <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= Language::string(220) ?>"></span></td>
                <td>CURRENT_SECTION_INDEX</td>
            </tr>
        </table>
    </div>
    <div class="notVisible">
        <input class="inputReturnVar" type="hidden" value="CURRENT_SECTION_INDEX" />
    </div>
</div>