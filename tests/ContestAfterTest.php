<?
require_once "test_config.inc";
require_once "OpcTest.php";
require_once  "lib/db.inc";


/* taken from SuController code */
class SuAuthAdapter implements Zend_Auth_Adapter_Interface {
        public function __construct($uid) {

                        $this->result = new Zend_Auth_Result(
                                Zend_Auth_Result::SUCCESS, $uid);
                        return ;
        }

        public function authenticate() {
                return $this->result ;
        }

}

class ContestAfterTest extends OpcTest
{
	public function setUp ()
	{
		global $test_nonadmin_uid;
		/* create a contest */
		system ("../backend/admin/addcontest.php  --id Test --name TestContest --start-time '-3 hour' --duration '2 hours' ");	
		webconfig::$multi_contest = true;

		parent::setUp ();
		/* "login" */
		Zend_Loader::loadClass('Zend_Auth');
		$adapter = new SuAuthAdapter ($test_nonadmin_uid);
		Zend_Auth::getInstance()->authenticate($adapter);

		$session = new Zend_Session_Namespace (webconfig::$session_namespace);
		$session->__set ("contestid", 'Test');

	}

	public function tearDown ()
	{
		unlink ("../backend/data/contests/Test.xml");
	}

	public function testContestCanAccessProblems() 
	{
		/* no exceptions, right? */
		
		$this->dispatch("/problems/");
		$this->assertController ("problems");
		$this->assertNotRedirect();
	}

	public function testCannotAccessSubmitForm ()
	{
		$this->dispatch ("/submit");
		$this->assertController("error");
		$this->assertAction ("before");
	}

}
