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
		global $test_nonadmin_uid1;
		parent::setUp ();
		/* "login" */
		Zend_Loader::loadClass('Zend_Auth');
		$adapter = new SuAuthAdapter ($test_nonadmin_uid1);
		Zend_Auth::getInstance()->authenticate($adapter);
	}
	public function testContestCanAccessProblems() 
	{
		$this->dispatch("/problems/");
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

	public function testMyOwnSubmission ()
	{
		print_r ($_SESSION);
		global $submission_owned_by_uid1;
		$this->dispatch ("/results/{$submission_owned_by_uid1}");
		$this->assertController ("results");
		$this->assertNotRedirect();
		$this->assertAction("index");
	}

	public function testSomebodyElsesSubmission ()
	{
		global $submission_owned_by_uid2;
		$this->dispatch ("/results/{$submission_owned_by_uid2}");
		$this->assertNotRedirect();
		$this->assertController ("error");
		$this->assertAction ("illegal");
	}
}
