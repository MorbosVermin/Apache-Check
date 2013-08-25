package com.waitwha.apache;

import java.util.ArrayList;

import com.waitwha.util.ListUtils;

/**
 * <b>ApacheCheck</b><br/>
 * <small>Copyright (c)2013 Mike Duncan <a href="mailto:mike.duncan@waitwha.com">mike.duncan@waitwha.com</a></small><p />
 *
 * TODO Document this class/interface.
 *
 * @author Mike Duncan <mike.duncan@waitwha.com>
 * @version $Id$
 * @package com.waitwha.apache
 */
public class Container extends ArrayList<INode> implements INode {

	private static final long serialVersionUID = 1L;
	private String name;
	private ArrayList<String> values;
	
	public Container(String name, ArrayList<String> values)  {
		super();
		this.name = name;
		this.values = values;
	}
	
	public Container()  {
		this("Global", new ArrayList<String>());
	}
	
	public String getName()  {
		return this.name;
	}
	
	public void setName(String name)  {
		this.name = name;
	}
	
	public ArrayList<String> getValues()  {
		return this.values;
	}
	
	public String getValuesAsString()  {
		return ListUtils.join(" ", this.values);
	}

	@Override
	public boolean isDirective() {
		return false;
	}

	@Override
	public ArrayList<INode> children() {
		return this;
	}

}
