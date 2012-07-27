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

class TestSessionQTIAssessmentItem extends OTable
{
    public $TestSession_id = 0;
    public $QTIAssessmentItem_id = 0;
    public $TestSection_id = 0;
    public $XML = "";
    public static $mysql_table_name = "TestSessionQTIAssessmentItem";

    public function get_TestSession()
    {
        return TestSession::from_mysql_id($this->TestSession_id);
    }
    
    public function get_TestSection()
    {
        return TestSection::from_mysql_id($this->TestSection_id);
    }
    
    public function get_QTIAssessmentItem()
    {
        return QTIAssessmentItem::from_mysql_id($this->QTIAssessmentItem_id);
    }

    public static function create_db($delete = false)
    {
        if ($delete)
        {
            if (!mysql_query("DROP TABLE IF EXISTS `TestSessionQTIAssessmentItem`;"))
                    return false;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS `TestSessionQTIAssessmentItem` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
            `TestSession_id` bigint(20) NOT NULL,
            `TestSection_id` bigint(20) NOT NULL,
            `QTIAssessmentItem_id` bigint(20) NOT NULL,
            `XML` text NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `TestSession_id` (`TestSession_id`,`TestSection_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            ";
        return mysql_query($sql);
    }

}

?>