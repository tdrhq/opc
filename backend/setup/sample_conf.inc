<?php

/* Database Support */

/* SQLite (default, easiest to set up) */
config::$DB_Adapter = "Pdo_Sqlite";
config::$DB_Name = dirname(__FILE__) . "/data/sqlite/db";

/* Postgres and MySQL*/
// config::$DB_Adapter = "Pdo_pgsql"; // default value, use "Mysqli" for MySQL
// config::$DB_Name = "";
// config::$DB_User = "";
// config::$DB_Password = "";
// config::$DB_Hostname = "localhost";

