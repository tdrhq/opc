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
 * @file   TimeDifference.php -- Facebook style time differences
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "Zend/View/Helper/Abstract.php";

class Zend_View_Helper_TimeDifference
	extends Zend_View_Helper_Abstract
{
	public function timeDifference ($timestamp, $from)
	{
		/* how many seconds ago? */
		$diff = $from - $timestamp;
		if ($diff < 0) return "In the future!";
		if ($diff == 0) return "just now";

		$length = array (1 => "second",
				60 => "minute",
				60*60 => "hour",
				60*60*24 => "day",
				60*60*24*7 => "week",
				(60*60*24*365)/12 => "month",
				(60*60*24*365) => "year");

		$ret = "$diff seconds ago";
		foreach ($length as $l => $val) {
			if ($diff < $l) break;
			$ans = (int) ($diff/$l);
			$ret = "$ans " . $val;
			if ($ans > 1) $ret .= "s";
			$ret .= " ago";
		}

		return $ret;
	}
}
