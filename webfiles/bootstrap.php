<?


error_reporting(E_ALL|E_STRICT);
require_once "./config.inc" ; 

date_default_timezone_set (webconfig::$default_timezone);
set_include_path('.' . PATH_SEPARATOR . './library'
     . PATH_SEPARATOR . './application/models/'
     . PATH_SEPARATOR . get_include_path());

require_once "Zend/Loader.php";
Zend_Loader::loadClass('Zend_Controller_Front');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Auth_Adapter_Interface') ;
Zend_Loader::loadClass('Zend_Layout') ;
Zend_Layout::startMvc(array('layoutPath' => 'application/views/layouts'));
require_once "lib/user.inc" ;

class MyAuthAdapter implements Zend_Auth_Adapter_Interface { 
	var $result ; 
	public function __construct($username, $password) { 
		$user = User::factory($username) ;

		if ( !empty($user) && 
		     $user->matchPassword($password) ) { 
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

class App {

	public function bootstrap () 
	{
                /* setup auth session */
		$auth = Zend_Auth::getInstance() ;
		Zend_Loader::loadClass('Zend_Auth_Storage_Session');
		$auth->setStorage(new Zend_Auth_Storage_Session(webconfig::$session_namespace));
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			Zend_Loader::loadClass ('UserModel');
			$users = new UserModel ();
			$row = $users->getRow(Zend_Auth::getInstance()->getIdentity());
			$tz = $users->getTimezone($row);
			if (!empty($tz)) {
				date_default_timezone_set ($tz);
			} else {
				date_default_timezone_set (webconfig::$default_timezone);
			}
		}
		
/* decide which contest I'm running on */
		Zend_Loader::loadClass('Zend_Session_Namespace');
		$session = new Zend_Session_Namespace(webconfig::$session_namespace);
		if ( isset($session->contestid) && webconfig::$multi_contest ) {
			
			/* just validate to see if I'm allowed on this contest */
			require_once "lib/contest.inc" ;
			$contest = Contest::factory($session->contestid);
			if ( empty($contest) or !$contest->authenticateUser($auth->getIdentity()) ) { 
				$session->contestid = "general" ; 
				$session->contestname = "" ; 
			}
			webconfig::$contest_id = $session->contestid ; 
			webconfig::$contest_name = $contest->getFriendlyName() ;
		}
		
		if (empty(webconfig::$static_baseurl)) {
			webconfig::$static_baseurl = dirname($_SERVER['SCRIPT_NAME']);
			if (webconfig::$static_baseurl == "/")
				webconfig::$static_baseurl = "";
		}
		
/* setup the controller and routes */
		$frontController = Zend_Controller_Front::getInstance();
		$frontController->setControllerDirectory('./application/controllers');
		
		$router = $frontController->getRouter(); // returns a rewrite router by default
		
		$router->addRoute ('comtestroute', new Zend_Controller_Router_Route ('contests/:contestid/:controller/:action', array ('controller' => 'index', 'action' => 'index')));

		/* for each of the following routes, we need two versions, with
		 * and without the contests prefix */

		$router->addRoute('problems', new Zend_Controller_Router_Route('problems/:probid', array('controller' => 'problems', 'action' => 'view')));
		$router->addRoute('problemsc', new Zend_Controller_Router_Route('contests/:contestid/problems/:probid', array('controller' => 'problems', 'action' => 'view')));
		
		
		$router->addRoute('submit', new Zend_Controller_Router_Route('submit/success/:id', array('controller' => 'submit', 'action' => 'success')));
		$router->addRoute('submitc', new Zend_Controller_Router_Route('contests/:contestid/submit/success/:id', array('controller' => 'submit', 'action' => 'success')));
		
		
		$router->addRoute('results', new Zend_Controller_Router_Route('results/:id', array('controller' =>'results', 'action' => 'index'))) ;
		$router->addRoute('resultsc', new Zend_Controller_Router_Route('contests/:contestid/results/:id', array('controller' =>'results', 'action' => 'index'))) ;
		
		
		
		$router->addRoute('users', new Zend_Controller_Router_Route('users/:user', array('controller' => 'users', 'action' => 'index')));
		$router->addRoute('usersc', new Zend_Controller_Router_Route('contests/:contestid/users/:user', array('controller' => 'users', 'action' => 'index')));
		
		$router->addRoute('ranks', new Zend_Controller_Router_Route('status/:user', array('controller' => 'ranks', 'action' => 'user')));
		$router->addRoute('ranksc', new Zend_Controller_Router_Route('contests/:contestid/status/:user', array('controller' => 'ranks', 'action' => 'user')));
		

		$router->addRoute('data', new Zend_Controller_Router_Route('problems/:probid/:file', array('controller'=>'problems', 'action'=>'file')));
		$router->addRoute('datac', new Zend_Controller_Router_Route('contests/:contestid/problems/:probid/:file', array('controller'=>'problems', 'action'=>'file')));

		
		$router->addRoute('pages', new Zend_Controller_Router_Route('pages/:page', array('controller' => 'pages')));
		$router->addRoute('pagesc', new Zend_Controller_Router_Route('contests/:contestid/pages/:page', array('controller' => 'pages')));
		
		$router->addRoute('su', new Zend_Controller_Router_Route ('su/:user', array('controller' => 'su')));
		
	} /* function bootstrap */
} /* class App */

if (empty ($__zend_test_no_dispatch)) {
	$app = new App ();
	$app->bootstrap();
	Zend_Controller_Front::getInstance()->dispatch();

	Zend_Loader::loadClass ("Zend_Controller_Plugin_ErrorHandler");
	Zend_Controller_Front::getInstance()->registerPlugin (new Zend_Controller_Plugin_ErrorHandler());
} 
