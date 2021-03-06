dnl                                               -*- Autoconf -*-
dnl Process this file with autoconf to produce a configure script.

AC_PREREQ(2.61)
AC_INIT(FULL-PACKAGE-NAME, VERSION, BUG-REPORT-ADDRESS)
AC_CONFIG_SRCDIR([programs/runner.c])
# AC_CONFIG_HEADER([config.h])

AM_INIT_AUTOMAKE([-Wall -Werror foreign])

dnl Checks for programs.
AC_PROG_CXX
AC_PROG_CC
AC_CHECK_PROG(PHP5_CLI,php5,php5)

dnl Checks for libraries.

dnl Checks for header files.
AC_HEADER_DIRENT
AC_HEADER_STDC
AC_HEADER_SYS_WAIT
AC_CHECK_HEADERS([fcntl.h stdlib.h string.h sys/time.h unistd.h])

dnl Checks for typedefs, structures, and compiler characteristics.
AC_C_CONST
AC_TYPE_PID_T

dnl Checks for library functions.
AC_FUNC_FORK
AC_CHECK_FUNCS([strchr strdup])

dnl Check for PHP library functions, is there a better way?
AC_MSG_CHECKING([for php-tidy])
if (php --re tidy | grep -q ^Exception); then
   	AC_MSG_ERROR([php libtidy module not found])
else
	AC_MSG_RESULT(yes)
fi

AC_MSG_CHECKING([for php-sqlite])
if (php --re sqlite | grep -q ^Exception); then
   	AC_MSG_ERROR([php sqlite extension not found])
else
	AC_MSG_RESULT(yes)
fi

AC_MSG_CHECKING([for php postgresql])
if (php --re pgsql | grep -q ^Exception); then
   AC_MSG_RESULT([no, Postgres disabled])
   AC_DEFINE(HAVE_POSTGRES, [0])
else
	AC_MSG_RESULT(yes)
	AC_DEFINE(HAVE_POSTGRES, [1])
fi

AC_CONFIG_FILES([Makefile
	programs/Makefile
	doc/Makefile])

AC_OUTPUT
