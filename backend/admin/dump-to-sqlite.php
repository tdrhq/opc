#!/usr/bin/env php
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
 * @file   dump-to-sqlite.php
 * @author Arnold Noronha <arnold@cmi.ac.in>
 */

require_once "../config.inc";
require_once "Zend/Loader.php";
require_once "lib/db.inc";

Zend_Loader::loadClass ("Zend_Db");
/* dump the current database into an sqlite3 with the schema.sqlite */

$db = Zend_Db::factory("Pdo_Sqlite", array ('dbname' => $argv[1]));
$old = contestDB::get_zend_db ();

$schema = file_get_contents ("../setup/schema.sqlite");

$db->getConnection()->exec ($schema);

$old->setFetchMode (Zend_Db::FETCH_ASSOC);
/* problemdata */
$res = $old->query ("select id,rowid,numcases,nickname,state,owner,submissionlimit,resourcelimits from problemdata");
$all = $res->fetchAll();

foreach ($all as $row) {
	$db->insert ("problemdata", $row);
}

$res = $old->query ("select uid,username,isadmin,username as password from users");
$all = $res->fetchAll();

foreach ($all as $row) {
        $db->insert ("users", $row);
}

$res = $old->query ("select id,uid,problemid,owner,lang,state,score,time,notcounted,hash from submissionqueue");
$all = $res->fetchAll();

foreach ($all as $row) {
        $db->insert ("submissionqueue", $row);
}

