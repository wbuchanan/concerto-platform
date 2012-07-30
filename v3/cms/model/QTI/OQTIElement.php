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

class OQTIElement {

    public $node = null;

    const VALIDATION_ERROR_TYPES_XML = 1;
    const VALIDATION_ERROR_TYPES_ATTRIBUTE_NOT_AVAILABLE = 2;
    const VALIDATION_ERROR_TYPES_ATTRIBUTE_REQUIRED = 3;
    const VALIDATION_ERROR_TYPES_CHILD_NOT_AVAILABLE = 4;
    const VALIDATION_ERROR_TYPES_CHILD_REQUIRED = 5;
    const VALIDATION_ERROR_TYPES_CLASS_NOT_EXISTS = 6;

    public static $possible_attributes = array();
    public static $required_attributes = array();
    public static $possible_children = array();
    public static $required_children = array();
    public static $class_map = array(
        "default" => "DefaultExp",
        "null" => "NullExp",
        "and" => "AndExp"
    );

    public function __construct($node) {
        $this->node = $node;
    }

    public function validate() {
        $result = $this->validate_possible_attributes();
        if (json_decode($result)->result != 0)
            return $result;

        $result = $this->validate_required_attributes();
        if (json_decode($result)->result != 0)
            return $result;

        $this->set_attributes();

        $result = $this->validate_possible_children();
        if (json_decode($result)->result != 0)
            return $result;

        $result = $this->validate_required_children();
        if (json_decode($result)->result != 0)
            return $result;

        $result = $this->validate_children();
        return $result;
    }

    private function validate_possible_attributes() {
        if (in_array("*", static::$possible_attributes))
            return json_encode(array("result" => 0));
        $attributes = $this->node->attributes;
        foreach ($attributes as $attr) {
            if (!in_array($attr->nodeName, static::$possible_attributes))
                return json_encode(array("result" => self::VALIDATION_ERROR_TYPES_ATTRIBUTE_NOT_AVAILABLE, "section" => static::$name, "target" => $attr->nodeName));
        }
        return json_encode(array("result" => 0));
    }

    private function validate_required_attributes() {
        foreach (static::$required_attributes as $attr) {
            if (!$this->node->hasAttribute($attr))
                return json_encode(array("result" => self::VALIDATION_ERROR_TYPES_ATTRIBUTE_REQUIRED, "section" => static::$name, "target" => $attr));
        }
        return json_encode(array("result" => 0));
    }

    private function validate_possible_children() {
        if (in_array("*", static::$possible_children))
            return json_encode(array("result" => 0));
        foreach ($this->node->childNodes as $node) {
            if ($node->nodeType != XML_ELEMENT_NODE)
                continue;
            $class_name = self::get_mapped_class_name($node->nodeName);
            if (!class_exists($class_name)) {
                return json_encode(array("result" => self::VALIDATION_ERROR_TYPES_CLASS_NOT_EXISTS, "section" => static::$name, "target" => $class_name));
            }
            $match = false;
            foreach (static::$possible_children as $children) {
                if ($node->nodeName == $children || is_subclass_of($class_name, ucfirst($children)) || is_subclass_of($class_name, "A" . ucfirst($children))) {
                    $match = true;
                    break;
                }
            }
            if (!$match)
                return json_encode(array("result" => self::VALIDATION_ERROR_TYPES_CHILD_NOT_AVAILABLE, "section" => static::$name, "target" => $node->nodeName));
        }
        return json_encode(array("result" => 0));
    }

    private function validate_required_children() {
        foreach (static::$required_children as $child) {
            $class_name_child = self::get_mapped_class_name($child);
            if (!class_exists($class_name_child)) {
                return json_encode(array("result" => self::VALIDATION_ERROR_TYPES_CLASS_NOT_EXISTS, "section" => static::$name, "target" => $class_name_child));
            }
            $found = false;
            foreach ($this->node->childNodes as $node) {
                if ($node->nodeType != XML_ELEMENT_NODE)
                    continue;
                $class_name_node = self::get_mapped_class_name($node->nodeName);
                if ($node->nodeName == $child || is_subclass_of($class_name_node, $class_name_child) || is_subclass_of($class_name_node, "A" . $class_name_child)) {
                    $found = true;
                    break;
                }
            }
            if (!$found)
                return json_encode(array("result" => self::VALIDATION_ERROR_TYPES_CHILD_REQUIRED, "section" => static::$name, "target" => $child));
        }
        return json_encode(array("result" => 0));
    }

    private function validate_children() {
        if (in_array("*", static::$possible_children))
            return json_encode(array("result" => 0));
        foreach ($this->node->childNodes as $node) {
            if ($node->nodeType != XML_ELEMENT_NODE)
                continue;
            $class_name = self::get_mapped_class_name($node->nodeName);
            if (!class_exists($class_name)) {
                return json_encode(array("result" => self::VALIDATION_ERROR_TYPES_CLASS_NOT_EXISTS, "section" => static::$name, "target" => $class_name));
            }
            $child = new $class_name($node);
            $result = $child->validate();
            if (json_decode($result)->result != 0)
                return $result;
            $this->set_children($child);
        }
        return json_encode(array("result" => 0));
    }

    public static function get_mapped_class_name($class_name) {
        if (array_key_exists($class_name, self::$class_map))
            $class_name = self::$class_map[$class_name];
        $class_name = ucfirst($class_name);
        return $class_name;
    }

    private function set_children($child) {
        $name = $child::$name;
        if (!property_exists(ucfirst(static::$name), $name))
            return;
        if (is_array($this->$name))
            array_push($this->$name, $child);
        else
            $this->$name = $child;
    }

    private function set_attributes() {
        $attributes = $this->node->attributes;
        foreach ($attributes as $attr) {
            $attr_name = $attr->nodeName;
            $this->$attr_name = $attr->nodeValue;
        }
    }

}

?>