<?php
/**
 * Copyright 2009 Arnold Noronha
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
 * @file   CompilerAbstract.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */


abstract class CompilerAbstract
{
	abstract public function compile ($pathtocode);
	abstract public function get_exec_dir ();
	abstract public function get_exec_string ();
	abstract public function get_error ();

	public function fix_options_array ($options) {
		return $options;
	}

	/**
	 * useful function to convert a memory size specification into num
	 * of bytes. e.g., 2kB = 2048 bytes. 
	 */
	protected static function convert_memsize_to_bytes ($memstring)
	{
		/* this code was adapted from runner.c */
		$c = "-";
		sscanf ($memstring, "%d%c", $val, $c);
		$c = strtolower ($c);
		if ($c == "-") return $val;
		if ($c == "k") return $val*1024;
		if ($c == "m") return $val*1024*1024;
		if ($c == "g") return $val*1024*1024*1024;
		
		throw new Exception ("Badly formatted memory string");
	}
}
