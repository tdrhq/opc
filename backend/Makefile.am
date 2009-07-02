
SUBDIRS=programs doc

docs:

default_contest: opc_directories
	test -d data/contests/general || mkdir -p data/contests/general
	chmod a+rx data/contests/general
	test -f data/contests/general.xml || cp setup/general.xml data/contests/general.xml
	chmod a+r data/contests/general.xml

opc_directories:
	[ `whoami` != "root" ] 
	test -d data/problems || mkdir -p data/problems
	chmod a+r data/problems
	test -d data/results || mkdir -p data/results
	chmod a+r data/results
	test -d data/uploads || mkdir -p data/uploads
	!(test -O data/uploads) || chmod a+w data/uploads
	test -d data/users || mkdir -p data/users 
	!(test -O data/users) || chmod a+w data/users 
	test -d data/contests || mkdir -p data/contests
	chmod a+rx data/contests
	test -d data/sqlite || mkdir -p data/sqlite
	!(test -O data/sqlite) || chmod a+rwx data/sqlite
	touch data/sqlite/db
	!(test -O data/sqlite/db) || chmod a+rw data/sqlite/db

local_config: 
	if ! [ -f local_config.inc ] ; then cp setup/sample_conf.inc local_config.inc ; fi 
	@/usr/bin/php setup/test_config.php || ( echo "There are errors in your local_config.inc" && false ) 

zend_test: local_config
	@/usr/bin/php setup/test_zend.php || ( echo "Unable to find Zend. Please store zend in the root directory, or point config::path_to_zend to the correct location in local_config.inc. If you haven't downloaded Zend yet, please do so now." && false)

database_test:
	@$(PHP5_CLI) setup/install_database.php   

all-local: local_config zend_test opc_directories database default_contest 

database: database_test
	$(PHP5_CLI) setup/install_db_tables.php

secure: programs/runner
	test `whoami` == "root" 
	test -f programs/runner 
	test -d data/uploads
	chown www-data:www-data data/uploads
	chmod og-w data/uploads
	chmod a+r data/uploads
	chown root:root programs/runner
	chmod a+s programs/runner
	chown www-data data/sqlite
	chown www-data data/sqlite/db
	chmod o-w data/sqlite
	chmod o-w data/sqlite/db


