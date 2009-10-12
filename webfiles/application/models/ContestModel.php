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
 * @file   ContestModel.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */
require_once "lib/contest.inc";

class ContestModel  
{
	public function getRow($id) 
	{
		$contest = Contest::factory($id) ;
		return $contest ;
	}

	public function findAll() {
		$ret = array() ;
		if ($handle = opendir(config::getFilename("data/contests/"))) {
			while (false !== ($file = readdir($handle))) {
				$info = pathinfo($file) ;
				if ( !isset($info['extension']) || $info['extension'] != 'xml') continue;
				
				$name = $info['filename'] ;
				
				$contest = Contest::factory($name); 
				if ($contest) array_push($ret, $contest);
			}
			closedir($handle);
		} 
		return $ret ;
	}

	public function getContestState($contestname) 
	{
		$contest = Contest::factory((string)$contestname);
		if ( empty($contest) ) return "before" ;

		$start = $contest->getContestTime() ;

		$end = $contest->getContestEndTime(); 

		$current = time() ;
		if ( $current < $start ) return "before" ;
		if ( $current < $end ) return "ongoing" ;
		return "after" ;
	}
}
