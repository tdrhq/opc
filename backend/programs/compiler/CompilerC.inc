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
 * @file   CompilerC.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "CompilerGenericGNU.inc" ;

class CompilerC extends CompilerGenericGNU { 

  public function __construct($param) {
	$this -> compiler_string = "/usr/bin/gcc -Wall -lm -O2 -x c " ;
	parent::__construct($param) ;
  }

};

?>
