<?

require_once "../backend/config.inc";
require_once "Zend/Loader.php";
require "Zend/Test/PHPUnit/ControllerTestCase.php";
require_once "test_config.inc";

$testdir = getcwd();
chdir ("../webfiles");
require "zend-test-index.php";
chdir ("../tests");
echo "$testdir\n";

/* Test that Zend Test is working! */

abstract class OpcTest extends Zend_Test_PHPUnit_ControllerTestCase {
	public function setUp ()
	{
		$bootstrap = new App ();
		$this->bootstrap = array ($bootstrap, 'bootstrap');
		parent::setUp ();
	}

}
