<?
require_once "OpcDataTest.php";
require_once "lib/db.inc";
require_once "test_config.inc";
require_once "../backend/programs/submissions-processor.inc";

class PreTest extends OpcDataTest 
{

	/**
	 * @dataProvider provider
	 */
	public function testSubmission ($a, $b, $c)
	{	
		$cwd = realpath(getcwd());
		$sub = SubmissionTable::get_submission ($a);
		if (is_file ($sub->getXmlFile()))
		    $oldResult = file_get_contents ($sub->getXmlFile ());
		else $oldResult = "";
		SubmissionTable::set_score($a, 23);
		$sp = new SubmissionProcessor();
		$sp->process ($a);
		$db = contestDB::get_zend_db ();
		$res = $db->select()->from("submissionqueue")->where("id=$a")->query();
		$row = $res->fetch();
		$this->assertEquals ($b, $row->score);
		$this->assertEquals ($c, $row->state);

		$sub = SubmissionTable::get_submission ($a);
		$this->assertNotEquals ($oldResult,
			file_get_contents ($sub->getXmlFile ()));
		$sub->validateResultXML ();
	}
	
	public function provider ()
	{
		global $test_submissions;
		return $test_submissions;
	}
}
