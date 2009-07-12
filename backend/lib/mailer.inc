<?

class Mailer 
{
  static get_mailer () 
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

    

