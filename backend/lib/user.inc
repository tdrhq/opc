<?

require_once "config.inc" ;
require_once "lib/db.inc" ; 

/**
 * A class used to represent a User.
 */
class User { 

  static  $tablename = "teaminfo" ;

  /**
   * uid
   */
  public $uid;

  /**
   * The username.
   * @deprecated 
   */
  public $_username ;

  /**
   * A boolean indicating whether the user is an admin.
   */
  public $_isadmin ; 

  /**
   * The users password. @private, @see matchPassword
   */
  private $_password; 

  /**
   * The dom object, maynot always be initialized. @private, 
   * @see getDOMDocument
   */
  private $_dom ; 
  
  /**
   * The XPath object, may not always be initialized. @private
   * @see getXPath
   */
  private $_xp ; 

  function __construct($user) { 
	$db = contestDB::get_zend_db() ;
	$s = $db->select()->from(User::$tablename)->where('teamname = ?', $user);
	$obj = $s->query()->fetch() ;

	if ( empty($obj) ) { 
	  throw new Exception("User does not exist") ;
	}
	$this->uid = $obj->uid;
	$this->_username = $obj->teamname; 
	$this->_isadmin = $obj->isadmin ;
	$this->_password = $obj->pwd; 
  }

  /**
   * Given a username, return a User object of it.
   * @param $user The username
   * @return an object of type User is successful, or NULL on failure.
   */
  static function factory($user) { 
	  try { 
		  return new User($user) ; 
	  } catch ( Exception $e) { 
		  return NULL ;
	  }
  }

  /**
   * Get the username corresponding to this User object.
   * @return string contianing username.
   */
  public function getUsername() { 
	return $this->_username; 
  }
  
  /**
   * Is the user an admin?
   * @return TRUE if the user is an admin.
   */
  public function isAdmin() { 
	return $this->_isadmin ; 
  }

  /**
   * Verify that password matches the password in the database.
   * @param $password The password to verify
   * @return TRUE if the password matches.
   */
  public function matchPassword($password) { 
	return $this->_password == $password ; 
  }

  /**
   * Set the password for this account.
   * @param $password The new password.
   */
  public function setPassword($password) { 
	$db = contestDB::get_zend_db() ;
	$user = $db->quote($this->getUsername());
	$db->update(self::$tablename, array("pwd" => "$password"), "teamname =$user");
  }

  /**
   * A one-time loading of all the XML file, DOM and XP.
   * @private
   * @see getXPath, getDOMDocument
   */
  private function _loadXML() { 
	if ( !empty($this->_xp)) return ; 
	$this->_dom = new DOMDocument() ; 
	$xmlfile = config::getFilename("data/users/{$this->_username}.xml") ; 
	if ( !is_file($xmlfile) ) { 
	  file_put_contents($xmlfile, "<user></user>", LOCK_EX) ;
	}
	$this->_dom->load($xmlfile);
	$this->_dom->formatOutput = TRUE ; 
	$this->_xp = new DOMXPath($this->_dom) ;
  }

  /**
   * Load the XML file if required, and return a DOMXPath object to it.
   * @return DOMXPath
   */
  public function getXPath() { 
	$this->_loadXML() ; 
	return $this->_xp ; 
  }

  /**
   * Load the XML file if required, and return a DOMDocument object to it.
   * @return DOMDocument.
   */
  public function getDOMDocument() { 
	 $this-> _loadXML();
	return $this->_dom ; 
  }

  /**
   * Save any changes made to the XML file (using XPath or DOMDocument) into
   * the correct locations. This does *NOT* save passwords, or isAdmin flags.
   */
  public function  save() { 
	$user = $this->getUsername() ;
	$file = config::getFilename("data/users/$user.xml") ;
	
	$ret = file_put_contents($file, $this->getDOMDocument()->saveXML(), LOCK_EX);
	return !empty($ret) ;
  }

  /**
   * Create a new user with the given username and password.
   * @param $user The required username
   * @param $password The required password.
   * @return true if user was created successfully, else it will return false.
   */
  static function create($user, $password) { 
	  $db = contestDB::get_zend_db() ; 
	  $res = $db->insert( self::$tablename,
						 array("teamname" => $user, "pwd" => $password) );
	  if (empty($res) or $res == 0 ) { 
		  return false ; 
	  }

	  file_put_contents(config::getFilename("data/users/$user.xml"),
						"<user></user>", LOCK_EX);
	  return true ;
  }
}



