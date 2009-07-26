#!/usr/bin/php5
<?

/**
 * Purpose:
 * 
 * This processes command line input to compile and
 * run code and produces XMLised output of the submission
 * result.
 */


chdir( dirname($argv[0]));
require_once "../config.inc" ;
require_once "lib/db.inc" ;
require_once "lib/submissions.inc";
require_once "lib/problems.inc";
require_once "submissions-processor.inc";


if (empty(config::$results_directory))
  die("Please specify a results directory in config.inc or"
	  . " local_config.inc " );

if ($argc < 2) {
  echo "Usage: ". $argv[0] . " <submissioncode> \n";
  echo "Not meant to be called directly\n";
  exit(1) ;
 }

if ($argc > 2 &&  $argv[2] == "--debug" ) {
  define(DEBUG,1);
}

/*
 * Submission ID
 */
$sub_id = $argv[1] ; 
SubmissionProcessor::process ($sub_id);




