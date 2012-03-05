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

class TestServer
{
    private static $max_clients = 100;
    private static $max_idle_time = 60;
    private static $debug = true;
    private $last_action_time;
    private $main_sock;
    private $clients;

    public function log_debug($message)
    {
        $lfh = fopen(Ini::$path_temp . "test_server.log
            ", "a");
        fwrite($lfh, $message);
        fclose($lfh);
    }

    public function stop()
    {
        foreach ($this->clients as $client)
        {
            if (array_key_exists("sock", $client) && is_resource($client["sock"]))
            {
                socket_close($client["sock"]);
            }
        }
        socket_close($this->sock);
        if (self::$debug) log_debug("TestServer stopped");
    }

    public function start()
    {
        $this->last_action_time = time();
        if (self::$debug) log_debug("TestServer started");
        $this->main_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->main_sock, "127.0.0.1");
        socket_listen($this->main_sock);
        $this->clients = array();

        while (true)
        {
            $read[0] = $this->main_sock;
            for ($i = 0; $i < self::$max_clients; $i++)
            {
                if (isset($this->clients[$i]['sock']))
                        $read[$i + 1] = $this->client[$i]['sock'];
            }

            if (socket_select($read, null, null, 5) < 1) continue;

            if (in_array($this->main_sock, $read))
            {
                for ($i = 0; $i < self::$max_clients; $i++)
                {
                    if (empty($this->client[$i]['sock']))
                    {
                        if (self::$debug) log_debug("Client #" . $i . " added");
                        $this->client[$i]['sock'] = socket_accept($this->main_sock);
                        break;
                    }
                    if ($i == self::$max_clients - 1)
                    {
                        if (self::$debug)
                                log_debug("Limit of " . self::$max_clients . " clients reached");
                    }
                }
            }

            for ($i = 0; $i < self::$max_clients; $i++)
            {
                if (isset($this->client[$i]['sock']))
                {
                    if (in_array($this->client[$i]['sock'], $read))
                    {
                        $input = socket_read($this->client[$i]['sock'], 4096);
                        if ($input == null)
                        {
                            if (self::$debug)
                                    log_debug("Connection with client #" . $i . " terminated");
                            socket_close($this->client[$i]['sock']);
                            unset($this->client[$i]);
                        }
                        else
                        {
                            if (self::$debug)
                                    log_debug("Recieved data from client #" . $i);
                            $this->last_action_time = time();
                        }
                    }
                    else
                    {
                        socket_close($this->client[$i]['sock']);
                        unset($this->client[$i]);
                    }
                }
            }
            if (time() - $this->last_action_time > self::$max_idle_time) break;
        }
        $this->stop();
    }

}

?>
