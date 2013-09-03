package com.waitwha.apache;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.logging.Level;
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
public class Configuration {

	private static final Pattern COMMENT_LINE = Pattern.compile("^[\\s*]?#.*");
	private static final Pattern DIRECTIVE_LINE = Pattern.compile("^[\\s*]?([a-zA-Z]*)\\s*(.*)");
	private static final Pattern CONTAINER_START_LINE = Pattern.compile("^[\\s*]?<([a-zA-Z]*)\\s*(.*)>");
	private static final Pattern CONTAINER_END_LINE = Pattern.compile("^[\\s*]?</([a-zA-Z]*)>");
	private static final Logger log = LogManager.getLogger(Configuration.class.getName());

	private Container global;
	private String serverRoot;
	private boolean continuation;
	
	private Configuration(String path, boolean includeFiles) throws ConfigurationParsingException, IOException {
		super();
		File configFile = new File(path);
		if(!configFile.exists())
			throw new FileNotFoundException("The file '"+ path +"' could not be found.");
		
		//Add global container
		this.global = new Container();
		
		this.continuation = false;
		int lineNum = 0;
		log.info("Reading configuration file at '"+ path +"'");
		try (BufferedReader br = new BufferedReader(new FileReader(path))) {
			String line = null;
			while((line = br.readLine()) != null)  {
				lineNum++;
				line = line.trim();
				if(COMMENT_LINE.matcher(line).matches())
					continue;
				
				log.info("Parsing: "+ line +" (line "+ lineNum +" from "+ path +")");
				addLine(line);
			}
		}
		
		serverRoot = new File(path).getParent();
		for(INode node : global.search("ServerRoot"))
			serverRoot = ((Directive)node).getValuesAsString();
		
		if(includeFiles)  {
			ArrayList<INode> includes = this.getNodesByName("Include");
			log.info("Parsing additional/included configuration(s): "+ includes.size());
			for(INode include : includes)  {
				Configuration c = Configuration.getInstance(serverRoot +"/"+ ((Directive)include).getValuesAsString(), includeFiles);
				global.addAll(c.getGlobal());
			}
		}
		
		log.info("Completed reading/parsing configuration file at '"+ path +"'. Read "+ lineNum +" line(s).");
	}
	
	/**
	 * Returns the last opened Container appended to the Configuration or the global Container.
	 *  
	 * @return Container
	 */
	private Container getLastContainer()  {
		Container lContainer = null;
		
		for(INode node : global)  {
			if(!node.isDirective() && ((Container)node).isOpen())  {
				lContainer = (Container)node;
			}
		}
		
		return (lContainer != null) ? lContainer : global;
	}
	
	public Container getGlobal()  {
		return this.global;
	}
	
	/**
	 * Returns the INode implementation (i.e. Directive or Container) objects 
	 * within this Configuration by the given name. Remember, Apache does not
	 * enforce naming so it is highly possible to override your proviously set
	 * directive/container with a later defined one. Additionally, some
	 * settings can be appended to as with the Port directive.
	 * 
	 * @param name	Name of the directive/container objects to return.
	 * @return	ArrayList<INode>
	 */
	private ArrayList<INode> getNodesByName(String name)  {
		ArrayList<INode> nodes = new ArrayList<INode>();
		for(INode node : global)  {
			if(!node.isDirective())
				nodes.addAll(node.search(name));
			else if(node.getName().equals(name))
				nodes.add(node);
			
		}
		
		return nodes;
	}
	
	/**
	 * Processes a line from the configuration file which is not a commented one.
	 * 
   * @param line	A non-commented line within the configuration file.
	 */
	private void addLine(String line)  {
		if(line.length() == 0)
			return;
		
		//If this is a closing container line, close the last Container object.
		if(CONTAINER_END_LINE.matcher(line).matches())  {
			Container c = this.getLastContainer();
			log.fine("Closing container: "+ c.getName() +" ("+ line +")");
			c.close();
			return;
		}
		
		//If this is a continuation of another line...
		if(continuation)  {
			INode n = this.getLastContainer();
			log.info("Appending to directive/container: "+ n.getName());
			if(n.isDirective())
				((Directive)n).add(line);
			else
				((Container)n).addValue(line);
			
			this.continuation = line.endsWith("\\");
			return;
		}
		
		/*
		 * Start processing of Directive or Container line.
		 */
		INode node = null;
		Matcher m = CONTAINER_START_LINE.matcher(line);
		if(m.matches())  {
			node = new Container(m.group(1));
			if(m.groupCount() >= 1)
				for(int i = 2; i < m.groupCount(); i++)
					((Container)node).addValue(m.group(i));
			
		}else if((m = DIRECTIVE_LINE.matcher(line)).matches())  {
			node = new Directive(m.group(1));
			for(int i = 2; i < m.groupCount(); i++)
				((Directive)node).add(m.group(i));
			
		}
		
		if(node != null)
			this.getLastContainer().add(node);
		else
			log.warning("Could not parse configuration line: "+ line);
		
		this.continuation = line.endsWith("\\");
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
		LogManager.APP_NAME = "apache-check";
		try  {
			Configuration c = Configuration.getInstance(args[0]);
			log.info("Parsed configuration successfully: "+ c.getGlobal().children().size() +" nodes.");
			
		}catch(Exception e) {
			e.printStackTrace();
			
		}
	}

}
