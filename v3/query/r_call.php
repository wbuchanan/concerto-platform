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
$time = time();
if (!isset($ini))
{
    require_once'../Ini.php';
    $ini = new Ini();
}

$session = null;
$result = array();
if (array_key_exists('sid', $_POST))
{
    $session = TestSession::from_mysql_id($_POST['sid']);

    if ($session != null)
    {
        if (!array_key_exists('values', $_POST)) $_POST['values'] = array();

        if (array_key_exists('btn_name', $_POST))
        {
            array_push($_POST['values'], json_encode(array(
                        "name" => "LAST_PRESSED_BUTTON_NAME",
                        "value" => $_POST['btn_name']
                    )));
        }

        if (Ini::$timer_tamper_prevention && $session->time_limit > 0 && $time - $session->time_tamper_prevention - Ini::$timer_tamper_prevention_tolerance > $session->time_limit)
        {
            if (Ini::$r_instances_persistant)
            {
                if (TestServer::is_running())
                        TestServer::send("close:" . $session->id);
            }
            else
            {
                $session->mysql_delete();
            }

            $result = array(
                "data" => array(
                    "TIME_LIMIT" => 0,
                    "HTML" => "",
                    "TEST_ID" => 0,
                    "TEST_SESSION_ID" => 0,
                    "STATUS" => TestSession::TEST_SESSION_STATUS_TAMPERED,
                    "TEMPLATE_ID" => 0
                )
            );
        }
        else $result = $session->resume($_POST['values']);
    }
}
else
{
    if (array_key_exists('tid', $_POST))
    {
        $session = TestSession::start_new($_POST['tid']);

        if (!array_key_exists('values', $_POST)) $_POST['values'] = array();

        $result = $session->run_test(null, $_POST['values']);
    }
}

echo json_encode($result);
?>