
#include <stdio.h>
#include <stdlib.h>
#include <math.h>

#include "coefficients.h"

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
elo games lastgame 0 0 ... 0

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

int get_places(int N,int*in,int*out_places)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    out_places[i]=in[(DATASIZE+1)*i];
    if(out_places[i]<=0|| out_places[i]>10) return -2;
  }
  return 0;
}

int get_elo_games_lg(int N,int*in,int*out_elo,int*out_games,int*out_lg)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    out_elo[i]=in[(DATASIZE+1)*i+1];
    out_games[i]=in[(DATASIZE+1)*i+2];
    out_lg[i]=in[(DATASIZE+1)*i+2];
  }
  return 0;
}

void print_results(int N,int*elo,int*games,int*lg)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    int j=0;
    printf("%d %d %d ",elo[i],games[i],lg[i]);
//     printf("%d %d %d ",i_score,newgames,newlastgame);
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

int update_games_lg(int N,int*games,int*lg,int now)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    games[i]+=1;
    lg[i]=now;
  }
  return 0;
}

int update_elo(int N,int*places,int*elo,int*games,int*lg)
{
  // only elo will be modified

  // select coefficients
  double winbonus=COEF_WINBONUS/100.0;
  double std_diff=(double)STD_DIFF;
  double speed=(double)COEF_SPEED;

  // convert elo to double
  double*elof=(double*)malloc(sizeof(double)*N);
  int i=0;
  for(i=0;i<N;i++)
  {
    elof[i]=(double)elo[i];
  }
  // start main calculation
  for(i=0;i<N;i++)
  {
    double expect=0.0; // expected score
    int j=0;
    for(j=0;j<N;j++)
    {
      if(i==j) continue;
      double diff = elof[j]-elof[i];
      // NOTE! places are not necessarely sorted
      double beforebonus = 1.0/(1.0 + pow(2.0,diff/std_diff));
      if(places[i]==1 || places[j]==1)
      {
        expect += winbonus*beforebonus;
      }
      else
      {
        expect += beforebonus;
      }
    }
    double wonpoints=(double)(10-places[i]);
    if(places[i]==1) wonpoints=9.0*winbonus;
    // updated rating
    double newelo = elof[i]+speed*(wonpoints-expect);
    // convert back to ints
    elo[i] = (int)round(newelo);
  }
  return 0;
}

int main()
{
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
    int now=data[1]; // unix timestamp
    int*elos=(int*)malloc(sizeof(int)*N);
    int*places=(int*)malloc(sizeof(int)*N);
    int*games=(int*)malloc(sizeof(int)*N);
    int*lg=(int*)malloc(sizeof(int)*N);
    retval=get_places(N,&(data[2]),places);
    if(retval!=0)
    {
      printf("-1 there was an error while reading data\n");
      return -2;
    }
    retval=get_elo_games_lg(N,&data[2],elos,games,lg);
    if(retval!=0)
    {
      printf("-1 there was an error while reading data\n");
      return -2;
    }
    update_games_lg(N,games,lg,now);
    update_elo(N,places,elos,games,lg);
    print_results(N,elos,games,lg);
    free(elos);
    free(games);
    free(lg);
    free(places);
    free(data);
  }
  return 0;
}
