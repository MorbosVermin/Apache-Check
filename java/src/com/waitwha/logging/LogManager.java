package com.waitwha.logging;

import java.util.logging.FileHandler;
import java.util.logging.Handler;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * <b>ApacheCheck</b><br/>
 * <small>Copyright (c)2013 Mike Duncan <a href="mailto:mike.duncan@waitwha.com">mike.duncan@waitwha.com</a></small><p />
 *
 * TODO Document this class/interface.
 *
 * @author Mike Duncan <mike.duncan@waitwha.com>
 * @version $Id$
 * @package com.waitwha.logging
 */
public class LogManager {

	public static String APP_NAME = "appName";
	
	public static final Logger getLogger(String clazzName)  {
		Logger log = java.util.logging.Logger.getLogger(clazzName);
		log.setLevel(Level.INFO);
		
		StandardLoggingFormatter formatter = new StandardLoggingFormatter();
		for(Handler handler : log.getHandlers())
			handler.setFormatter(formatter);
		
		try  {
			FileHandler logFileHandler = new FileHandler(APP_NAME +".log");
			logFileHandler.setFormatter(formatter);
			log.addHandler(logFileHandler);
			
		}catch(Exception e)  {
			log.warning("Could not open/read log file '"+ APP_NAME +".log'");
		}
		
		return log;
	}
	
}
