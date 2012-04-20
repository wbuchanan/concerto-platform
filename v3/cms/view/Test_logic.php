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

if (!$logged_user->is_module_writeable($class_name)) die(Language::string(81));
if (!$logged_user->is_object_editable($obj)) die(Language::string(81));
?>

<script>
    $(function(){
        Methods.iniIconButton("#btnDebugTest", "lightbulb");
        Methods.iniIconButton("#btnLogicVariables", "star");
        Methods.iniIconButton(".btnAddLogicSection", "plus");
        $( "#divTestLogic" ).sortable({
            items: "div.sortable",
            handle: ".sortableHandle"
        });
        
        Test.contentsToRefresh=0;
<?php
if ($oid != 0)
{
    $sections = TestSection::from_property(array("Test_id" => $obj->id));
    ?>
                Test.listenToSectionChanged=false;
                Test.setCounter(<?= $obj->get_max_counter() ?>);
    <?php
    $late_refresh_sections = array();
    $nested_refresh_sections = array();

    foreach ($sections as $section)
    {
        $vals = $section->get_values();
        ?>
                        Test.uiWriteSection(
        <?= $section->TestSectionType_id ?>, 
        <?= $section->parent_counter == 0 ? "null" : '$("#divSectionSubContent_' . $section->parent_counter . '")' ?>, 
        <?= $section->parent_counter ?>, 
        <?= $section->counter ?>,
                        null,
        <?= $section->id ?>,
                        false,
        <?= $section->TestSectionType_id == 3 ? "false" : "true" ?>
                    );
        <?php
        if ($section->TestSectionType_id == 3)
                array_push($late_refresh_sections, $section);
    }

    foreach ($late_refresh_sections as $section)
    {
        $vals = $section->get_values();
        ?>
                        Test.uiRefreshSectionContent(
        <?= $section->TestSectionType_id ?>,
        <?= $section->counter ?>, 
                        [<?= $vals[0] ?>], 
        <?= $section->id ?>
                    );
        <?php
    }
    ?>
                Test.listenToSectionChanged=true;
    <?php
}
?>
    });
</script>

<div class="margin" align="center">
    <table>
        <tr>
            <td>
                <button id="btnLogicVariables" onclick="Test.uiShowVarsDialog()"><?= Language::string(144) ?></button>
            </td>
            <td>
                <button id="btnDebugTest" onclick="Test.uiIniDebug()"><?= Language::string(284) ?></button>
            </td>
        </tr>
    </table>
</div>

<?php include Ini::$path_internal."cms/view/Test_security.php"; ?>
<br/>
<div class="ui-widget-header margin"><?= Language::string(359) ?></div>
<div id="divTestLogic" class="">
    <div id="divTestEmptyLogic" class="margin padding ui-state-error" align="center">
        <?= Language::string(145) ?>
    </div>

</div>

<div id="divTestDialog" class="notVisible">
    <?php include Ini::$path_internal . 'cms/view/Test_section_dialog.php'; ?>
</div>