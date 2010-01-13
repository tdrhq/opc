<?php

require_once "test_config.inc";
require_once "OpcTest.php";
require_once "../webfiles/application/views/helpers/BuildUrl.php";

class BuildUrlTest extends OpcTest
{
	/**
	 * @dataProvider provider
	 */
	public function testBuildUrl ($base, $sub, $final)
	{
		Zend_Controller_Front::getInstance()->setBaseUrl ($base);
		$buildUrl = new Zend_View_Helper_BuildUrl ();
		$response = $buildUrl->buildUrl ($sub);
		$this->assertEquals ($final, $response);
	}

	public function provider ()
	{
		return array (
			array("", "/problems/test", "/problems/test"),
			array("/", "/index.php", "/index.php"),
			array("/index.php", "/problems/WPE", "/index.php/problems/WPE"),
			array("/opc/index.php/", "/problems", "/opc/index.php/problems"),
			array("http://www.cmi.ac.in", "/problems/test/", "http://www.cmi.ac.in/problems/test/")
			);
	}
}