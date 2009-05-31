<?

class EmailController extends Zend_Controller_Action 
{
	public function indexAction() 
	{
		$auth = Zend_Auth::getInstance() ; 
		if ( !$auth->hasIdentity() ) throw new Exception("Please login");
		$user = User::factory($auth->getIdentity());
		if (!$user->isAdmin()) throw new Exception("You must be an admin to view this page.");
		
		$this->_helper->viewRenderer->setNoRender();
		Zend_Loader::loadClass("UserModel");
		$usermodel = new UserModel();
		$users = $usermodel->getUserList() ;
		foreach ($users as $username) {
			$user = User::factory($username) ;
			$memcount = $usermodel->getMemberCount($user);
			for($i = 0; $i < $memcount; $i++) {
				$member = $usermodel->getMember($user, $i);
				if (!empty($member->email) )
					echo "{$member->email}, ";
			}
		}
	}
}
