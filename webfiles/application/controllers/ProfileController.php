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
 * @file   ProfileController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "lib/user.inc";
require_once "lib/logger.inc";

class ProfileController extends Zend_Controller_Action { 
	var $user = "" ; 
	var $password = "" ; 
	var $members = array() ;
	var $membercount = 1 ; 
	public function indexAction() { 
		/* fill in with the current user details etc. */
	}

	/**
	 * Get the information from the POSTDATA and validate to see
	 * if all is good. 
	 */
	public function validate() { 
		$this->log->info("validating...\n");
		$this->user = $this->_request->get("user") ;
		$this->password = $this->_request->get("password") ;
		$this->confirm = $this->_request->get("confirm") ;
		$this->name0 = $this->_request->get("name0") ;
		$this->email0 = $this->_request->get("email0") ;
		$this->institute = $this->_request->get("institute") ;
		$this->country = $this->_request->get("Country");
		$this->timezone = $this->_request->get("timezone");

		if ( webconfig::$team_size > 1 ) {
			$this->name1 = $this->_request->get("name1");
			$this->email1 = $this->_request->get("email1");
		} else { 
			$this->name1 = "" ;
			$this->email1 = "" ; 
		}

		if ( webconfig::$team_size > 2 ) {
			$this->name2 = $this->_request->get("name2");
			$this->email2 = $this->_request->get("email2");
		} else { 
			$this->name2 = "" ;
			$this->email2 = "" ;
		}

		$auth = Zend_Auth::getInstance() ; 
		if ( $auth->hasIdentity()) { 
			if ( $this->user != $auth->getIdentity()) {
				$this->_forward("illegal", "error") ;
				return ;
			}
		} else { 
			/* this user should not exist */
			$_uu = User::factory($this->user);
			if ( isset( $_uu) ){
				throw new Exception("Username is in use.");
			}
		}


		$email_pattern = "/^[a-zA-Z0-9\+_\.]+@[a-zA-Z0-9_\-]+\.[a-zA-Z0-9_\-\.]+$/";
		$name_pattern = "/^[ _a-zA-Z0-9\.]*$/";
		$user_pattern= "/^[a-zA-Z0-9\_]+$/";

		if ( !preg_match($user_pattern, $this->user) ) {
			throw new Exception("Username not valid.\n") ;
		}

		if (empty($this->name0) ) { 
			throw new Exception("Please provide a name") ;
		}
		if ( !preg_match($name_pattern, $this->name0) ){ 
			throw new Exception("Name not valid.\n") ;
		}

		if ( empty( $this->email0) ) 
			throw new Exception("Please provide an email address.");

		if ( !empty($this->name1) && empty($this->email1) ) 
			throw new Exception("Please provide an email address for second member.");

		if ( !empty($this->name2) && empty($this->email2)) 
			throw new Exception("Please provide an email address for third member.");
			
		if ( !preg_match($email_pattern, $this->email0) ) { 
			throw new Exception("Email not valid.\n") ;	
		}

		if ( !empty($this->email1) && !preg_match($email_pattern, $this->email1) ) {
			throw new Exception("Second email not valid\n");
		}

		if ( !empty($this->email2) && !preg_match($email_pattern, $this->email2) ){
			throw new Exceptino("Third email not valid\n") ;
		}


		/* password sanity check */
		if ( empty($this->password) ) { 
			throw new Exception("Password cannot be empty");
		}
		if ( $this->password != $this->confirm) { 
			throw new Exception("Confirmed password does not match.");
		}
	}

	public function copyToView() { 
		$this->view->user = $this->user ; 
		$this->view->name0 = $this->name0 ; 
		$this->view->email0 = $this->email0 ; 
		$this->view->name1 = $this->name1 ; 
		$this->view->email1 = $this->email1 ; 
		$this->view->name2 = $this->name2 ;
		$this->view->email2 = $this->email2 ;
		$this->view->error_message = $this->error_message ; 
		$this->view->log = $this->log; 
		$this->view->institute = $this->institute ;
		$this->view->country = $this->country;
		$this->view->timezone = $this->timezone;
	}

