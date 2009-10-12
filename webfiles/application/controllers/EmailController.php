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
 * @file   EmailController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

class EmailController extends Zend_Controller_Action 
{
	public function indexAction() 
	{
		$auth = Zend_Auth::getInstance() ; 
		if ( !$auth->hasIdentity() ) throw new Exception("Please login");
		$user = User::factory($auth->getIdentity());
		if (!$user->isAdmin()) throw new Exception("You must be an admin to view this page.");
		
		$this->_helper->viewRenderer->setNoRender();
		Zend_Loader::loadClass("UserModel");
		$usermodel = new UserModel();
		$users = $usermodel->getUserList() ;
		foreach ($users as $username) {
			$user = User::factory($username) ;
			$memcount = $usermodel->getMemberCount($user);
			for($i = 0; $i < $memcount; $i++) {
				$member = $usermodel->getMember($user, $i);
				if (!empty($member->email) )
					echo "{$member->email}, ";
			}
		}
	}
}
