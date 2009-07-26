<?

chdir ("../webfiles");
require_once "../backend/config.inc";
require_once "Zend/Loader.php";
Zend_Loader::loadClass ("Zend_Test_PHPUnit_ControllerTestCase");
require_once "zend-test-index.php";

/* Test that Zend Test is working! */
require_once "OpcTest.php";
require_once "lib/db.inc";

function safeSystem ($string) {
	system ($string, $ret);
	if ($ret != 0) {
		echo "$string: failed\n";
//		exit (1);
	}
}
class OpcDataTest extends OpcTest {
	public function setUp ()
	{
		$this->assertFalse (contestDB::get_zend_db()->isConnected());
		safeSystem ("if [ -d ../backend/data ] ; then rm -rf ../backend/data-old && mv -fT ../backend/data ../backend/data-old; else true; fi");
		safeSystem ("cp -r ../tests/data ../backend/data");
		parent::setUp ();
	}
	public function testDummy ()
	{
	}

	public function tearDown ()
	{
		contestDB::get_zend_db ()->closeConnection();
		safeSystem ("rm -rf ../backend/data");
		safeSystem ("if [ -d ../backend/data-old ] ; then mv -fT ../backend/data-old ../backend/data; else true; fi");
	} 
}
