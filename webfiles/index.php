<?

require_once "./config.inc";

/* if this is called we can assume that mod_rewrite is disabled */
webconfig::$static_baseurl = dirname ($_SERVER['SCRIPT_NAME']);
if (webconfig::$static_baseurl == "/")
	webconfig::$static_baseurl = "";
require_once "Zend/Loader.php";
Zend_Loader::loadClass ("Zend_Controller_Front");
Zend_Controller_Front::getInstance()->setBaseUrl($_SERVER['SCRIPT_NAME']);

require_once "bootstrap.php";
