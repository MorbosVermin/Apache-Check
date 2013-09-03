package com.waitwha.apache;

import java.util.ArrayList;
import java.util.logging.Logger;

import com.waitwha.logging.LogManager;
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

	private static final Logger log = LogManager.getLogger(Container.class.getName());
	private static final long serialVersionUID = 1L;
	private String name;
	private ArrayList<String> values;
	private boolean open;
	
	public Container(String name, ArrayList<String> values)  {
		super();
		this.name = name;
		this.values = values;
		this.open = true;
	}
	
	public Container(String name)  {
		this(name, new ArrayList<String>());
	}
	
	public Container()  {
		this("Global");
	}
	
	@Override
	public String getName()  {
		return this.name;
	}
	
	/**
	 * @see java.util.ArrayList#add(java.lang.Object)
	 */
	@Override
	public boolean add(INode e) {
		//log.info("["+ this.getName() +"] Added node: "+ e.getName() +" ("+ e.getClass().getName() +")");
		return super.add(e);
	}

	public void setName(String name)  {
		this.name = name;
	}
	
	public void addValue(String value)  {
		if(value.contains(" "))
			for(String v : value.split(" "))
				this.values.add(v);
		
		else
			this.values.add(value);
		
	}
	
	public ArrayList<String> getValues()  {
		return this.values;
	}
	
	public String getValuesAsString()  {
		return ListUtils.join(" ", this.values);
	}
	
	public boolean isOpen()  {
		return this.open;
	}
	
	public void close()  {
		this.open = false;
	}

	@Override
	public boolean isDirective() {
		return false;
	}

	@Override
	public ArrayList<INode> children() {
		return this;
	}

	public ArrayList<INode> search(String name)  {
		ArrayList<INode> res = new ArrayList<INode>();
		for(INode node : this.children())  {
			
			if(!node.isDirective())
				res.addAll(node.search(name));
			
			else if(node.getName().equals(name))
				res.add(node);
			
		}
		
		return res;
	}
	
}
