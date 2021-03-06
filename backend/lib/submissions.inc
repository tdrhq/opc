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
 * @file   submissions.inc
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once  (dirname(__FILE__) . "/../config.inc" );
require_once  "lib/db.inc" ;
require_once "lib/user.inc" ;

/**
 * Represents a single submission.
 */
class Submission 
{ 
	/**
	 * The submission id.
	 */
	public $id; 
	
	/**
	 * The user who owns this submission.
	 * @deprated
	 */
	public $user; 
	
	/**
	 * The user uid who owns this submission
	 */
	public $uid;
	
	/**
	 * The user/team who owns the submission. Should always be same as
	 * $user. 
	 * @deprecated Use $user instead.
	 */
	public $team; 
	
	/**
	 * The problem id this submission was submitted against.
	 */
	public $problemid; 
	
	/**
	 * The programming language of this submission. 
	 */
	public $lang; 
	
	/**
	 * The state of this submission. 
	 * This records the state of the submission when the submission was queried.
	 */
	public $state; 
	
	/**
	 * The set score of the submission.
	 */
	public $score; 
	
	/**
	 * The time at which the submission was made. This is PHP time, and not 
	 * necessarily a human readable time. However its best to assume that it
	 * can be anything.
	 * @warning Changes to this variable will not be recorded when using
	 * commitChanges()
	 */
	public $time;
	
	/**
	 * The path to the result file.
	 * @deprecated This is now redundant since the pathtoresult can be computed
	 * from the id of the code. @see getPathToResult() 
	 */
	public $pathtoresult; 
	
	/**
	 * A boolean value indicating whether this submission is counted toward the
	 * the ranklist (whatever that may be) in the frontend. Typically can be
	 * used to invalidate a users submission, without affecting his score or
	 * submission counters.
	 */
	public $notcounted; 
	
	/**
	 * A md5sum hash of the code. Note that this is allowed to be empty, it may
	 * be used if the frontend wants to prevent multiple submissions to the
	 * same problem.
	 */
	public $hash;
	
	/**
	 * The owner (contest/realm) of the submission 
	 */
	public $owner; 
	
	/**
	 * Accessor functions
	 */
	
	public function getId() 
	{ 
		return $this->id; 
	}
	
	public function getUser() 
	{ 
		return $this->user; 
	}

	public function getProblemId() 
	{ 
		return $this->problemid; 
	}

	public static function getPathToCodeFromId ($id)
	{
		$id = (int) $id;
		$dirnum = intval ($id / 1000);
		$filenum = $id % 1000;

		$dir = get_file_name ("data/uploads/$dirnum");
		if (!is_dir ($dir)) {
			mkdir ($dir);
			chmod ($dir, 0777);
		}

		$filename = get_file_name (sprintf ("data/uploads/%d/%03d", $dirnum, $filenum));
		return $filename;
	}

	public function getPathToCode() 
	{ 
		if (!empty($this->pathtocode)) 
			return config::getFilename($this->pathtocode); 
		
		return self::getPathToCodeFromId ($this->id);
	}

	public function getLang() 
	{ 
		return $this->lang; 
	}

	public function getScore() 
	{ 
		return $this->score; 
	}
	
	public function getTime() 
	{ 
		return $this->time; 
	}
	
	public function getPathToResult() 
	{ 
		return config::getFilename("data/results/{$this->id}.xml");
	}
	
	public function getIsCounted() 
	{ 
		return !empty($this->notcounted); 
	}
	
	public function getHash() 
	{ 
		return $this->hash; 
	}
	public function getOwner() 
	{ 
		return $this->owner;
	}
	public function getXmlFile() 
	{ 
		return config::getFilename("data/results/{$this->id}.xml");
	}
	/**
	 * construct this object from another object which looks like the database
	 * contents
	 * @param object, which is return value from pg_get_obj or suitable database
	 * query.
	 */
	function __construct($obj) 
	{ 
		if (empty($obj)) throw new Exception ("shit");
		$this->id = $obj->id; 
		$this->team = $this->user = $this->uid = $obj->uid;
		$this->problemid = $obj->problemid; 
		$this->lang = $obj->lang; 
		$this->state = $obj->state; 
		$this->score = $obj->score;
		$this->pathtoresult = $this->getPathToResult(); 
		$this->pathtocode = $this->getPathToCode();
		$this->notcounted = $obj->notcounted; 
		$this->hash = $obj->hash; 
	}
	
