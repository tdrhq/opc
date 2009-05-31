<?
require_once "DB.php";
require_once "Zend/Db.php" ;

/**
 * Database abstraction functions.
 *
 * @todo Unfortunate these functions don't do a very good job abstracting the 
 *    the PGsql interfaces. Rather they just make like simple and less 
 *    redundant. Hopefully in the future we will move to using PEAR database
 *    access techniques.
 * @author Arnold
 */
class contestDB {
  
  /**
   * The database access object. It gets initialized only after the first
   * database function is called, and remains initialized till the script
   * dies.
   */
  private static $pgdb = NULL ;  

  /**
   * Loads the database. 
   *
   * @return Nothing useful, don't rely on its return value.
   */
  static function get_database() {
    // currently I some code seems to be manually closing connections
    // so I just create new connection everytime
    if ( contestDB::$pgdb == NULL )
	  contestDB::$pgdb =  pg_connect( 
								  "host=" . config::$DB_Hostname . 
								  " dbname=" . config::$DB_Name .  
								  " user=" . config::$DB_User .
								  " password=" . config::$DB_Password ) 
	 or die("Unable to connect to database." );  

	contestDB::reconnect() ; 
	return NULL ;
	
  }

  /**
   * connects to the database. Same as contestDB::get_database() 
   */
  static function connect() {
	return contestDB::get_database(); 
  }

  /**
   * Reconnect? I have no clue. It seems to connect disregarding whether 
   * or not we already have an open connection.
   */
  static function reconnect() { 
	contestDB::$pgdb =  pg_connect( 
								   "host=" . config::$DB_Hostname . 
								   " dbname=" . config::$DB_Name .  
								   " user=" . config::$DB_User .
								   " password=" . config::$DB_Password,
								   PGSQL_CONNECT_FORCE_NEW) 
	  or die("Unable to connect to database." );  
  }

  /**
   * Close the existing database connection. Note that calling this function
   * is _NOT_ required.
   */
  static function close() {
   if ( contestDB::$pgdb ) {
	 pg_close(contestDB::$pgdb);
	 contestDB::$pgdb=NULL;
   } 
  }
 
  /**
   * Abstracts the pg_sql query interface.
   * 
   * Although this might seem unnecessary, it might be useful, esp 
   * for debugging, as we could output the queries we're making here
   * @param $str the query string
   * @return the query return object as return by pg_query
   */
  static function query( $str ) {
	assert(contestDB::$pgdb) ;
	$ret= pg_query($str) ;
	if ( ! $ret ) {
	  
	  throw new Exception ("SQL Query failed: " . $str);
	}
	return $ret;
  }
  /**
   * Return a dsn for PEAR::DB
   *
   */
  static function get_dsn ( ) {
	return array ( 
				  'phptype' => 'pgsql' ,
				  'username' => config::$DB_User , 
				  'password' => config::$DB_Password ,
				  'hostspec' => config::$DB_Hostname, 
				  'database' => config::$DB_Name ) ;
	
  }

  /**
   * Gets a dsn string usable with PEAR::DB
   * @return a DSN string.
   */
  static function get_dsn_string()  {
	return "pgsql://" . config::$DB_User . ":" 
	  . config::$DB_Password . "@" . config::$DB_Hostname 
	  . "/" .config::$DB_Name ;
  }

  /**
   * Why satisfy yourself with a dsn string. Get a PEAR::DB database
   * object.
   * @return PEAR::DB database object, pointing to the contest database.
   * @deprecated Use get_zend_db for Zend_Db object instead.
   */
  static function get_pear_db() {
	return DB::connect( contestDB::get_dsn_string() ) ;
  }

  /**
   * Get a Zend_Db object. 
   */
  static function get_zend_db() { 
	$ret = Zend_Db::factory('Pdo_Pgsql', 
							array('host' => config::$DB_Hostname,
								  'username' => config::$DB_User, 
								  'password' => config::$DB_Password,
								  'dbname' => config::$DB_Name)) ;
	$ret-> setFetchMode(Zend_Db::FETCH_OBJ);
	return $ret ; 
  }
} ; 


