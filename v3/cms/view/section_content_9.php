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

//$vals[0] - csid

$vals = $_POST['value'];
$section = null;
if (array_key_exists('oid', $_POST) && $_POST['oid'] != 0)
{
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
}
$section = CustomSection::from_mysql_id($vals[0]);
$parameters = $section->get_parameter_CustomSectionVariables();
$returns = $section->get_return_CustomSectionVariables();
?>

<div class="ui-widget-header" align="center">
    <table>
        <tr>
            <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars($section->description, ENT_QUOTES) ?>"></span></td>
            <td><?= $section->name . " ( " . $section->get_system_data() . " )" ?></td>
        </tr>
    </table>
</div>
<br/>

<input type="hidden" class="controlValue<?= $_POST['counter'] ?>" value="<?= $vals[0] ?>" />
<?php
$j = 1;
if (count($parameters) > 0)
{
    ?>
    <b><?= Language::string(106) ?>:</b>
    <div class="ui-widget-content ui-state-focus">
        <div>
            <table>
                <?php
                for ($i = 0; $i < count($parameters); $i++)
                {
                    ?>
                    <tr>
                        <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars($parameters[$i]->description, ENT_QUOTES) ?>"></span></td>
                        <td><?= $parameters[$i]->name ?></td>
                        <td><b><?= Language::string(279) ?></b> <input type="text" class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all comboboxVars" value="<?= htmlspecialchars(isset($vals[$j]) ? $vals[$j] : $parameters[$i]->name, ENT_QUOTES) ?>" /></td>
                    </tr>
                    <?php
                    $j++;
                }
                ?>
            </table>
        </div>
    </div>
    <br/>
    <?php
}

if (count($returns) > 0)
{
    ?>
    <b><?= Language::string(113) ?>:</b>
    <div class="ui-widget-content ui-state-focus">
        <div>
            <table>
                <?php
                for ($i = 0; $i < count($returns); $i++)
                {
                    ?>
                    <tr>
                        <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars($returns[$i]->description, ENT_QUOTES) ?>"></span></td>
                        <td><?= $returns[$i]->name ?></td>
                        <td><?= Language::string(279) ?> <input onchange="Test.uiSetVarNameChanged($(this))" type="text" class="ui-state-focus comboboxSetVars comboboxVars controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all" value="<?= htmlspecialchars(isset($vals[$j]) ? $vals[$j] : $returns[$i]->name, ENT_QUOTES) ?>" /></td>
                    </tr>
                    <?php
                    $j = $j + 3;
                }
                ?>
            </table>
        </div>
    </div>
    <?php
}
?>