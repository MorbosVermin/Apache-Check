<?php
/**
 * Apache Check: XML Rules Library
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
 * @version	$Id: XmlRules.lib.php 669 2012-02-23 21:39:03Z mduncan $
 */
class XmlRulesParser {

  private $file;
  private $dom;
  
  public function __construct($file)  {
    $this->file = $file;
    $this->dom = simplexml_load_file($file);
  }
  
  public function getFile()  {
    return $this->file;
  }
  
  public function getDom()  {
    return $this->dom;
  }
  
  public static final function parse($file)  {
    $xml = new XmlRulesParser($file);
    return $xml->getDom();
  }
  
}
?>