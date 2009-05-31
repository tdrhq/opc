#!/usr/bin/php5

<?
 
chdir( dirname($argv[0]) );

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

require_once "../config.inc" ;
require_once "lib/db.inc" ;

ob_implicit_flush(true);

$dom = new DOMDocument("1.0", "UTF-8") ;
$dom->formatOutput = TRUE ; 
$root = $dom->createElement("problem") ;
$dom->appendChild($root) ;

echo "Please ensure you've created the necessary testcases and\n " ;
echo "outputfiles for the problem before running this\n" ;

$id = readline( "Enter the problem ID(need not be an integer, but you may keep it so):  " ) ;

readline_add_history($id) ;
$element = $dom->createComment("id is used for informative purposes only, "
 . "and does not overload the id stored in the database or as the current "
							   . "filename.") ;
$root->appendChild($element) ;
$element = $dom->createElement("id", $id) ;
$root->appendChild($element) ;


if ( !is_file(get_file_name("data/problems/$id.html") ) ){ 
  echo "data/problems/$id.html does not exist. Aborting right now.\n" ;
  exit(1) ;
 }
$nick = readline( "Enter a nickname for the problem: " ) ;

$element = $dom->createComment(
			"nickname is also stored for informative purposes only.");
$root->appendChild($element);

$element = $dom->createElement("nick", $nick) ;
$root->appendChild($element) ;
readline_add_history($nick) ;

$contest = readline( "Enter a contest. Leave as blank if you want to add it 'general', or if you're not running in multicontest mode. ");
if ( empty($contest) ) $contest = 'general' ;   
require_once "lib/contest.inc";
$c = Contest::factory($contest);
if ( empty($c) ) { 
	die("That contest does not exist.\n");
}

$numcases = 0 + readline("Number of testcases: ") ;

readline_add_history($numcases) ; 

echo "I'm going to ask you details for each test case now. " ;


for( $i = 0 ; $i < $numcases ; $i ++ ) {
  echo "Test Case #$i: \n" ;

  $testcase = $dom->createElement("test") ;

  $def = "data/problems/$id/$i.in" ;
  if ( !is_file(get_file_name($def)) ) $def = "no-file-found" ;
  echo "Path to input data [$def]. "; 
  $element = $dom->createElement("inputpath", $def) ;
  $testcase->appendChild($element) ;


  $def = "data/problems/$id/$i.out" ; 
  if ( !is_file(get_file_name($def)) ) $def = "no-file-found" ;
  echo "Path to output data [$def]. " ;
  $element = $dom->createElement("outputpath", $def) ;
  $testcase->appendChild($element) ;

  echo "And score for this test case? : ";
  fscanf(STDIN,"%d\n", $sc ) ;
  $element = $dom->createElement("score", $sc) ;
  $testcase ->appendChild($element) ;

  $root->appendChild($testcase);

 }
echo "What is the submission limit for this problem?:" ;
$sublim = trim(fgets(STDIN)) ;

$res = $dom->createElement("resourcelimits") ;
$root->appendChild($res) ;

echo "What is the memory usage limit for a submission? [e.g 16M, 100k]:" ;
$memlim = trim(fgets(STDIN)) ; 
$element = $dom->createElement("memory", $memlim) ;
$res->appendChild($element) ;

echo "How much CPU time is a submission allowed [in seconds, decimal points allowd]?:" ; 
$cpulim = trim(fgets(STDIN)) ;
$element = $dom->createElement("runtime", $cpulim) ;
$res->appendChild($element) ;

$element = $dom->createElement("output", "") ;
$res->appendChild($element) ;

echo "\nAdditional resourcelimits: This is actually deprecated, but can be used
for fine grained control over some advanced resouce limits. A typical usage
would be:
    mem=64M stack=8M fsize=2000000 time=3
(Note that fsize should be specified in bytes!)
However in the event that a limit is overridden specifically in the XML file, 
the overriden value will take precedence.

Enter a resource limit string: ";

$rlim = trim(fgets(STDIN)); 
$element = $dom->createElement("resourcelimits_string", $rlim) ;
$root->appendChild($element) ;

file_put_contents(get_file_name("data/problems/" . $id . ".xml" ),
				  $dom->saveXML());
chmod(get_file_name("data/problems/$id.xml"), 0755) ;

echo "-----LOG-----\n" ;
echo $dom->saveXML();

contestDB::connect() ;

$id=pg_escape_string($id);
$nick=pg_escape_string($nick);
$rlim=pg_escape_string($lim);

$sql = "insert into problemdata (id,numcases,nickname,state,submissionlimit,owner) values 
('$id',$numcases,'$nick','ok',$sublim,'$contest')" ;

echo "DEBUG: ". $sql . "\n"  ;
echo "\n\n";
contestDB::query($sql ) ;



echo "Done, copy your problem statement to " . config::$problems_directory 
. "/$id.{html|pdf|ps}. Note that html file should contain only the body "
. "part!\n";


?>



