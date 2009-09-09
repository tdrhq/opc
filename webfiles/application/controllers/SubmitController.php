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
 * @file   SubmitController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "lib/problems.inc" ;
require_once "lib/upload.inc" ;

class SubmitController extends Zend_Controller_Action { 
	public function preDispatch() 
	{
		$this->contestmodel = new ContestModel; 
		$this->state  = $this->contestmodel->getContestState(webconfig::getContestId());
		if ( $this->state != "ongoing" ){
			$user = User::factory(Zend_Auth::getInstance()->getIdentity());
			if (!$user || !$user->isAdmin())  
				$this->_forward("{$this->state}", "error", NULL, array()); 
		}
	}
	public function init() 
	{
		Zend_Loader::loadClass("ContestModel") ;
	}
	public function indexAction () { 
		$this->view->title = "Submit a solution" ;
		$this->view->problems = ProblemTable::get_problem_list(webconfig::getContestId(), 0, 100) ;
		$this->view->problem_code = $this->_request->get("probid") ;
	}

	public function uploadAction() { 

		if ( !$this->_request->isPost()) 
			$this->_redirect(webconfig::getContestRelativeBaseUrl()) ;
		
		$auth = Zend_Auth::getInstance() ; 
		if ( !$auth->hasIdentity()) $this->_redirect(webconfig::getContestRelativeBaseUrl() . "auth/login") ;
		
		$lang = $this->_request->get("lang") ;
		$prob = $this->_request->get("probid") ;
		$source = $_FILES['source']['tmp_name'] ; 

		if ( empty($lang) or empty($prob) or empty($source) ) { 
			$this->_redirect(webconfig::getContestRelativeBaseUrl() . "/error/illegal") ;
			return ;
		}
		
		$id = UploadSubmission::upload($auth->getIdentity(), 
					 $prob, 
					 $lang,
					 $source,
					 ProblemTable::get_problem($prob)->owner) ;
		
		if ( $id == -1 ) { 
			$this->view->message = "You are trying to submit the same "
				. "solution twice!" ; 
		} else if ( $id == -2 ) { 
			$this->view->message = "You have exceeded the submission "
				. "limit on this problem." ;
		} else if ( $id < 0 ) { 
			$this->view->message = "Unknown error" ; 
		} else { 
			$this->_redirect(webconfig::getContestRelativeBaseUrl () . "submit/success/$id") ;
		}

		throw new Exception("shouldn't be here\n");
	}

	public function successAction () { 
		$this->view->id = $this->_request->get("id") ; 
	}
}
