<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once  "lib/db.inc";



class ContestAfterTest extends OpcDataTest
{
	var $contest, $problem;
	public function setUp ()
	{
		global $test_nonadmin_uid1;
		global $test_non_general_contest;
		global $test_non_general_contest_problem;

		$this->contest = $test_non_general_contest;
		$this->problem = $test_non_general_contest_problem;

		parent::setUp ();
		
		/* create a contest */
		system ("../backend/admin/addcontest.php  --id {$this->contest} --name TestContest --start-time '-10 minutes' --duration '9 minutes' --quiet");	
		webconfig::$multi_contest = true;
	}


	public function lprov ()
	{
		global $test_nonadmin_uid1;
		return array (array ($test_nonadmin_uid1), array (""));
	}

	/**
	 * @dataProvider lprov
	 */
	public function testContestCanAccessProblems($user) 
	{
		/* no exceptions, right? */
		if (!empty ($user)) $this->login ($user);
		$this->dispatch("/contests/{$this->contest}/problems/");
		$this->assertNotRedirect ();
		$this->assertController ("problems");
		$this->assertAction ("index");
	}

	/**
	 * @dataProvider lprov
	 */
	public function testContestCanViewProblems($user) 
	{
		/* no exceptions, right? */
		if (!empty ($user)) $this->login ($user);
		$this->dispatch("/contests/{$this->contest}/problems/{$this->problem}");
		$this->assertNotRedirect ();
		$this->assertController ("problems");
		$this->assertAction ("view");
	}

	public function testCannotAccessSubmitForm ()
	{
		$this->dispatch ("/contests/{$this->contest}/submit");
		$this->assertController("error");
		$this->assertAction ("after");
	}

	public function testAdminCanAccessSubmitForm ()
	{
		global $test_admin_uid;
		$this->login ($test_admin_uid);
		$this->dispatch ("/contests/{$this->contest}/submit");
		$this->assertController ("submit");
		$this->assertAction ("index");
	}
	
	/**
	 * @dataProvider lprov
	 */
	public function testCannotForceSubmit ($user)
	{
		if (!empty ($user)) $this->login ($user);
		$this->request->setMethod("POST")->setPost (array("probid" => $this->problem,
			"lang" => 'cpp', 'MAX_FILE_SIZE' => '100000')) ;
		/* dummy file to upload. */
		$_FILES ['source'] = array ();
		$_FILES['source'] ['tmp_name'] = tempnam ("/tmp", "prefix");	
		$this->dispatch ("/contests/{$this->contest}/submit/upload");
	
		$this->assertController ("error");
		$this->assertAction ("after");
	}

	public function testAdminCanSubmit ()
	{
		global $test_admin_uid;
		$this->login ($test_admin_uid);

		$this->request->setMethod("POST")->setPost (array("probid" => $this->problem,
			"lang" => 'cpp', 'MAX_FILE_SIZE' => '100000')) ;
		/* dummy file to upload. */
		$_FILES ['source'] = array ();
		$_FILES['source'] ['tmp_name'] = tempnam ("/tmp", "prefix");	
		$this->dispatch ("/contests/{$this->contest}/submit/upload");
	
		$this->assertController ("submit");
		$this->assertRedirect ();
	}

}
