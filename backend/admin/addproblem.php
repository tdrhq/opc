#!/usr/bin/php5
<?
 
/* read commandline options */
$namespace = "";
for ($i = 1; $i < $argc; $i++) {
	if ($argv[$i] == "--id")
		$id = $argv[++$i];
	else if ($argv[$i] == "--nick")
		$nick = $argv[++$i];
	else if ($argv[$i] == "--contest")
		$contest = $argv[++$i];
	else if ($argv[$i] == "--num-test-case")
		$numcases = $argv[++$i];
	else if ($argv[$i] == "--scores") {
		assert (!empty($numcases));
		for ($t = 0; $t < $numcases; $t++) {
			$scores [$t] = (int) $argv[++$i];
			assert(!empty($scores[$t]));
		}
	}
	else if ($argv[$i] == "--memory-limit")
		$memlim = $argv[++$i];
	else if ($argv[$i] == "--cpu-limit")
		$cpulim = $argv[++$i];
	else if ($argv[$i] == "--submission-limit")
		$sublim = $argv[++$i];
	else if ($argv[$i] == "--resource-limits") /* deprecated */
		$rlim = $argv[++$i];
	else if ($argv[$i] == "--checker")
		$checker = $argv[++$i];
	else if ($argv[$i] == "--only-update")
		$onlyupdate = true;
	else if ($argv[$i] == "--use-sample") 
		$namespace = "sample.";
	else if ($argv[$i] == "--force")
		$force_overwrite = true;
	else if (empty($archive) && substr ($argv[$i], 0, 1) != "-"){
		$archive = realpath ($argv[$i]);
	}
	else {
		echo "Unknown option: " . $argv[$i] . "\n";
		exit (1);
	}
		
}

chdir(dirname($argv[0]));


/* delete these functions if you are having compiler troubles*/
if ( ! function_exists("readline") )  {
  function readline($s) {
	echo $s ; 
	
	return trim(fgets(STDIN)) ;
  }
 }

if ( ! function_exists("readline_add_history") ) {
  function readline_add_history($s) {
	
  }
 }
/******/

function uncompress_archive ($file, $probid) {
	global $force_overwrite;

	$oldpwd = getcwd ();
	$file = realpath($file);

	chdir ("../data/problems/");
	if (empty($force_overwrite))
		$opt = "kxvzf";
	else 
		$opt = "xvzf";

	system ("tar $opt $file $probid/", $return_val);

	if ($return_val != 0) {
		echo "Error in extracting archive. Either the archive doesn't have $probid/ or perhaps you tried installing the problem before? Use --force in that case to overwrite.\n";
		exit (1);
	}
	
	if (!is_file ("$probid/problem.html")) {
		echo "ERROR: problem.html problem description missing in archive.\n";
		exit (1);
	}
	copy ("$probid/problem.html", "$probid.html");

	chdir ($oldpwd);
}

require_once "../config.inc" ;
require_once "lib/db.inc" ;

ob_implicit_flush(true);

$dom = new DOMDocument("1.0", "UTF-8") ;
$dom->formatOutput = TRUE ; 
$root = $dom->createElement("problem") ;
$dom->appendChild($root) ;

if (empty($id))
	$id = readline( "Enter a unique problem ID: " ) ;

$element = $dom->createComment("id is used for informative purposes only, "
 . "and does not overload the id stored in the database or as the current "
							   . "filename.") ;
$root->appendChild($element) ;
$element = $dom->createElement("id", $id) ;
$root->appendChild($element) ;

if (!empty($archive)) {
	uncompress_archive ($archive, $id);
}

if ( !is_file(get_file_name("data/problems/$id.html") ) ){ 
  echo "data/problems/$id.html does not exist. Aborting right now.\n" ;
  exit(1) ;
 }

if (empty($nick))
	$nick = readline( "Enter a nickname for the problem: " ) ;

$element = $dom->createComment(
			"nickname is also stored for informative purposes only.");
$root->appendChild($element);

$element = $dom->createElement("nick", $nick) ;
$root->appendChild($element) ;

if (empty($contest))
	$contest = readline( "Enter a contest. Leave as blank if you want to add it 'general', or if you're not running in multicontest mode. ");

if (empty($contest) ) $contest = 'general' ;   
require_once "lib/contest.inc";
$c = Contest::factory($contest);
if ( empty($c) ) { 
	die("That contest does not exist.\n");
}

if (empty($numcases)) 
	$numcases = 0 + readline("Number of testcases: ") ;


echo "I'm going to ask you details for each test case now. " ;


for( $i = 0 ; $i < $numcases ; $i ++ ) {
  echo "Test Case #$i: \n" ;

  $testcase = $dom->createElement("test") ;

  $def = "data/problems/$id/$namespace$i.in" ;
  if ( !is_file(get_file_name($def)) ) 
	  die("$def: not found\n");

  $element = $dom->createElement("inputpath", $def) ;
  $testcase->appendChild($element) ;


  $def = "data/problems/$id/$namespace$i.out" ; 
  if ( !is_file(get_file_name($def)) ) 
	  die("$def: not found\n");

  $element = $dom->createElement("outputpath", $def) ;
  $testcase->appendChild($element) ;

  if (empty($scores[$i])) {
	  echo "Score for test case $i? : ";
	  fscanf(STDIN,"%d\n", $sc ) ;
  } else
	  $sc = $scores[$i];

  $element = $dom->createElement("score", $sc) ;
  $testcase ->appendChild($element) ;

  $root->appendChild($testcase);

 }

if (empty($sublim)) $sublim = 10000;

$res = $dom->createElement("resourcelimits") ;
$root->appendChild($res) ;

if (empty($memlim)) {
	echo "What is the memory usage limit for a submission? [e.g 16M, 100k]:" ;
	$memlim = trim(fgets(STDIN)); 
}

$element = $dom->createElement("memory", $memlim) ;
$res->appendChild($element) ;

if (empty($cpulim)) {
	echo "How much CPU time is a submission allowed [in seconds, decimal points allowd]?:" ; 
	$cpulim = trim(fgets(STDIN)) ;
}

$element = $dom->createElement("runtime", $cpulim) ;
$res->appendChild($element) ;

$element = $dom->createElement("output", "") ;
$res->appendChild($element) ;


if (!empty($checker)) {
	$element = $dom->createElement ("checker", $checker);
	$root->appendChild ($element);
}

if (empty($rlim)) $rlim = "";
$element = $dom->createElement("resourcelimits_string", $rlim) ;
$root->appendChild($element) ;

file_put_contents(get_file_name("data/problems/" . $id . ".xml" ),
				  $dom->saveXML());
chmod(get_file_name("data/problems/$id.xml"), 0755) ;

echo "-----LOG-----\n" ;
echo $dom->saveXML();

$db = contestDB::get_zend_db() ;

$id=pg_escape_string($id);
$nick=pg_escape_string($nick);
$rlim=pg_escape_string($rlim);

if (empty($onlyupdate)) {
	$sql = "insert into problemdata (id,numcases,nickname,state,submissionlimit,owner) values 
('$id',$numcases,'$nick','ok',$sublim,'$contest')" ;
} else {
	$sql = "update problemdata set numcases=$numcases,nickname='$nick',
state='ok',submissionlimit=$sublim,owner='$contest' where id='$id'";

}

	echo "DEBUG: ". $sql . "\n"  ;
echo "\n\n";
$db->query($sql ) ;



echo "Done, copy your problem statement to " . config::$problems_directory 
. "/$id.{html|pdf|ps}. Note that html file should contain only the body "
. "part!\n";


?>



