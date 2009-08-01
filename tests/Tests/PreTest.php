<?
require_once "OpcDataTest.php";
require_once "lib/db.inc";

require_once "../backend/programs/submissions-processor.inc";

class PreTest extends OpcDataTest 
{

	/**
	 * @dataProvider provider
	 */
	public function testSubmission ($a, $b)
	{	
		$cwd = realpath(getcwd());
		SubmissionTable::set_score($a, 23);
		SubmissionProcessor::process ($a);
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
