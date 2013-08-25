package com.waitwha.logging;

import java.util.Date;
import java.text.SimpleDateFormat;
import java.util.logging.Formatter;
import java.util.logging.LogRecord;

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
public class StandardLoggingFormatter extends Formatter {

	public static final SimpleDateFormat UNIX_DATE_FORMAT = 
			new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	
	/**
	 * @see java.util.logging.Formatter#format(java.util.logging.LogRecord)
	 */
	@Override
	public String format(LogRecord record) {
		return String.format("[%s] %s %s\n", 
				UNIX_DATE_FORMAT.format(new Date()), 
				record.getLevel().toString().toUpperCase(), 
				record.getMessage());
	}

}
