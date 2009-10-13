
## The CMI Online Programming Contest Judge

### About

The CMI Judge is a configurable automated Judge for Online Programming
Contests. The CMI-Judge was originally designed for the popular CMI
Online Programming Contest, and has been used, in various versions,
since 2004. CMI also uses it every year for its onsite Fiesta
Programming Contest. The Indian Association for Research in Computing
Science (IARCS) uses CMI-Judge for conducting several rounds of the
Indian Computing Olympiad.

The CMI Judge is currently maintained by Arnold Noronha
<arnold@cmi.ac.in>, a grad student at the University of Pennsylvania.

### System Requirements

CMIJ has been used on various flavors of Debian and Ubuntu. That said,
it should not be very hard to get it to run on other flavors of
Linux/*nix. It will not work on Windows.

The basic requirements:

* An Apache Web Server, with mod-php5 for running PHP scripts.
* PHP5, with the Command Line Interfact. (php5-cli on Debian)
* A database backend: SQLite3, Postgres, or MySQL.

Source code:

* CMI-Judge code: "git clone git://github.com/tdrhq/opc.git"
* Zend Framework: http://framework.zend.com/download/overview

In order to build and set up the system you're going to need:

* GNU build-essential, g++ 

Several other packages:

* The PHP database modules, depending on which database backend
  you're using. (php5-sqlite, php5-pgsql, or php5-mysql on Debian)
* Frontend formatting tools: php5-tidy, php-geshi (see below)
* Compilers and interpreters for running the submissions:
  g++, gcc, gcj (for Java), python, perl.

Recommended:

* GeSHi (>=1.0.8). GeSHi is used for syntax highlighting. Latest
  Debian distributions have this version and can be installed with
  "apt-get install php-geshi". You can also download GeSHi into the 
  backend/ directory.

In addition, for a high-security environment, you might want to use
debootstrap to create a chroot-ed environment for running the contest.

### Installation

Copy the "Zend" directory from Zend Framework into the "backend"
directory of the CMI Judge. I imagine you know have the following
directory structure:
  
    /backend
    /backend/Zend
    /webfiles

* Backend setup for SQLite:

  SQLite will not scale, and is not recommended for production
  use. Nevertheless, it's the easiest to set up, and very convenient
  if you just want to play around with the Judge.

    ~$ cd backend/
    ~/backend$ ./autogen.sh
    ~/backend$ make
    ~/backend$ sudo make secure (optional)

  And that's it.

* Backend setup for PostgreSQL or MySQL. 

  Follow the instructions for SQLite. You need to create a database and
  a database user in either Postgres or MySQL for this step. 

  Now edit backend/local_config.inc with the following lines:
 
    <?php
    config::$DB_Name="judgedbname" ; //that you created earlier
    config::$DB_User="dbusername" ; 
    config::$DB_Password="dbpassword" ;
    config::$DB_Hostname="localhost"; //or the host name if it's different
    config::$DB_Adapter="Pdo_Pgsql"; //or Pdo_Mysqli
  
At this point, your setup should work. You should be able to access
the judge by pointing to the webfiles directory using HTTP.

Once you add a problem, you can submit solutions. In order to process
submissions as they come, you need to run
backend/programs/queuemanager.php in the background.

See Advanced Frontend Setup for more configuration details.

### Adding a Problem
 
We currently do not support web-based uploads of problems. Please use
the following script from the command line to add a new problem:

    backend/admin/addproblem.php

### Adding a Contest

We currently do not support web-based creation of contests. Please use
the following script from the command line to add a new contest:

    backend/admin/addcontest.php
  

### Alternatives

When this Judge was first written, there were few, if any,
configurable open source judges available. Today, almost every
University has its own [buggy] judge. There are few credible open
source alternatives available:

 * PC^2    <http://www.ecs.csus.edu/pc2/>

    PC^2 is one of the most popular contest Judges, but as of today
    does not support an online mode of operation. It is used for almost
    all onsite contests, including the ACM ICPC World Finals.

 * Mooshak <http://mooshak.dcc.fc.up.pt/~zp/mooshak/>

    Probably one of the more popular web based Judges. I have had
    experience with it as a participant at an ACM ICPC Regionals, in
    2006. At that time it was buggy. I think it provides far more
    configurability that the CMI Judge.

 * hackzor <http://code.google.com/p/hackzor/>

    Hackzor is a simple Python based judge from Anna University,
    Chennai. I mention it because I used it as a basis for some parts
    of the CMI Judge. I don't believe it is actively mainted. 

Apart from that, several Online Programming Archives can directly host
contests for you. You might want to contact one of these:

 * The IARCS Online Judge  <http://opc.iarcs.org.in>

    Maintained by me :-). This archive is based on the CMI Online
    Judge, and hosted reliably on Amazon EC2, courtesy of IARCS. I
    would help you organize your contest, and we can work out some
    amount of compensation for my efforts. :-)

 * SPOJ <http://www.spoj.pl>

    SPOJ is well known for hosting many private contests. 

 * CodeChef <http://www.codechef.com>

    CodeChef is based on the SPOJ (I think), and I do think they hold
    private contests.

 * UVa Judge <http://uva.onlinejudge.org/>

    One of the oldest and most well known online judges.
