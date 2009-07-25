<?

class IndexController extends Zend_Controller_Action { 
	function preDispatch ()
	{
		if ($this->_request->get("action") == "index")
			$this->_forward("index", "pages", NULL, array ('page' => 'home'));
	}

	function indexAction () {
		$this->_redirect("pages/home");
	}
	
	function loginAction($username, $password) { 
	}

}
