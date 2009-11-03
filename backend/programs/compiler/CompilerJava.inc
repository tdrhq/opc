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
 * @file   CompilerJava.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

class CompilerJava { 

  var $compiler_string ; 
  
  var $pathtocode ; 

  var $error ; 

  public function __construct() {
	if ( !empty(config::$compiler_string_java) )
	  $this->compiler_string = config::$compiler_string_java ;
	$this->compiler_string = 'javac ' ;
  }
  public function  compile($pathtocode){
	
	//Copy the file to Main.java within the same directory
	if ( config::$enable_unique_java_classname) {

	  $dir = trim(`mktemp -d`) ; 

	  $this->pathtocode = $dir . "/Main.java" ;

	  if (! copy( $pathtocode, $this->pathtocode ) ) {
		throw new Exception("Unable to copy file");
	  }
	  $pathtocode = $this->pathtocode ;
	  
	} else { 
	  throw new Exception("please enable unique java classname");
	}

	$ret =runandgetoutput( $this->compiler_string . 
						   " " . escapeshellarg($pathtocode),
						   $bluff, $this->error) ;
	return ($ret==0) ; 
  }
  public function get_error(){
	return $this -> error;
  }
  public function get_exec_string() { 
	return "/usr/bin/env java Main" ;
  }
  public function get_exec_dir() {
	return dirname($this->pathtocode) ;
  }
  public function __destruct() {
	unlink($this->pathtocode) ;
	unlink(dirname($this->pathtocode). "/Main.class" );
	rmdir(dirname($this->pathtocode)) ;
  }

}
