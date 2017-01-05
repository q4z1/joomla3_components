#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <time.h> /* only for random*/

/* you have to link math.h with -lm */

#define probabilitysteps 1000 /* for rounding to integer */


int fitprediction(int N,double*inout);
void row_projections(int N,double*inout);
void col_projections(int N,double*inout);
double row_error(int N,double*in);
double col_error(int N,double*in);

int main()
{
  srand(time(NULL));
  int N=10;
  double data[100];
  createrandomdata(N,data);
  int retval=fitprediction(N,data);
  printf("return value: %d\n",retval);
  return 0;
}

// TODO: test with random values
// TODO: integer rounding

int createrandomdata(int N,double*out)
{
   int i=0;
   for(i=0;i<N*N;i++)
   {
     out[i]=(rand()&0xff)/((double)255.0);
   }
   return 0;
}

void fill_default_prediction_1(int N,double*out)
{
  int i=0;
  for(i=0;i<N*N;i++)
  {
    out[i]=1.0/N;
  }
  return;
}

int fitprediction(int N,double*inout)
{
  /* first step: scale such that sum==N */
  int i=0;
  double sum=0.0;
  for(i=0;i<N*N;i++)
  {
    if(inout[i]<0.0) inout[i]=0.0;
    sum+=inout[i];
  }
  printf("totalsum=%f\n",sum);
  if(sum<=0.0) return 1; /* error*/
  for(i=0;i<N*N;i++)
  {
    inout[i]*=N/sum;
  } /* done with scaling */

  double tolerance=1.0e-5;
  int iterlimit = N*N+0x42;
  for(i=0;i<iterlimit;i++)
  {
    row_projections(N,inout);
    double error=col_error(N,inout);
    printf("iteration %d, col_error=%.7f\n",i,error);
    if(error<tolerance) return 0; // done
    col_projections(N,inout);
    error=row_error(N,inout);
    printf("iteration %d, row_error=%.7f\n",i,error);
    if(error<tolerance) return 0; // done
  }
  return 2; /* another error */
}

#define x_row_col(a,b) inout[a*N+b]
#define x_col_row(a,b) inout[b*N+a]

/* we write the code once, copy it, and swap "row" and "col" */

/* start duplicate code */
void row_projections(int N,double*inout)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    /* projection of row i */
    double sum=0.0;
    int j=0;
    for(j=0;j<N;j++)
    {
      sum+=x_row_col(i,j);
    }
    if(sum<=0.0) /* error, load default */
    {
      for(j=0;j<N;j++)
      {
        x_row_col(i,j)=1.0/N;
      }
      continue;
    }
    /* now scale back */
    for(j=0;j<N;j++)
    {
      x_row_col(i,j)*=1.0/sum;
    }
  }
  return;
}

double row_error(int N,double*inout)
{
  int i=0;
  double total_error=0.0;
  for(i=0;i<N;i++)
  {
    /* calculate error of row i */
    double sum=0.0;
    int j=0;
    for(j=0;j<N;j++)
    {
      sum+=x_row_col(i,j);
    }
    total_error+=fabs(sum-1.0);
  }
  return total_error;
}
/* end duplicate code */


/* start copied code */

void col_projections(int N,double*inout)
{
  int i=0;
  for(i=0;i<N;i++)
  {
    /* projection of col i */
    double sum=0.0;
    int j=0;
    for(j=0;j<N;j++)
    {
      sum+=x_col_row(i,j);
    }
    if(sum<=0.0) /* error, load default */
    {
      for(j=0;j<N;j++)
      {
        x_col_row(i,j)=1.0/N;
      }
      continue;
    }
    /* now scale back */
    for(j=0;j<N;j++)
    {
      x_col_row(i,j)*=1.0/sum;
    }
  }
  return;
}

double col_error(int N,double*inout)
{
  int i=0;
  double total_error=0.0;
  for(i=0;i<N;i++)
  {
    /* calculate error of col i */
    double sum=0.0;
    int j=0;
    for(j=0;j<N;j++)
    {
      sum+=x_col_row(i,j);
    }
    total_error+=fabs(sum-1.0);
  }
  return total_error;
}

/* end copied code */


int roundprediction(int N,double*in,int*out)
{
  /* step 1: round every entry*/
  int i=0;
  for(i=0;i<N;i++)
  {
    out[i]=(int)round(in[i]*probabilitysteps);
  }
  /* step 2: determine total rounding error */
  int sum=0;
  for(i=0;i<N;i++)
  {
    sum+=out[i];
  }
  int total_rounding_error=sum-N*probabilitysteps;
  if( total_rounding_error>probabilitysteps) return 3; /* error, very unlikely */
  int sign=0;
  if(total_rounding_error>0) sign=1;
  if(total_rounding_error<0) sign=-1;
//   TODO: modify such that total_rounding_error is 0

// exchange in rows/cols, who has more abs(error)

// exchange errors between rows and cols

  return -1;
}
