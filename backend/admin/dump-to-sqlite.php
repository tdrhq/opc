#!/usr/bin/php
<?

require_once "../config.inc";
require_once "Zend/Loader.php";
require_once "lib/db.inc";

Zend_Loader::loadClass ("Zend_Db");
/* dump the current database into an sqlite3 with the schema.sqlite */

$db = Zend_Db::factory("Pdo_Sqlite", array ('dbname' => $argv[1]));
$old = contestDB::get_zend_db ();

$schema = file_get_contents ("../setup/schema.sqlite");

$db->getConnection()->exec ($schema);

$old->setFetchMode (Zend_Db::FETCH_ASSOC);
/* problemdata */
$res = $old->query ("select id,rowid,numcases,nickname,state,owner,submissionlimit,resourcelimits from problemdata");
$all = $res->fetchAll();

foreach ($all as $row) {
	$db->insert ("problemdata", $row);
}

$res = $old->query ("select username,isadmin,username as password from users");
$all = $res->fetchAll();

foreach ($all as $row) {
        $db->insert ("users", $row);
}

$res = $old->query ("select id,uid,problemid,owner,lang,state,score,time,notcounted,hash from submissionqueue");
$all = $res->fetchAll();

foreach ($all as $row) {
        $db->insert ("submissionqueue", $row);
}

