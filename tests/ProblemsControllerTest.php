<?

require_once "OpcTest.php";
require_once  "lib/db.inc";
class ProblemsControllerTest extends OpcTest
{
	public function testBasicIndex() 
	{
		/* no exceptions, right? */
		$this->dispatch("/problems/");
	}

	public function testBasicProblemDescription ()
	{
		/* tricky, I don't know which problem to use at this point */
		$db = contestDB::get_zend_db();
		$res = $db->query("select id from problemdata where owner='general' limit 1");
		$obj = $res->fetchObject();
		$this->dispatch ("/problems/{$obj->id}");

		$this->assertNotRedirect();
		$this->assertController ("problems");
		$this->assertAction ("view");
	}
}
