#include <iostream>
#include <string>
#include <cassert>
#include <cstdio>
#include <cstdlib>
#include <string.h>
#include <fstream>
#include <sstream>
#include <sys/types.h>
#include <dirent.h>
#include <sys/time.h>
#include <sys/resource.h>
#include <unistd.h>
#include <errno.h>
#include <time.h>
#include <iomanip>
#include <signal.h>
#include <sys/wait.h>
#include <sys/types.h>
#include <map>
#include <fcntl.h>
#include <getopt.h>
using namespace std;


int MFILE=4;
#define NPROC 0
#define MB  1024*1024
const int return_internal=1;
const int return_badstatus=2;

map<string,int> limits;
char* infile = NULL;
char* outfile = NULL;
double timelimit = 1.0;
char* chrootdir = NULL;
bool debug = false;

void init_default_limits () 
{
	limits["stack"]=8*1024*1024;
	limits["mem"]= 64*1024 * 1024;
	limits["fsize"]=50*MB; //specified in bytes?
	limits["time"] = 2 ; /* this value will not be used as is. Take a look at the timelimit variable in main () */ 
	/*
	 * Under a proper chroot, a limit on number of files
	 * should be unecessary. A limit of 4 works fine for C and C++
	 * submissions but causes problems on some machines for Java.
	 */
	limits["file"]=16;
	limits["timehard"]=0;
	limits["nproc"] = 1 ; /* dangerous don't change */
}


int unformatvalue (char* s) 
{
	char c = '-';
	int val;
	sscanf(s, "%d%c", &val, &c);
	if ( c == '-' ) return val;
	c = tolower (c);
	
	if ( c == 'k' ) return val * 1024;
	else if (c == 'm') return val*1024*1024;
	else {
		fprintf(stderr, "WARNING: %c: Unknown size specifier in '%s'\n", c, s);
		return val;
	}
	
	return val; //not implemented for time!
}

void print_usage ()
{
	printf (
		"usage: runner [options] progname progarg1 progarg2 ... \
									\
  options:\n								\
     --input=<file>        redirect program input from file\n		\
     --output=<file>       redirect program output to file\n		\
     --mem=<size>          set the runtime memory limit to <size>\n	\
     --stack=<size>        set the runtime stack limit to <size>\n	\
     --time=<seconds>      set the run time limit in seconds (real number)\n \
     --fsize=<size>        set the limit on amount of data outputted\n	\
     --chroot=<dir>        chroot to the given directory before executing\n \
     --debug               increase verbosity, do not redirect stderr.\n \
     --help                display this help page\n			\
\n									\
  <size> is in human readable format (12M, 12k etc., case insensitive.)\n \
  If no suffix is provided, it is understood to be bytes. 1k is 1024 bytes,\n \
  and 1M is 1024k.\n							\
\n									\
  This program is a part of the CMI Online Programming Contest Judge.\n	\
  Copyright 2007-2009 Chennai Mathematical Institute. This program\n	\
  is licensed under GNU General Public License, version 2.\n		\
\n									\
");
}

int parse_args (int argc, char* argv[])
{
	while (1){
		struct option lopts [] = {
			{"input", 1, NULL, 0},
			{"output", 1, NULL, 0},
			{"stack", 1, NULL, 0},
			{"mem", 1, NULL, 0},
			{"fsize", 1, NULL, 0},
			{"time", 1, NULL, 0},
			{"open-files", 1, NULL, 0},
			{"timehard", 1, NULL, 0},
			{"chroot", 1, NULL, 0},
			{"debug", 0, NULL, 0},
			{"help", 0, NULL, 0},
			NULL
		};
		
		int index;
		int c = getopt_long (argc, argv, "", lopts, &index);
		
		if (c == -1) break;
		
		if (c != 0) {
			print_usage ();
			exit (1); /* parsing failed? */
		}
		
		if (strcmp (lopts[index].name, "input") == 0)
			infile = strdup (optarg);
		else if (strcmp (lopts[index].name, "output") == 0)
			outfile = strdup (optarg);
		else if (strcmp (lopts[index].name, "open-files") == 0)
			limits["files"] = atoi (optarg);
		else if (strcmp (lopts[index].name, "chroot") == 0)
			chrootdir = strdup (optarg);
		else if (strcmp (lopts[index].name, "debug") == 0)
			debug = true;
		else if (strcmp (lopts[index].name, "help") == 0) {
			print_usage ();
			exit (0);
		}
		else if (strcmp (lopts[index].name, "time") == 0) {
			timelimit = atof (optarg);
			fprintf(stderr, "Parsed time limit is: %f\n", timelimit) ;
			if (limits["timehard"] == 0) 
				limits["timehard"] = int(timelimit + 1); 
		}
		else {
			limits [lopts[index].name] = unformatvalue (optarg);
		}
	}
	
	/* return the execute command */
	if (optind == argc) {
		fprintf (stderr, "No program name given.\n");
		print_usage ();
		exit (0);
	}
	return optind;
}

