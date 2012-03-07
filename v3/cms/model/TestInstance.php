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

class TestInstance
{
    private $r = null;
    private $pipes;
    public $code_execution_halted = false;
    private static $max_idle_time = 60;
    public $is_working = false;

    public function is_started()
    {
        if ($this->r == null) return false;
        if (is_resource($this->r))
        {
            $status = proc_get_status($this->r);
            return $status["running"];
        }
        else return false;
    }

    private function start()
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $this->r = proc_open("/usr/bin/R --no-save", $descriptorspec, $this->pipes, Ini::$path_temp);
        if (is_resource($this->r)) return true;
        else return false;
    }

    private function stop()
    {
        if ($this->is_open())
        {
            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);
            return proc_close($this->r);
        }
        return null;
    }

    public function send($code)
    {
        fwrite($this->pipes[0], $code . "
        print('CODE EXECUTION FINISHED')
        ");
        $this->is_working = true;
    }

    public function read()
    {
        $this->code_execution_halted = false;
        stream_set_blocking($this->pipes[1], 0);
        stream_set_blocking($this->pipes[2], 0);
        $cont = true;
        $result = "";
        $error = "";
        do
        {
            while ($append = fread($this->pipes[1], 4096))
            {
                $result.=$append;
            }
            if (strpos($result, '"CODE EXECUTION FINISHED"') !== false || strpos($result, "Error:") !== false)
            {
                $cont = false;
                $this->is_working = false;
            }
            else
            {
                while ($append = fread($this->pipes[2], 4096))
                {
                    $error.=$append;
                }
                if (strpos($error, "Error:") !== false)
                {
                    $cont = false;
                    $result = $error;
                    $this->code_execution_halted = true;
                    $this->is_working = false;
                }
            }
        }
        while ($cont);

        return $result;
    }

}

?>
