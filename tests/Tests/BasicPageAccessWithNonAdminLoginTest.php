<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once  "lib/db.inc";


/* taken from SuController code */
require_once "SuAuthAdapter.php";

class BasicPageAccessWithNonAdminLogin extends OpcDataTest
{
	public function setUp ()
	{
		global $test_nonadmin_uid1;
		parent::setUp ();
		$this->login ($test_nonadmin_uid1);
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