int subprocess (int argc, char* argv[])
{
	
	rlimit rlp;
	
	rlp.rlim_cur = rlp.rlim_max = limits["timehard"] ; 
	/* This is a security issue, but is important to catch 
	   time limit exceeded's correctly. */
	rlp.rlim_max = rlp.rlim_cur + 1 ;
	if ( setrlimit(RLIMIT_CPU,&rlp) != 0 )
		perror("setrlimit: RLIMIT_CPU");
	
	fprintf(stderr, "Time limit is set to %d (hard:%d) seconds\n", 
		(int) rlp.rlim_cur, (int) rlp.rlim_max );
	
	rlp.rlim_cur = rlp.rlim_max = limits["mem"] ;		\
	if ( setrlimit(RLIMIT_DATA ,&rlp) != 0 ) 
		perror("setrlimit: RLIMIT_DATA: ");
	fprintf(stderr, "Memory limit is set to %d bytes\n", limits["mem"]);
	
	rlp.rlim_cur = rlp.rlim_max = limits["mem"]  ; 
	if ( setrlimit(RLIMIT_AS,&rlp) != 0 ) 
		perror("setrlimit: RLIMIT_AS");
	
	rlp.rlim_cur = rlp.rlim_max = limits["fsize"]; 
	if (setrlimit(RLIMIT_FSIZE,&rlp) != 0)
		perror("setrlimit: RLIMIT_FSIZE");
	
	rlp.rlim_cur = rlp.rlim_max = limits["file"]; 
	if (setrlimit(RLIMIT_NOFILE,&rlp) != 0) 
		perror("setrlimit: RLIMIT_NOFILE");
	
	rlp.rlim_cur = limits["stack"]; 
	rlp.rlim_max = rlp.rlim_cur + 1024 ; 
	if ( setrlimit(RLIMIT_STACK,&rlp) != 0 ) 
		perror("setrlimit: RLIMIT_STACK");
	
	if(infile && freopen(infile,"r",stdin)==NULL)
	{
		perror("ERRIN");
		fprintf(stderr,"Internal error: Couldn't redirect input to stdin\n");
		return 23;
	}
	
	if(outfile && freopen(outfile,"w",stdout)==NULL)
	{
		perror("ERROUT");
		fprintf(stderr,"Internal error: Couldn't redirect output to stdout\n");
		return 24;
	}
	
	if (!chrootdir)
		fprintf(stderr,"Chroot dir not specified.\n");
	else {
		if (chdir(chrootdir) != 0) {
			perror ("Unable to change directory, chroot will be ineffective");
		}
		if (chroot(chrootdir)  != 0)  {
			perror("Chroot failed. Continuing anyway") ; 
			chrootdir = NULL ;
		}
	}
	
	fprintf(stderr,"Before setres[gu]id: Effective uid=%d gid=%d \n",geteuid(),getegid());
	if ( setresgid(65534,65534,65534) != 0 or setresuid(65534,65534,65534)!=0 ){
		perror("Unable to set the permissions of the running program\n" 
		       "This is a severe security issue! Try chowning runner to "
		       "root and setting the suid bit on\nContinuing anyway.");
	}
	
	rlp.rlim_cur = rlp.rlim_max = limits["nproc"];  
	if ( setrlimit(RLIMIT_NPROC,&rlp) != 0 ) 
		perror("setrlimit: RLIMIT_PROC");
	
	fprintf(stderr,"Ready to exec with: Effective uid=%d gid=%d \n",geteuid(),getegid());
	
	if (geteuid () == 0 || getegid () == 0) {
		fprintf (stderr, "FATAL: we're running as root!");
		return 1;
	}
	
	if (!debug) {
		if ( freopen("/dev/null", "w", stderr) == NULL ) 
		{
			perror("freopen");
			fprintf(stderr,"Internal error: Failed to redirect stderr\n");
			return 25;
		}
		else
			fprintf (stderr, "debug: not redirecting stderr on purpose.\n");
	}
	
	if (chrootdir) {
		argv[0] = argv[0] + strlen(chrootdir) - 1 ;
		argv[0][0] = '/' ; 
		fprintf(stderr, "The exec is at %s relative to %s\n", argv[0], chrootdir);
	}
	
	char** commands = new char *[argc + 1];
	for (int i = 0; i < argc; i++)
		commands [i] = argv[i];
	commands [argc] = NULL;
	execve(argv[0], commands, NULL) ;
	perror("Unable to execute program") ;
	
	exit (26);
}

