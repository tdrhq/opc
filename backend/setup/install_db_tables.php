#!/usr/bin/php
<?
chdir(dirname(__FILE__));
require_once "../config.inc" ;
require_once "lib/db.inc" ;

if (strcasecmp(config::$DB_Adapter, "Pdo_Pgsql") == 0)
	$sql = file_get_contents("./schema.pg") ;
else if (strcasecmp(config::$DB_Adapter, "Pdo_Sqlite") == 0)
	$sql = file_get_contents("./schema.sqlite");
else if (strcasecmp(config::$DB_Adapter, "Pdo_Mysql") ==0)
	$sql = file_get_contents("./schema.mysql");
else {
	echo "Unable to recognize database adapter\n";
	exit (1);
}	

try {
	contestDB::get_zend_db()->getConnection()->exec("$sql");
} catch (Exception $e) {
	echo "Notice: database tables are already installed from previous make\n";
}

