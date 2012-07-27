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

class QTIAssessmentItemElement extends OQTIElement {
    public $identifier = "";
    public $title = "";
    public $label = "";
    public $lang = "";
    public $adaptive = "";
    public $timeDependent = "";
    public $toolName = "";
    public $toolVersion = "";
    
    public $responseDeclaration = array();
    public $outcomeDeclaration = array();
    public $templateDeclaration = array();
    public $templateProcessing = null;
    public $stylesheet = array();
    public $itemBody = null;
    public $responseProcessing = null;
    public $modalFeedback = array();
    
    public static $name = "assessmentItem";
    public static $possible_children = array(
        "responseDeclaration",
        "outcomeDeclaration",
        "templateDeclaration",
        "templateProcessing",
        "stylesheet",
        "itemBody",
        "responseProcessing",
        "modalFeedback"
    );
    public static $required_children = array(
    );
    public static $possible_attributes = array(
        "identifier",
        "title",
        "label",
        "lang",
        "adaptive",
        "timeDependent",
        "toolName",
        "toolVersion",
        "xsi:schemaLocation"
    );
    public static $required_attributes = array(
        "identifier",
        "title",
        "adaptive",
        "timeDependent"
    );
}

?>