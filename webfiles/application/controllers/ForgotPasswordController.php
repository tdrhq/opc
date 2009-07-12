<?
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
	
	function testPasswordRecovery ($user, $key) 
	{
		$savedkey = $cache->get ($user);
		if ($savedkey != $key) return false;
		else return true;
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
		$mail->setBodyText ($key);
		
		$mail->addTo ($userm->getMember($obj, 0)->email,
			      $userm->getMember($obj, 0)->name);
		$mail->setSubject ("Password Reset");
		$mail->send();
	}
}
