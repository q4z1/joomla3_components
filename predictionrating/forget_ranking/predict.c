#include <stdio.h>
#include <stdlib.h>
// #include <math.h> /* maybe link with -lm */
#include "fit.h" /* contains math.h */
#include "coefficients.h"

/*
compile:
$ gcc -lm forget_predict.c fit.c -o forget_predict
execute:
$ ./forget_predict
*/

/* input format:
integers, seperated by spaces, ends with newline
1: N // number of players
1: time // as unix timestamp
for each player: 
 DATASIZE: data entries for the players

total size: N*DATASIZE+1

WARNING: this program will crash given wrong input

output format:
for each player:
  for each place:
    probability of reaching place i
start with -1 in case of error
*/
/* DATA format for forget ranking :
 score games last_game 0 0 ... 0
*/


int predict_first_places(int N,double*scores,double*out)
{
  double weight[N];
  int i=0;
  double weightsum=0.0;
  for(i=0;i<N;i++)
  {
    weight[i]=exp(scores[i]/COEF_PREDICT_1);
    weightsum+=weight[i];
  }
  for(i=0;i<N;i++)
  {
    out[i]=weight[i]/weightsum;
  }
  return 0;
}

int predict_other_places(int N,double*scores,double*inout,int place)
{
  /* place goes from 1-N */
  double weight[N];
  int i=0;
  double weightsum=0.0;
  for(i=0;i<N;i++)
  {
    weight[i]=exp(scores[i]/COEF_PREDICT_2);
    weightsum+=weight[i];
  }
  double secondsum=0.0;
  for(i=0;i<N;i++)
  {
    weight[i]=weight[i]/weightsum;
    double used_prob=0.0;
    int j=0;
    for(j=0;j<place-1;j++)
    {
      used_prob+=inout[j*N+i];
    }
//     if(used_prob>=1.0) used_prob=1.0;
    weight[i]=weight[i]*(1.0-used_prob);
    secondsum+=weight[i];
  }
  for(i=0;i<N;i++)
  {
    inout[(place-1)*N+i]=weight[i]/secondsum;
  }
  return 0;
}

int approximate_prediction(int N,double*scores,double*out)
{
  predict_first_places(N,scores,out);
  int i=0;
  for(i=2;i<N+1;i++)
  {
    predict_other_places(N,scores,out,i);
  }
  return 0;
}

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
  *out=(int*)malloc(sizeof(int)*(DATASIZE*N+2));
  (*out)[0]=N;
  for(i=1;i<N*DATASIZE+2;i++)
  {
    (*out)[i]=read_number();
    if((*out)[i]==-(1<<29)) return -2; /* error */
  }
  return 0;
}

int convert_to_scores(int N,int*in,double*out)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    out[i]=(in[DATASIZE*i])/(float)SCOREFACTOR;
  }
  return 0;
}

void print_results(int N,int*data)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    int j=0;
    for(j=0;j<N;j++)
    {
      // print players first
      printf("%d",data[j*N+i]);
      if(j!=N-1 || i!=N-1) printf(" "); // dont print space for last entry
    }
  }
  printf("\n"); // end of line
  fflush(stdout);
  return;
}

int main()
{
  int max_repititions=(1<<29);
//   int max_repititions=1;
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
    convert_to_scores(N,&(data[2]),scores);
    double*predictions=(double*)malloc(sizeof(double)*N*N);
    int*final_pred=(int*)malloc(sizeof(int)*N*N);
    approximate_prediction(N,scores,predictions);
  //   printmatrixf(N,predictions);
    fitprediction(N,predictions);
    roundprediction(N,predictions,final_pred);
    print_results(N,final_pred);

    free(scores);
    free(predictions);
    free(data);
    free(final_pred);
  }
  return 0;
}
