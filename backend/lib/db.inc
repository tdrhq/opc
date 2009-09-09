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
 * @file   db.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */
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


