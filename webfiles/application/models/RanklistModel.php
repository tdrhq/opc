<?

function myrankssort($a, $b) 
{ 
	return ( ($a["score"]<$b["score"] or ($a["score"] == $b["score"] 
					      and $a["time"] < $b["time"] )) 
		 ? 1 : -1 ) ;
}

function mygetRanks ($obj, $user, $prob, $owner) 
{ 
	return $obj->getRanksHelper($user, $prob, $owner) ;
}


/**
 * A read-only model to get a ranklist.
 */

class RanklistModel 
{
	public function __construct() 
	{
		Zend_Loader::loadClass("Zend_Cache");
		$this->cache = Zend_Cache::factory('Function', 'File', 
					     array('lifetime' => 60,
						   'automatic_serialization' => true ) );
	}

	public function getRanks($user, $prob, $owner) 
	{
		return $this->cache->call("mygetRanks", array($this, $user, $prob, $owner) );

	}
	public function getRanksHelper($user, $prob, $owner) {
		$db = contestDB::get_zend_db() ; 
		if ( empty($owner) ) $owner = webconfig::$contest_id ; 
		
		$res = $db -> select() -> from(array("s" => "submissionqueue"))
                             ->join(array("t" => "teaminfo"), 's.team = t.teamname')->
					       where ("s.score > 0")->where('t.isadmin is null or t.isadmin = \'false\'') ; 

		if (!empty($user) ) 
			$res->where("team = ?", $user) ;
		
		if ( !empty($prob) ) 
			$res->where("problemid = ?", $prob) ;

		$res->where("owner = ?", $owner) ;
			
		$res = $res->query() ;
		$ret = array() ; 

		$scores = array();
		/* build the score object */ 
					       
		$all = $res->fetchAll() ; 


		
		foreach( $all as $entry) { 
			if ( !array_key_exists($entry->team, $ret) ){ 
				$ret[$entry->team] = array(
							   "team"=>$entry->team
					) ;
			}

			$prob = $entry->problemid; 
			if ( !isset($ret[$entry->team][$prob])) {
				$ret[$entry->team][$prob] = array("score" => 0,
								  "time" =>0);

			}

			if ( $entry->score == $ret[$entry->team][$prob]['score']) { 
				$ret[$entry->team][$prob]['time'] = min(
					$ret[$entry->team][$prob]['time'],
					$entry->time);
			}

			else if ( $entry->score > $ret[$entry->team][$prob]['score']) {
				$ret[$entry->team][$prob]['score'] = 
					$entry->score ; 
				$ret[$entry->team][$prob]['time'] = $entry->time ;
			}
			
		}

		foreach ($ret as &$team) { 
			$score = 0 ; 
			$time = 0 ;
			foreach($team as $problem) { 
				if ( !is_array($problem) ) continue; 
				$score += $problem['score']; 
				//$time = max($problem['time'], $time) ;
				if ( $time < $problem['time']) 
					$time = $problem['time'] ;
			}
			$team['score'] = $score ; 
			$team['time'] = $time ;
		}


		usort($ret, "myrankssort") ;
		return $ret ; 
	}
}
