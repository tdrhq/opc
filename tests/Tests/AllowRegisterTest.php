<?

require_once "OpcTest.php";
require_once  "lib/db.inc";

class AllowRegisterTest extends OpcDataTest
{
	public function setUp ()
	{
		webconfig::$allow_register = false;
		parent::setUp ();
	}

	public function testRegistrationFails() 
	{
		/* no exceptions, right? */
		$this->request->setMethod("POST")->setPost(array('user' => 'dfsdfdfewwww_ewr', 'password' => 'simplepassword', 
	 'confirm' => 'simplepassword', 'name0' => 'Arnold', 'email0' => 'arn@arn.com', 'timezone' => 'localtime'));
		$this->dispatch("/profile/register2");
		$this->assertController ("error");

		/* todo: assertAction not working. What am I doing wrong? */
		//$this->assertAction ("regnotavailable");
	}

}
