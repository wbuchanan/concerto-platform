<?php

/*
  Concerto Testing Platform,
  Web based adaptive testing platform utilizing R language for computing purposes.

  Copyright (C) 2011  Psychometrics Centre, Cambridge University

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Language
{
    public static $xml;

    public static function string($id)
    {
        $lang = "en";
        if (isset($_SESSION['lng'])) $lang = $_SESSION['lng'];
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(self::$xml);
        $xpath = new DOMXPath($doc);
        $string = $xpath->query("/root/strings/string[@id='$id']/$lang");
        foreach ($string as $s) return $s->nodeValue;
    }

    public static function languages()
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(self::$xml);
        $xpath = new DOMXPath($doc);
        $lngs = $xpath->query("/root/languages/language");
        return $lngs;
    }

    public static function load_dictionary()
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->load(Ini::$path_internal . "/cms/dictionary/dictionary.xml");
        self::$xml = $doc->saveXML();
    }

    public static function load_js_dictionary($client = false)
    {
        echo"<script>";

        $lang = "en";
        if (isset($_SESSION['lng'])) $lang = $_SESSION['lng'];
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML(self::$xml);
        $xpath = new DOMXPath($doc);
        $ids = $xpath->query("/root/strings/string[@js='1']");
        foreach ($ids as $id)
        {
            $id = $id->getAttribute("id");
            echo"dictionary['s" . $id . "']='" . addcslashes(self::string($id), "'") . "';
                ";
        }
        echo"</script>";
    }

}

?>
