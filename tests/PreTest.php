<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once "lib/db.inc";

class PreTest extends OpcDataTest 
{

	/**
	 * @dataProvider provider
	 */
	public function testSubmission ($a, $b)
	{	
		$cwd = realpath(getcwd());
		$argv = array ();;
		$argv[0] = "../backend/programs/submissions.php";
		$argv[1] = $a;
		$argc = 2;		
include_once "../backend/programs/submissions.php";
		echo getcwd ();
		chdir ($cwd);
		unset ($argv);
		$db = contestDB::get_zend_db ();
		$res = $db->select()->from("submissionqueue")->where("id=$a")->query();
		$row = $res->fetch();
		print_r ($row);
		$this->assertEquals ($b, $row->score);
	}
	
	public function provider ()
	{
		global $test_submissions;
		return $test_submissions;
	}
}
