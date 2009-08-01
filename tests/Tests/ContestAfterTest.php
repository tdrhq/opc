<?
require_once "test_config.inc";
require_once "OpcTest.php";
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

		/* create a contest */
		system ("../backend/admin/addcontest.php  --id {$this->contest} --name TestContest --start-time '-10 minutes' --duration '9 minutes' --quiet");	
		webconfig::$multi_contest = true;

		parent::setUp ();
	}

	public function testContestCannotAccess() 
	{
		/* no exceptions, right? */
		
		$this->dispatch("/contests/{$this->contest}/problems/");
		$this->assertNotRedirect ();
		$this->assertController ("problems");
		$this->assertAction ("index");
	}

	public function testCannotAccessSubmitForm ()
	{
		$this->dispatch ("/contests/{$this->contest}/submit");
		$this->assertController("error");
		$this->assertAction ("after");
	}
	
	public function testCannotForceSubmit ()
	{
		$this->request->setMethod("POST")->setPost (array("probid" => $this->problem,
			"lang" => 'cpp', 'MAX_FILE_SIZE' => '100000')) ;
		/* dummy file to upload. */
		$_FILES ['source'] = array ();
		$_FILES['source'] ['tmp_name'] = tempnam ("/tmp", "prefix");	
		$this->dispatch ("/contests/{$this->contest}/submit/upload");
	
		$this->assertController ("error");
		$this->assertAction ("after");
	}

}
