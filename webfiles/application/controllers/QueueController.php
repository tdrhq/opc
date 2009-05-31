<?
require_once "lib/submissions.inc" ;
require_once "lib/user.inc";
class QueueController extends Zend_Controller_Action { 
	public function indexAction() { 

		$offset = (int) $this->_request->get("offset") ;
		$limit = (int) $this->_request->get("limit") ;
		if ( empty($offset) ) $offset = 0 ; 
		if ( empty($limit) ) $limit = 100 ;

		$user = $this->_request->get("user") ;

		if ( webconfig::$enable_queue_privacy ) { 
			$auth = Zend_Auth::getInstance();
			if (!$auth->hasIdentity())
				$this->_forward("login", "error", NULL, array());
			else { 
				$userobj = User::factory($auth->getIdentity());
				if (!$userobj->isAdmin()) 
					$user = $auth->getIdentity();
			}
		}
		$this->view->queue_status = SubmissionTable::get_queue(
			$offset, $limit, $user, webconfig::$contest_id);
		$this->view->queue_size = SubmissionTable::get_count($user) ;
		$this->view->offset = $offset; 
		$this->view->limit = $limit ;
	}
	public function mineAction() {
		$this->_forward("index", "queue", NULL, array("user" => 
							      Zend_Auth::getInstance()->getIdentity()));
	}
}
