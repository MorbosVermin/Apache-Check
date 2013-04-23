#!/usr/bin/php
<?php
/**
 * Apache Configuration Check Utility
 * Copyright (c)2012 Mike Duncan <mike.duncan@waitwha.com>
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @version	$Id: apache_check.php 669 2012-02-23 21:39:03Z mduncan $
 */

require("Apache.lib.php");
require("XmlRules.lib.php");
//TODO Intro ErrorHandler class to this project!
ini_set("display_errors", "1");
ini_set("log_errors", "0");
ini_set("error_log", "");
error_reporting(E_ALL ^E_NOTICE);
//require("ErrorHandler.class.php");
//set_error_handler(array("ErrorHandler", "handle"));

function help()  {
  global $rules_filename;
  echo "Syntax: [php] apache_check.php -f <path> [-x <path>] [-r <path>]\n";
  echo "Options:\n";
  echo "  -f <path> Required, the Apache configuration file to parse.\n";
  echo "  -x <path> Optional, an alternative XML rule set file (default = ". $rules_filename .")\n";
  echo "  -r <path> Optional, use an alternative ServerRoot (default is derived from ServerRoot directive).\n\n"; 
  exit(1);
}

if($argc == 0)
  help();

$rules_filename = dirname(__FILE__) ."/../rules/centos-rhel-httpd.xml";
$options = getopt("f:x:r:");  
$file = (isset($options["f"]) ? $options["f"] : null);
if(is_null($file) || (! file_exists($file)))
  help();


if(isset($options["x"]))  {
  $rules_filename = $options["x"];
  echo "** Using XML rule set at ". $rules_filename ."\n";
}

if(isset($options["r"]))  {
  define("APACHE_SERVER_ROOT", $options["r"]);
  echo "** Overriding ServerRoot value with '". $options["r"] ."'.\n";
}

echo "Parsing ". $file .", please wait...";
$p = new ConfigurationParser($file);
$c = $p->parse();
echo "done.\n";

echo "Parsing included configuration file(s), please wait...";
$c->parseIncludedFiles();
echo "done.\n";

echo "Parsing rules from ". $rules_filename .", please wait...";
$xml = XmlRulesParser::parse($rules_filename);
echo "done.\n";

echo "Checking Apache configuration against rule set (". $xml->count() ." rules)...\n";
$i = 1;
$score = 0;
$errors = array();
foreach($xml as $rule)  {
  if(strcasecmp($rule["type"], "module") == 0)  {
    echo $i .". Checking for module: ". $rule["name"] ."...";
    $loadmodules = $c->find("loadmodule");
    $found = false;
    foreach($loadmodules as $d)  {
      $s = $d->getValues();
      $found = (strcasecmp(basename($s[1]), $rule["name"]) == 0);
      if($found)
        break;
      
    }    
    
    echo "done.\n";
    if($found)  {
      array_push($errors, $rule["message"] ." ". $rule["resolution"] ." (". $rule["level"] .")");
      $score += $rule["level"];
    }
    
  }else if(strcasecmp($rule["type"], "directive") == 0)  {
    echo $i .". Checking value of directive: ". $rule["name"] ."...";
    $dd = $c->find($rule["name"]);
    if(count($dd) == 0)
      echo "no directive found...";
    
    $ok = false;
    foreach($dd as $d)  {
      $ok = (! (preg_match($rule["value"], $d->getValuesAsString())));
      if(! $ok)
        break;
      
    }
    
    echo "done.\n";
    if(! $ok)  {
      array_push($errors, $rule["message"] ." ". $rule["resolution"] ." (". $rule["level"] .")");
      $score += $rule["level"];
    }
  }
  
  $i++;
}

echo "\n** Audit completed. Score = ". $score ."\n";
echo "Server Info\n--------------------------------------\n";
echo "Root:   ". $c->getServerRoot() ."\n";
echo "User:   ". $c->getUser() ."\n";
echo "Group:  ". $c->getGroup() ."\n";

$ports = array();
foreach($c->getPorts() as $port)
  array_push($ports, $port->getValuesAsString());
  
echo "Listen: ". join(", ", $ports) ."\n\n";

foreach($errors as $error)
  echo "* ". $error ."\n";

?>