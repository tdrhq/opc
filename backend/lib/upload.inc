<?

include_once dirname(__FILE__) . "/../config.inc" ;
include_once "lib/submissions.inc" ;
include_once "lib/problems.inc";
/**
 * This class simulates the process of uploading a solution.
 */
class UploadSubmission { 
	
	/**
	 * Upload a submission.
	 * Note that while this function does most sanity checks, there are some
	 * crucial tests that need to be done before calling this (this is to
	 * keep the distinction of the backend and frontend clean. In 
	 * particular the  frontend needs to check the following:
	 *  - That the user ($user) is logged in and authenticated. 
	 *  - That $lang lies in one of the languages chosen to be allowed at 
	 *    the frontend level. For instance, the backend may support python
	 *    and perl, but you may not want the frontend to allow a submission
	 *     to those languages for whatever reason.
	 *  - That the file size is under sane limits. (Unless the problem 
	 *    resource
	 *    limits itself specifies a file size, in which case that will be 
	 *    checked here.
	 * 
	 * @param $user The username under which this submission is to be made.
	 *        This has to be a valid registered user.
	 * @param $prob The problem code to which the submission is being made.
	 * @param $lang the programming language used.
	 * @param $source_file path to a temporary file containing the source 
	 *        code.
	 * @param $owner realm under which this is uploaded. Usually refers to the
	 *        contest.
	 * @return if successful, it will return a positive integer indicating
	 *        the submission id. On failure, it will return a negative 
	 *        integer
	 *        indicating the issue ( -1 if the submission exists by hash,
	 *        -2 if you've crossed the submission limit. On encountering 
	 *        a fatal error an Exception will be thrown. 
	 */
	static function upload($user, $prob, $lang, $source_file, $owner = 'general' ) {
		if ( empty($user) or empty($prob) or empty($lang) 
		     or empty($source_file) ) { 
			throw new Exception("One or more parameters missed\n");
		}
		if ( ProblemDetails::factory($prob)->owner != $owner ) {
		  throw new Exception("Problem not owned by the given owner");
		}
		if ( !is_file($source_file ) ) 
			throw new Exception("File does not exist\n");

		if ( empty(config::$upload_directory) or 
		     !is_dir(config::$upload_directory)) {
			throw new Exception("Upload directory [$tmpdir] does "
				. "not exist :(");
		}
		
		
		$obj = SubmissionTable::add_to_queue($user,$prob,$lang) ;

		$pathtocode = $obj->getPathToCode()  ;

		if ( ! copy($source_file, $pathtocode) ) { 
			$obj->notcounted = true ; 
			$obj->commitChanges() ;
			throw new Exception("DEBUG: Failed to move temp upload to $pathtocode");
		}
		
		
		if (empty(config::$uploaded_files_group )) {
			chmod($pathtocode,0755);
		} else {
			chgrp($pathtocode,config::$uploaded_files_group) ;
			chmod($pathtocode,0750);
		}
		
		$_user = User::factory($user) ; 

		$is_admin = $_user->isAdmin() ;
				
		if ( config::$enable_hash_test && !$is_admin ){
			$hash = hash_file("md5", $pathtocode);
			
			$ret = SubmissionTable::exists_submission_by_hash 
				( $user, $prob, $hash );
			if ($ret) {
				return -1 ;
			}
		}

		$count = SubmissionTable::get_count($user,$prob,true) ;
		if ( !$is_admin && $count >= 
		     ProblemDetails::factory($prob)->getSubmissionLimit() ) { 
			return -2 ;
		}
		
		$obj -> score = 0 ;
		$obj -> time = time() ; 
		$obj -> hash = isset($hash) ? $hash : "" ; 
		$obj->owner = $owner ;
		$obj->state = "waiting" ;
		$obj->commitChanges() ;
		return $obj->id ;
	}

}
