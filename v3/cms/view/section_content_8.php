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

$vals = array();
if (array_key_exists('value', $_POST))
{
    $vals = $_POST['value'];
}
if (array_key_exists('oid', $_POST) && $_POST['oid'] != 0)
{
    $section = TestSection::from_mysql_id($_POST['oid']);
    $vals = $section->get_values();
}

$table = Table::from_mysql_id($vals[3]);
$description = Language::string(372);
if ($table != null)
{
    $description.=" " . Language::string(373) . ":<hr/>" . $table->get_description();
}
?>

<!-- type and table -->
<table>
    <tr>
        <td><b><?= Language::string(247) ?></b></td>
        <td><span class="spanIcon ui-icon ui-icon-help tooltip" title="<?= htmlspecialchars($description, ENT_QUOTES) ?>"></span></td>
        <td>
            <select id="selectTableModTable_<?= $_POST['counter'] ?>" class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all" onchange="Test.uiRefreshSectionContent(<?= $_POST['type'] ?>, <?= $_POST['counter'] ?>, Test.getSectionValues(Test.sectionDivToObject($('#divSection_<?= $_POST['counter'] ?>'))))">
                <option value="0">&lt;no table selected&gt;</option>
                <?php
                $sql = $logged_user->mysql_list_rights_filter("Table", "`name` ASC");
                $z = mysql_query($sql);
                while ($r = mysql_fetch_array($z))
                {
                    $table = Table::from_mysql_id($r[0]);
                    ?>
                    <option value="<?= $table->id ?>" <?= isset($vals[3]) && $vals[3] == $table->id ? "selected" : "" ?> ><?= $table->name ?> ( <?= $table->get_system_data() ?> )</option>
                <?php } ?>
            </select>
        </td>
    </tr>
</table>
<div align="center">
    <?= Language::string(248) ?> <input type="radio" name="radioTableModType_<?= $_POST['counter'] ?>" class="radioTableModType_<?= $_POST['counter'] ?> radioTableModType" <?= !isset($vals[0]) || $vals[0] == 0 ? "checked" : "" ?> value="0" onchange="Test.uiRefreshSection(<?= $_POST['counter'] ?>,Test.sectionTypes.tableModification)" />, 
    <?= Language::string(249) ?> <input type="radio" name="radioTableModType_<?= $_POST['counter'] ?>" class="radioTableModType_<?= $_POST['counter'] ?> radioTableModType" <?= $vals[0] == 1 ? "checked" : "" ?> value="1" onchange="Test.uiRefreshSection(<?= $_POST['counter'] ?>,Test.sectionTypes.tableModification)" />, 
    <?= Language::string(250) ?> <input type="radio" name="radioTableModType_<?= $_POST['counter'] ?>" class="radioTableModType_<?= $_POST['counter'] ?> radioTableModType" <?= $vals[0] == 2 ? "checked" : "" ?> value="2" onchange="Test.uiRefreshSection(<?= $_POST['counter'] ?>,Test.sectionTypes.tableModification)" />
</div>

