<?

class RanksController extends Zend_Controller_Action { 
	public function indexAction() { 
		if (webconfig::getContest()->isQueuePrivate()) {
			$user = User::factory(Zend_Auth::getInstance()->getIdentity());
			if (!$user || !$user->isAdmin())
				$this->_forward("privacy", "error", NULL, array());
		}
		$user = $this->_request->get("user") ;
		$prob = $this->_request->get("prob") ;
		$this->view->user = $user; 
		$this->view->prob = $prob ;
		Zend_Loader::loadClass("RanklistModel") ;
		$ranklist = new RanklistModel ; 
		$this->view->ranks = $ranklist->getRanks($user, $prob, webconfig::getContestId());
	}

	public function userAction() { 
		$user = $this->_request->get("user") ;
		$prob = $this->_request->get("prob") ;
		$this->view->user = $user; 
		$this->view->prob = $prob; 
		$this->view->ranks = $this->cache->call("mygetRanks", 
							 array($this, $user, $prob) );
	}

}


