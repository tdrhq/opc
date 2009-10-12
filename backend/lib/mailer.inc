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
 * @file   mailer.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once dirname(__FILE__) . "/../config.inc";
require_once "Zend/Loader.php";

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

  static function self_test ()
  {
	$mail = Mailer::get_mailer();
	$mail->setBodyText ("This is a test email");
	$mail->addTo ($argv[1]);
	$mail->setSubject ("Test Email");
	$mail->send();

  }
}


