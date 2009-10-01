<?
require_once "OpcTest.php";


/* Test that Zend Test is working! */
require_once "lib/db.inc";

function safeSystem ($string) {
	system ($string, $ret);
	if ($ret != 0) {
		echo "$string: failed\n";
		assert (false);
	}
}
class OpcDataTest extends OpcTest {
	public function setUp ()
	{
		$blankdir = "/tmp/opc-blank-dir";
		safeSystem ("rm -rf $blankdir");
		safeSystem ("mkdir $blankdir");
		$datadir = get_file_name ("data/");
		$testdatadir = getcwd () . "/data";
		safeSystem ("unionfs-fuse -o cow,nonempty,exec,allow_other $blankdir=RW:$testdatadir=RO $datadir");
		parent::setUp ();
	}
	public function testDummy ()
	{
	}

	public function tearDown ()
	{
		contestDB::get_zend_db ()->closeConnection();
		$datadir = get_file_name ("data/");
		safeSystem ("fusermount -zu $datadir");
	} 
}
