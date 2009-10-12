<?php
/**
 * Copyright 2007-2009 Chennai Mathematical Institute
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file   AuthController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "lib/logger.inc";

class AuthController  extends Zend_Controller_Action
{
	function init()
	{
		$this->log = Logger::get_logger ();
	}
	function indexAction()
	{
		$this->_redirect(webconfig::getContestRelativeBaseUrl());
	}

	function loginAction() {
		$this->view->login_redirect = webconfig::getContestRelativeBaseUrl();
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
		$this->_redirect (webconfig::getContestRelativeBaseUrl()) ;
	}
}
