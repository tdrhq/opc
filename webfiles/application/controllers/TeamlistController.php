<?



class TeamlistController extends Zend_Controller_Action { 

	
	public function indexAction() { 
		/* duh, do it untidyly */
		$db = contestDB::get_zend_db() ;
		$res = $db->select()->where("isadmin != ?", false)->
			order("teamname"); 

	}
}
