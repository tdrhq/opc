<?php

require_once "Zend/View/Helper/Abstract.php";

/**
 * Build a Uri from given parameters
 */
class Zend_View_Helper_BuildUrl
	extends Zend_View_Helper_Abstract
{
	static $links = array (); /**< list of all links generated */

	private function concat ($url1, $url2)
	{
		if (substr ($url1, -1) == "/")
			$url1 = substr ($url1, 0, -1);
		return $url1 . $url2;
	}

	public function buildUrl ($url)
	{
		$ret = $this->_buildUrl ($url);
		Zend_View_Helper_BuildUrl::$links [] = $ret;
		return $ret;
	}

	public function getStaticBaseDirectory ()
	{
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl ();
		if (basename ($baseUrl) == "index.php")
			return dirname ($baseUrl);
		return $baseUrl;
	}

	public function _buildUrl ($url)
	{
		/* does this $url need building? */
		if (substr ($url, 0, 1) != "/")
			return $url;

		/* fix the $url */
		$baseurl = Zend_Controller_Front::getInstance()->getBaseUrl ();

		if (substr ($url, 0, strlen ("/public/")) == "/public/")
			$url = $this->concat ($this->getStaticBaseDirectory(), $url);
		else {
			if (webconfig::getContestId () != "general")
				$url = $this->concat ("/contests/" . webconfig::getContestId (), $url);
			$url = $this->concat ($baseurl, $url);
		}

		return $url;
	}

}