	function commitChanges() 
	{ 
		if (empty($this->id)) throw Exception ("Bad");
		$db = contestDB::get_zend_db(); 
		$data = array(
			"uid" => new Zend_Db_Expr($this->uid),
			"problemid" => $this->getProblemId() ,
			"lang" => $this->getLang(), 
			"state" => $this->state,
			"score" => new Zend_Db_Expr ($this->score), 
			"hash" => "{$this->hash}");
		if ($this->notcounted) 
			$data["notcounted"] = true;
		
		if ($this->getOwner()) 
			$data['owner'] = $this->getOwner(); 
		
		$where = array("id = {$this->id}" );
		
		$db->update(SubmissionTable::tablename, $data, $where);
	}

	function validateResultXML ()
	{
		$xml = get_file_name ("data/results/{$this->id}.xml");
		$dom = new DomDocument;
		$dom->load ($xml);
		return $dom->relaxNGValidate (get_file_name ("schema/result.rng"));
	}

}

/**
 * Every submission is known by a submission ID 
 * 
 * Submission ID is assumed to be auto generated by the database
 * see docs/db for details.
 */

class SubmissionTable 
{
	
	/**
	 * The tablename used for all submission accesses.
	 */
	const tablename='submissionqueue';
	
	/**
	 * This is the id of the postgresql serial that allows us to keep track
	 * of the current submission id. 
	 * @see add_to_queue() 
	 */
	const serial   ='submissionqueue_id'; 
	
	/**
	 * Add a submission to queue
	 *
	 * @param $user (string) Team/User name
	 * @param $problemid (string) problem ID
	 * @param $lang (string) language
	 *
	 * @return (class Submission) A submission class.
	 */
	public static function add_to_queue ($uid, $problemid, $lang) 
	{
		$db = contestDB::get_zend_db(); 
		$isadm = User::factory($uid)->isAdmin();
		
		if (!is_numeric($uid)) 
			throw new Exception ("uid must be numeric");
		
		$data = array();
		if ($uid) $data['uid'] = new Zend_Db_Expr ($uid); 
		if ($problemid) $data['problemid'] = $problemid; 
		if ($lang) $data['lang'] = $lang; 
		$data['state'] = 'processing'; 
		$data['score'] = 0;
		
		$res = $db->insert(SubmissionTable::tablename, $data);
		
		$id = $db->lastInsertId();
		if (empty($id)) {
			/* probably running on Postgres */
			$id = $db->lastSequenceId("submissionqueue_id");
		}
		return self::get_submission($id);
	}
	
	/**
	 * Get submissions from the queue
	 * 
	 * @param $id The numeric submission ID
	 * @return  Returns and object with the data
	 */
	public static function get_submission($id) 
	{
		$id = (int) $id; 
		$db = contestDB::get_zend_db();
		$res = $db->select()
			->from(SubmissionTable::tablename)
			->where("id = ?", $id)->query();
		
		$obj = $res->fetch ();
		if (empty($obj)) return NULL; 
		return new Submission($obj) ;
	}
	
	public static function get_submission_xml_file($id) 
	{ 
		return realpath(get_file_name("data/uploads/$id.xml"));
	}

	/**
	 * Get a list of waiting submissions, in a way decided ``nice''
	 * for the queue-manager
	 *
	 * @param $limit (integer) number of elements to get
	 */
	public static function get_waiting_queue($limit) 
	{
		$db = contestDB::get_zend_db ();  
		$stmt = $db->select()
			->from(SubmissionTable::tablename, 'id')
			->where ("state='waiting'");
		if ($limit != 0) $stmt = $stmt->limit ($limit);
		$stmt->order ('id asc');
		$res = $stmt->query ();

		if (! $res )
			throw new Exception("Unable to get waiting queue");
		
		$arr = $res->fetchAll ();
		$ret = array ();
		foreach ($arr as $item) 
		{
			array_push ($ret, $item->id);
		}
		return $ret;
	}
	
