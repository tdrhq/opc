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
 * @file   CompilerGcj.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "CompilerGenericGNU.inc" ; 


class CompilerGcj extends CompilerGenericGNU { 

  var $pathtocode ; 

  public function __construct($param) {
	$this -> compiler_string = "/usr/bin/gcj -Wall --main=Main " ;
	parent::__construct($param) ;
  }
  
  public function compile($pathtocode) {
	//first copy this to some temporary directory
	$this->tmpdir = trim(`mktemp -d`) ; 
	
	$this->pathtocode = $this->tmpdir . "/Main.java" ;
	
	if (! copy( $pathtocode, $this->pathtocode ) ) {
	  throw new Exception("Unable to copy file");
	}

	return parent::compile($this->pathtocode) ;
  }

  public function __destruct() {
	unlink($this->pathtocode) ;
	rmdir(dirname($this->pathtocode) ) ;
	parent::__destruct() ;
  }

  public function fix_options_array ($options)
  {
	  if (empty($options["mem"])) return $options;
	  $options["mem"] = $this->convert_memsize_to_bytes (config::$java_additional_memory) + $this->convert_memsize_to_bytes ($options["mem"]);
	  return $options;
  }

};


