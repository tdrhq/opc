#include <stdio.h>
#include <stdlib.h>

#ifndef MB
	#define MB 65 /* use gcc -DMB=<val> to change */
#endif
#define BIGNUM (MB*1024*1024/sizeof(int))

int main()
{
	int *ar = (int*) malloc (sizeof(int)*BIGNUM);
	int i;

	if (!ar) {
		perror ("woo, failed");
		return 0;
	}

	/* 
	 * prevent any loop optimizations, nothing profound about the 
         * way we achieve this. :-)
	 */
	for (i = 1; i < BIGNUM; i++)
		ar[i] = (rand () % 2) + ar[rand() % BIGNUM] + ar[i-1];

	printf ("OK %d\n", ar[BIGNUM/2]);
	
	return 0;
}