	/**
	 * Set the submission state. A submission state is typical of the form
	 * "Waiting", "Queued", "Accepted", "Wrong Answer" etc., however you are 
	 * allowed to set any string. 
	 * @param $id The numeric submission id.
	 * @param $state the new state of the submission.
	 * @param $oldstate The oldstate of the submission. If $oldstate is not NULL
	 *        then the the state of the submission will be changed only if the
	 *        the state was $oldstate previously.
	 * @return TRUE if the state was changed. FALSE on error and/or the previous
	 *        state was not $oldstate.
	 */
	public static function set_state($id, $state, $oldstate = NULL) 
	{
		assert (!is_numeric($state));
		$db = contestDB::get_zend_db ();
		/* WARNING TODO: $state isn't quoted */
		$id = $db->quote ($id, 'INTEGER');
		$where = array ("id=$id");
		if (!empty($oldstate)) $where [] = 'state=' . $db->quote ($oldstate);
		
		$n = $db->update (SubmissionTable::tablename, array('state' => $state),
				  $where);
		return ($n > 0);
	}
	
	/**
	 * Get the state of the submission.
	 * @param $id the id
	 * @return the state
	 */
	public static function get_state($id) 
	{ 
		$db = contestDB::get_zend_db ();
		$res = $db->select()->from(SubmissionTable::tablename)->query();
		$obj = $res->fetch();
		if (empty($obj)) return "";
		else return $obj->state;
	}  
	/**
	 * Only sets the score in the queue database.
	 * Does NOT update the score in the caches/team results
	 * or for that matter any other database.
	 *
	 * @param $id integer the submission id
	 * @param $score integer the new score
	 */
	public static function set_score ($id, $score) 
	{
		$db = contestDB::get_zend_db ();
		$id = $db->quote($id, 'INTEGER');
		
		return $db->update (SubmissionTable::tablename, array('score' => $db->quote ($score, 'INTEGER')), array("id=$id"));
	}
	
	/**
	 * Attempts to find duplicate submissions. Seaches earlier submissions
	 * "hash" field to see if there exists a submission with the given $hash.
	 * @param $team Restrict the search to submissions by this team.
	 * @param $probid Restrict the search to submissions to this problem.
	 * @param $hash The hash of the file to search for.
	 * @return TRUE if and only if the hash was found.
	 */
	public static function exists_submission_by_hash($uid,$probid,$hash) 
	{
		$db = contestDB::get_zend_db();
		$res = $db->select()
			->from(SubmissionTable::tablename)
			->where('hash = ?', $hash);
		if (!empty($team))
			$res = $res->where('team = ?', new Zend_Db_Expr($team))
				->where('problemid=?', $probid);
		$res = $res->query()->fetch ();
		return (!empty($res));
	}
	
	/**
	 * Gets a list of elements in the queue. Note that this doesn't restrict
	 * the elements to "Waiting" or "Queue"d elements.
	 * @param $offset (Integer) the starting point to get the queue from. 
	 *        Note that this need not always be the Submission id.
	 * @param $limit (integer) the number of elements to fetch. 
	 * @param $user (string) limit the searches to a particular user
	 *        if !empty($user). Default value is "".
	 * @return list of submission objects.
	 */
	public static function get_queue ($offset, $limit, $user='', $owner=NULL) 
	{
		$db = contestDB::get_zend_db();
		$query = $db->select() 
			->from(self::tablename)
			->join("users", "submissionqueue.uid = users.uid") 
			->limit($limit, $offset); 
		
		if (!empty($user) && !is_numeric($user)) 
			$query = $query->where('username = ?', $user);
		if (!empty($user) && is_numeric($user)) 
			$query = $query->where('submissionqueue.uid = ?', new Zend_Db_Expr ($user));
		if (!empty($owner)) 
			$query = $query->where('owner = ?', $owner);
		
		$res = $query->order("id DESC")->query();
		
		if (empty($res))
			throw new Exception("Unable to get queue");
		
		return $res->fetchAll();
	}
	
	/**
	 * Get a count of number of objects that matches the given criteria.
	 * @param $uid user id to match.
	 * @param $problemid problemid to match.
	 * @param $counted if $counted is true, then only problems that have
	 *        "noncounted" field to true are matched.
	 * @return integer signifying the number of elements.
	 */
	public static function get_count(
		$uid = NULL,
		$problemid = NULL,
		$counted = NULL) 
	{ 
		$db = contestDB::get_zend_db(); 
		$query = $db->select()->from(self::tablename, array("res" => "COUNT(*)"));
		
		if (!empty($uid) && is_numeric($uid)) $query = $query->where('uid = ?', new Zend_Db_Expr($uid));
		if (!empty($problemid))
			$query = $query->where('problemid = ?', $problemid);

		if (!empty($counted))
			$query = $query->where('notcounted = ?', $counted);

		$ret = $query -> query(); 
		$res = $ret->fetch(); 
		return $res->res; 
	}
}

