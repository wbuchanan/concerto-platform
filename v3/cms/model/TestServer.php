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
    private static $max_idle_time = 3600;
    public static $debug = true;
    private $last_action_time;
    private $main_sock;
    private $clients;
    private $instances;
    public static $host = "127.0.0.1";
    public static $port = 8888;

    public static function log_debug($message, $timestamp=true)
    {
        $lfh = fopen(Ini::$path_temp . "test-server.log", "a");
        fwrite($lfh, ($timestamp ? date("Y-m-d H:i:s") . " --- " : "") . $message . "\r\n");
        fclose($lfh);
    }

    public function stop()
    {
        foreach ($this->clients as $k => $v)
        {
            $this->close_instance($k);
        }
        socket_close($this->main_sock);
        if (self::$debug)
                self::log_debug("TestServer->stop() --- TestServer stopped");
    }

    public static function send($data)
    {
        if (self::$debug)
        {
            self::log_debug("TestServer::send() --- Client sends data");
        }
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket)
        {
            if (self::$debug)
            {
                self::log_debug("TestServer::send() --- Error: (socket_create) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
            }
            return false;
        }
        $result = socket_connect($socket, self::$host, self::$port);
        if (!$result)
        {
            if (self::$debug)
            {
                self::log_debug("TestServer::send() --- Error: (socket_connect) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
            }
            socket_close($socket);
            return false;
        }
        socket_write($socket, $data . "\n", strlen($data . "\n"));
        if (self::$debug)
        {
            self::log_debug("TestServer::send() --- sent data");
            self::log_debug($data, false);
        }
        $result = socket_read($socket, 32648);
        if (self::$debug)
        {
            self::log_debug("TestServer::send() --- data recieved");
            self::log_debug($result, false);
        }
        socket_close($socket);
        if (!$result)
        {
            if (self::$debug)
            {
                self::log_debug("TestServer::send() --- Error: (socket_read) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
            }
            return false;
        }
        return trim($result);
    }

    public static function is_running()
    {
        if (self::$debug)
        {
            //self::log_debug("TestServer::is_running() --- Checking if server is running");
        }
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket)
        {
            if (self::$debug)
            {
                self::log_debug("TestServer::is_running() --- Error: (socket_create) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
            }
            return false;
        }
        $result = @socket_connect($socket, self::$host, self::$port);
        socket_close($socket);
        if (!$result)
        {
            if (self::$debug)
            {
                //self::log_debug("TestServer::is_running() --- Server is not running");
            }
            return false;
        }
        if (self::$debug)
        {
            self::log_debug("TestServer::is_running() --- Server is running");
        }
        return true;
    }

    public static function start_process()
    {
        if (self::$debug)
        {
            self::log_debug("TestServer::start_process() --- Starting server process");
        }
        session_write_close();
        $command = 'nohup ' . Ini::$path_php_exe . ' ' . Ini::$path_internal . 'cms/query/socket_start.php '.Ini::$path_internal.' > /dev/null 2>&1 & echo $!';
        exec($command);
        while (!self::is_running())
        {
            usleep(1);
        }
        if (self::$debug)
        {
            self::log_debug("TestServer::start_process() --- Server process started");
        }
        session_start();
    }

    public function start()
    {
        $this->last_action_time = time();
        if (self::$debug)
                self::log_debug("TestServer->start() --- TestServer started");
        $this->main_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->main_sock)
        {
            if (self::$debug)
            {
                self::log_debug("TestServer->start() --- Error: (socket_create) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
                self::log_debug("TestServer->start() --- Server halted!");
            }
            return;
        }

        if (!socket_set_option($this->main_sock, SOL_SOCKET, SO_REUSEADDR, 1))
        {
            if (self::$debug)
            {
                self::log_debug("TestServer->start() --- Error: (socket_set_option) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
                self::log_debug("TestServer->start() --- Server halted!");
            }
            $this->stop();
            return;
        }

        if (!socket_bind($this->main_sock, self::$host, self::$port))
        {
            if (self::$debug)
            {
                self::log_debug("TestServer->start() --- Error: (socket_bind) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
                self::log_debug("TestServer->start() --- Server halted!");
            }
            $this->stop();
            return;
        }
        if (!socket_listen($this->main_sock))
        {
            if (self::$debug)
            {
                self::log_debug("TestServer->start() --- Error: (socket_listen) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
                self::log_debug("TestServer->start() --- Server halted!");
            }
            $this->stop();
            return;
        }
        $this->clients = array();
        $this->instances = array();

        if (self::$debug)
                self::log_debug("TestServer->start() --- TestServer initialized");

        do
        {
            if (time() - $this->last_action_time > self::$max_idle_time)
            {
                if (self::$debug)
                        self::log_debug("TestServer->start() --- Reached max idle time");
                break;
            }
            foreach ($this->clients as $k => $v)
            {
                if ($this->instances[$k]->is_timedout())
                {
                    if (self::$debug)
                    {
                        self::log_debug("TestServer->start() --- Client '$k' timedout");
                    }
                    $this->close_instance($k);
                }
            }

            if (!socket_set_nonblock($this->main_sock))
            {
                if (self::$debug)
                {
                    self::log_debug("TestServer->start() --- Error: (socket_set_nonblock)");
                    self::log_debug("TestServer->start() --- Server halted!");
                    break;
                }
            }
            $client_sock = @socket_accept($this->main_sock);
            if (!$client_sock)
            {
                continue;
            }

            $read = socket_read($client_sock, 32648);
            if (!$read)
            {
                if (self::$debug)
                {
                    self::log_debug("TestServer->start() --- Error: (socket_read) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
                    continue;
                }
            }
            $input = trim($read);
            if ($input != "")
            {
                if (self::$debug)
                {
                    self::log_debug("TestServer->start() --- data recieved");
                    self::log_debug($input, false);
                }
                if ($input == "exit")
                {
                    if (self::$debug)
                            self::log_debug("TestServer->start() --- Exit command recieved");
                    break;
                }
                $this->last_action_time = time();
                $client = $this->get_client($client_sock, $input);
                $this->interpret_input($client, $input);
            }
        }
        while (true);

        $this->stop();
    }

    private function close_instance($key)
    {
        if (array_key_exists($key, $this->instances))
        {
            if ($this->instances[$key]->is_started())
            {
                $this->instances[$key]->stop();
                unset($this->instances[$key]);
            }
        }
        if (array_key_exists($key, $this->clients))
        {
            socket_close($this->clients[$key]["sock"]);
            unset($this->clients[$key]);
        }
        if (self::$debug)
        {
            self::log_debug("TestServer->close_instance() --- Client '$key' closed");
        }
    }

    private function get_client($client_sock, $input)
    {
        $data = json_decode($input);
        $key = "sid" . $data->session_id;

        if (!array_key_exists($key, $this->clients))
        {
            $this->clients[$key] = array();
            $this->clients[$key]["sock"] = $client_sock;
            if (self::$debug)
            {
                self::log_debug("TestServer->get_client() --- Client '$key' added");
            }
        }
        else
        {
            if (is_resource($this->clients[$key]["sock"]))
            {
                socket_close($this->clients[$key]["sock"]);
                $this->clients[$key]["sock"] = $client_sock;
            }
            if (self::$debug)
            {
                self::log_debug("TestServer->get_client() --- Client '$key' loaded");
            }
        }
        return $this->clients[$key];
    }

    private function interpret_input($client, $input)
    {
        $data = json_decode($input);
        $key = "sid" . $data->session_id;

        if (!array_key_exists($key, $this->instances))
        {
            $this->instances[$key] = new TestInstance($data->session_id);
            if (self::$debug)
            {
                self::log_debug("TestServer->interpret_input() --- Client '$key' test instance created");
            }
        }
        if (!$this->instances[$key]->is_started())
        {
            $this->instances[$key]->start();
            if (self::$debug)
            {
                self::log_debug("TestServer->interpret_input() --- Client '$key' test instance started");
            }
        }

        $this->instances[$key]->send($data->code);
        if (self::$debug)
        {
            self::log_debug("TestServer->interpret_input() --- Client '$key' test data sent");
            self::log_debug($data->code, false);
        }
        $response = $this->instances[$key]->read();
        if (self::$debug)
        {
            self::log_debug("TestServer->interpret_input() --- Client '$key' test data read");
            self::log_debug($response, false);
        }

        $response = array(
            "return" => $this->instances[$key]->code_execution_halted ? 1 : 0,
            "code" => $data->code,
            "output" => $response
        );

        $response = json_encode($response);

        if (!socket_write($client["sock"], $response . "\n"))
        {
            if (self::$debug)
                    self::log_debug("TestServer->interpret_input() --- Error: (socket_write) " . socket_last_error() . " - " . socket_strerror(socket_last_error()));
        }
        else
        {
            if (self::$debug)
            {
                self::log_debug("TestServer->interpret_input() --- Client '$key' test response sent back");
                self::log_debug($response, false);
            }
        }
    }

}

?>
