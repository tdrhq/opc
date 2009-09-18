#include <stdio.h>
#include <assert.h>

int main ()
{
	int p = fork ();
	if (p == 0) {
		printf ("OK\n");
		return 0;
	}
	if (p < 0) {
		perror ("great, failed");
		assert (0);
	}
	return 0;
}
