<? 
require_once "lib/user.inc";
class UsersController extends Zend_Controller_Action { 
	public function indexAction()  { 
		$_user = $this->_request->get("user") ;
		if (webconfig::getContest()->isQueuePrivate()) {
			$user = User::factory(Zend_Auth::getInstance()->getIdentity());
			if (!$user || !$user->isAdmin())
				$this->_forward("privacy", "error", NULL, array());	
		}
		
		if ( empty($_user) ) {
			$this->_redirect("/") ;
		}
		/* fillin information from User XML data */
		Zend_Loader::loadClass("UserModel") ;
		$userm = new UserModel ; 
		$user = $userm->getRow($_user) ;

		$this->view->username = $user->_username;
		$this->view->inst = $userm->getInstitute($user) ;
		$this->view->name = $userm->getMember($user, 0)->name ;
	}

}
