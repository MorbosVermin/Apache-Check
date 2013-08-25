package com.waitwha.apache;

import java.util.ArrayList;

import com.waitwha.util.ListUtils;

/**
 * <b>ApacheCheck</b><br/>
 * <small>Copyright (c)2013 Mike Duncan <a href="mailto:mike.duncan@waitwha.com">mike.duncan@waitwha.com</a></small><p />
 *
 * Represents a Directive within an Apache configuration.
 *
 * @author Mike Duncan <mike.duncan@waitwha.com>
 * @version $Id$
 * @package com.waitwha.apache
 */
public class Directive extends ArrayList<String> implements INode {
	
	private static final long serialVersionUID = 1L;
	private String name;
	
	public Directive(String name)  {
		super();
		this.name = name;
	}
	
	public String getName()  {
		return this.name;
	}
	
	public void setName(String name)  {
		this.name = name;
	}
	
	public String getValuesAsString()  {
		return ListUtils.join(" ", this);
	}
	
	@Override
	public String toString()  {
		return this.getName().concat(" " + this.getValuesAsString());
	}

	@Override
	public boolean isDirective() {
		return true;
	}

	@Override
	public ArrayList<INode> children() {
		return null;
	}

}
