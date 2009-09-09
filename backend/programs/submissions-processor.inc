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

class SubmissionProcessor 
{
	function runcase ($input, $output, $score, $j /* Judge */, $scorer)
	{
		if (defined("DEBUG")) 
			echo "Running on $input\n";
		
		$ret = $j->run ($input,
				$this->prob->getResourceLimitString(), 
				$response);
	
		if (!$ret)
			return $scorer->processCaseRun ($response);
		else if (!$scorer->processCaseRun (NULL))
			assert (false); /* seriously? */
		
		if (defined('DEBUG')) echo "Verifying\n";
		$ret = $j->verify($output);

		if (defined('DEBUG')) echo "Verified\n";
		if ($this->prob->doesCheckerOutputScore)
			return $scorer->processCaseVerify ($j->checker_score, 
							     $ret);
		else return $scorer->processCaseVerify ($score, $ret);
	}		
			
	function process ($sub_id, $debug = NULL)
	{
		$this->sub = SubmissionTable::get_submission($sub_id) ;
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
				       $this->sub->lang, $this->sub->checker);
		
		if ($this->prob->grading_style == "opc")
			$scorer = new OpcScorer ();
		else
			$scorer = new IoiScorer ();
		
		
		SubmissionTable::set_state($this->sub->id, "Compiling");
		$compileOptions = "";
		if (!empty ($this->prob->compileOptions[$this->sub->lang])) 
			$compileOptions = $this->prob->compileOptions[$this->sub->lang];
		if (!$j->compile($compileOptions)) {
			SubmissionTable::set_state($this->sub->id, "Compile Error") ;
			SubmissionTable::set_score ($this->sub->id, 0);
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
			SubmissionTable::set_score($this->sub->id, $scorer->getScore());
			SubmissionTable::set_state($this->sub->id, $scorer->getResponse());

		}
		
		$res = get_file_name ("data/results/{$this->sub->id}.xml") ;
		$xml = $j -> end();
		
		if (!file_put_contents($res, $xml->flush(), LOCK_EX )) {
			// No write permissions perhaps?
			echo "Unable to write to XML results file\n";
			assert (false);
			exit(1);
		}
		chmod($res,0644);
	}
}
