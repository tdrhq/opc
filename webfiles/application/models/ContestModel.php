<?
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
