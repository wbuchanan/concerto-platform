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

class QTIAssessmentItem extends OModule
{
    public $name = "";
    public $XML = "";
    public $description = "";
    public $xml_hash = "";
    
    public static $exportable = true;
    public static $mysql_table_name = "QTIAssessmentItem";

    public function __construct($params = array())
    {
        $this->name = Language::string(457);
        parent::__construct($params);
    }
    
    public function get_pre_HTML_R_code(){
        
    }
    
    public function get_HTML(){
        
    }
    
    public function get_post_HTML_R_code(){
        
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `QTIAssessmentItem`;")) return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `QTIAssessmentItem` (
            `id` bigint(20) NOT NULL auto_increment,
            `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL default '0000-00-00 00:00:00',
            `name` text NOT NULL,
            `XML` text NOT NULL,
            `description` text NOT NULL,
            `xml_hash` text NOT NULL,
            `Sharing_id` int(11) NOT NULL,
            `Owner_id` bigint(20) NOT NULL,
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ";
        return mysql_query($sql);
    }

}

?>