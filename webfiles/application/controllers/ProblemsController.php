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
                $this->state  = $this->contestmodel->getContestState(webconfig::getContestId());
                if ( $this->state == "before" )
                        $this->_forward("before", "error", NULL, array());
        }

	function indexAction () {
		/* build query */
		$db = contestDB::get_zend_db();
		$this->view->title = "Problems" ;

                $curuser = Zend_Auth::getInstance()->getIdentity();
                if (empty($curuser)) {
			$this->view->problems = ProblemTable::get_problem_list(webconfig::getContestId(), 0, 100);
			return;
                }

		/* case 2: build a complex query */
		$query = "select distinct p.rowid as rowid,p.id as id,p.nickname as nickname,s.state as state from problemdata  as p left join (select * from submissionqueue where uid = ? and state='Accepted') as s on p.id = s.problemid where p.owner=? group by p.rowid,p.id,p.nickname,s.state order by rowid;";
		
		$this->view->problems = $db->fetchAll ($query, array($curuser, webconfig::getContestId()));
	}
	
	function viewAction () { 
		$this->view->problem_code = $this->_request->get("probid") ;
		$prob = ProblemTable::get_problem ("{$this->view->problem_code}") ;

		if (empty($prob) || $prob->owner != webconfig::getContestId()) { 
			$this->_redirect("/");
		}

		$this->view->prob = $prob; 

		$this->view->content_html = file_get_contents(get_file_name("data/problems/"  
							  . $this->_request->get("probid")
									    . ".html")) ;


		if (function_exists("tidy_parse_string") && $this->_request->get("tidy") != "false") {
			/* tidy to XHTML strict */
			$opt = array("output-xhtml" => true,
				     "add-xml-decl" => true,
				     "clean" => true,
				     "doctype" => "strict",
				     "show-body-only" => true);
			$this->view->content_html = tidy_parse_string($this->view->content_html, $opt);
			tidy_clean_repair ($this->view->content_html);
		}
		if ($this->_request->get("plain") == "true") {
			$this->_helper->layout->disableLayout ();
			$this->_helper->viewRenderer->setNoRender ();
			$this->getResponse()->setBody ($this->view->content_html);
		}
	}
	

}
