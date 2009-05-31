<?

require_once "lib/contest.inc" ;

class ContestsController extends Zend_Controller_Action 
{
	public function indexAction ()
	{
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) { 
			$this->view->error_message = "You need to login to change the contest."; 
			return; 
		} 

		$contest = $this->_request->get("contest") ;
		$contestM = Contest::factory($contest); 

		if (!$contestM->authenticateUser($auth->getIdentity())) {
			$this->view->error_message = "You have not been granted access to this contest. This might be a restricted contest.";
			return;
		}

		$session = new Zend_Session_Namespace(webconfig::$session_namespace);
		$session->contestid = $contest; 
		$session->contestname = "Custom Contest";

		
		$this->_redirect("/");
	}
}
