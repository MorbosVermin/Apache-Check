<?php
/**
 * Apache Configuration Library
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
 * @version	$Id: Apache.lib.php 696 2012-04-10 15:37:21Z mduncan $
 */

class ConfigurationParser  {
  
  private $file;
  private $config;
  
  public function __construct($file)  {
    if(! file_exists($file))
      throw new FileNotFoundException("Configuration file not found: ". $file);
    
    $this->file = $file;
    $this->config = new Configuration();
  }
  
  /**
   * Parses the file (given in constructor) and returns the Configuration object 
   * for the parsed results. 
   *
   */
  public final function parse()  {
    $lines = file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $buffer = "";
    
    foreach($lines as $num => $line)  {
      $line = rtrim($line);
      if((strlen($line) <= 1) || preg_match('/^\s*#+/', $line))
        continue;
      
      //Multi-lined directives...
      if(preg_match('/^\s*(.*)\s+\\\$/', $line, $m))  {
        $buffer .= $m[1] ." ";
        continue;
        
      }else if(strlen($buffer) > 0)  {
        $line = $buffer . $line;
        $buffer = "";
        
      }
      
      //Begin container directive...
      if(preg_match('/^\s*<(\w+)(?:\s+([^>]*)|\s*)>\s*$/', $line, $m))  {
        $this->config->add(new Container($m[1], $m[2]));
      
      //End container directive...
      }else if(preg_match('/^\s*<\/(\w+)\s*/', $line))  {
        $this->config->setIsContainer(false);
        
      }else if(preg_match('/^\s*(\w+)(?:\s+(.*?)|)\s*$/', $line, $m))  {
        $this->config->add(new Directive($m[1], $m[2]));
        
      }
      
    }
    
    return $this->config;
  }
  
}

class ParsingException extends Exception {

  public function __construct($msg, $code=-1)  {
    parent::__construct($msg, $code);
  }

}

class Directive  {
  
  private $name;
  private $values;
  
  public function __construct($name, $values=array())  {
    $this->name = $name;
    if(! is_null($values))
      $this->values = (is_array($values) ? $values : explode(" ", $values));
    
  }
  
  public function isContainer()  {
    return false;
  }
  
  public function getName()  {
    return $this->name;
  }
  
  public function getValues()  {
    return $this->values;
  }
  
  public function getValuesAsString()  {
    return join(" ", $this->values);
  }
  
  public function setValues($values)  {
    $this->values = $values;
  }
  
  public function __toString()  {
    return $this->getName() . ((count($this->values) > 0) ? " ". join(" ", $this->values) : "");
  }
  
}

class Container extends Directive implements Iterator  {
  
  private $directives;
  private $pos;
  
  public function __construct($name, $values=array())  {
    parent::__construct($name, $values);
    $this->directives = array();
    $this->pos = 0;
  }
  
  public function isContainer()  {
    return true;
  }
  
  public function add($directive)  {
    array_push($this->directives, $directive);
  }
  
  public function remove($directive)  {
    $i = 0;
    foreach($this->directives as $d)  {
      if($d === $directive)
        unset($this->directives[$i]);
        
      $i++;
    }
  }
  
  public function clear()  {
    $this->directives = array();
  }
  
  public function current()  {
    return $this->directives[$this->pos];
  }
  
  public function rewind()  {
    $this->pos = 0;
  }
  
  public function next()  {
    $this->pos++;
  }
  
  public function valid()  {
    return ($this->pos < count($this->directives));
  }
  
  public function key()  {
    return $this->pos;
  }
  
  public function __toString()  {
    $r = "<". $this->getName() . ((count($this->getValues()) > 0) ? " \"". join(" ", $this->getValues()) ."\"" : "") .">\n";
    foreach($this->directives as $d)
      $r .= $d ."\n";
      
    $r .= "</". $this->getName() .">";
    return $r;
  }
  
}

class Configuration  {
  
  private $directives;
  private $container;
  private $serverroot;
  
  public function __construct()  {
    $this->directives = array();
    $this->container = false;
    $this->serverroot = null;
  }
  
  public function getDirectives()  {
    return $this->directives;
  }
  
  public function add($directive)  {
    if($this->container)  {
      $container = array_pop($this->directives);
      $container->add($directive);
      array_push($this->directives, $container);
    
    }else{
      $this->container = $directive->isContainer();
      array_push($this->directives, $directive);
    }
      
  }
  
  public function get($name)  {
    foreach($this->directives as $d)
      if(strcasecmp($d->getName(), $name) == 0)
        return $d;
    
    return null;
  }
  
  public function contains($name)  {
    $d = $this->get($name);
    return (! is_null($d));
  }
  
  public function setIsContainer($container)  {
    $this->container = $container;
  }
  
  public function getServerRoot()  {
    if(defined("APACHE_SERVER_ROOT"))
      return APACHE_SERVER_ROOT;
    
    foreach($this->directives as $d)
      if(strcasecmp("ServerRoot", $d->getName()) == 0)
        $this->serverroot = str_replace("\"", "", $d->getValuesAsString());
    
    return $this->serverroot;
  }
  
