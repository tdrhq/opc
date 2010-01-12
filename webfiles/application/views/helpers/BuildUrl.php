<?php

require_once "Zend/View/Helper/Abstract.php";

/**
 * Build a Uri from given parameters
 */
class Zend_View_Helper_BuildUrl
	extends Zend_View_Helper_Abstract
{
	public function buildUrl ($url)
	{
		/* fix the $url */
		$baseurl = Zend_Controller_Front::getInstance()->getBaseUrl ();
		$static_baseurl = webconfig::$static_baseurl;

		if (substr ($url, 0, strlen ("/public/")) == "/public/")
			$url = $static_baseurl . $url;
		else if (substr ($url, 0, 1) == "/")
			$url = $baseurl . $url;
	  
		return $url;
	}

}