	public function saveXML() { 
		Zend_Loader::loadClass("UserModel") ;
		
		$userm = new UserModel() ;

		$user = $userm->getRow($this->user) ;
		
		if ( $userm->getMemberCount($user) == 0 ) 
			$userm->addMember($user) ;

		$mem = $userm->getMember($user, 0) ;
		$mem->name = $this->name0 ; 
		$mem->email = $this->email0 ;

		$this->log->info("$mem->name; $mem->email") ;

		$userm->setMember($user, 0, $mem);

		if ( webconfig::$team_size > 1) { 
			$mem = $userm->getMember($user, 1);
			$mem->name = $this->name1 ; 
			$mem->email = $this->email1 ;
			$userm->setMember($user, 1, $mem) ;
		}

		if ( webconfig::$team_size > 2 ) {
			$mem = $userm->getMember($user, 2);
			$mem->name = $this->name2; 
			$mem->email = $this->email2; 
			$userm->setMember($user, 2, $mem) ;
		}

		$userm->setInstitute($user, $this->institute) ;
		$userm->setCountry($user, $this->country) ;
		$userm->setTimezone($user, $this->timezone) ;
		$this->log->info("Profile is seemingly saved");
	}

	public function init() { 
		$this->user = "" ; 
		$this->password = "" ; 
		$this->confirm = "" ; 
		$this->name0 = "" ; 
		$this->email0 = "" ; 
		$this->name1 = "" ;
		$this->email1 = "" ;
		$this->name2 = "" ; 
		$this->email2 = "" ;
		$this->error_message = "" ;
		$this->institute = "" ;
		$this->country = "" ; 
		$this->timezone = "Asia/Calcutta"; 
		$this->log = Logger::get_logger ();
	}

	public function postDispatch() { 
		$this->copyToView() ;
	}
	public function registerAction() { 
		if (!webconfig::$allow_register)
			$this->_forward("regnotavailable", "error", NULL, array());
		$this->view->mode = "register" ;
		$this->copyToView() ; 
	}
	public function register2Action() { 
                if (!webconfig::$allow_register)
                        $this->_forward("regnotavailable", "error", NULL, array());
	
		$this->view->mode = "register" ; 
		
		$this->log->info("Registration in progress") ;
		try { 
			$this->validate() ;
		} catch ( Exception $e ) { 
			$this->error_message = $e->getMessage() ; 
			$this->log->err("Registration Exception: $this->error_message");
			return;
		}

		$this->log->info("Validated.. seems fine to continue.");
		$u = User::factory($this->user) ;
		if ( !empty($u) ) { 
			$this->error_message = "This username is already in ".
			"use" ;
			$this->log->info("Username in use: {$this->user}");
			return; 
		}

		if ( !User::create($this->user, $this->password) ){ 
			$this->error_message = "Unknown error has occured. " .
				"Can you please contact us with these details?";
			$this->log->alert("$this->error_message") ;
			return ;
		}

		$u = User::factory($this->user) ;
		assert (!empty($u) ) ; 

		
		$this->saveXML () ;

		$this->copyToView() ;
		$this->_redirect(webconfig::getContestRelativeBaseUrl () . "/profile/success/user/{$this->user}") ;
	}

	public function successAction() { 
		$this->view->username = $this->_request->get("user") ;
	}

	public function updateAction() { 
		$this->view->mode = "update" ; 
		
		/* fill in the existing details */
		$auth = Zend_Auth::getInstance() ;
		if (!$auth->hasIdentity()) { 
			$this->_forward ("illegal", "error") ;
			return ;
		}
		$this->user = $auth->getIdentity() ;
		Zend_Loader::loadClass("UserModel") ;
		$userm = new UserModel ; 

		$user = $userm->getRow($this->user) ;
		$this->user = $user->_username;
		$this->institute = $userm->getInstitute($user) ; 
		$this->country = $userm->getCountry($user) ;
		$this->timezone = $userm->getTimezone($user) ;
		$this->name0 = $userm->getMember($user, 0)->name ; 
		$this->email0 = $userm->getMember($user, 0)->email; 

		$count = $userm->getMemberCount($user) ; 

		if ( $count > 1 ) { 
			$this->name1 = $userm->getMember($user, 1)->name;
			$this->email1 = $userm->getMember($user,1)->email ;
		} 
		
		if ( $count > 2 ) {
			$this->name2 = $userm->getMember($user, 2)->name;
			$this->email2 = $userm->getMember($user, 2)->email ;
		}
		$this->copyToView() ;
	}

	public function update2Action() { 
		$this->view->mode = "update" ;

		try { 
			$this->validate() ; 
		} catch (Exception $e ) { 
			$this->error_message = $e->getMessage() ; 
			$this->copyToView() ;
			return ;
		}

		$this->saveXML() ;
		
		$this->copyToView() ;
		User::factory($this->user)->setPassword($this->password);
		$this->_redirect(webconfig::getContestRelativeBaseUrl () . "/profile/profile-update-success");
	}
	public function profileUpdateSuccessAction() 
	{
	}
}
