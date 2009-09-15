#include <stdio.h>
#include <time.h>

#define TIMEOUT 2
int main ()
{
	time_t s = time (NULL);	
	while (time (NULL) - s < TIMEOUT);
	printf ("OK\n");
	return 0;
}
