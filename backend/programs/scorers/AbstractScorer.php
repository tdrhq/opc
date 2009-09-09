<?php
/**
 * Copyright 2009 Chennai Mathematical Institute
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
 * @file   AbstractScorer.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

class AbstractScorer
{
	/**
	 * Called after each test case is run.
	 *
	 * @return true if the Judge should continue processing, false
	 *         otherwise.
	 */
	public function processCaseRun ($response)
	{
		assert (false);
	}
		
	/**
	 * Called after the each test case output has been verified.
	 * (Will not be called for cases where the run failed, and will
	 *  not be called if processCaseRun returned false for this run)
	 *
	 * @param $score The score allocated to this test case, or the
	 *               score returned from the checker.
	 * @param $checker_response true if the judge deemed this as correct,
	 *                       non-zero, otherwise
	 *
	 * @return true if the Judge should continue processing, false
	 *         otherwise.
	 */
	public function processCaseVerify ($score, $checker_response)
	{
		assert (false);
	}

	/**
	 * After all the cases have been processed, or after one of 
	 * processCaseRun or processCaseVerify has returned false, this
	 * function is called to get the final score of the submission.
	 *
	 * @return integer The score of the submission
	 */
	public function getScore ()
	{
		assert (false);
	}

	/**
	 * After all the cases have been processed, or after one of 
	 * processCaseRun or processCaseVerify has returned false, this
	 * function is called to get the final state of the submission.
	 *
	 * @return integer The final Judge response
	 */
	public function getResponse ()
	{
		assert (false);
	}
	
	/**
	 * A reusable function to format the runtime error in a standard
	 * way.
	 *
	 * @param string the response from the judge.
	 * 
	 * @return string the formatted message.
	 */
	protected function formatRTE ($response)
	{
		if ($response == "TLE")
			return "Time Limit Exceeded";
		else return "Runtime Error ($response)";
	}
}