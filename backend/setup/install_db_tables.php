#!/usr/bin/php
<?
chdir(dirname(__FILE__));
require_once "../config.inc" ;
require_once "lib/db.inc" ;

$sql = file_get_contents("../database") ;

try {
	contestDB::get_zend_db()->getConnection()->exec("$sql");
} catch (Exception $e) {
	echo "Notice: database tables are already installed from previous make\n";
}

