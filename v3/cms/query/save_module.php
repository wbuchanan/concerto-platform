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
    require_once '../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null) die("Access denied!");

$obj = $_POST['class_name']::from_mysql_id($_POST['oid']);
if ($obj == null)
{
    $obj = new $_POST['class_name']();
    $vars = get_object_vars($obj);
    $is_ownable = false;
    foreach ($vars as $k => $v)
    {
        if ($k == "Owner_id")
        {
            $is_ownable = true;
            break;
        }
    }
    if ($is_ownable) $obj->Owner_id = $logged_user->id;
    if (isset($_POST['Sharing_id'])) $obj->Sharing_id = $_POST['Sharing_id'];
}

$_POST['oid'] = $obj->mysql_save_from_post($_POST);

echo json_encode(array("oid" => $_POST['oid']));
?>