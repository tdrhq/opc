<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once  "lib/db.inc";


/* taken from SuController code */
class SuAuthAdapter implements Zend_Auth_Adapter_Interface {
        public function __construct($uid) {

                        $this->result = new Zend_Auth_Result(
                                Zend_Auth_Result::SUCCESS, $uid);
                        return ;
        }

        public function authenticate() {
                return $this->result ;
        }

}

class BasicPageAccessWithNonAdminLogin extends OpcDataTest
{
	public function setUp ()
	{
		global $test_nonadmin_uid;
		parent::setUp ();
		/* "login" */
		Zend_Loader::loadClass('Zend_Auth');
		$adapter = new SuAuthAdapter ($test_nonadmin_uid);
		Zend_Auth::getInstance()->authenticate($adapter);
	}
	public function testContestCanAccessProblems() 
	{
		$this->dispatch("/problems/");
		echo $this->response->getBody();
		$this->assertController ("problems");
		$this->assertNotRedirect ();
	}

	public function testCanAccessSubmitForm ()
	{
		$this->dispatch ("/submit");
		$this->assertController("submit");
		$this->assertNotRedirect ();
	}

	public function testCanAccessQueueState ()
	{
		$this->dispatch ("/queue");
		$this->assertController ("queue");
		$this->assertNotRedirect ();
	}

	public function testCanAccessMySubmissions ()
	{
		$this->dispatch ("/queue/mine");
		$this->assertController ("queue");
		$this->assertNotRedirect();
	}

	public function testCanAccessRanks ()
	{
		$this->dispatch ("/ranks");
		$this->assertController ("ranks");
	}

	public function testCanViewProblem ()
	{
		$this->dispatch ("/problems/POINTS");
		$this->assertController ("problems");
		$this->assertNotRedirect();
	}

	public function testCanLogout ()
	{
		$this->dispatch ("/auth/logout");
		echo $this->response->getBody();
		//$this->assertRedirect ();
		$this->assertEquals (Zend_Auth::getInstance()->hasIdentity(), false);
	}

	public function testHome ()
	{
		$this->dispatch ("/");
		$this->assertController ("pages");
		$this->assertAction ("index");
		$this->assertNotRedirect ();
	}
}
