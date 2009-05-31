#!/usr/bin/php5
<?
 
chdir( dirname($argv[0]) );

require_once "../config.inc" ;
require_once "lib/db.inc" ;

ob_implicit_flush(true);


$user = $argv[1]; 
$prob = $argv[2];

$db = contestDB::get_zend_db() ;


$tmp = $db->select()->from('submissionqueue', 'max(id)') ->where('team = ?', $user)
	->where('problemid = ?', $prob) -> where ("state <> 'Compile Error'")->where("state <> 'waiting'")
->query()->fetch();

$id = $tmp->max;

if ( empty($id)) { 
	printf ("%-15sNo valid submission\t  0\n", $user);
	exit(0);
}

require_once "lib/submissions.inc"; 

$sub = SubmissionTable::get_submission($id);

//print_r($sub);

$dom = new DomDocument() ;
$dom->load($sub->getPathToResult());
$xp  = new DOMXPath($dom) ;

printf  ("%-15s", $user);


$score = 0.0; 
$testar = $xp->query("/judge/testcase");
foreach ($testar as $test) { 
	$exec_status = $xp->query("exec/status", $test)->item(0)->nodeValue;
	if ( $exec_status != "success" ) { 
		if ( $exec_status == "TLE" )
			echo "T" ; 
		else 
			echo "E" ; 
	} else { 
		$res = $xp->query("check/status", $test)->item(0)->nodeValue;

		$m = $xp->query("check/score", $test);
		if ( $m->length ) { 
			$ress = $m->item(0)->nodeValue ;
			if ( $ress == "5" ) 
				echo "Y" ;
			else if ( $ress == "1" )
				echo "P"; 
			else if ( $res != "success" ) 
				echo "N" ;
			else echo "?" ;
			
			$ress = (double) $ress ;
			$score += $ress/10;	

		}
		else {  
			if ( $res != "success" ) 
				echo "N" ;
			else { 
				echo "Y";
				$score += 1.0; 
			}
		}
	}
	echo " ";
}

printf ("\t%3s\n", $score);

