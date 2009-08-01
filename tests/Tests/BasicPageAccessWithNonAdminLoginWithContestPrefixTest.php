<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once  "lib/db.inc";


/* taken from SuController code */
require_once "SuAuthAdapter.php";

class BasicPageAccessWithNonAdminLoginWithContestPrefix extends OpcDataTest
{
	var $contest;
	var $problem;
	public function setUp ()
	{
		global $test_nonadmin_uid;
		global $test_non_general_contest, $test_non_general_contest_problem;
		/* create a contest */

		$this->contest = $test_non_general_contest;
		$this->problem = $test_non_general_contest_problem;

		webconfig::$multi_contest = true;
		parent::setUp ();
		system ("../backend/admin/addcontest.php  --id {$this->contest} --name TestContest --start-time '-1 hour' --duration '2 hours' ");	
		/* "login" */
		Zend_Loader::loadClass('Zend_Auth');
		$adapter = new SuAuthAdapter ($test_nonadmin_uid);
		Zend_Auth::getInstance()->authenticate($adapter);
	}

	public function testContestCanAccessProblems() 
	{
		$this->dispatch("/contests/{$this->contest}/problems/");
		echo $this->response->getBody();
		$this->assertController ("problems");
		$this->assertNotRedirect ();
	}

	public function testCanAccessSubmitForm ()
	{
		$this->dispatch ("/contests/{$this->contest}/submit");
		$this->assertController("submit");
		$this->assertNotRedirect ();
	}

	public function testCanAccessQueueState ()
	{
		$this->dispatch ("/contests/{$this->contest}/queue");
		$this->assertController ("queue");
		$this->assertNotRedirect ();
	}

	public function testCanAccessMySubmissions ()
	{
		$this->dispatch ("/contests/{$this->contest}/queue/mine");
		$this->assertController ("queue");
		$this->assertNotRedirect();
	}

	public function testCanAccessRanks ()
	{
		$this->dispatch ("/contests/{$this->contest}/ranks");
		$this->assertController ("ranks");
	}

	public function testCanViewProblem ()
	{
		$this->dispatch ("/contests/{$this->contest}/problems/{$this->problem}");
		$this->assertController ("problems");
		$this->assertAction ("view");
		$this->assertNotRedirect();
	}

	public function testCanLogout ()
	{
		$this->dispatch ("/contests/{$this->contest}/auth/logout");
		echo $this->response->getBody();
		//$this->assertRedirect ();
		$this->assertEquals (Zend_Auth::getInstance()->hasIdentity(), false);
	}
	public function testHome ()
	{
		$this->dispatch ("/contests/{$this->contest}/");
		$this->assertController ("pages");
		$this->assertAction ("index");
		$this->assertNotRedirect ();
	}

}
