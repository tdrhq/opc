<?

require_once "../backend/config.inc";
require_once "Zend/Loader.php";
require "Zend/Test/PHPUnit/ControllerTestCase.php";
require_once "test_config.inc";
require_once "../webfiles/App.php";

/* Test that Zend Test is working! */

abstract class OpcTest extends Zend_Test_PHPUnit_ControllerTestCase {
	public function setUp ()
	{
		$app = new App ();

		/* the following line is a Zend_Test or PHPUnit feature that
		 * calls $app->bootstrap. @todo: link to documentation. */
		$this->bootstrap = array ($app, 'bootstrap');
		parent::setUp ();
	}
}
