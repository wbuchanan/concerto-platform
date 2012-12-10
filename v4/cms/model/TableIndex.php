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

class TableIndex {

    public $name = "";
    public $non_unique = "";
    public $columns = "";

    public static function from_mysql_table($name) {
        $indexes = array();
        $sql = sprintf("SHOW INDEXES IN `%s`", mysql_real_escape_string($name));
        $z = mysql_query($sql);
        while ($r = mysql_fetch_array($z)) {
            $found = false;
            foreach ($indexes as $index) {
                if ($index->name == $r['Key_name']) {
                    $found = true;
                    $index->columns.="," . $r['Column_name'];
                    break;
                }
            }
            if (!$found) {
                $index = new TableIndex();
                $index->name = $r['Key_name'];
                $index->non_unique = $r['Non_unique'];
                $index->columns = $r['Column_name'];
                array_push($indexes, $index);
            }
        }
        return $indexes;
    }

    public function get_type() {
        if ($this->name == "PRIMARY")
            return "primary";
        return $this->non_unique == 0 ? "unique" : "index";
    }

}

?>
