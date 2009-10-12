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
 * @file   ContestsController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

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
