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
if ($logged_user == null) header("Location: ".Ini::$path_external."cms/index.php");

$table = Table::from_mysql_id($_GET['oid']);
if (!$logged_user->is_object_readable($table)) die(Language::string(81));

/**
 * Generatting CSV formatted string from an array.
 * By Sergey Gurevich.
 */
function array_to_scv($array, $header_row = true, $col_sep = ",", $row_sep = "\r\n", $qut = '"')
{
    if (!is_array($array) or !is_array($array[0])) return false;
    $output = "";
    //Header row.
    if ($header_row)
    {
        foreach ($array[0] as $key => $val)
        {
            if (is_numeric($key)) continue;
            //Escaping quotes.
            $key = str_replace($qut, "$qut$qut", $key);
            $output .= "$col_sep$qut$key$qut";
        }
        $output = substr($output, 1) . "\n";
    }
    //Data rows.
    foreach ($array as $key => $val)
    {
        $tmp = '';
        foreach ($val as $cell_key => $cell_val)
        {
            if (is_numeric($cell_key)) continue;
            //Escaping quotes.
            $cell_val = str_replace($qut, "$qut$qut", $cell_val);
            $tmp .= "$col_sep$qut$cell_val$qut";
        }
        $output .= substr($tmp, 1) . $row_sep;
    }

    return $output;
}

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Content-type: application/csv");
header('Content-Disposition: attachment; filename="table_' . $_GET['oid'] . '.csv"');

$rows = array();
$sql = sprintf("SELECT * FROM `%s`", $table->get_table_name());
$z = mysql_query($sql);
while ($r = mysql_fetch_array($z))
{
    array_push($rows, $r);
}

echo array_to_scv($rows, $_GET['header'] == 1, $_GET['delimeter'], "\r\n", $_GET['enclosure']);
exit();
?>