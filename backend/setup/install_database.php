#!/usr/bin/php5
<?

chdir(dirname($argv[0])) ;
if ( $argv[1] == "--check" ) { 
  /* check to see if a database is already in place */ 
  require_once "../config.inc" ; 
  require_once "lib/db.inc" ;
  if ( config::$DB_Name == "" or config::$DB_Password == "" or
	   config::$DB_User == "" or config::$DB_Hostname == "" ) { 
	echo ("You have not provided complete database information. Edit ./local_config.inc\n") ;
	exit(1) ;
  }

  /* the next function call will cause the script to die with 0 return value
   if it fails */
  $db = contestDB::get_zend_db () ; 
  $db->getConnection();

  echo "Database Check successful\n";
  exit(0) ;
 }

system( "./install_database.php --check | grep -q 'Database Check successful'",
		$return_var);
if ( $return_var  == 0 ) { 
  echo "Database check successful.\n" ;
  exit (0) ;
 } else {
  echo "ERROR: Database check failed. Update ./local_config.inc\n" ;
  exit(1) ;
}