  public function getDocumentRoots()  {
    $roots = $this->find("documentroot");
    $paths = array();
    foreach($roots as $d)
      array_push($paths, $d->getValuesAsString());
      
    return $paths;
  }
  
  public function getLoadedModules($fullpath=false)  {
    $modules = array();
    foreach($this->find("loadmodule") as $d)  {
      $values = $d->getValues();
      if(count($values) == 2)
        array_push($modules, (($fullpath) ? $this->getServerRoot() ."/" : "") . $values[1]);
      
    }
    
    return $modules;    
  }
  
  public function getIncludedFiles()  {
    $includeds = $this->find("include");
    $files = array();
    foreach($includeds as $d)  {
      $path = $d->getValuesAsString();
      if(strpos($path, "*") > 0)  {
        $dir = dirname($path);
        if(! file_exists($dir))
          $dir = $this->getServerRoot() ."/". $dir;
        
        try  {
          $ff = new FileFinder($dir);
          $f = $ff->find(basename($path));
          foreach($f as $file)
            array_push($files, $this->getServerRoot() ."/". $file);
          
        }catch(FileNotFoundException $e)  {
          trigger_error("Could not search '". dirname($path) ."'; directory does not exist.", E_USER_WARNING);
          
        }
        
      }else
        array_push($files, $path);
      
    }
    
    return $files;
  }
  
  public function getUser()  {
    $user = null;
    foreach($this->find("user") as $d)
      $user = $d->getValuesAsString();
    
    return $user;
  }
  
  public function getGroup()  {
    $group = null;
    foreach($this->find("group") as $d)
      $group = $d->getValuesAsString();
    
    return $group;
  }
  
  /**
   * Searches SUB-scope-wise to find the directive by the 
   * given name.
   *
   * @param	$name	string 	Name of directive to search for.
   * @return array				Results
   */
  public function find($name, $root=null)  {
    $r = array();
    $name = trim($name);
    //trigger_error("Searching for '". $name ."' in '". (is_null($root) ? "<global>" : $root->getName()) ."'", E_USER_NOTICE);
    $base = (is_null($root) ? $this->directives : $root);
    
    foreach($base as $d)  {
      if(strcasecmp($name, $d->getName()) == 0)  {
        //trigger_error("Found result: ". $d->getName(), E_USER_NOTICE); 
        array_push($r, $d);
        
      }
        
      if($d->isContainer())  {
        $res = $this->find($name, $d);
        if(count($res) > 0)  {
          //trigger_error("Merging ". count($res) ." result(s) with global result set.", E_USER_NOTICE);
          $r = array_merge($r, $res);
        } 
      }  
    }
    
    //trigger_error("Search complete. Found ". count($r) ." results for '". $name ."'", E_USER_NOTICE);
    return $r;
  }
  
  public function getPorts()  {
    return $this->find("listen");
  }
  
  public function getVirtualHosts()  {
    return $this->find("virtualhost");
  }
  
  public function parseIncludedFiles()  {
    $files = $this->getIncludedFiles();
    foreach($files as $file)  {
      //echo "\t - ". $file ."\n";
      $p = new ConfigurationParser($file);
      $c = $p->parse();
      $this->directives = array_merge($this->directives, $c->getDirectives());
    }
  }
  
  public function __toString()  {
    $r = "";
    foreach($this->directives as $d)
      $r .= $d ."\n";
      
    return $r;
  }
  
}

class FileFinder  {

  private $parent;
  const FILTER_ENDS_WITH = 1;
  const FILTER_BEGINS_WITH = 2;
  const FILTER_CONTAINS = 3;
  
  public function __construct($parent)  {
    $this->parent = $parent;
  }
  
  public final function find($filter)  { 
    $type = (strpos($filter, "*") == 0) ? FileFinder::FILTER_ENDS_WITH : FileFinder::FILTER_CONTAINS;
    $files = array();
    if($handle = opendir($this->parent))  {
      while(($entry = readdir($handle)) !== false)  {
        if((strcmp($entry, ".") != 0) && (strcmp($entry, "..") != 0))  {
          if($this->filter($filter, $entry, $type))
            array_push($files, $this->parent ."/". $entry);
          
        }
      }
      
      closedir($handle);
    }else
      throw new FileNotFoundException("Directory not found or there was an issue reading this directory: ". $this->parent);
   
    return $files; 
  }
  
  private function filter($filter, $value, $type=FileFinder::FILTER_ENDS_WITH)  {
    $filter = str_replace(array("*", "."), array("", ""), $filter);
    switch($type)  {
      case FileFinder::FILTER_ENDS_WITH:
        return (strrpos($value, $filter) > 0);
        
      case FileFinder::FILTER_CONTAINS:
        return (strpos($value, $filter) >= 0);
      
    }
    
    return false;
  }
  
}

class FileNotFoundException extends Exception  {

  public function __construct($msg, $code=404)  {
    parent::__construct($msg, $code);
  }

}
?>