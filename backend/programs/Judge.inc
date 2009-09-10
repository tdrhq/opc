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
 * @file   Judge.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */
/**
 * Purpose:
 * 
 * Look at a submission and process it as if it were a webpage.
 * the output of this script will be stored so that teams
 * can view it from for complete information.
 *
 * @Author AJN
 */


require_once "config.inc" ;
ob_end_clean() ;
require_once "compiler/common.inc";
include_once "compiler/CompilerJava.inc" ;
include_once "compiler/CompilerCpp.inc" ;
include_once "compiler/CompilerC.inc" ;
include_once "compiler/CompilerGcj.inc" ;
include_once "compiler/CompilerGenericInterpreter.inc" ;

/**
 * The assumed "Compiler" interface (at this stage of development
 * the author is not aware of interfaces in PHP.) should
 * provide for the following primitives:
 *   - compile( $linktocode )
 *   - get_exec_string()  get a string to execute the compiled
 *                        binary.
 *   - cleanup ()         cleanup and bytecodes/executables 
 *                        lying around.
 */





class Judge {
  /**
   * The compiler object. 
   * Whatever value this takes, it should implement Compiler. 
   */
  var $compiler_object = NULL;
  
  var $path_to_code ;
  var $lang ;
  var $options;
  var $checker ;
  
  var $xmlw ;
  
  var $temp_output; 

  var $checker_score;
  
  var $checker_state;

  /**
   * Contructor. 
   * @param $_path_to_code path to the users submitted code.
   * @param $_lang language
   * @param $_checker what checker to use to verify the code.
   */
  public function __construct ($_path_to_code,$_lang,
							   $_checker = "diff -qwbBE ")  { 

	$this -> path_to_code = $_path_to_code;
	$this -> lang = $_lang;
	$this -> checker = $_checker ;
	$this -> xmlw = new XMLWriter() ;
	$this -> xmlw->openMemory();
	$this -> xmlw->setIndent(true);
	$this -> xmlw->startDocument('1.0','ISO-8859-1');
	$this -> xmlw->startElement('judge');
	$this -> temp_output = tempnam (
			config::$compile_temp_directory, "output" ) ;

  }


  /**
   * A generic compile function
   *
   */
  public function compile($param= "") {
	$this -> xmlw -> startElement("compiler") ;
	$lang = $this -> lang ;
	if ($lang == "cpp" ) 
	  $this->compiler_object = new CompilerCpp ($param) ;
	elseif ($lang == "c" )
	  $this->compiler_object = new CompilerC ($param) ;
	elseif ($lang == "java") 
	  $this->compiler_object = new CompilerJava;
	elseif ($lang == "gcj" ) 
	  $this->compiler_object = new CompilerGcj ($param);
	elseif ($lang == "perl") 
	  $this->compiler_object = new CompilerGenericInterpreter("perl") ;
	elseif ($lang == "python") 
	  $this->compiler_object = new CompilerGenericInterpreter("python");
	else 
	  throw new Exception("Unknown language: $lang\n");

	$this->xmlw -> writeElement("time", date('r'));
	
	$ret = $this->compiler_object->compile($this->path_to_code) ;
	
	/* produce neat output telling the user what happened
	*/


	if ( $ret && $this->compiler_object->get_error() !=""  ) {
	  $this->xmlw -> writeElement("status", "success") ;
	  $this->xmlw -> writeElement("warnings",
							$this->compiler_object -> get_error() );
	  $this->xmlw -> endElement() ;
	  return true;
	}
	else if ( $ret ){
	  $this->xmlw -> writeElement("status", "success");
	  $this->xmlw -> endElement() ;
	  return true;
	}
	else {
	  $this->xmlw -> writeElement("status", "error") ;
	  $this->xmlw -> writeElement("error", 
							$this->compiler_object->get_error() );
	  $this->xmlw -> endElement() ;

	  return false;
	}


	
  }

