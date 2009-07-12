<?

class Mailer 
{
  static function get_mailer () 
  {
    Zend_Loader::loadClass ("Zend_Mail");
    Zend_Loader::loadClass ("Zend_Mail_Transport_Smtp");
    
    if (!empty(config::$mailer_smtp_server)) {
      $tr = new Zend_Mail_Transport_Smtp (config::$mailer_smtp_server, config::$mailer_smtp_zend_config);
      Zend_Mail::setDefaultTransport ($tr);
    }

    $mail = new Zend_Mail ();
    $mail->setFrom (config::$mailer_admin_email, config::$mailer_admin_name);
    
    return $mail;
  }
}

/* mailer self test */
if (!empty($argc) && $argc == 2) {
	require_once dirname(__FILE__) . "/../config.inc";
	require_once "Zend/Loader.php";
	$mail = Mailer::get_mailer();
	$mail->setBodyText ("This is a test email");
	$mail->addTo ($argv[1]);
	$mail->setSubject ("Test Email");
	$mail->send();
}    

