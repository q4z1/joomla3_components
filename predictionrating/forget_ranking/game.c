#include <stdio.h>
#include <stdlib.h>

#include "coefficients.h"

/*
compile:
$ gcc forget_game.c -o forget_game
execute:
$ ./forget_game
*/

/* input format:
integers, seperated by spaces, ends with newline
1: N // number of players
1: time // as unix timestamp
for each player:
 1: place
 DATASIZE: data entries for the players

total size: 2+N*(DATASIZE+1)

WARNING: this program will crash given wrong input

output format:
for each player:
 DATASIZE: new data
start with -1 in case of error

total size: N*DATASIZE
*/

/* data format:
score games last_game 0 0 ... 0

*/

int read_number()
{
  int result=0;
  if(scanf("%d",&result)==1)
  {
    return result;
  }
  return -(1<<29); // ERROR
}

int read_data(int**out)
{
  int i=0;
  /* read first number */
  int N=read_number();
  if(N==-(1<<29) || N<=0)
  {
    return -1;
  }
  /* allocate memory */
  *out=(int*)malloc(sizeof(int)*((DATASIZE+1)*N+2));
  (*out)[0]=N;
  for(i=1;i<N*(DATASIZE+1)+2;i++)
  {
    (*out)[i]=read_number();
    if((*out)[i]==-(1<<29)) return -2; /* error */
  }
  return 0;
}

int get_scores_places(int N,int*in,double*out_scores,int*out_places)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    out_scores[i]=(in[(DATASIZE+1)*i+1])/(double)SCOREFACTOR;
    out_places[i]=in[(DATASIZE+1)*i];
    if(out_places[i]<=0|| out_places[i]>10) return -2;
  }
  return 0;
}

double new_score(int place,double old_score)
{
  int won_points=placepoints[place-1];
  double forgetratio=FORGET_PROMILLE/1000.0;
  return old_score*(1.0-forgetratio)+won_points;
}

int update_scores(int N,double*scores,int*places)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    scores[i]=new_score(places[i],scores[i]);
  }
  return 0;
}

void print_results(int N,double*scores,int*data)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    int i_score=(int)(scores[i]*SCOREFACTOR+0.5); // rounding
    int newgames=data[(DATASIZE+1)*i+2+1]+1;
    int newlastgame=data[1];
    int j=0;
    printf("%d %d %d ",i_score,newgames,newlastgame);
    for(j=3;j<DATASIZE;j++)
    {
      printf("0");
      if(i!=N || j!=DATASIZE-1)
      {
        printf(" ");
      }
    }
  }
  printf("\n");
  fflush(stdout);
  return;
}


int main()
{
//   int max_repititions=1;
  int max_repititions=(1<<29);
  int j=0;
  for(j=0;j<max_repititions;j++)
  {
    int*data=NULL;
    int retval=0;
    retval=read_data(&data);
    if(retval!=0)
    {
      printf("-1 there was an error while reading data\n");
      return -1;
    }
    int N=data[0];
    double*scores=(double*)malloc(sizeof(double)*N);
    int*places=(int*)malloc(sizeof(int)*N);
    retval=get_scores_places(N,&(data[2]),scores,places);
    if(retval!=0)
    {
      printf("-1 there was an error while reading data\n");
      return -2;
    }
    update_scores(N,scores,places);
    print_results(N,scores,data);
    free(scores);
    free(places);
    free(data);
  }
  return 0;
}
