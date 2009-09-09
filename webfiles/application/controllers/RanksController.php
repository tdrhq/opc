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
 * @file   RanksController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

class RanksController extends Zend_Controller_Action { 
	public function indexAction() { 
		if (webconfig::getContest()->isQueuePrivate()) {
			$user = User::factory(Zend_Auth::getInstance()->getIdentity());
			if (!$user || !$user->isAdmin())
				$this->_forward("privacy", "error", NULL, array());
		}
		$user = $this->_request->get("user") ;
		$prob = $this->_request->get("prob") ;
		$this->view->user = $user; 
		$this->view->prob = $prob ;
		Zend_Loader::loadClass("RanklistModel") ;
		$ranklist = new RanklistModel ; 
		$this->view->ranks = $ranklist->getRanks($user, $prob, webconfig::getContestId());
	}

	public function userAction() { 
		$user = $this->_request->get("user") ;
		$prob = $this->_request->get("prob") ;
		$this->view->user = $user; 
		$this->view->prob = $prob; 
		$this->view->ranks = $this->cache->call("mygetRanks", 
							 array($this, $user, $prob) );
	}

}


