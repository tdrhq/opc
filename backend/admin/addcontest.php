#!/usr/bin/php5
<?
 
/* read commandline options */
$namespace = "";
for ($i = 1; $i < $argc; $i++) {
	if ($argv[$i] == "--id")
		$id = $argv[++$i];
	else if ($argv[$i] == "--name")
		$name = $argv[++$i];
	else if ($argv[$i] == "--start-time")
		$start_time = $argv[++$i];
	else if ($argv[$i] == "--duration")
		$duration = $argv[++$i];
	else if ($argv[$i] == "--end-time")
		$end_time = $argv[++$i];
	else if ($argv[$i] == "--help") {
		display_help ();
		exit (1);
	}
	else {
		echo "Unknown option: " . $argv[$i] . "\n";
		exit (1);
	}
		
}

/* quick sanity checks */
if(!empty ($contest_length) && !empty($end_time)) {
	echo "Use only one of --duration or --end-time\n";
	exit (1);
}



function display_help ()
{
	
}

chdir(dirname($argv[0]));

require_once "../config.inc" ;
require_once "lib/db.inc" ;

ob_implicit_flush(true);

$dom = new DOMDocument("1.0", "UTF-8") ;
$dom->formatOutput = TRUE ; 
$root = $dom->createElement("contest") ;
$dom->appendChild($root) ;

$xsdformat = "%Y-%m-%dT%T";
if (!empty($start_time)) {
	$unix = strtotime ($start_time);
	$startTime = $dom->createElement ("contestTime", strftime ($xsdformat, $unix));
	$root->appendChild($startTime);

	if (!empty($duration))
		$unix = strtotime ($duration, strtotime ($start_time));
	else if (!empty($end_time))
		$unix = strtotime ($end_time);
	else {
		echo "You have to specify an end time or duration.\n";
		exit (1);
	}

	$endTime = $dom->createElement ("contestEndTime", strftime ($xsdformat, $unix));
	
	$root->appendChild ($endTime);
}


if (empty($name)) $name = "Unnamed contest";
$e = $dom->createElement ("name", $name);
$root->appendChild ($e);

$frontend = $dom->createElement ("frontend");
$root->appendChild ($frontend);

$home = $dom->createElement ("page", "Home");
$frontend->appendChild ($home);

$home->setAttribute ("id", "home");
$home->setAttribute ("href", "general/home.html");


file_put_contents (get_file_name ("data/contests/$id.xml"), $dom->saveXML ());
chmod(get_file_name("data/contests/$id.xml"), 0755) ;

echo "-----LOG-----\n" ;
echo $dom->saveXML();

echo "\nJust verify that the timestamps have been correctly parsed.\n";



