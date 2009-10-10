<?php

require_once "Zend/View/Helper/Abstract.php";
require_once "/usr/share/php-geshi/geshi.php"; /* todo! replace path to geshi */

class Zend_View_Helper_CodeHighlight
	extends Zend_View_Helper_Abstract
{
	public function codeHighlight ($source, $lang)
	{
		if ($lang == "cpp") $lang = "C++";
		if ($lang == "gcj") $lang = "Java";

		$geshi = new GeSHi ($source, $lang); 
		$geshi->set_header_type(GESHI_HEADER_PRE_TABLE);
		$geshi->enable_line_numbers (GESHI_NORMAL_LINE_NUMBERS);
		$geshi->set_overall_class ("geshi");
		$code = $geshi->parse_code ();
		return $code;
	}
}
