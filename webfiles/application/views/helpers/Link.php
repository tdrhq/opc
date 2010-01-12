<?php

require_once "Zend/View/Helper/Abstract.php";

/**
 * Build a Uri from given parameters
 */
class Zend_View_Helper_Link
	extends Zend_View_Helper_Abstract
{
	static $links = array ();
	/**
	 * Return the URI associated with the given options.
	 *
	 * 
	 *
	 * @param $url       The url to link to 
	 * @param $innerHtml The inner HTML inside the <a>
	 * @param $opt       An array of options
	 */
	public function link ($url, $innerHtml, $opt)
	{
		$ret = "<a href=\"" . htmlspecialchars ($url) . "\"";
		foreach ($opt as $key => $value)  
			$ret .= " $key=\"" . htmlspecialchars ($value) . "\"";
		$ret .= ">$innerHtml</a>";
		
		Zend_View_Helper_Link::$links [] = $url;
		return $ret;
	}

	public static function getAllLinked ()
	{
		return self::$links;
	}
}
