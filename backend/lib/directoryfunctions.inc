<?
//Creating directories for the contest
function createdirectories()
{
  global $dbconn,$programs_dir;
  $listofteamobjects = pg_query("select * from regdata");
  while ($object = pg_fetch_object($listofteamobjects))
    {
      recreatedirectory($object->team);
    }
  $listofadminobjects = pg_query("select * from admindata");
  while ($object = pg_fetch_object($listofadminobjects))
    {
      recreatedirectory($object->login);
    }
}

function createdirectory($name)
{
  global $dbconn,$programs_dir;
  $newdirectory = $programs_dir.$name;
  system("mkdir $newdirectory");
  system("chmod a+w $newdirectory");
  echo("mkdir $newdirectory<br>\n");
  echo("chmod a+w $newdirectory<br>\n");
  echo ("Adding programs directory of $name<br>\n");
}

function createoutputfiles($name)
{
  global $nproblems,$submission_limit;
  echo "Creating output files for $name<br>";
  global $dbconn,$programs_dir;
  $thedirectory = $programs_dir.$name."/";
  for($i=1;$i<=$nproblems;$i++)
    {
      for($j=1;$j<=$submission_limit;$j++)
	{
	  $filename = $thedirectory."$i.$j.out";
	  echo "Creating $filename";
	  system("touch $filename");
	  system("chmod a+w $filename");
	}
    }
}

function createalloutputfiles()
{
  global $dbconn,$programs_dir;
  $listofteamobjects = pg_query("select * from regdata");
  while ($object = pg_fetch_object($listofteamobjects))
    {
      createoutputfiles($object->team);
    }
  $listofadminobjects = pg_query("select * from admindata");
  while ($object = pg_fetch_object($listofadminobjects))
    {
      createoutputfiles($object->login);
    }
}

function removedirectory($name)
{
  global $dbconn,$programs_dir;
  $toberemoved=$programs_dir.$name;
  $systemout = system("rm -rf $toberemoved",$systemreturn);
  echo("rm -rf $toberemoved<br>\n");
  echo ("Removing programs of $name<br>\n");
  if($systemreturn)
    {
      echo "Failed";
    }
  else
    {
      echo "Done";
    }
}

function recreatedirectory($name)
{
  global $dbconn,$programs_dir;
  removedirectory($name);
  createdirectory($name);
}

?>