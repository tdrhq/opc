<?
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
	chdir( config::get_installation_dir() ) ;
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
