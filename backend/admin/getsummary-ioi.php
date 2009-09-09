#!/usr/bin/env php
<?php
/**
 * Copyright 2007-2009 Chennai Mathematical Institute
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file   getsummary-ioi.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */
 
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

