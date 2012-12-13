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

$owner = User::from_mysql_id($_POST['oid']);
$shares = json_decode($_POST['shares']);
?>

<fieldset class="padding ui-widget-content ui-corner-all margin">
    <legend>
        <table>
            <tr>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(649) ?>"></span></td>
                <td class=""><b><?= Language::string(648) ?></b></td>
            </tr>
        </table>
    </legend>
    <div class="horizontalMargin">
        <select id = "selectUserInviteeShareDialog" class = "fullWidth ui-widget-content ui-corner-all">
            <option value = "0">&lt;<?= Language::string(650) ?>&gt;</option>
            <?php
            $sql = sprintf("SELECT * FROM `%s`.`%s` WHERE `id`!='%s' ORDER BY `lastname` ASC, `firstname` ASC", Ini::$db_master_name, User::get_mysql_table(), $owner->id);
            $z = mysql_query($sql);
            while ($r = mysql_fetch_array($z)) {
                $user = User::from_mysql_result($r);
                $ignore = false;
                foreach ($shares as $share) {
                    if ($share->invitee_id == $user->id && $_POST['current_invitee_id'] != $share->invitee_id) {
                        $ignore = true;
                        break;
                    }
                }
                if (!$ignore) {
                    ?>
                    <option value="<?= $user->id ?>" name="<?= $user->get_full_name() ?>" institution="<?= $user->institution_name ?>" <?= $_POST['current_invitee_id'] == $user->id ? "selected" : "" ?>><?= $user->get_full_description() ?></option>
                    <?php
                }
            }
            ?>
        </select>
    </div>

</fieldset>