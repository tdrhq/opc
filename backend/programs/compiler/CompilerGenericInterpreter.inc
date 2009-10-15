<?php
/**
 * Copyright 2007-2009 Chennai Mathematical Institute
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file   CompilerGenericInterpreter.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */


class CompilerGenericInterpreter 
{ 
	var $interpreter ; 
	var $pathtocode ; 
	public function __construct($inter) 
	{
		assert (!empty($inter));
		$this->interpreter = $inter; 
	}

	public function __destruct()
	{
		if (defined ('DEBUG')) return;
		unlink ($this->pathtocode);
	}
	
	public function compile($pathtocode)  
	{
		$this->pathtocode = tempnam (config::$compile_temp_directory, "a");
		copy ($pathtocode, $this->pathtocode);
		return true;
	}

	public function get_error() 
	{ 
		return "" ; 
	}

	public function get_exec_string() 
	{
		$prefix = "";
		if (!empty (config::$chroot_dir)) $prefix = config::$chroot_dir;
		return "$prefix/usr/bin/env " . $this->interpreter 
			. "  " .$this->pathtocode ;
	}
	
	public function get_exec_dir() 
	{
		return ".";
	}
	
}
