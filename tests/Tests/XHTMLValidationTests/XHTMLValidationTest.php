<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once "lib/db.inc";
require_once "lib/upload.inc";
require_once "programs/submissions-processor.inc";
class XHTMLValidationTest extends OpcDataTest 
{
	public function setUp ()
	{
		parent::setUp ();
	}

	/**
	 * @dataProvider provider
	 */
	public function testPageValid ($page, $user)
	{
		if (!empty ($user)) $this->login ($user);
		$this->dispatch ($page);
		$dom = new DomDocument;
		Logger::get_logger ()->debug ($this->getResponse()->getBody());
		$dom->loadXML ($this->getResponse()->getBody(), LIBXML_DTDLOAD);
		$this->assertTrue ($dom->validate (), $this->getResponse()->getBody());
	}

	public function provider ()
	{
		global $problems_for_validation;
		global $test_nonadmin_uid1, $test_admin_uid;
		global $submission_owned_by_uid1, $submission_owned_by_uid2;
		global $test_submissions;

		assert (!empty($problems_for_validation));
		$ret = array ("/problems", 
			      "/queue", 
			      "/queue/index/user/arnstein",
			      "/queue/index/uid/263",
			      "/queue/index/problem/POINTS",
			      "/queue/index", 
			      "/submit", 
			      "/ranks", 
			      "/results/$submission_owned_by_uid1", 
			      "/results/$submission_owned_by_uid2");

		foreach  ($test_submissions  as $i) {
			$ret [] = "/results/{$i[0]}";
		}
		foreach ($problems_for_validation as $pr) 
			$ret [] = "/problems/$pr";
		$ret2 = array ();
		foreach ($ret as $r)
			$ret2[] = array($r, "");

		foreach ($ret as $r) 
			$ret2[] = array($r, $test_nonadmin_uid1);

		foreach ($ret as $r)
			$ret2[] = array ($r, $test_admin_uid);

		return $ret2;	
	}
}