<!-- set -->
<?php
if ($vals[0] == 0 || $vals[0] == 1)
{
    ?>
    <b><?= Language::string(251) ?></b>
    <br/>
    <?php
    for ($i = 0; $i < $vals[2]; $i++)
    {
        ?>
        <select class="controlValue<?= $_POST['counter'] ?> controlValue<?= $_POST['counter'] ?>_set ui-widget-content ui-corner-all">
            <option value="0">&lt;<?= Language::string(241) ?>&gt;</option>
            <?php
            $table = Table::from_mysql_id($vals[3]);
            if ($table != null)
            {
                $cols = $table->get_TableColumns();
                foreach ($cols as $col)
                {
                    ?>
                    <option value="<?= $col->index ?>" <?= $vals[4 + $i * 2] == $col->index ? "selected" : "" ?> ><?= $col->name ?></option>
                    <?php
                }
            }
            ?>
        </select> = 
        <input type="text" class="comboboxVars controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all" value="<?= htmlspecialchars(isset($vals[5 + $i * 2]) ? $vals[5 + $i * 2] : "", ENT_QUOTES) ?>" />
        <br/>
        <?php
    }
    ?>
    <table>
        <tr>
            <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddTableModSet(<?= $_POST['counter'] ?>)" title="<?= Language::string(129) ?>"></span></td>
            <td><?php
    if ($vals[2] > 0)
    {
        ?><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveTableModSet(<?= $_POST['counter'] ?>)" title="<?= Language::string(20) ?>"></span><?php } ?></td>
        </tr>
    </table>
    <?php
}
?>

<!-- where -->
<?php
if ($vals[0] == 1 || $vals[0] == 2)
{
    ?>
    <b><?= Language::string(242) ?></b>
    <br/>
    <?php
    $j = 4 + $vals[2] * 2;
    for ($i = 0; $i < $vals[1]; $i++)
    {
        ?>
        <select class="controlValue<?= $_POST['counter'] ?> controlValue<?= $_POST['counter'] ?>_link ui-widget-content ui-corner-all <?= $i == 0 ? "notVisible" : "" ?>">
            <option value="AND" <?= isset($vals[$j]) && $vals[$j] == "AND" ? "selected" : "" ?>><?= Language::string(227) ?></option>
            <option value="OR" <?= isset($vals[$j]) && $vals[$j] == "OR" ? "selected" : "" ?>><?= Language::string(228) ?></option>
        </select> 
        <?php $j++; ?>
        <select class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all">
            <option value="0">&lt;<?= Language::string(241) ?>&gt;</option>
            <?php
            $table = Table::from_mysql_id($vals[3]);
            if ($table != null)
            {
                $cols = $table->get_TableColumns();
                foreach ($cols as $col)
                {
                    ?>
                    <option value="<?= $col->index ?>" <?= isset($vals[$j]) && $vals[$j] == $col->index ? "selected" : "" ?> ><?= $col->name ?></option>
                    <?php
                }
            }
            ?>
        </select> 
        <?php $j++; ?>
        <select class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all">
            <option value="!=" <?= isset($vals[$j]) && $vals[$j] == "!=" ? "selected" : "" ?>><?= Language::string(221) ?></option>
            <option value="=" <?= isset($vals[$j]) && $vals[$j] == "=" ? "selected" : "" ?>><?= Language::string(222) ?></option>
            <option value=">" <?= isset($vals[$j]) && $vals[$j] == ">" ? "selected" : "" ?>><?= Language::string(223) ?></option>
            <option value=">=" <?= isset($vals[$j]) && $vals[$j] == ">=" ? "selected" : "" ?>><?= Language::string(224) ?></option>
            <option value="<" <?= isset($vals[$j]) && $vals[$j] == "<" ? "selected" : "" ?>><?= Language::string(225) ?></option>
            <option value="<=" <?= isset($vals[$j]) && $vals[$j] == "<=" ? "selected" : "" ?>><?= Language::string(226) ?></option>
            <option value="LIKE" <?= isset($vals[$j]) && $vals[$j] == "LIKE" ? "selected" : "" ?>><?= Language::string(243) ?></option>
            <option value="NOT LIKE" <?= isset($vals[$j]) && $vals[$j] == "NOT LIKE" ? "selected" : "" ?>><?= Language::string(244) ?></option>
        </select> 
        <?php $j++; ?>
        <input type="text" class="controlValue<?= $_POST['counter'] ?> ui-widget-content ui-corner-all comboboxVars" value="<?= htmlspecialchars(isset($vals[$j]) ? $vals[$j] : "", ENT_QUOTES) ?>" /> 
        <?php $j++; ?>
        <br/>
        <?php
    }
    ?>
    <table>
        <tr>
            <td><span class="spanIcon tooltip ui-icon ui-icon-plus" onclick="Test.uiAddTableModWhere(<?= $_POST['counter'] ?>)" title="<?= Language::string(229) ?>"></span></td>
            <td><?php if ($vals[1] > 0)
    { ?><span class="spanIcon tooltip ui-icon ui-icon-minus" onclick="Test.uiRemoveTableModWhere(<?= $_POST['counter'] ?>)" title="<?= Language::string(230) ?>"></span><?php } ?></td>
        </tr>
    </table>
    <?php
}
?>