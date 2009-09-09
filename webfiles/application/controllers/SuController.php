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
 * @file   SuController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

class SuAuthAdapter implements Zend_Auth_Adapter_Interface {
        var $result ;
        public function __construct($username) {
                $user = User::factory($username) ;

                if ( !empty($user) ) {
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


class SuController extends Zend_Controller_Action
{
	public function indexAction () 
	{
		/* make sure I'm an admin */
		$curuser = Zend_Auth::getInstance()->getIdentity();
		if ( empty($curuser) || !User::factory($curuser)->isAdmin()) {
			$this->_forward("illegal", "error");
			return;
		}
		$user = $this->_request->get("user");
		$authAdapter = new SuAuthAdapter($user) ;
		
		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid()) {
			$this->_redirect("/pages/home");
		} else { 
			$this->_forward("illegal", "error");
		}
	}
}
