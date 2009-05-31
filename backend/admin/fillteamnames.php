#!/usr/bin/php5 
<?

require_once "../config.inc" ;
require_once "lib/db.inc" ;

contestDB::connect() ;
$res = contestDB::query("select * from iddata") ;

while ( $obj = pg_fetch_object($res) ) {

	echo "$obj->team $obj->inst\n";
	contestDB::query("update teaminfo set inst='$obj->inst' where teamname='$obj->team'");
}
