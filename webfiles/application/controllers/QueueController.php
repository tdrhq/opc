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
 * @file   QueueController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */
require_once "lib/submissions.inc" ;
require_once "lib/user.inc";
class QueueController extends Zend_Controller_Action { 
	public function indexAction() { 

		$offset = (int) $this->_request->get("offset") ;
		$limit = (int) $this->_request->get("limit") ;
		if ( empty($offset) ) $offset = 0 ; 
		if ( empty($limit) ) $limit = 100 ;

		$user = $this->_request->get("user");
		$uid = $this->_request->get ("uid");

		if (webconfig::getContest()->isQueuePrivate()) { 
			$auth = Zend_Auth::getInstance();
			if (!$auth->hasIdentity())
				$this->_forward("login", "error", NULL, array());
			else { 
				$userobj = User::factory($auth->getIdentity());
				if (!$userobj->isAdmin()) 
					$user = $auth->getIdentity();
			}
		}

		Zend_Loader::loadClass ("Zend_Paginator");
		Zend_Loader::loadClass ("Zend_Paginator_Adapter_DbSelect");
		$db = contestDB::get_zend_db();
		$query = $db->select ()->from('submissionqueue')->join("users", "submissionqueue.uid = users.uid")->where ("owner = ?", webconfig::getContestId())->order("id desc");
		if (!empty($user)) $query = $query->where ("users.username = ?", $user);
		if (!empty($uid)) $query = $query->where ("users.uid = ?", $uid);

		$adapter = new Zend_Paginator_Adapter_DbSelect ($query);
		$this->view->paginator = new Zend_Paginator ($adapter);

		$this->view->paginator->setCurrentPageNumber ($this->_getParam ('page'));
		$this->view->paginator->setItemCountPerPage (50);
	}
	public function mineAction() {
		$this->_forward("index", "queue", NULL, array("uid" => 
							      Zend_Auth::getInstance()->getIdentity()));
	}
}
