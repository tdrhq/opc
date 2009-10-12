<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once  "lib/db.inc";

class ContestBeforeTest extends OpcDataTest
{
	var $contest, $problem;
	public function setUp ()
	{
		parent::setUp ();
		global $test_nonadmin_uid1;
		global $test_non_general_contest;
		global $test_non_general_contest_problem;

		$this->contest = $test_non_general_contest;
		$this->problem = $test_non_general_contest_problem;

		/* create a contest */
		system ("../backend/admin/addcontest.php  --id {$this->contest} --name TestContest --start-time '+1 hour' --duration '2 hours' --quiet");	
		webconfig::$multi_contest = true;

	}

	public function providerContestCannotAccess ()
	{
		global $test_nonadmin_uid1;
		$url = $this->providerAccessTest ();
		/* attach user to url */
		$ret = array ();
		foreach ($url as $i){
			$ret [] = array ($i, NULL);
			$ret [] = array ($i, $test_nonadmin_uid1);
		}
		return $ret;
	}

	/**
	 * @dataProvider providerContestCannotAccess
	 */
	public function testContestCannotAccess($url, $user) 
	{
		/* no exceptions, right? */
		if (!empty ($user)) $this->login ($user);
		$this->dispatch("/contests/{$this->contest}$url");
		$this->assertNotRedirect ();
		$this->assertController ("error");
		$this->assertAction ("before");
	}


	public function providerAccessTest ()
	{
		return array ("/problems", "/submit", "/problems/{$this->problem}", "/submit/?probid={$this->problem}"); 
	}
 
	/**
	 * @dataProvider providerCannotForceSubmit
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
		$this->assertAction ("before");
	}

	public function providerCannotForceSubmit ()
	{
		global $test_nonadmin_uid1;
		return array (array ($test_nonadmin_uid1), array (""));
	}

}
