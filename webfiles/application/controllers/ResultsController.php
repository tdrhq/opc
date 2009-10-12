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
 * @file   ResultsController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "lib/submissions.inc" ;

class ResultsController extends Zend_Controller_Action { 
	public function indexAction() { 
		if (!Zend_Auth::getInstance()->hasIdentity()){
			$this->_forward("login", "error", null, array());
			return;
		}
		$this->view->id = (int) $this->getRequest()->get("id") ;
		$download = $this->getRequest()->get("download") ;
		$this->view->sub = SubmissionTable::get_submission(
			$this->view->id) ;

		if (empty ($this->view->sub)) {
			$this->_forward ("404", "error");
			return;
		}

		$this->view->user = Zend_Auth::getInstance()->getIdentity() ; 
		$this->view->admin = User::factory($this->view->user)
			->isAdmin() ;

		if ( empty($this->view->sub) or $this->view->user != 
		     $this->view->sub->uid 
		     and !$this->view->admin  ) { 
			$this->_forward("illegal", "error") ;
			return;
		}

		if ($download == "true") { 
			$this->_helper->layout->disableLayout();
			$this->view->download = true ;  
			$this->_response->setHeader("Content-Type", "text/src") ;
		}
		else $this->view->download = false ;

		
	}
}
