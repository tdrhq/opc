<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once "lib/db.inc";
require_once "lib/upload.inc";
require_once "programs/submissions-processor.inc";
class UploadTest extends OpcDataTest 
{
	public function setUp ()
	{
		parent::setUp ();
	}

	/**
	 * @dataProvider provider
	 */
	public function testUpload ($user, $prob, $lang, $source, $owner, $score, $result)
	{	

		ob_start ();
		$a = UploadSubmission::upload ($user, $prob, $lang, $source, $owner);
		ob_end_clean ();

		$this->assertGreaterThan (0, $a);
		$b = $score;

		ob_start ();
		$sp = new SubmissionProcessor();
		$sp->process ($a);
		ob_end_clean ();
		$db = contestDB::get_zend_db ();
		$res = $db->select()->from("submissionqueue")->where("id=$a")->query();
		$row = $res->fetch();
		$this->assertEquals ($b, $row->score);
		$this->assertEquals ($result, $row->state);
		$sub = SubmissionTable::get_submission ($a);
		$this->assertNotEquals ($sub, NULL);
	}

	/**
	 * @dataProvider provider
	 */

	public function testUploadHashWorks ($user, $prob, $lang, $source, $owner, $score)
	{
		config::$enable_hash_test = true;
                $a = UploadSubmission::upload ($user, $prob, $lang, $source, $owner);
                $b = UploadSubmission::upload ($user, $prob, $lang, $source, $owner);

		$user = User::factory ($user);

		if (!$user->isAdmin())
			$this->assertEquals (-1, $b);
		else
			$this->assertGreaterThan (0, $b);
		
	}	
	public function provider ()
	{
		global $test_uploads;
		return $test_uploads;
	}
}
