#include <iostream>
#include <string>
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
using namespace std;


int MFILE=4;
#define NPROC 0
#define MB  1024*1024
const int return_internal=1;
const int return_badstatus=2;

map<string,int> limits;

void init_default_limits () {
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

int unformatvalue (char* s) {
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

//Run $1 with input $2 and output to $3 (with memory limit $4 KB and time limit $5 s)
int main(int narg,char* arg[])
{
  init_default_limits();
  double timelimit = 1 ; 

  if(narg<4)
    {
      fprintf(stderr,"Usage: %s executable input_from output_to [option1=value1] [option2=value2] ... \n",arg[0]);
      return 1;
    }

  char* exe = arg[1];
  char* infile = arg[2];
  char* outfile= arg[3];

  char *chrootdir = getenv ( "CMI_JUDGE_CHROOT_DIR" ) ;
  if ( chrootdir && strlen(chrootdir) == 0 ) chrootdir = NULL ;

  
  for ( int i = 4; i < narg; i++ ){

    char * p = strchr (arg[i], '=' );
    if( !p ) {
      fprintf(stderr, "WARNING: %s: bad argument.\n", arg[i]);
      return 1;
    } else *p=' ';

    char key[100],val[100]; //possible buffer overflows? Could be issue!

    sscanf(arg[i],"%s %s", key, val);
    if ( limits.count(key) == 0 ) {
      fprintf(stderr, "WARNING: %s: Unknown limit parameter.\n", key );
      continue;
    }

    if ( strcmp(key, "time") == 0 )  { 
      sscanf(val, "%lf", &timelimit) ; 
      fprintf(stderr, "Parsed time limit is: %lf\n", timelimit) ;
      if ( limits["timehard"] == 0 ) 
	limits["timehard"] = int(  timelimit + 1 ) ; 

    } else 
      limits[key] = unformatvalue(val);
  }
  limits["timehard"] = max(limits["timehard"], 1+int(timelimit) ) ;


  char * argv [ 100] ; 
  istringstream iss (exe) ;
  string cur ; 
  int i = 0 ;
  for(i = 0 ; iss>>cur ; i++ ) {
    argv[i] = strdup(cur.c_str());
  }
  argv[i] = NULL ;


  if ( chrootdir && strncmp(chrootdir, argv[0], strlen(chrootdir)) != 0 ) {
    fprintf(stderr, "The executable file must be on the chrooted "
	    " drive. For one, keep your temp directory on the chroot "
	    "partition. I'm disabling chroot for now.\n") ;
    chrootdir = NULL;
  }

  /* close any inherited file descriptors. Can somebody tell me if this
   * is right? */
  for (int i = 3; i < 20; i++)
	  close (i);

  pid_t pid = fork();
  
  if (pid==0) {

    rlimit rlp;

    rlp.rlim_cur = rlp.rlim_max = limits["timehard"] ; 
    /* This is a security issue, but is important to catch 
       time limit exceeded's correctly. */
    rlp.rlim_max = rlp.rlim_cur + 1 ;
    if ( setrlimit(RLIMIT_CPU,&rlp) != 0 )
    	perror("setrlimit: RLIMIT_CPU");

    fprintf(stderr, "Time limit is set to %d (hard:%d) seconds\n", 
	    (int) rlp.rlim_cur, (int) rlp.rlim_max );

    rlp.rlim_cur = rlp.rlim_max = limits["mem"] ;  \
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



    if(freopen(infile,"r",stdin)==NULL)
      {
        perror("ERRIN");
        fprintf(stderr,"Internal error: Couldn't redirect input to stdin\n");
    	return 23;
      }
      
    if(freopen(outfile,"w",stdout)==NULL)
      {
        perror("ERROUT");
        fprintf(stderr,"Internal error: Couldn't redirect output to stdout\n");
    	return 24;
      }

    if ( !chrootdir )
      fprintf(stderr,"Chroot dir not specified.\n");
    else  if ( chroot(chrootdir)  != 0 )  {
      perror("Chroot failed. Continuing anyway") ; 
      chrootdir = NULL ;
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


#ifndef DEBUG    
    if ( freopen("/dev/null", "w", stderr) == NULL ) 
      {
	perror("freopen");
	fprintf(stderr,"Internal error: Failed to redirect stderr\n");
	return 25;
      }
#endif    

    if ( chrootdir) {
      argv[0] = argv[0] + strlen(chrootdir) - 1 ;
      argv[0][0] = '/' ; 
    }
    fprintf(stderr, "The exec is at %s relative to %s\n", argv[0], chrootdir);
    execve(argv[0],argv,environ) ;
    perror("Unable to execute program") ;

    return 26;
  }


  int status; 
  struct rusage usage ;  
  wait4(pid,&status, 0, &usage); //Wait for child to terminate
  
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
	  printf ("EXIT Bad return value. Your program returned %d, when it should return 0. This could be due to various reasons including excessive memory usage, or simply forgetting to put a 'return 0' at the end of your code.\n", WEXITSTATUS(status));
	  exit (0);
  }

  return 0;

}
