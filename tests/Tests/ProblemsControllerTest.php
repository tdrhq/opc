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
		global $general_contest_problem;
		/* tricky, I don't know which problem to use at this point */
		$db = contestDB::get_zend_db();
		$this->dispatch ("/problems/$general_contest_problem");

		$this->assertNotRedirect();
		$this->assertController ("problems");
		$this->assertAction ("view");
	}
}
