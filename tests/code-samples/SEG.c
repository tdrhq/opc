#include <stdio.h>
#include <time.h>

int main ()
{
	int *ar = NULL, i;
	for (i = 0; ; i++)
		ar [i] = ar[i >> 1] + 1; /* cough, nothing profound */
	return ar [time (NULL)]; /* should not optimize this out */
}
