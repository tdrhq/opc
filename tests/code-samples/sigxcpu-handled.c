#include <stdio.h>
#include <time.h>
#include <signal.h>

#define TIMEOUT 200 /* very large, we want to see this break */

void handler ()
{
	printf ("Caught\n"); /* why does this not get printed? */
	return; 
}

int main ()
{
	time_t s = time (NULL);	
	signal (SIGXCPU, handler);
	while (time (NULL) - s < TIMEOUT);
	printf ("OK\n");
	return 0;
}
