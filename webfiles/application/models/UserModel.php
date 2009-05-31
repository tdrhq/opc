<?

require_once "lib/user.inc" ;

/**
 * The User class in the backend API, does not handle information regarding
 * the user that should not affect the backend. This is information like
 * members, email ids, contacts and other meta information. However the backend
 * does keep a convenient XML file for each user, which the frontend is allowed
 * to use in whatever way it wants -- which is what this does.
 */
class UserModel { 
	public function createRow($user)  
	{
		User::create($user, "") ;
		$ret = User::factory($user) ;
		return $ret ; 
	}

	public function getRow($user) 
	{ 
		return User::factory($user) ;
	}
	public function getUserList()
	{
		$db = contestDB::get_zend_db() ;
		$res = $db->select()->from("teaminfo")->query()->fetchAll() ;
		$ret = array() ;
		foreach( $res as $elem) {
			array_push($ret, $elem->teamname);
		}
		return $ret;
	}
	public function getInstitute($obj) 
	{ 
		return $this->getField($obj, "institute");
	}

	public function setInstitute($obj, $inst) 
	{ 
		$this->setField($obj, "institute", $inst);
	}
	
	public function setTimezone($obj, $tz) 
	{
		$this->setField($obj, "timezone", $tz);
	}

	public function getTimezone($obj) 
	{
		return $this->getField($obj, "timezone");
	}

	public function getField($obj, $field) 
	{
		$ret = $obj->getXPath()->query("/user/$field") ;
		if ( $ret->length > 0 ) 
			return $ret->item(0)->nodeValue ;
		return "" ; 
	}
	public function setField($obj, $field, $value) 
	{
		$ret = $obj->getXPath()->query("/user/$field");
		if ( $ret->length == 0 ) {
			$node = $obj->getDOMDocument()->createElement($field, $value);
			$obj->getXPath()->query("/user")->item(0)
				->appendChild($node);
			$obj->save() ;
			return ;
		}
		$ret->item(0)->nodeValue = $value ;
		$obj->save() ;
	}
	public function getCountry($obj) 
	{ 
		return $this->getField($obj, "country");
	}

	public function setCountry($obj, $value) 
	{ 
		$this->setField($obj, "country", $value);
	}
	public function getRegistrationTime($obj) 
	{ 
		return $this->getField($obj, "regtime");
	}

	public function setRegistrationTime($obj, $value) 
	{ 
		$this->setField($obj, "regtime");
	}

	public function getMember ($obj, $num) 
	{ 
		$ret = new stdClass () ;
		$ret->name = "" ; 
		$ret->email = "" ; 

		$memnode = $obj->getXPath()->query("/user/member") ;
		if ( $memnode->length <= $num ) { 
			return $ret ;
		}

		$ret->name = $obj->getXPath()->query("name", $memnode->item($num))->item(0)->nodeValue ; 
		$ret->email = $obj->getXPath()->query("email", $memnode->item($num))->item(0)->nodeValue ;

		return $ret ;
	}
		
	public function addMember($obj ) 
	{ 
		$node = $obj->getDOMDocument()->createElement("member") ;
		$subnode = $obj->getDOMDocument()->createElement("name") ;
		$node->appendChild($subnode) ;

		$subnode = $obj->getDOMDocument()->createElement("email") ;
		$node->appendChild($subnode) ;

		$obj->getXPath()->query("/user")->item(0)->appendChild($node);
	}

	public function setMember($obj, $num, $member) 
	{ 
		do { 
			$ret = $obj->getXPath()->query("/user/member") ;
			if ( $ret->length <= $num ) $this->addMember($obj); 
			else break ;
		} while (true) ;
		
		$node = $ret->item($num) ;

		$n1 = $obj->getXPath()->query("name", $node)->item(0); 
		$n1->nodeValue = $member->name; 
		$obj->getXPath()->query("email", $node)->item(0)->nodeValue = 
			$member->email ; 
		
		$obj->save() ;
	}

	public function getMemberCount($obj) 
	{
		$res = $obj->getXPath()->query("/user/member");
		return $res->length;
	}

	
}
