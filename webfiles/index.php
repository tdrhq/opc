<?

error_reporting (E_ALL | E_STRICT);
require_once "./config.inc";
/* if this is called we can assume that mod_rewrite is disabled */

require_once "Zend/Loader.php";
Zend_Loader::loadClass ("Zend_Controller_Front");
Zend_Controller_Front::getInstance()->setBaseUrl($_SERVER['SCRIPT_NAME']);

require_once "bootstrap.php";
