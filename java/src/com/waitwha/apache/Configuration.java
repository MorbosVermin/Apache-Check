package com.waitwha.apache;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.logging.Logger;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import com.waitwha.logging.LogManager;

/**
 * <b>ApacheCheck</b><br/>
 * <small>Copyright (c)2013 Mike Duncan <a href="mailto:mike.duncan@waitwha.com">mike.duncan@waitwha.com</a></small><p />
 *
 * Apache Configuration consisting of an array of Directive objects.
 *
 * @author Mike Duncan <mike.duncan@waitwha.com>
 * @version $Id$
 * @package com.waitwha.apache
 */
public class Configuration extends ArrayList<INode> {

	private static final long serialVersionUID = 1L;
	private static final Pattern COMMENT_LINE = Pattern.compile("^#");
	private static final Pattern CONTAINER_START_LINE = Pattern.compile("^<([a-zA-Z0-9\\-_]) ");
	private static final Pattern CONTAINER_END_LINE = Pattern.compile("^</([a-zA-Z0-9\\-_]) ");
	private static final Logger log = LogManager.getLogger(Configuration.class.getName());

	private Configuration(String path, boolean includeFiles) throws ConfigurationParsingException, IOException {
		super();
		File configFile = new File(path);
		if(!configFile.exists())
			throw new FileNotFoundException("The file '"+ path +"' could not be found.");
		
		try (BufferedReader br = new BufferedReader(new FileReader(path))) {
			String line = null;
			int lineNum = 0;
			while((line = br.readLine()) != null)  {
				lineNum++;
				line = line.trim();
				if(COMMENT_LINE.matcher(line).matches())
					continue;
				
				Matcher m = CONTAINER_START_LINE.matcher(line);
				if(m.matches())  {
					
				}
			}
		}
	}
	
	/**
	 * Returns a new instance of Configuration for the String path given.
	 * 
	 * @param path	String path to the file to parse.
	 * @return	Configuration
	 * @throws ConfigurationParsingException
	 * @throws IOException 
	 */
	public static final Configuration getInstance(String path, boolean includeFiles) throws ConfigurationParsingException, IOException {
		return new Configuration(path, includeFiles);
	}
	
	/**
	 * Returns a new instance of Configuration for the String path given; and all of the included files 
	 * are automatically included.
	 * 
	 * @param path	String path to the file to parse.
	 * @return	Configuration
	 * @throws ConfigurationParsingException
	 * @throws IOException 
	 */
	public static final Configuration getInstance(String path) throws ConfigurationParsingException, IOException {
		return Configuration.getInstance(path, true);
	}
	
	/**
	 * @param args
	 */
	public static void main(String[] args) {

	}

}
