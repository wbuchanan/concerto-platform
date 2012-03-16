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

if ($readable)
{
    ?>

    <script>
        $(function(){
            $( "#div<?= $class_name ?>RadioMenu" ).buttonset(); 
        });
    </script>

    <div align="center" id="div<?= $class_name ?>RadioMenu">
        <input type="radio" id="radio<?= $class_name ?>List" name="radio<?= $class_name ?>" checked="checked" onclick="<?= $class_name ?>.uiShowList();" />
        <label for="radio<?= $class_name ?>List"><?= Language::string(337) ?></label>
        <?php
        if ($writeable)
        {
            ?>
            <input type="radio" id="radio<?= $class_name ?>Form" name="radio<?= $class_name ?>" disabled="disabled" onclick="<?= $class_name ?>.uiShowForm();" />
            <label for="radio<?= $class_name ?>Form"><?= Language::string(338) ?> <?= Language::string(73) ?></label>
            <?php
        }
        ?>
    </div>

    <div align="center" id="div<?= $class_name ?>List" class="table">
        <?php include Ini::$path_internal . 'cms/view/includes/list.inc.php'; ?>
    </div>

    <div align="center" id="div<?= $class_name ?>Form" class="table" style="display:none;">
        <?php include Ini::$path_internal . 'cms/view/' . $class_name . '_form.php'; ?>
    </div>
    <?php
}
?>