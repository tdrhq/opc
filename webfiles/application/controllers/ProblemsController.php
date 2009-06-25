<?

require_once "lib/problems.inc" ;

class ProblemsController extends Zend_Controller_Action { 
	var $content_html = "" ; 
        public function preDispatch()
        {
                $curuser = Zend_Auth::getInstance()->getIdentity();
                if ( !empty($curuser) && User::factory($curuser)->isAdmin()) {
                        return; /* no other tests needed for admin */
                }

		Zend_Loader::loadClass("ContestModel");
                $this->contestmodel = new ContestModel;
                $this->state  = $this->contestmodel->getContestState(webconfig::$contest_id);
                if ( $this->state == "before" )
                        $this->_forward("before", "error", NULL, array());
        }

	function indexAction () {
		$this->view->title = "Problems" ;
		$this->view->problems = ProblemTable::get_problem_list(webconfig::$contest_id, 0, 100);
	}
	
	function viewAction () { 
		$this->view->problem_code = $this->_request->get("probid") ;
		$prob = ProblemTable::get_problem ("{$this->view->problem_code}") ;

		if (empty($prob) || $prob->owner != webconfig::$contest_id ) { 
			$this->_redirect("/");
		}

		$this->view->prob = $prob; 

		$this->view->content_html = file_get_contents(get_file_name("data/problems/"  
							  . $this->_request->get("probid")
									    . ".html")) ;

		if ($this->_request->get("plain") == "true") {
			$this->_helper->layout->disableLayout ();
			$this->_helper->viewRenderer->setNoRender ();
			$this->getResponse()->setBody ($this->view->content_html);
		}
	}
	

}
