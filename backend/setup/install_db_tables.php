#!/usr/bin/php
<?
chdir(dirname(__FILE__));
require_once "../config.inc" ;
require_once "lib/db.inc" ;

$sql = file_get_contents("../database") ;
echo $sql ;

contestDB::get_zend_db()->getConnection()->exec("$sql");


