<?

class IndexController extends Zend_Controller_Action { 
	function indexAction () {
		$this->_redirect("/pages/home");
	}
	
	function loginAction($username, $password) { 
	}

}
