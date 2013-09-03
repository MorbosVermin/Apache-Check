package com.waitwha.apache;

import java.util.ArrayList;

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
public interface INode {

	public ArrayList<INode> search(String name);
	public String getName();
	public boolean isDirective();
	public ArrayList<INode> children();
	
}
