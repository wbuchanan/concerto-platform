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
    require_once'../Ini.php';
    $ini = new Ini();
}

$template = Template::from_mysql_id($_POST['template_id']);
$vals = TestSection::from_property(array("counter" => $_POST['values']["LOAD_HTML_SECTION_INDEX"], "Test_id" => $_POST['values']["TEST_ID"]), false)->get_values();
$html = $template->get_html_with_return_properties($vals);

foreach ($template->get_inserts() as $k)
{
    $var_value = "";
    $reference = $template->get_insert_reference($k, $vals);
    if (array_key_exists($reference, $_POST['values']))
            $var_value = $_POST['values'][$reference];
    $html = str_replace("{{" . $k . "}}", $var_value, $html);
}

echo json_encode(array("html" => $html));
?>