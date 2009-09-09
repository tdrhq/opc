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
 * @file   ErrorController.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

class ErrorController extends Zend_Controller_Action { 
	public function illegalAction() { 
	}
	public function beforeAction() {
	}
	public function afterAction() {
	}
        public function loginAction() {
        }
	public function regnotavailableAction() {
	}
	public function privacyAction() {
	}
	public function errorAction()
	{
		global $__zend_test_no_dispatch;

		$errors = $this->_getParam('error_handler');
		
		switch ($errors->type) {
		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
			if (!empty($__zend_test_no_dispatch)) throw new Exception ("404 Page Not Found");
			// 404 error -- controller or action not found
			$this->getResponse()
				->setRawHeader('HTTP/1.1 404 Not Found');
			$this->view->message = "404 Page Not Found";
			break;
		default:
			if (!empty($__zend_test_no_dispatch)) throw $errors->exception;
			$exception = $errors->exception;
			$this->view->message = "An exception was thrown. We would appreciate it if you could report this bug to us. \n\n" 
			  . ($exception->getMessage() . "\n"
				    . $exception->getTraceAsString());
			break;
		}
	}
}
