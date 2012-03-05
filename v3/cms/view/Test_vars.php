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

<span></span>
<div class="padding ui-widget-content ui-corner-all margin">
    <div class="padding ui-widget-content ui-corner-all margin">
        <table class="fullWidth">
            <thead>
                <tr>
                    <th class="noWrap horizontalPadding ui-widget-header"><?= Language::string(149) ?></th>
                    <th class="noWrap horizontalPadding ui-widget-header"><?= Language::string(150) ?></th>
                    <th class="noWrap horizontalPadding ui-widget-header"><?= Language::string(151) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($_POST['vars'] as $var)
                {
                    $var = json_decode($var);
                    ?>
                    <tr>
                        <td class="noWrap horizontalPadding ui-widget-header"><?= $var->name ?></td>
                        <td class="horizontalPadding ui-widget-content">
                            <ul>
                                <?php
                                foreach ($var->section as $section)
                                {
                                    echo "<li>" . $section->counter . ": " . $section->name . "</li>";
                                }
                                ?>
                            </ul>
                        </td>
                        <td class="horizontalPadding ui-widget-content">
                            <ul>
                                <?php
                                $i = 0;
                                foreach ($var->type as $type)
                                {
                                    echo "<li>" . $type . "</li>";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>