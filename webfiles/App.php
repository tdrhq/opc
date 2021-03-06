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
 * @file   bootstrap.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */


error_reporting(E_ALL|E_STRICT);
set_include_path(dirname (__FILE__) . PATH_SEPARATOR . dirname (__FILE__) . '/application/models/'
		 . PATH_SEPARATOR . get_include_path());
require_once (dirname (__FILE__) . "/config.inc"); 
				
date_default_timezone_set (webconfig::$default_timezone);


require_once "Zend/Loader.php";
Zend_Loader::loadClass('Zend_Controller_Front');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Auth_Adapter_Interface') ;
Zend_Loader::loadClass('Zend_Layout') ;
require_once "lib/user.inc" ;

class MyAuthAdapter implements Zend_Auth_Adapter_Interface { 
	var $result ; 
	public function __construct($username, $password) { 
		$user = User::factory($username) ;

		if ( !empty($user) && 
		     $user->matchPassword($password) ) { 
			$this->result = new Zend_Auth_Result(
				Zend_Auth_Result::SUCCESS, $user->uid); 
			return ; 
		}
		$this->result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE,
						  "", array() ) ; 
	}

	public function authenticate() { 
		return $this->result ; 
	}

}

class App {

	public function bootstrap () 
	{
		Zend_Layout::startMvc(array('layoutPath' => dirname (__FILE__) . '/application/views/layouts'));
                
		/* setup auth session */
		$auth = Zend_Auth::getInstance() ;
		Zend_Loader::loadClass('Zend_Auth_Storage_Session');
		$auth->setStorage(new Zend_Auth_Storage_Session(webconfig::$session_namespace));
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			Zend_Loader::loadClass ('UserModel');
			$users = new UserModel ();
			$row = $users->getRow(Zend_Auth::getInstance()->getIdentity());
			$tz = $users->getTimezone($row);
			if (!empty($tz)) {
				date_default_timezone_set ($tz);
			} else {
				date_default_timezone_set (webconfig::$default_timezone);
			}
		}
		
/* decide which contest I'm running on */
		Zend_Loader::loadClass('Zend_Session_Namespace');
		$session = new Zend_Session_Namespace(webconfig::$session_namespace);
		if ( isset($session->contestid) && webconfig::$multi_contest ) {
			
			/* just validate to see if I'm allowed on this contest */
			require_once "lib/contest.inc" ;
			$contest = Contest::factory($session->contestid);
			if ( empty($contest) or !$contest->authenticateUser($auth->getIdentity()) ) { 
				$session->contestid = "general" ; 
				$session->contestname = "" ; 
			}
			webconfig::$contest_id = $session->contestid ; 
			webconfig::$contest_name = $contest->getFriendlyName() ;
		}
		
/* setup the controller and routes */
		$frontController = Zend_Controller_Front::getInstance();
		$frontController->setControllerDirectory(dirname (__FILE__) . '/application/controllers');
		
		$router = $frontController->getRouter(); // returns a rewrite router by default
		
		$router->addRoute ('contestroute', new Zend_Controller_Router_Route ('contests/:contestid/:controller/:action/*', array ('controller' => 'index', 'action' => 'index')));


		/* for each of the following routes, we need two versions, with
		 * and without the contests prefix */

		$router->addRoute('problems', new Zend_Controller_Router_Route('problems/:probid', array('controller' => 'problems', 'action' => 'view')));
		$router->addRoute('problemsc', new Zend_Controller_Router_Route('contests/:contestid/problems/:probid', array('controller' => 'problems', 'action' => 'view')));
		
		
		$router->addRoute('submit', new Zend_Controller_Router_Route('submit/success/:id', array('controller' => 'submit', 'action' => 'success')));
		$router->addRoute('submitc', new Zend_Controller_Router_Route('contests/:contestid/submit/success/:id', array('controller' => 'submit', 'action' => 'success')));
		
		
		$router->addRoute('results', new Zend_Controller_Router_Route('results/:id', array('controller' =>'results', 'action' => 'index'))) ;
		$router->addRoute('resultsc', new Zend_Controller_Router_Route('contests/:contestid/results/:id', array('controller' =>'results', 'action' => 'index'))) ;
		
		
		
		$router->addRoute('users', new Zend_Controller_Router_Route('users/:user', array('controller' => 'users', 'action' => 'index')));
		$router->addRoute('usersc', new Zend_Controller_Router_Route('contests/:contestid/users/:user', array('controller' => 'users', 'action' => 'index')));
		
		$router->addRoute('ranks', new Zend_Controller_Router_Route('status/:user', array('controller' => 'ranks', 'action' => 'user')));
		$router->addRoute('ranksc', new Zend_Controller_Router_Route('contests/:contestid/status/:user', array('controller' => 'ranks', 'action' => 'user')));
		

		$router->addRoute('data', new Zend_Controller_Router_Route('problems/:probid/:file', array('controller'=>'problems', 'action'=>'file')));
		$router->addRoute('datac', new Zend_Controller_Router_Route('contests/:contestid/problems/:probid/:file', array('controller'=>'problems', 'action'=>'file')));

		
		$router->addRoute('pages', new Zend_Controller_Router_Route('pages/:page', array('controller' => 'pages')));
		$router->addRoute('pagesc', new Zend_Controller_Router_Route('contests/:contestid/pages/:page', array('controller' => 'pages')));
		
		$router->addRoute('su', new Zend_Controller_Router_Route ('su/:user', array('controller' => 'su')));
		
	} /* function bootstrap */

	/**
	 * Set's up gracefully trapping frontend errors. Do not use this when
	 * running automated tests since then errors can't be automatically
	 * trapped.
	 */
	public function setupErrorHandling ()
	{
		Zend_Loader::loadClass ("Zend_Controller_Plugin_ErrorHandler");
		Zend_Controller_Front::getInstance()->registerPlugin (new Zend_Controller_Plugin_ErrorHandler());
	}

	public function dispatch ()
	{
		Zend_Controller_Front::getInstance()->dispatch();
	}
} /* class App */

