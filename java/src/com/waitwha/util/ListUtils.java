package com.waitwha.util;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

/**
 * <b>ApacheCheck</b><br/>
 * <small>Copyright (c)2013 Mike Duncan <a href="mailto:mike.duncan@waitwha.com">mike.duncan@waitwha.com</a></small><p />
 *
 * TODO Document this class/interface.
 *
 * @author Mike Duncan <mike.duncan@waitwha.com>
 * @version $Id$
 * @package com.waitwha.util
 */
public class ListUtils {
	
	@SuppressWarnings({ "rawtypes", "unchecked" })
	public static List merge(List...lists)  {
		List r = new ArrayList<Object>(lists[0]);
		for(int i = 1; i < lists.length; i++)  {
			r.addAll((Collection<Object>) lists[i]);
		}
		
		return r;
	}
	
	public static String join(String delimiter, ArrayList<String> list) {
		String r = "";
		for(String s : list)
			r += delimiter + s;
		
		return r;
	}

}
