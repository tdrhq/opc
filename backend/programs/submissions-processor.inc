<?

require_once "../config.inc" ;
require_once "lib/db.inc" ;
require_once "lib/submissions.inc";
require_once "lib/problems.inc";
require_once dirname(__FILE__) . "/Judge.inc";

/**
 * Change a db array into a PHP array
 * @param $dbarr String representing the DB array
 * @return A PHP array
 * @see http://in.php.net/pgsql User comments.
 */
function pgToPhpArray($dbarr) {
  // Take off the first and last characters (the braces)
  $arr = substr($dbarr, 1, strlen($dbarr) - 2);
  
  // Pick out array entries by carefully parsing.  This is necessary in order
  // to cope with double quotes and commas, etc.
  $elements = array();
  $i = $j = 0;       
  $in_quotes = false;
  while ($i < strlen($arr)) {
	// If current char is a double quote and it's not escaped, then
	// enter quoted bit
	$char = substr($arr, $i, 1);
	if ($char == '"' && ($i == 0 || substr($arr, $i - 1, 1) != '\\'))
	  $in_quotes = !$in_quotes;
	elseif ($char == ',' && !$in_quotes) {
	  // Add text so far to the array
	  $elements[] = substr($arr, $j, $i - $j);
	  $j = $i + 1;
	}
	$i++;
  }
  // Add final text to the array
  $elements[] = substr($arr, $j);
  
  // Do one further loop over the elements array to remote double quoting
  // and escaping of double quotes and backslashes
  for ($i = 0; $i < sizeof($elements); $i++) {
	$v = $elements[$i];
	if (strpos($v, '"') === 0) {
	  $v = substr($v, 1, strlen($v) - 2);
	  $v = str_replace('\\"', '"', $v);
	  $v = str_replace('\\\\', '\\', $v);
	  $elements[$i] = $v;
	}
  }
  
  return $elements;
}

class SafeException extends Exception {
};
    

class SubmissionProcessor 
{
  static function process ($sub_id)
  {
    /*
     *  Information about the submission
     */ 
    $info = SubmissionTable::get_submission($sub_id) ;
    
    /*
     * Information about the problem
     */
    $prob = ProblemTable::get_problem($info -> problemid );
    
    if ( is_array ($prob->scoreweights ) )
      $scoreweights = $prob->scoreweights ; 
    else 
      $scoreweights = pgToPhpArray($prob->scoreweights) ;
    
    if ( is_array($prob->inputpaths ) ) 
      $inputpaths = $prob->inputpaths ; 
    else 
      $inputpaths   = pgToPhpArray($prob->inputpaths);
    
    if ( is_array($prob->outputpaths )) 
      $outputpaths = $prob->outputpaths ; 
    else
      $outputpaths  = pgToPhpArray($prob->outputpaths);
    
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
	
	echo "Running on " . $inputpaths[$i] . " \n" ;
	$ret = $j -> run ($inputpaths[$i],$prob->getResourceLimitString(), $response);
	
	if ( ! $ret ) {
	  echo "Runtime Error\n";
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
	
	echo "Verifying\n";
	$ret = $j -> verify($outputpaths[$i]);
	
	if ( ! $ret ){
	  if ($prob->grading_style != "ioi")
	    SubmissionTable::set_state($info->id,"Wrong Answer");
	  if (empty($ioi_result)) $ioi_result = "Wrong Answer" ; 
	  $j -> end_test_case() ;
	  echo "Wrong answer and/or other error\n";
	  if ( $prob->grading_style == "opc" ) 
	    throw new SafeException () ;
	  else if ( $prob->grading_style == "ioi" )  { 
	    $allsucc = false ; 
	    continue ; 
	  } else
	    assert(false) ;
	}
	
	
	echo "Verified\n";
	
	if ( $prob->doesCheckerOutputScore ) {
	  $cur_score += $j->checker_score;
	  echo "Score: $j->checker_score\n";
	  echo "State: $j->checker_state\n";
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