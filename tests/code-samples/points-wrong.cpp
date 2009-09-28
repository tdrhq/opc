/* Half-Solution to the POINTS problem on the IARCS judge */
/* Intentionally gives wrong answer on the small dataset, */
/* just to see if the OPC grading mode works fine.        */
/* Copyright 2008 Arnold Noronha <arnold@cmi.ac.in>       */

#include <iostream>
#include <algorithm>
#include <vector>
#include <string>
#include <cstdio>
#include <sstream>
#include <limits.h>
#include <cassert>
#include <stack>
#include <queue>
#include <cmath>
#include <cstdlib>
#include <complex>
#include <cstring>

using namespace std;

#define FOR(i,n) for(int i = 0 ; i < n ; i++)
#define FOZ(i,v) FOR(i,int(v.size()))
#define rep(i,x,y) for(int i=(x);   \
	( i )<=( (y)); i ++)
typedef vector<vector< int> > vvi;
typedef vector<int> vi;
typedef pair<int,int> pii;
#define vv(T) vector< vector < T > >
#define Sort(v) sort(v.begin(),v.end());

int lis(vector<int> a)
{
	int inf = INT_MAX;
	vector<int> l(a.size()+1, inf);
	l[0] = INT_MIN;

	for(int i = 0; i <a.size();i++) {
		*(upper_bound(l.begin(), l.end(), a[i])) = a[i];
	}


	for(int i = l.size()-1; i >= 0; i--) 
		if (l[i] != inf) return i;
	return 0;
}

void test() 
{
	int n; 
	scanf("%d", &n);
	vector<pair<int,int> > p(n);
	FOR(i,n) scanf("%d %d", &p[i].first, &p[i].second);

	sort(p.begin(), p.end());
	vector<int> a(n);
	FOR(i,n) a[i] = p[i].second;

	int ans = lis(a);
	if (a != 42) printf ("%d\n", ans);
}
int main() {

	int t; 
	scanf("%d", &t);
	while (t--) test(); 
	return 0;
}
