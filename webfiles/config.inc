<?php
/**
 * @file
 * provides a configuration for the web interface. 
 */

ob_start() ;


/*
 * Set up PHP errors by default. 
 */
error_reporting(E_ALL) ;
ini_set('display_errors', 'On') ;


class webconfig {

  	static $backend_location = "../backend/";

	static $base_url="" ;
	
	static $contest_name='Please modify webconfig::$contest_name in webfiles/local_config.inc'; 

	static $team_size = 1 ; 

	/**
	 * The contest to run. The backend has a concept of owner which means
	 * every submission can *belong* to a particular contest. This specifies
	 * the contest if for this particular checkout of the frontend.
	 */
	static $contest_id = 'general'; 

	static $widgets = array() ;

	/**
	 * A namespace for Session. Use this if you have multiple installations
	 * on the same domain. Each session should have a different namespace.
	 */
	static $session_namespace = 'global' ; 

	static $allow_register    = true;

	static $multi_contest     = true;

	/**
	 * Logo file, it's your responsibility to make sure it fits the CSS!
	 * The file should be in the images directory.
	 */
	static $logo = "cmi-logo1.png" ; 

	/**
	 * Logo href, the link this logo links to.
	 */
	static $logo_href = "http://www.cmi.ac.in" ;

	/**
	 * Default timezone to set for all time displays.
	 */
	static $default_timezone = "Asia/Calcutta" ;

	static $static_baseurl = "";

	static $enable_gravatars = false;
	
	static function getContestId ()
	{
		$session = new Zend_Session_Namespace(webconfig::$session_namespace);
		if (isset ($session->contestid)) return $session->contestid;
		else return webconfig::$contest_id;
	}

	static function getContestName ()
	{
		$session = new Zend_Session_Namespace (webconfig::$session_namespace);
		if (isset ($session->contestname)) return $session->contestname;
		else return webconfig::$contest_name;
	}

	static function getContest()
	{
		return Contest::factory (self::getContestId());
	}
}

webconfig::$backend_location = realpath(dirname(__FILE__) . "/../backend/") ;

if (is_file ( dirname(__FILE__)  . "/local_config.inc" )) 
	require dirname(__FILE__) . "/local_config.inc" ;


require_once webconfig::$backend_location  . "/config.inc" ;



/*
 * This is only for backward compatibility.
 */
config::$base_url = webconfig::$base_url ;

if (array_key_exists("OS", $_ENV) && trim($_ENV["OS"])=="Windows_NT")
	set_include_path(get_include_path().";". dirname(__FILE__) );
else
	set_include_path(get_include_path().":". dirname(__FILE__) );



