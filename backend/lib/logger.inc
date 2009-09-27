<?php
/**
 * Copyright 2009 Arnold Noronha
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
 * @file   logger.inc -- system-wide logging mechanism
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once dirname(__FILE__) . "/../config.inc";
require_once "Zend/Loader.php";

class Logger
{
	private static $logger = null;

	/**
	 * Get a Zend_Log object that can be used for logging.
	 *
	 * @param  void
	 * @return Zend_Log object 
	 */
	static function get_logger () 
	{
		if (self::$logger) return $this->logger;
		Zend_Loader::loadClass ("Zend_Log");
		Zend_Loader::loadClass ("Zend_Log_Writer_Stream");

		$filename = "data/logs/" . getmyuid() . ".log";
		$writer = new Zend_Log_Writer_Stream (get_file_name ($filename));
		
		self::$logger = new Zend_Log ();
		self::$logger->addWriter ($writer);

		return self::$logger;
	}

	/**
	 * If this script is run in CLI mode, call at most once to set the log
	 * level that should be logged to stderr.
	 *
	 * @param void
	 * @return void
	 */
	static function set_stderr_loglevel ()
	{
		self::get_logger ();
		$writer2 = new Zend_Log_Writer_Stream ("php://stderr");
		
		self::$logger->addWriter ($writer2);
	}
}


