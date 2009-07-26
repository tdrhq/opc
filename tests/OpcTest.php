<?

chdir ("../webfiles");
require_once "../backend/config.inc";
require_once "Zend/Loader.php";
Zend_Loader::loadClass ("Zend_Test_PHPUnit_ControllerTestCase");
require_once "zend-test-index.php";

/* Test that Zend Test is working! */

class OpcTest extends Zend_Test_PHPUnit_ControllerTestCase {
	public function setUp ()
	{
		$bootstrap = new App ();
		$this->bootstrap = array ($bootstrap, 'bootstrap');
		parent::setUp ();
	}

	public function testDummy ()
	{
	}
}
