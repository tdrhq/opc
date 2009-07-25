<?

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
