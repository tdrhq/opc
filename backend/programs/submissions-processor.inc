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
 * @file   submissions-processor.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once dirname(__FILE__) . "/../config.inc" ;
require_once "lib/db.inc" ;
require_once "lib/submissions.inc";
require_once "lib/problems.inc";
require_once dirname(__FILE__) . "/Judge.inc";


class SafeException extends Exception {
};
    

require_once "scorers/OpcScorer.php";
require_once "scorers/IoiScorer.php";
require_once "lib/logger.inc";

class SubmissionProcessor 
{	
	/**
	 * if true, do not update the database or result files once the
	 * submission is processed.
	 */
	private $dry_run = false;

	public function setDryRun ($_dry_run)
	{
		$this->dry_run = $_dry_run;
	}

	public function setState ($state)
	{
		if ($this->dry_run) {
			Logger::get_logger()->info ("dry run: state $state");
			return;
		}

		assert (!empty($this->sub));
		SubmissionTable::set_state ($this->sub->id, $state);
	}

	public function setScore ($score)
	{
		if ($this->dry_run) {
			Logger::get_logger()->info ("dry run: score $score");
			return;
		}

		assert (!empty($this->sub));
		SubmissionTable::set_score ($this->sub->id, $score);
	}

	public function setResultXml ($xml)
	{
		if ($this->dry_run) {
			Logger::get_logger ()->debug ("dry run: $xml");
			return;
		}

		$res = get_file_name ("data/results/{$this->sub->id}.xml") ;
		
		if (!file_put_contents($res, $xml, LOCK_EX )) {
			// No write permissions perhaps?
			Logger::get_logger()->log ("Unable to write to $res!", Zend_Log::ALERT);
			assert (false);
			exit(1);
		}
		chmod($res,0644);
	}

	function runcase ($input, $output, $score, $j /* Judge */, $scorer)
	{
		$logger = Logger::get_logger ();
		$logger->log ("Running on $input", Zend_Log::DEBUG);
		
		$ret = $j->run ($input,
				$this->prob->getResourceLimitsArray(), 
				$response);
	
		if (!$ret)
			return $scorer->processCaseRun ($response);
		else if (!$scorer->processCaseRun (NULL))
			assert (false); /* seriously? */
		
		$logger->log ("Verifying with $output", Zend_Log::DEBUG);
		$ret = $j->verify($output);

		$logger->log ("Verification complete", Zend_Log::DEBUG);

		if ($this->prob->doesCheckerOutputScore)
			return $scorer->processCaseVerify ($j->checker_score, 
							     $ret);
		else return $scorer->processCaseVerify ($score, $ret);
	}		
			
	function process ($sub_id, $debug = NULL)
	{
		$this->sub = SubmissionTable::get_submission($sub_id) ;
		assert (!empty ($this->sub));
		
		$this->prob = ProblemTable::get_problem($this->sub->problemid );
		
		assert (is_array($this->prob->scoreweights));
		assert (is_array($this->prob->inputpaths)); 
		assert (is_array($this->prob->outputpaths));
		assert (is_file($this->sub->pathtocode));

		if (empty($this->prob->checker))
			$j = new Judge ($this->sub->pathtocode,
					$this->sub->lang);
		else 
			$j = new Judge($this->sub->pathtocode, 
				       $this->sub->lang, $this->prob->checker);
		
		if ($this->prob->grading_style == "opc")
			$scorer = new OpcScorer ();
		else
			$scorer = new IoiScorer ();
		
		
		$this->setState ($this->sub->id, "Compiling");
		$compileOptions = "";
		if (!empty ($this->prob->compileOptions[$this->sub->lang])) 
			$compileOptions = $this->prob->compileOptions[$this->sub->lang];
		if (!$j->compile($compileOptions)) {
			$this->setState ("Compile Error") ;
			$this->setScore (0);
		}
		else {
			for ($i = 0 ; $i < $this->prob->numcases; $i++){
				SubmissionTable::set_state($this->sub->id, "Running($i)");
				$j ->start_test_case("" . $i , "Test Case #$i for " .
						     $this->prob->scoreweights[$i] . " points" );
				if (!$this->runcase (
					    $this->prob->inputpaths[$i],
					    $this->prob->outputpaths[$i],
					    $this->prob->scoreweights[$i],
					    $j, $scorer)) {
					$j->end_test_case ();
					break;
				} 
				$j->end_test_case ();
			}
			$this->setScore ($scorer->getScore());
			$this->setState ($scorer->getResponse());

		}
		$xml = $j->end();
		$this->setResultXml ($xml->flush());
	}
}
