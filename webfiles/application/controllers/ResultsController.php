<?

require_once "lib/submissions.inc" ;

class ResultsController extends Zend_Controller_Action { 
	public function indexAction() { 
		if (!Zend_Auth::getInstance()->hasIdentity()){
			$this->_forward("login", "error", null, array());
			return;
		}
		$this->view->id = (int) $this->getRequest()->get("id") ;
		$download = $this->getRequest()->get("download") ;
		$this->view->sub = SubmissionTable::get_submission(
			$this->view->id) ;
		$this->view->user = Zend_Auth::getInstance()->getIdentity() ; 
		$this->view->admin = User::factory($this->view->user)
			->isAdmin() ;

		if ( empty($this->view->sub) or $this->view->user != 
		     $this->view->sub->team 
		     and !$this->view->admin  ) { 
			$this->_redirect("error/illegal") ;
		}

		if ($download == "true") { 
			$this->_helper->layout->disableLayout();
			$this->view->download = true ;  
			$this->_response->setHeader("Content-Type", "text/src") ;
		}
		else $this->view->download = false ;

		
	}
}
