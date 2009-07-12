<?

config::$script_time            =time() ;
class config { 
  /**
   * The base URL of the webfiles direcory, as seen
   * by the outside world.
   * Remember to keep a '/' at the end.
   * @deprecated Use webconfig::$base_url instead. 
   */
  static $base_url="" ;
  
  //before,after,ongoing,suspended
  const registration_status="ongoing" ;
  const contest_status="before";

  static $contest_name="Please modify local_config.inc"; 
  
  // hour,minute,second,month,day,year
  static $registration_start_time;
  static $registration_close_time;

  static $start_time  ;
  static $end_time ;
  /**
   *Team Size : at most 3 is implemented right now. 
   */
  const max_team_size = 2 ; 

 
  /** database configuration */
  static $DB_Hostname="";
  static $DB_Name ="";
  static $DB_User ="";
  static $DB_Password ="";
  static $DB_Adapter = "Pdo_Pgsql"; /* use Mysqli for MySQL */

  /* Submission Aspects */
  /**
   * Load problem list on submit page
   *
   * Default: Enabled, disable this for long contests
   * with lots of problems, as this can add to load on the
   * SQL server.
   * 
   * TYPE: boolean
   */
  static $enable_problem_list_on_submit = true;

  /* Directory Configurations */

  /**
   * Path to Zend. You can leave it as blank if Zend is already in the path.
   */
  static $path_to_zend ; 

  /**
   * Programs dir
   * 
   * Location of the programs directory. 
   *
   * TYPE: string 
   */
  static $programs_directory = 'programs/' ;
 
  /**
   * Problems dir
   *
   * Location of the problems statement directory.
   * TYPE: string
   */
  static $problems_directory = 'data/problems/';

  /**
   * Results directory.
   *
   * Stores the results per submission in XML form.
   * Since this is such a important decision, it is being
   * left blank by default. And the queuemanager may fail to 
   * load if this is empty.
   * 
   * TYPE: string
   */
  static $results_directory = 'data/results';
  /**
   * Temporary directory for storing compiled executables.
   *
   * TYPE: string 
   */
  static $compile_temp_directory = '/tmp/';

  /**
   * Directory to store uploaded submissions
   * 
   * Defaults to /tmp/. Its important to change
   * this to a directory with proper permissions, to both 
   * the apache user and the user under whom the queuemanager
   * is running. In a distributed system this should be on 
   * the NFS.
   *
   * TYPE: string
   */
  static $upload_directory = 'data/uploads';

  /**
   * Maximum file upload size in kilobytes.
   *
   * TYPE: integer
   */
  static $max_upload_size = 100 ;

  /**
   * Default problem grading style. Possible values that it can take are
   * "opc", "ioi", "spoj". @see ProblemDetails::grading_style 
   */
  static $default_grading_style = "opc" ;

  /** Frontend aspects */
  static $frontend_css = ""; 
  static $script_time ; //static time when this script was called

  /**
   * Handle how the format string for time in the queue viewer
   * looks like. May want to change it depending on length
   * of contests.Format is as used by strptime.
   *
   * TYPE: string
   */
  static $info_queue_date_string = "%H:%M:%S";

  const data_dir="";//config::base_dir . "/data/";


  /**
   * Syslog name for the server log
   *
   * TYPE: string
   */
  static $log_file = "opclog" ;

  /** Don't change these unless you know what you're doing */

  /**
   * For how long should the queue sleep if it finds no 
   * submission waiting on the database. (in microseconds)
   * 
   * TYPE: float
   */
  static $queue_inactive_sleep_time=0.5;

  /**
   * The directory that the runner should attempt to chroot to.
   * If this directory does not exist, the runner will continue in 
   * insecure mode.
   *
   */
  static $chroot_dir = NULL ;

  /**
   * No. of seconds the score updater should wait before updating
   * the score table again. 
   * 
   * TYPE: float
   */
  static $score_updater_sleep_time=10 ; 
  /**
   * Enable hash testing to see if the user has submitted
   * the same solution twice .
   * 
   * TYPE: boolean
   */
  static $enable_hash_test = false;

