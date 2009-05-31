<?
include "../config.inc" ; 
include "lib/db.inc";


contestDB::connect() ;

$res = contestDB::query("select * from teaminfo where isadmin!=true or
isadmin is null" ) ;


while ( $obj = pg_fetch_object($res) ) {

  $team = $obj->teamname ;
  echo $team  ."\n"; 
 $oldtime = microtime(true) ;

        echo "Attempt $trials\n";
        $ret = contestDB::query("

        SELECT * FROM scoretable WHERE team = '$team' FOR UPDATE;
        UPDATE scoretable SET score = _score, time = _maxtime  FROM
        (SELECT sum(score) as _score,max(time) as _maxtime
        FROM ppst where team = '$team')
        AS team_score WHERE team = '$team' ;

        ");





                             

 }
