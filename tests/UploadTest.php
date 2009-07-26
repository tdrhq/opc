<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once "lib/db.inc";
require_once "lib/upload.inc";

class UploadTest extends OpcDataTest 
{

	/**
	 * @dataProvider provider
	 */
	public function testUpload ($user, $prob, $lang, $source, $owner, $score)
	{	

		$a = UploadSubmission::upload ($user, $prob, $lang, $source, $owner);

		$this->assertGreaterThan (0, $a);
		$b = $score;

		$cwd = realpath(getcwd());
		$argv = array ();;
		$argv[0] = "../backend/programs/submissions.php";
		$argv[1] = $a;
		$argc = 2;		
include "../backend/programs/submissions.php";
		echo getcwd ();
		chdir ($cwd);
		unset ($argv);
		$db = contestDB::get_zend_db ();
		$res = $db->select()->from("submissionqueue")->where("id=$a")->query();
		$row = $res->fetch();
		print_r ($row);
		$this->assertEquals ($b, $row->score);
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
