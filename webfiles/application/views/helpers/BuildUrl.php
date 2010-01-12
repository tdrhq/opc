<?php

require_once "Zend/View/Helper/Abstract.php";

/**
 * Build a Uri from given parameters
 */
class Zend_View_Helper_BuildUrl
	extends Zend_View_Helper_Abstract
{
	private function concat ($url1, $url2)
	{
		if (substr ($url1, -1) == "/")
			$url1 = substr ($url1, 0, -1);
		return $url1 . $url2;
	}

	public function buildUrl ($url)
	{
		/* does this $url need building? */
		if (substr ($url, 0, 1) != "/")
			return $url;

		/* fix the $url */
		$baseurl = Zend_Controller_Front::getInstance()->getBaseUrl ();
		$static_baseurl = webconfig::$static_baseurl;

		if (substr ($url, 0, strlen ("/public/")) == "/public/")
			$url = $this->concat ($static_baseurl, $url);
		else if (substr ($url, 0, 1) == "/") {
			if (webconfig::$contest_id != "general")
				$url = $this->concat ("/contests/" . webconfig::$contest_id, $url);
			$url = $this->concat ($baseurl, $url);
		}

		return $url;
	}

}
