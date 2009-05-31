<?

class Contest 
{
	var $id ;
		
	var $contestTime ; 
		
	var $contestEndTime ;

	var $contestDuration ;
	
	var $isPriv = false;

	var $enable_queue_privacy = false; 
	
	/**
	 * The admin of the contest. May not be used at the moment.
	 */
	var $admin ;
	
	var $_dom ;
	var $_xp ;
	
	/**
	 * A database table indicating the list of registrants for the 
	 * current contest. Keep it NULL if everybody is allowed to 
	 * participate.
	 */
	var $roster ; 

	public function __construct($id)
	{
		$this->_dom = new DOMDocument() ;
		$xmlfile = config::getFilename("data/contests/{$id}.xml");
		if ( !is_file($xmlfile) ) throw new Exception() ;
		$this->_dom->load($xmlfile) ;
		$this->_xp = new DOMXPath($this->_dom) ;

		$this->id = $id ;

		$res = $this->_xp->query("/contest/contestTime"); 
		
		if ($res->length) $this->contestTime 
			= strtotime($res->item(0)->nodeValue);
		else
			$this->contestTime = 0 ;

		$res = $this->_xp->query("/contest/contestEndTime");

		if ($res->length) 
			$this->contestEndTime = strtotime($res->item(0)->nodeValue) ;
		else $this->contestEndTime = 1999999999;

		$res = $this->_xp->query("/contest/private");
		if ($res->length and ($res->item(0)->nodeValue != "no")) {
			$this->isPriv = true;
		} 
		else
			$this->isPriv = false; 


		/* queue-privacy */
		$res = $this->_xp->query("/contest/enable-queue-privacy") ; 
		if ($res->length and ($res->item(0)->nodeValue != "no" 
				      and $res->item(0)->nodeValue != "false") )
			$this->enable_queue_privacy = true; 
		else
			$this->enable_queue_privacy = false; 

		$res = $this->_xp->query("/contest/roster") ;

		if( $res->length) 
			$this->roster = $res->item(0)->nodeValue ;
	}
	public static function factory($id) 
	{
		if ( !preg_match("/^[a-zA-Z0-9]*$/", $id)) return NULL ;
		try {
			return new Contest($id);
		} catch (Exception $e) {
			return NULL ;
		}
	}

	public function getContestTime() 
	{
		return $this->contestTime;
	}

	public function getContestEndTime() 
	{
		return $this->contestEndTime;
	}

	/**
	 * Get the DOMXPath object of the contest XML config file.
	 * @returns DOMXPath
	 */
	public function getXPath() 
	{
		return $this->_xp ;
	}

	/**
	 * Get a public friendly name for the contest.
	 *
	 * @return string
	 */
	public function getFriendlyName()
	{
		$res = $this->getXPath()->query("/contest/name")->item(0) ;
		if (empty($res)) return "" ; 
		return $res->nodeValue ;
	}

	/**
	 * Given a username, check if the user is in the contest roster.
	 * 
	 * @return boolean
	 */
	public function authenticateUser($user) 
	{
		if (empty($this->roster)) return true;
		
		$db = contestDB::get_zend_db() ;
		$res = $db->select()->from($this->roster)->where('teamname = ?',
								 $user)->query()->fetchAll(); 
		return !empty($res);
	}

	/**
	 * Determine if this has private flag on
	 *
	 * @return boolean
	 */
	public function isPrivate() 
	{
		return $this->isPriv; 
	}

	/**
	 * Should submissions for this contest be private?
	 *
	 * @return boolean
	 */
	public function isQueuePrivate() 
	{
		return $this->enable_queue_privacy ;
	}
}


