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
 * @file   queuemanager.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

chdir(dirname($argv[0])) ;

$conffile = "../config.inc" ;


include_once $conffile  ;
ob_end_clean();
include_once "lib/db.inc";
include_once "lib/submissions.inc" ;
require_once "HookAgent.php" ;

include_once dirname(__FILE__) . "/compiler/common.inc" ;

/* options */
for ($i = 1; $i < $argc; $i++) {
	if ($argv[$i] == "--exit-on-done") {
		$exit_on_done = true;
	}
}

/**
 * State of submission Constants.
 */

define(SUBMISSION_STATE_WAITING,'waiting');
define(SUBMISSION_STATE_RUNNING,'queued');
define(SUBMISSION_STATE_COMPLETED,'completed');
define(SUBMISSION_STATE_FATAL,'fatal');

/*
 *
 *  Main Code
 *
 */
						

class ContestQueueManager {
  /**
   * The table where the submissiondata is stored.
   * 
   */
  public $table = 'submissiondata' ;
  public $id ; 
  public $info = array() ; 
  public $terminating = false;
  
  function __construct() {

  }
  
 
  /**
   * Get a list of waiting submissions in the queue.
   *
   * @returns array(string)
   */

  function get_waiting_queue($c)  
  { 
	return SubmissionTable::get_waiting_queue($c) ;
  }
  
  
  /**
   * Process a submission by its submission ID
   * 
   * @param $id string Submission id
   */
  function start_process_submission ($id) 
  {
	$this->id = $id ;
	$info = array("id" => $id ) ;
	$res =SubmissionTable::set_state($id, SUBMISSION_STATE_RUNNING ,
								SUBMISSION_STATE_WAITING);
	if ( !$res) { 
	  echo "Processing $id failed, possible race conditition.\n";
	  return true;
	}
	echo "Processing $id\n" ;

	exec(config::get_path_to_evaluator() .  " $id 2>/dev/null",$output,$ret);

	if ( $ret) {
	  SubmissionTable::set_state($id,SUBMISSION_STATE_FATAL ) ; 
	  echo "FATAL ERROR: Submission ID $id could not be run!\n";
	  echo "exec: returned $ret\n";
	  echo "Program returned: $output\n";
	  $info['state'] = 'fatal' ; 
	  $agent = new HookAgent($id, $this->info) ;
	  $agent->run_hooks() ; 
	  return false;
	}

	$agent = new HookAgent($id, $this->info) ;
	$agent->run_hooks() ;
	return true;
  }


  /**
   * Start the judge.
   * 
   * @returns void
   */ 
  function start_queue () {
	global $exit_on_done;
	fprintf(STDOUT,"The queue has started.\n");
	while ( !$this->terminating  ) {
	  $ar = $this->get_waiting_queue(1) ;
	  if ( count($ar) > 1 ) { 
		throw new Exception("Too many elements");
	  }
	  if ( empty($ar))  {
		if (!empty($exit_on_done)) exit(0);
		$ms = config::$queue_inactive_sleep_time * 1000000 ; 
		usleep(mt_rand($ms/2,$ms)) ;
		//sleep(1);
		continue ;
	  }

	  foreach ( $ar as $x ) {
		/* Note this includes a fork! */
		$this->start_process_submission($x) ;
	  }
	}
	echo "Exiting gracefully.\n" ;
	exit (0);
  }

 
}

$queue = new ContestQueueManager  ;
declare (ticks = 1);

function sigterm_handler ($signo) 
{
	if ($signo == SIGINT || $signo == SIGTERM) {
		global $queue;
		$queue->terminating = true;
	} else 
		echo "Warning: received $signo, expected SIGTERM\n";
}

if (function_exists ("pcntl_signal")) {
	pcntl_signal (SIGINT, "sigterm_handler");
	pcntl_signal (SIGTERM, "sigterm_handler");
} else {
  fprintf (STDOUT, "Warning: pcntl_signal does not exist, cannot kill cleanly!\n");
}

$queue -> start_queue() ;


