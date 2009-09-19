<?
require_once "test_config.inc";
require_once "OpcDataTest.php";
require_once "lib/db.inc";
require_once "lib/upload.inc";
require_once "programs/submissions-processor.inc";
class XHTMLValidationTest extends OpcDataTest 
{
	public function setUp ()
	{
		parent::setUp ();
	}

	/**
	 * @dataProvider provider
	 */
	public function testPageValid ($page)
	{	
		$this->dispatch ($page);
		$dom = new DomDocument;
		$dom->loadXML ($this->getResponse()->getBody(), LIBXML_DTDLOAD);
		$this->assertEquals (true, $dom->validate ());
	}

	public function provider ()
	{
		global $problems_for_validation;
		assert (!empty($problems_for_validation));
		$ret = array ("/problems", "/queue", "/queue/index", "/submit", "/ranks");
		foreach ($problems_for_validation as $pr) 
			$ret [] = "/problems/$pr";
		$ret2 = array ();
		foreach ($ret as $r)
			$ret2[] = array($r);
		return $ret2;	
	}
}
