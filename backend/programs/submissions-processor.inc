<?

require_once dirname(__FILE__) . "/../config.inc" ;
require_once "lib/db.inc" ;
require_once "lib/submissions.inc";
require_once "lib/problems.inc";
require_once dirname(__FILE__) . "/Judge.inc";


class SafeException extends Exception {
};
    

class SubmissionProcessor 
{
  static function process ($sub_id, $debug = false)
  {
    /*
     *  Information about the submission
     */ 
    $info = SubmissionTable::get_submission($sub_id) ;
    
    /*
     * Information about the problem
     */
    $prob = ProblemTable::get_problem($info -> problemid );
    
    assert (is_array ($prob->scoreweights));
    $scoreweights = $prob->scoreweights; 
    
    assert (is_array($prob->inputpaths)); 
    $inputpaths = $prob->inputpaths; 
    
    assert (is_array($prob->outputpaths));
    $outputpaths = $prob->outputpaths; 
    
    /*
     * The judge object
     */
    if ( empty($prob->checker) )
      $j = new Judge ($info->pathtocode ,$info->lang);
    else 
      $j = new Judge($info->pathtocode, $info->lang, $prob->checker);
    
    $cur_score = 0 ;
    
    /* A bogus Exception for the purpose of error handling
     * during the compile-run-verify cycle.
     */
    
    try  {
      SubmissionTable::set_state($info->id, "Compiling");
      
      $compileOptions = "";
      if (!empty ($prob->compileOptions[$info->lang])) $compileOptions = $prob->compileOptions[$info->lang];
      if ( ! $j -> compile($compileOptions) ) {
	SubmissionTable::set_state( $info->id, "Compile Error") ;
	throw new SafeException ;
      }
      
      $allsucc = true ;
      $ioi_result = "" ; /* used only during IOI grading */
      for ( $i = 0 ; $i < $prob->numcases ; $i ++ ){
	SubmissionTable::set_state($info->id, "Running($i)");
	
	$j ->start_test_case("" . $i , "Test Case #$i for " .
			     $scoreweights[$i] . " points" );
	
	if ($debug) echo "Running on " . $inputpaths[$i] . " \n" ;
	$ret = $j -> run ($inputpaths[$i],$prob->getResourceLimitString(), $response);
	
	if ( ! $ret ) {
	  if ($debug) echo "Runtime Error\n";
	  if ( $response != "TLE" ) {  
	    if ( $prob->grading_style == "opc") 
	      SubmissionTable::set_state(
					 $info->id,"Runtime Error ($response)");
	    else if (empty($ioi_result) ) 
	      $ioi_result = "Runtime Error ($response)" ;
	  }
	  else { 
	    if ($prob->grading_style == "opc" )
	      SubmissionTable::set_state(
					 $info->id,"Time Limit Exceeded") ;
	    else if (empty($ioi_result) ) 
	      $ioi_result = "Time Limit Exceeded";
	  }
	  $j -> end_test_case() ;
	  if ( $prob->grading_style == "opc" ) 
	    throw new SafeException () ; 
	  else if ( $prob->grading_style == "ioi") { 
	    $allsucc = false ; 
	    continue ;
	  } else assert(false) ;
	  
	}
	
	if ($debug) echo "Verifying\n";
	$ret = $j -> verify($outputpaths[$i]);
	
	if ( ! $ret ){
	  if ($prob->grading_style != "ioi")
	    SubmissionTable::set_state($info->id,"Wrong Answer");
	  if (empty($ioi_result)) $ioi_result = "Wrong Answer" ; 
	  $j -> end_test_case() ;
	  if ($debug) echo "Wrong answer and/or other error\n";
	  if ( $prob->grading_style == "opc" ) 
	    throw new SafeException () ;
	  else if ( $prob->grading_style == "ioi" )  { 
	    $allsucc = false ; 
	    continue ; 
	  } else
	    assert(false) ;
	}
	
	
	if ($debug) echo "Verified\n";
	
	if ( $prob->doesCheckerOutputScore ) {
	  $cur_score += $j->checker_score;
	  if ($debug) echo "Score: $j->checker_score\n";
	  if ($debug) echo "State: $j->checker_state\n";
	  if ( $prob->grading_style == "ioi" && 
	       empty($ioi_result) && !empty($j->checker_state)
	       && $j->checker_state != "Accepted") {
	    $ioi_result = $j->checker_state;
	    $allsucc = false ;
	  }
	}
	else $cur_score += $scoreweights[$i] ;
	
	$j -> end_test_case() ;
      }
      
      if ( $i == $prob ->  numcases and ( $prob->grading_style == "opc" or
					  $allsucc ) ) 
	SubmissionTable::set_state($info->id,"Accepted");
      else {
	if ( empty($ioi_result) ) $ioi_result  = "BAD" ;
	SubmissionTable::set_state($info->id, $ioi_result) ;
      }
      
    } 
    catch(SafeException $e) {
      /*
       * Don't do anything. By throwing a SafeException, we've guaranteed
       * that the state has been set to what it should be
       */
    }
    
    
    /*
     * Set the score, save the XML file etc. 
     */
    SubmissionTable::set_score($info->id,$cur_score) ;
    
    $res = config::$results_directory . "/" .$info->id . ".xml" ;
    $xml = $j -> end() ;
    
    if (! file_put_contents($res, $xml->flush(), LOCK_EX )) {
      // No write permissions perhaps?
      exit(1);
    }
    chmod($res,0644);
  }
}
