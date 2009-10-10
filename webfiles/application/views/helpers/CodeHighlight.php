<?php

require_once "Zend/View/Helper/Abstract.php";
if (!include_once ("/usr/share/php-geshi/geshi.php")) {
    if (!include_once ("geshi/geshi.php")) 
	Logger::get_logger()->warn ("GeSHi not found in path, code formatting disabled");
}	

class Zend_View_Helper_CodeHighlight
	extends Zend_View_Helper_Abstract
{
	public function codeHighlight ($source, $lang)
	{
		if ($lang == "cpp") $lang = "C++";
		if ($lang == "gcj") $lang = "Java";

		if (!class_exists ("GeSHi")) 
			return "<!-- GeSHi disabled --> <pre>" . htmlspecialchars ($source) . "</pre>";

		$geshi = new GeSHi ($source, $lang); 
		$geshi->set_header_type(GESHI_HEADER_PRE_TABLE);
		$geshi->enable_line_numbers (GESHI_NORMAL_LINE_NUMBERS);
		$geshi->set_overall_class ("geshi");
		$code = $geshi->parse_code ();
		return $code;
	}
}