int main (int argc, char* argv[])
{
	init_default_limits ();
	int cmd_start_index = parse_args (argc, argv);
	
	/* be safe on the timehard! */
	limits["timehard"] = max(limits["timehard"], 1 + int(timelimit)) ;
	
	if (chrootdir && strncmp(chrootdir, argv[cmd_start_index], strlen(chrootdir)) != 0 ) {
		fprintf(stderr, "The executable file must be on the chrooted "
			" drive. For one, keep your temp directory on the chroot "
			"partition. I'm disabling chroot for now.\n");
		free (chrootdir);
		chrootdir = NULL;
	}
	
	/* close any inherited file descriptors. Can somebody tell me if this
	 * is right? */
	for (int i = 3; i < 200; i++)
		close (i);
	
	pid_t pid = fork();
	
	if (pid==0) {
		return subprocess (argc - cmd_start_index, argv + cmd_start_index);
	}
	
	
	pid_t hardlimit_monitor = fork ();
	if (hardlimit_monitor == 0) {
		sleep (6*limits["timehard"]);
		/* if I reached here, then the main process is still running, upto
		 * some race condition possibilities */
		fprintf (stderr, "Severe hardlimit (%d) reached. Possibly malicious, or "
			 "overloaded system.\n", 6*limits["timehard"]);
		kill (pid, 9);
		return 0;
	}
	
	int status; 
	struct rusage usage ;  
	
	/*
	 * Correctness: Pid dies on its own or, hardlimit_monitor process
	 * kills it. In both cases, this works, except perhaps the Pid
	 * process can be called for kill twice.
	 */
	wait4(pid,&status, 0, &usage); //Wait for child to terminate
	kill (hardlimit_monitor, 9);
	waitpid (hardlimit_monitor, NULL, 0);
	
	// lets output the limits.
	fflush(stderr) ; /* ordering of output of child and parent should be right */
	double usertime = float(usage.ru_utime.tv_sec) + 
		float(usage.ru_utime.tv_usec)/1000000 ;
	fprintf(stderr, "Usertime: %lf\n", usertime);
	
	double systime = float(usage.ru_stime.tv_sec) +
		float(usage.ru_stime.tv_usec)/1000000 ;
	fprintf(stderr, "Systime: %lf\n", systime);
	fprintf(stderr, "Runtime: %lf\n", usertime+systime);
	
	if(WIFSIGNALED(status))
	{
		int signal = WTERMSIG(status);
		
		fprintf(stderr, "Error in program: Status %d Signal %d\n",
			status,signal);
		
#define die(s) { printf(s) ; exit(0) ; }   
		if(signal==SIGXCPU) die("TLE Time limit exceeded (H)\n");
		if(signal==SIGFPE)  die("FPE Floating point exception\n");
		if(signal==SIGILL)  die("ILL Illegal instruction\n");
		if(signal==SIGSEGV) die("SEG Segmentation fault\n");
		if(signal==SIGABRT) die("ABRT Aborted (got SIGABRT)\n");
		if(signal==SIGBUS)  die("BUS Bus error (bad memory access)\n");
		if(signal==SIGSYS)  die("SYS Invalid system call\n");
		if(signal==SIGXFSZ) die("XFSZ Output file too large\n");
		if(signal==SIGKILL) die("KILL Your program was killed (probably because of excessive memory usage)\n");
		
		die("UNK Unknown error, possibly your program does not return 0, or maybe its some fault of ours!");
		
	}
	
	if ( usertime + systime > timelimit ) die("TLE Time Limit exceeded\n") ;
	
	if (!WIFEXITED(status)) {
		printf ("EXIT Program exited abnormally. This could be due to excessive memory usage, or any runtime error that is impossible to determine.\n");
		exit (0);
	}
	
	if (WEXITSTATUS(status) != 0) { 
		fprintf (stderr, "Program return value: %d\n", WEXITSTATUS(status));
		printf ("EXIT Program did not return 0\n", WEXITSTATUS(status));
		exit (0);
	}
	
	return 0;
	
}
