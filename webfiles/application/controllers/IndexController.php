<?

class IndexController extends Zend_Controller_Action { 
	function indexAction () {
		$this->_redirect(webconfig::getContestRelativeBaseUrl() . "/pages/home");
	}
	
	function loginAction($username, $password) { 
	}

}
