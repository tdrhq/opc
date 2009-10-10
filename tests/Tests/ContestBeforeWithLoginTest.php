<?
require_once "test_config.inc";
require_once "OpcTest.php";
require_once  "lib/db.inc";
require_once "SuAuthAdapter.php";

require_once "ContestBeforeTest.php";

class ContestBeforeWithLoginTest extends ContestBeforeTest
{
	var $contest, $problem;
	public function setUp ()
	{
		global $test_nonadmin_uid1;
		parent::setUp ();
		/* "login" */
		$this->login ($test_nonadmin_uid1);
	}

}
