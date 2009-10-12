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
 * @file   ShortenText.php -- Replace a long text with "This is a..."
 *            and use a title for completion.
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "Zend/View/Helper/Abstract.php";

class Zend_View_Helper_ShortenText
	extends Zend_View_Helper_Abstract
{
	
	public function shortenText ($text, $len)
	{
		if (strlen ($text) <= $len) 
			$short = $text;
		else $short = substr ($text, 0, $len - 3) . "...";
		return "<span title='" . htmlentities ($text) . "'>$short</span>";
	}
}
