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
 * @file   HookAgent.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */
require_once "lib/problems.inc" ;

class HookAgent { 
  var $id ; 
  var $info = array() ; 
  function __construct($id, $info) { 
	
	$this->id = (int)$id; 
	$this->info = $info ; 
	$this->info[id] = "$id" ; 
  }

  /**
   * Run a set hook.
   * @param the string pointing to the location of the hook.
   * @return whatever the hook returned
   */
  function run_single_hook($hook) { 
	/* replace the $hook with suitable variables */ 
	
	foreach ( $this->info as $key => $value ) { 
	  $hook = str_replace("{". $key . "}", $value, $hook) ;
	}

	echo "Running hook: $hook\n" ;
	$d = getcwd() ; 
	chdir(dirname(__FILE__) . "/..") ;
	system($hook, $ret) ; 
	chdir($d) ;

	return $ret ;
  }

  /**
   * Runs all the set hooks for the given problem. 
   */
  function run_hooks() {
	$sub = SubmissionTable::get_submission($this->id) ;
	$this->info['problem'] = $sub->problemid; 
	$xml = new DOMDocument() ; 
	$xml_path = ProblemTable::get_problem_xml_file($sub->problemid) ;
	if ( empty($xml_path) or !is_file($xml_path) ) return ;

	$xml->load($xml_path);
	$xp = new DOMXPath($xml) ;

	$res = $xp->query("/problem/hook"); 
	foreach( $res as $hook )  { 
	  $this->run_single_hook($hook->nodeValue) ;
	}
	
	/* run hooks on the submission file */
	$xml = new DOMDocument() ; 
	$xml_path = SubmissionTable::get_submission_xml_file($this->id) ;
	if ( empty($xml_path) or !is_file($xml_path) ) return ; 
	$xml->load($xml_path) ;
	$xp = new DOMXPath($xml) ;

	$res = $xp->query("/submission/hook") ; 
	foreach($res as $hook) { 
	  $this->run_single_hook($hook->nodeValue) ;
	}
  }
  
  
}
