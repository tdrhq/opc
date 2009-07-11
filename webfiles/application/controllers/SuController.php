<?

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
