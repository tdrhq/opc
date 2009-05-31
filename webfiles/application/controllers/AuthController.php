<?php

class AuthController  extends Zend_Controller_Action
{
	function init()
	{
		Zend_Loader::loadClass("Zend_Log_Writer_Stream") ;
		Zend_Loader::loadClass("Zend_Log");
		$this->mock = new Zend_Log_Writer_Stream ("/tmp/opc-login-log") ;
		$this->log =& new Zend_Log($this->mock) ;

	}
	function indexAction()
	{
		$this->_redirect('/');
	}

	function loginAction() {
		$this->view->login_redirect = "/" ;
		$this->view->message = "" ; 
		if ($this->_request->isPost()) {
			// collect the data from the user
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$username = $f->filter($this->_request->getPost('username'));
			$password = $f->filter($this->_request->getPost('password'));
			if (empty($username)) {
				$this->view->login_message = 'Please provide a username.';
			} else {
				$authAdapter = new MyAuthAdapter($username, 
								 $password); 

				Zend_Loader::loadClass('Zend_Auth') ;
				$auth = Zend_Auth::getInstance();
				$result = $auth->authenticate($authAdapter);
				if ($result->isValid()) {
					$this->log->info($auth->getIdentity() . 
							 " has logged on.");

					$this->_redirect($this->_request->get("redirectto"));
				} else {
					$this->view->login_message = 'Login failed.';
					
				}
			}
		}
		
		$this->view->title = "Log In" ;
		
	}

	function logoutAction() { 
		$this->log->info(Zend_Auth::getInstance()->getIdentity() 
				 . " has logged off."); 
		Zend_Auth::getInstance()->clearIdentity() ;
		$this->_redirect ("/") ;
	}
}
