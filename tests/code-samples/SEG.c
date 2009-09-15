#include <stdio.h>

int main ()
{
	int ar [10], i;
	for (i = 0; ; i++)
		ar [i] = ar[i/3] + ar[i/2]; /* cough, nothing profound */
	return 1;
}
