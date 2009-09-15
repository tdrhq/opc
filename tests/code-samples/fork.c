#include <stdio.h>

int main ()
{
	int p = fork ();
	if (p == 0) {
		printf ("OK\n");
		return 0;
	}
	if (p < 0) perror ("great, failed");
	return 0;
}
