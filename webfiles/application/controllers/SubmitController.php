<?

require_once "lib/problems.inc" ;
require_once "lib/upload.inc" ;

class SubmitController extends Zend_Controller_Action { 
	public function preDispatch() 
	{
		$this->contestmodel = new ContestModel; 
		$this->state  = $this->contestmodel->getContestState(webconfig::getContestId());
		if ( $this->state != "ongoing" ){
			$user = User::factory(Zend_Auth::getInstance()->getIdentity());
			if (!$user || !$user->isAdmin())  
				$this->_forward("{$this->state}", "error", NULL, array()); 
		}
	}
	public function init() 
	{
		Zend_Loader::loadClass("ContestModel") ;
	}
	public function indexAction () { 
		$this->view->title = "Submit a solution" ;
		$this->view->problems = ProblemTable::get_problem_list(webconfig::getContestId(), 0, 100) ;
		$this->view->problem_code = $this->_request->get("probid") ;
	}

	public function uploadAction() { 

		if ( !$this->_request->isPost()) 
			$this->_redirect("/") ;
		
		$auth = Zend_Auth::getInstance() ; 
		if ( !$auth->hasIdentity()) $this->_redirect("auth/login") ;
		
		$lang = $this->_request->get("lang") ;
		$prob = $this->_request->get("probid") ;
		$source = $_FILES['source']['tmp_name'] ; 

		if ( empty($lang) or empty($prob) or empty($source) ) { 
			$this->_redirect("/error/illegal") ;
			return ;
		}
		
		$id = UploadSubmission::upload($auth->getIdentity(), 
					 $prob, 
					 $lang,
					 $source,
					 ProblemTable::get_problem($prob)->owner) ;
		
		if ( $id == -1 ) { 
			$this->view->message = "You are trying to submit the same "
				. "solution twice!" ; 
		} else if ( $id == -2 ) { 
			$this->view->message = "You have exceeded the submission "
				. "limit on this problem." ;
		} else if ( $id < 0 ) { 
			$this->view->message = "Unknown error" ; 
		} else { 
			$this->_redirect("/submit/success/$id") ;
		}

		throw new Exception("shouldn't be here\n");
	}

	public function successAction () { 
		$this->view->id = $this->_request->get("id") ; 
	}
}
