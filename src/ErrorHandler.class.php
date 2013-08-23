<?php
/**
 * Error Handler Class
 * Copyright (c)2013 Mike Duncan <mike.duncan@waitwha.com>
 *
 * @author	Mike Duncan <mike.duncan@waitwha.com>
 * @version	$Id$
 */
class ErrorHandler  {

  public static $verbose = false;
  public static $debugMode = false;
  
  /**
   * Handles errors safely for your pleasures.
   */
  public static final function handle($errno, $errstr, $errfile, $errline, $errcontext)  {
     
     if(error_reporting() == 0)
       return; //No point continuing if no error_reporting.
       
     $out = sprintf("[%s] ", date("Y-m-d H:i:s"));
     switch($errno)  {
       case	E_ERROR:
       case E_USER_ERROR:
        $out .= "ERROR ";
        break;
         
       case E_WARNING:
       case E_USER_WARNING:
        $out .= "WARN ";
        break;
        
       case E_NOTICE:
       case E_USER_NOTICE:
        $out .= "INFO ";
        break;
        
       default:
        $out .= $errno ." ";
        
     }
     
     
     $out .= $errstr;
     if(ini_get("log_errors") || ErrorHandler::$debugMode)
       $out .= sprintf(" (on line %d of %s)", $errline, $errfile);
     
     ErrorHandler::log($out);     
     return true;
  }
  
  /**
   * Writes messages to console or logfile.
   *
   * @param		string	$msg	Message to write.
   */ 
  private static final function log($out)  {
    if(ini_get("log_errors"))  {
       $logfile = ini_get("error_log");
       $f = fopen($logfile, "a+");
       if($f !== false)  {
         fputs($f, $out ."\n");
         fclose($f);
       }
     
    }else
       echo $out ."\n";
    
  }
  
  /**
   * Writes a INFO message.
   *
   * @param string	$msg	Message to write.
   */
  public static final function info($msg)  {
    if(ErrorHandler::$verbose)
      ErrorHandler::log(sprintf("[%s] INFO %s", date("Y-m-d H:i:s"), $msg)); 
    
  }
  
  /**
   * Writes a WARNING message.
   *
   * @param string	$msg	Message to write.
   */
  public static final function warn($msg)  {
    if(ErrorHandler::$verbose)
      ErrorHandler::log(sprintf("[%s] WARN %s", date("Y-m-d H:i:s"), $msg));
    
  }
  
  /**
   * Writes a ERROR message.
   *
   * @param string	$msg	Message to write.
   */
  public static final function error($msg)  {
    ErrorHandler::log(sprintf("[%s] ERROR %s", date("Y-m-d H:i:s"), $msg));
  }
  
  /**
   * Writes a DEBUG message, however ErrorHandler::debugMode must be TRUE 
   * for this to complete.
   *
   * @param	string	$msg	Message to write.
   */
  public static final function debug($msg)  {
    if(ErrorHandler::$debugMode)
      ErrorHandler::log(sprintf("[%s] DEBUG %s", date("Y-m-d H:i:s"), $msg));
    
  }

}
?>