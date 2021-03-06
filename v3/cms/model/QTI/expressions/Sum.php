<?php

/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2013, The Psychometrics Centre, Cambridge University

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

class Sum extends AExpression {

    //children
    public $expression = array();
    public static $name = "sum";
    public static $possible_attributes = array();
    public static $required_attributes = array();
    public static $possible_children = array(
        "expression"
    );
    public static $required_children = array(
        "expression"
    );

    public function __construct($node, $parent) {
        parent::__construct($node, $parent);
        self::$possible_attributes = array_merge(parent::$possible_attributes, self::$possible_attributes);
        self::$required_attributes = array_merge(parent::$required_attributes, self::$required_attributes);
        self::$possible_children = array_merge(parent::$possible_children, self::$possible_children);
        self::$required_children = array_merge(parent::$required_children, self::$required_children);
    }

    public function get_R_code() {
        $code = "if(";
        $i = 0;
        foreach ($this->expression as $exp) {
            if ($i > 0)
                $code.="||";
            $code.=sprintf("is.null(%s)", $exp->get_R_code());
            $i++;
        }
        $code.=") NULL else { ";
        $i = 0;
        foreach ($this->expression as $exp) {
            if ($i > 0)
                $code.="+";
            $code.=sprintf("(%s)", $exp->get_R_code());
            $i++;
        }
        $code.=" } ";
        return $code;
    }

    public function get_cardinality() {
        return "single";
    }

    public function get_baseType() {
        return "float";
    }

}

?>