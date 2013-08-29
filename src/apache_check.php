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
ini_set("log_errors", "0");
ini_set("error_log", "");
ini_set("include_path", ini_get("include_path") .":". dirname(__FILE__));
error_reporting(E_ALL ^E_NOTICE);

//You may need to change this value depending on your site's physical location.
date_default_timezone_set("America/New_York");

require_once("Apache.lib.php");
require_once("XmlRules.lib.php");
require_once("ErrorHandler.class.php");
set_error_handler(array("ErrorHandler", "handle"));

function help()  {
  global $rules_filename;
  echo "Syntax: [php] apache_check.php [-v] -f <path> [-x <path>] [-r <path>] [-l <log file>]\n";
  echo "Options:\n";
  echo "  -f <path> Required, the Apache configuration file to parse.\n";
  echo "  -x <path> Optional, an alternative XML rule set file (default = ". $rules_filename .")\n";
  echo "  -r <path> Optional, use an alternative ServerRoot (default is derived from ServerRoot directive).\n"; 
  echo "  -l <path> Optional, log to file at path given.\n";
  echo "  -d        Optional, enables debug messages helpful in troubleshooting issues.\n";
  echo "  -v        Optional, enabled more verbosity.\n\n";
  exit(1);
}

if($argc == 0)
  help();

$rules_filename = dirname(__FILE__) ."/../rules/centos-rhel-httpd.xml";
$options = getopt("l:f:x:r:vd");
$file = (isset($options["f"]) ? $options["f"] : null);
if(is_null($file) || (! file_exists($file)))
  help();
  
if(isset($options["d"]))
  ErrorHandler::$debugMode = true;

if(isset($options["v"]))  {
  ErrorHandler::$verbose = true;  
  
}else if(isset($options["l"]))  {
  ini_set("log_errors", "1");
  ini_set("error_log", $options["l"]);
}

if(isset($options["x"]))  {
  $rules_filename = $options["x"];
  ErrorHandler::debug("Using XML rule-set located at ". $rules_filename);  
}

if(isset($options["r"]))  {
  define("APACHE_SERVER_ROOT", $options["r"]);
  ErrorHandler::debug("Overriding ServerRoot value with '". $options["r"] ."'");
}

$start_time = date("U");
ErrorHandler::info("Parsing Apache configuration file ". $file);
$p = new ConfigurationParser($file);
$config = $p->parse();
$config->parseIncludedFiles();
$end_time = date("U");
ErrorHandler::debug("Completed parsing Apache configuration file(s) in ". ($end_time - $start_time) ."s");

$xml = XmlRulesParser::parse($rules_filename);
ErrorHandler::info("Checking ". count($config->getDirectives()) ." directive(s) for issues using rule-set '". basename($rules_filename) ."' (". $xml->count() ." rules)");

$i = 1;
$score = 0;
$errors = array();
foreach($xml as $rule)  {
  ErrorHandler::debug("Processing rule '". $rule["name"] ."' (". $rule["type"] .")");
  
  if(strcasecmp($rule["type"], "module") == 0)  {
    $loadmodules = $config->find("loadmodule");
    $found = false;
    foreach($loadmodules as $d)  {
      $s = $d->getValues();
      $found = (strcasecmp(basename($s[1]), $rule["name"]) == 0);
      if($found)
        break;
      
    }    
    
    if($found)  {
      ErrorHandler::debug("Found LoadModule directive for module '". $rule["name"] ."' (". $rule["level"] .")");
      array_push($errors, $rule["message"] ." ". $rule["resolution"] ." (". $rule["level"] .")");
      $score += $rule["level"];
    }
    
    
  }else if(strcasecmp($rule["type"], "directive") == 0)  {
    $dd = $config->find($rule["name"]);
    $ok = false;
    $d2 = null;
    foreach($dd as $d2)  {
      if(is_null($d2))
        continue;
      
      ErrorHandler::debug("Testing directive '". $d2->getName() ."' for value(s): ". $rule["value"]);
      $ok = (preg_match($rule["value"], $d2->getValuesAsString()));
      if($ok)  {
        ErrorHandler::debug("Directive '". $d2->getName() ."' matched value(s) for rule '". $rule["name"] ."'");
        break;
      }
    }
    
    if($ok)  {
      array_push($errors, $rule["message"] ." ". $rule["resolution"] ." (". $rule["level"] .")");
      $score += $rule["level"];
    }
    
  }
  
  $i++;
}

ErrorHandler::info("Completed audit. Score = ". $score);

$ports = array();
foreach($config->getPorts() as $port)
  array_push($ports, $port->getValuesAsString());

if(ErrorHandler::$verbose && !ErrorHandler::$debugMode)  {
  echo "\n";
  echo "Audit Results\n-----------------------------------------------------------------\n";
  echo "Audit Score:    ". $score ."\n";
  echo "Server Root:    ". $config->getServerRoot() ."\n";
  echo "Server Port(s): ". join(", ", $ports) ."\n";
  echo "User/Group:     ". $config->getUser() ."/". $config->getGroup() ."\n\n";
  echo "\n";
  foreach($errors as $error)  {
    $level = "LOW";
    $l = substr($error, strlen($error) - 2, strlen($error));
    switch($l)  {
      case 4:
      case 5:
      case 6:
        $level = "MEDIUM";
        break;
        
      case 6:
      case 7:
      case 8:
        $level = "HIGH";
        break;
    }
    
    echo sprintf("[%s] %s\n", $level, $error);
  }
  
  echo "\n";
}

exit($score);
?>