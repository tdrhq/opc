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
		$errors = $this->_getParam('error_handler');
		
		switch ($errors->type) {
		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
			// 404 error -- controller or action not found
			$this->getResponse()
				->setRawHeader('HTTP/1.1 404 Not Found');
			$this->view->message = "404 Page Not Found";
			break;
		default:
			$this->view->message = ($exception->getMessage() . "\n"
				    . $exception->getTraceAsString());
			break;
		}
	}
}
