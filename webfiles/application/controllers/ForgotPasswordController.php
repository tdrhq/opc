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
 * @file   ForgotPasswordController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */
require_once "lib/mailer.inc";

class ForgotPasswordController extends Zend_Controller_Action
{
	function initPasswordRecovery ($user)
	{
		$str = "";
		for ($i = 0; $i < 16; $i++) {
			$str = $str . mt_rand (0, 9);
		}

		$old = $this->cache->load($user);
		
		if (!empty($old)) $str = $old;
		
		$this->cache->save ($str, $user);
		return $str;
	}
	

	function preDispatch ()
	{
		Zend_Loader::loadClass ("Zend_Cache");
		$this->cache = Zend_Cache::factory('Core', 'File', array ('lifetime' => 2*3600, 'automatic_serialization' => true));
	}

	function indexAction ()
	{
	}

	function submitAction ()
	{
		Zend_Loader::loadClass ("UserModel");
		$userm = new UserModel ();

		$user = $this->_request->get("user");
		$obj =  $userm->getRow($user);

		if (empty($obj)) 
			$this->_redirect ("/");

		$key = $this->initPasswordRecovery ($user);

		/* send email */
		$mail = Mailer::get_mailer ();
		$baseurl = Zend_Controller_Front::getInstance()->getBaseUrl();

		if ($baseurl == "/") $baseurl = "";
		$mail->setBodyText ("

You are receiving this email because you wished to reset your password
on http://{$_SERVER['SERVER_NAME']}$baseurl.

Please use the following link to reset your password:
   http://{$_SERVER['SERVER_NAME']}$baseurl/forgot-password/reset?user=$user&key=$key

");
		
		$mail->addTo ($userm->getMember($obj, 0)->email,
			      $userm->getMember($obj, 0)->name);
		$mail->setSubject ("Password Reset");
		Logger::get_logger ()->info ("Sending password reset to " . $userm->getMember ($obj, 0)->email);
		$mail->send();
	}

	function resetAction ()
	{
		Zend_Loader::loadClass ("UserModel");
		$userm = new UserModel ();
		$user = $this->_request->get("user");
		$key = $this->_request->get ("key");

		$obj = $userm->getRow ($user);

		if (empty($obj) or empty($key)) 
			$this->_redirect ("/");

		$savedkey = $this->cache->load ($user);

		if ($savedkey != $key)
			$this->_redirect ("/");

		$this->cache->remove ($user);

		$pass = "";
		for ($i = 0; $i < 8; $i++) {
			$pass = $pass . mt_rand (0, 9);
		}

		/* send email */
		$mail = Mailer::get_mailer ();
		$baseurl = Zend_Controller_Front::getInstance()->getBaseUrl();
		if ($baseurl == "/") $baseurl = "";
		$mail->setBodyText ("

You are receiving this email because you wished to reset your password
on http://{$_SERVER['SERVER_NAME']}$baseurl.

We have changed your password to: $pass

Please try logging in using this password and change it as soon as
possible.

");
		$mail->addTo ($userm->getMember($obj, 0)->email,
			      $userm->getMember($obj, 0)->name);
		$mail->setSubject ("Password Reset");
		$mail->send();
		
		$obj->setPassword ($pass);
	}
}
