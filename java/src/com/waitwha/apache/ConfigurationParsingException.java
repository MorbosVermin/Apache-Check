package com.waitwha.apache;

import java.text.ParseException;

/**
 * <b>ApacheCheck</b><br/>
 * <small>Copyright (c)2013 Mike Duncan <a href="mailto:mike.duncan@waitwha.com">mike.duncan@waitwha.com</a></small><p />
 *
 * TODO Document this class/interface.
 *
 * @author Mike Duncan <mike.duncan@noaa.gov>
 * @version $Id$
 * @package com.waitwha.apache
 */
public final class ConfigurationParsingException extends ParseException {

	private static final long serialVersionUID = 1L;

	public ConfigurationParsingException(String s, int errorOffset) {
		super(s, errorOffset);
	}

}
