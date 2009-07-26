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

  static  $db;
  /**
   * Get a Zend_Db object. 
   */
  static function get_zend_db() { 
	if (!empty(self::$db)) return self::$db;
	self::$db = Zend_Db::factory(config::$DB_Adapter, 
							array('host' => config::$DB_Hostname,
								  'username' => config::$DB_User, 
								  'password' => config::$DB_Password,
								  'dbname' => config::$DB_Name)) ;
	self::$db-> setFetchMode(Zend_Db::FETCH_OBJ);
	return self::$db ; 
  }
} ; 


