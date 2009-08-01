<?
require_once "test_config.inc";
require_once "OpcTest.php";
require_once  "lib/db.inc";
require_once "SuAuthAdapter.php";


require_once "ContestAfterTest.php";

class ContestAfterWithLoginTest extends ContestAfterTest
{
	var $contest, $problem;
	public function setUp ()
	{
		global $test_nonadmin_uid1;
		parent::setUp ();
		/* "login" */
		Zend_Loader::loadClass('Zend_Auth');
		$adapter = new SuAuthAdapter ($test_nonadmin_uid1);
		Zend_Auth::getInstance()->authenticate($adapter);

	}

}
