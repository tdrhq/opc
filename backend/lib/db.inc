<?
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