  /**
   * Run the code agains the given testdata_input (input file) 
   * with $options to be given to runner, and the error response
   * will be stored in response.
   *
   * @param[in] $testdata_input the path to the input file.
   * @param[in] $options the options to be passed to runner.
   * @param[out]  $response the output of the runner (basically error output,
   *      but all you should be concerned with is that this has useful
   *      information.
   * @return TRUE if the run was succesful
   */
  public function run ($testdata_input,$options,&$response) {

	$this->xmlw -> startElement("exec") ;
	$this->xmlw -> writeElement("time", date('r'));
	//	echo $this ->compiler_object->get_exec_string();
	// throw new Exception("Not compiled");
	
	//TODO: FIX
	$output = "" ;
	$olddir = getcwd() ;

	chdir( $this->compiler_object->get_exec_dir() );

	$res = runandgetoutputerr
	  (config::get_path_to_runner () . 
	   (empty(config::$chroot_dir) ? "" : "--chroot=" . config::$chroot_dir) .
	   " $options " .
	   " --input={$testdata_input} " .
	   " --output={$this->temp_output} " . 
	   $this->compiler_object->get_exec_string (), $output, $err);

	chdir ( $olddir) ;

	if ($res != 0 ) {
		$this->xmlw -> writeElement("status","unknown");
	}
	else if ( !empty($output) ) {
	  /* Program has aborted abnormally */
	  $ar = explode (' ', trim($output), 2 );
	  
	  $this->xmlw -> writeElement("status", $ar[0]) ;
	  $response = $ar[0] ;
	  $this->xmlw -> writeElement("description", $ar[1]);
	}
	else {
	  $this->xmlw->writeElement("status", "success");
	}

	$this->xmlw->writeElement("stdout",$output);
	if ( $err != "" ) { 
	  $this->xmlw-> writeElement("stderr", $err) ;
	  /* Parse the stderr for result */ 
	  $arr = explode('Runtime: ', $err) ;
	  $runtime = 0.0+ $arr[1] ; 
	  $this->xmlw->writeElement("runtime", $runtime) ;
	} 
	$this->xmlw -> endElement() ;
	return ($res == 0  && empty($output))  ;

  }

  /**
   * Check the already generated output against a known
   * output (usually the official output)
   *
   * Also adds a correctness->status line in the XML output.
   * success indicates ACCEPTED, and error indicates NOT ACCEPTED.
   *
   * @param $output string The official output
   * @return boolean accepted or not
   */
  public function verify($output) {
	$this->xmlw -> startElement("check");
	$this->xmlw -> writeElement("time", date('r'));
	$ret = runandgetoutputerr ( 
				 $this->checker . " " 
				 . $this->temp_output . " " 
				 . escapeshellarg($output) . " " 
				 . $this->options,
				 $sOutput,$sOutputError
				  ) ;
	if ( $ret == 0 )
	  $this->xmlw -> writeElement("status", "success" ) ;
	else
	  $this->xmlw -> writeElement("status", "error" );
	$this->xmlw->writeElement("stderr", $sOutputError);
	$this->xmlw->writeElement("stdout", $sOutput);
	$ar = explode(" ", $sOutput, 2);
	$this->checker_score = (int) trim($ar[0]); 
	$this->checker_state = trim(empty($ar[1])?"":$ar[1]);
	if ( !empty($this->checker_state)){
	  $this->xmlw->writeElement("message", $this->checker_state . 
				    "for score: "
				  . $this->checker_score);
	  $this->xmlw->writeElement("score", $this->checker_score);
	  $this->xmlw->writeElement("result", $this->checker_state);
	}
	$this->xmlw -> endElement() ;
	return ($ret == 0) ;
    
  }

  public function end() {
	$this -> xmlw ->endElement() ; //judge, root element
	$this -> xmlw -> endDocument() ;
	return $this -> xmlw ;
  }
  
  public function __destruct() {
	/* cleanup temps */
	if (defined('DEBUG')) echo "Cleaning up Judge files\n";
	else unlink ($this->temp_output) ;
   }

  /**
   * Start a testcase series
   * 
   * @param $identifier string Identifier to identify this input case
   * @param $message string A custom message to add to XML output
   * @param $rawxml string raw xml header, don't use unless necessary
   */
  public function start_test_case( $identifier,
								   $message="",
								   $rawxml="") {
	$this -> xmlw ->startElement("testcase") ;
	$this -> xmlw ->writeElement("identifier", $identifier) ;
	if (!empty($message))
	  $this -> xmlw -> writeElement("message", $message) ;
	if ( !empty($rawxml) )
	  $this->xmlw ->writeRaw ($rawxml);
  }

  public function end_test_case() {
	/* typically we would like to ensure this is a valid call
	 * but lets forget that for now.
	 */
	$this->xmlw->writeElement("time",date('r'));
	$this->xmlw->endElement();
  }
}




function __judge_self_test () {
  global $argv, $argc ;
  //  echo config::get_programs_dir();
  printf( "Starting self test\n" ) ;

  $j = new Judge ( $argv[1],
				   $argv[2] ) ;

  //echo $j -> doJudge () -> flush ();

  $j -> start_test_case("easy", "Easy for 10 points" );
  $ret =  $j -> compile ()  ;

  $j -> run ( $argv[3], $argv[5] ) ;

  $j -> verify($argv[4] );
  $j -> end_test_case() ;
  echo $j -> end() -> flush();
}


//__judge_self_test() ;
