#!/usr/bin/env php
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
 * @file   submissions.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

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
	$debug = true;
	define (DEBUG, 1);
} else
	$debug = false;  

/*
 * Submission ID
 */
$sub_id = $argv[1] ; 
SubmissionProcessor::process ($sub_id, $debug);




