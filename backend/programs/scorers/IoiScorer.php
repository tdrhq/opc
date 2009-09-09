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
 * @file   IoiScorer.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once dirname(__FILE__) . "/AbstractScorer.php";

class IoiScorer extends AbstractScorer
{
	private $response;
	private $score = 0;

	public function processCaseRun ($response)
	{
		if ($this->response != NULL) 
			return true; /* not interested in any more messages */
		$this->response = $this->formatRTE ($response);
		return true;
	}
		
	public function processCaseVerify ($score, $checker_response)
	{
		if ($checker_response) {
			$this->score += $score;
			return true;
		}

		if ($this->response != NULL) return true;
		else $this->response = "Wrong Answer";
		return true;
	}

	public function getScore ()
	{
		return $this->score;
	}

	public function getResponse ()
	{
		if ($this->response == NULL) return "Accepted";
		return $this->response;
	}
}