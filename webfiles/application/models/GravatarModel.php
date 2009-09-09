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
 * @file   GravatarModel.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

function getgravatar ($user) 
{ 
	Zend_Loader::loadClass("UserModel");
	$userm = new UserModel();
	$user = $userm->getRow ($user);
	$email = $userm->getMember ($user, 0)->email;

	$grav_url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5( strtolower($email) ).
		'&size=40';

	return $grav_url;
}


class GravatarModel
{
	public function __construct() 
	{
		Zend_Loader::loadClass("Zend_Cache");
		$this->cache = Zend_Cache::factory('Function', 'File', 
					     array('lifetime' => 60,
						   'automatic_serialization' => true ) );
	}

	public function getGravatar($user) 
	{
		return $this->cache->call("getgravatar", array($user) );

	}
}