  /**
   * The apache user/group as whom the web PHP scripts are run,
   * Note, that persons not associated with the contest should
   * not be in the group if they are a threat to security. 
   * Leaving as blank will work, but can be a security issue if
   * non-admins have user accounts on the NIS.
   *
   * TYPE: string
   */
  static $uploaded_files_group = '' ;

  static $compiler_string_java = 'javac ';

  /**
   * If this is enabled, all java submissions will have 
   * to use Main as a classname, and therefore have a filename
   * Main.java. This is possibly more secure than the other
   * system where the java classname is extracted from
   * uploaded filename.
   *
   * TYPE: boolean
   */
  static $enable_unique_java_classname = true;

  /**
   * Supported languages as associative array
   *
   * TYPE: array(string=>string)
   */
  static $supported_submission_languages 
	= array ("cpp" => "C++ (g++ 4.1)",
			 "c" => "C (gcc 4.1)",
			 "gcj" => "Java (gcj 4.1)",
			 );

  /** Mailer system */

  static $mailer_smtp_server = ""; /**< leave empty to use Sendmail */
  static $mailer_smtp_zend_config = array ('auth' => 'login',
				    'username' => '',
				    'password' => '');
  static $mailer_admin_email = "";
  static $mailer_admin_name = "";

  static function get_installation_dir() { 
	return realpath( dirname(__FILE__) ) ;
  }
  static function get_data_dir() {
    return config::get_installation_dir(); 
  }
  static function get_programs_dir() {

	return config::$programs_directory ;
  }
  static function get_problems_dir() { 
	  return config::$problems_directory ;
  }

  static function get_path_to_runner() {
    return config::get_programs_dir() . "/runner" ;
  }
  static function get_path_to_evaluator() {
	return config::get_programs_dir() . "submissions.php" ;
  }
  static function get_log_file() { 
	return get_file_name(config::$log_file) ;
  }

  /**
   * Returns the absolute path of the path specified. If the path is
   * is relative, the the path is completed from the root of the installation.
   */
  function getFilename($file) { 
	if ( $file == NULL ) return $file ;
	if (substr($file, 0, 1) != "/" ) 
	  $file = config::get_installation_dir() . "/" . $file ; 
	$ret = realpath($file) ;
	if( empty($ret) ) 
	  return $file ; 
	if ( is_dir($ret) ) return "$ret" . "/" ;
	else return $ret ; 
  }
  
};


function get_file_name($file) { 
  return config::getFilename($file) ;
}

/* Windows development fix for include path */
if ( array_key_exists("OS", $_ENV) && trim($_ENV["OS"])=="Windows_NT")
set_include_path(get_include_path().";".config::get_installation_dir() );
else
set_include_path(get_include_path().":".config::get_installation_dir() );


/*
 * Include a custom config file if found. 
 * consider this a hack for development purposes :)
 */
if (is_file ( config::get_installation_dir() . "/local_config.inc" )) 
	include config::get_installation_dir() . "/local_config.inc" ;

/*
 * Fix all relative paths given in local_config.inc
 */


config::$programs_directory = get_file_name(config::$programs_directory) ;
assert(!empty(config::$programs_directory));
config::$problems_directory = get_file_name(config::$problems_directory) ;
assert(!empty(config::$problems_directory));
config::$results_directory = get_file_name(config::$results_directory) ;
assert(!empty(config::$results_directory));
config::$compile_temp_directory =get_file_name(config::$compile_temp_directory);
config::$upload_directory = get_file_name(config::$upload_directory) ;
assert(!empty(config::$upload_directory)) ;
config::$chroot_dir = get_file_name(config::$chroot_dir) ;
config::$log_file = get_file_name(config::$log_file) ;

if ( !empty(config::$path_to_zend) )
	  set_include_path(get_include_path() . ":" .
	                                     config::getFilename(config::$path_to_zend . "/../" ));

/* 
 * If chroot directory is specified, make sure compile temp
 * directory lies under it.
 */
if ( !empty(config::$chroot_dir) and
	 strncmp(config::$chroot_dir, config::$compile_temp_directory, 
			 strlen(config::$chroot_dir)) != 0 ) {
  die("Compile temporary directory should lie under the chroot directory");
 }

