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
 * @file   Queue/Awakener/Timeout.php --- awaken the queue ever few seconds
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once dirname(__FILE__) . "/../../config.inc";

class QueueAwakenerTimeout
{
	private $timeout = 2;
	public function __construct ()
	{
		$this->timeout = config::$queue_inactive_sleep_time;
	}

	public function wait ()
	{
		$ms = config::$queue_inactive_sleep_time * 1000000 ;
                usleep(mt_rand($ms/2,$ms)) ;
	}

	/*
	 * called, for example when the program is Ctrl+C-ed. For Timeout
	 * we don't have to do anything, for different type of 
 	 * awakeners you might want to kill the blocking call.
	 */
	public function cancel ()
	{	
	}
}

