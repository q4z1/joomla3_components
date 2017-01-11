#include <stdio.h>
#include <stdlib.h>
#include "fit.h"
#include <time.h> /* only for random*/

/* you have to link math.h with -lm */
/* compilation: 
$ gcc -lm fit.c
*/

#define probabilitysteps 1000 /* for rounding to integer */


void row_projections(int N,double*inout);
void col_projections(int N,double*inout);
int createrandomdata(int N,double*out);
double row_error(int N,double*in);
double col_error(int N,double*in);

void printmatrix(int N,int*data)
{
  int i,j;
  for(i=0;i<N;i++)
  {
    for(j=0;j<N;j++)
    {
      printf("%3d ",data[i*N+j]);
    }
    printf("\n");
  }
}

void printmatrixf(int N,double*data)
{
  int i,j;
  for(i=0;i<N;i++)
  {
    for(j=0;j<N;j++)
    {
      printf("%7.4f ",data[i*N+j]);
    }
    printf("\n");
  }
}


/*
int main()
{
  srand(time(NULL));
  int N=10;
  double data[100];
  int rounded_data[100];
  createrandomdata(N,data);
  int retval=fitprediction(N,data);
  printf("return value: %d\n",retval);
  retval=roundprediction(N,data,rounded_data);
  printf("return value: %d\n",retval);
  printmatrix(N,rounded_data);
  return 0;
}
*/

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
//   printf("totalsum=%f\n",sum);
  if(sum<=0.0) return 1; /* error*/
  for(i=0;i<N*N;i++)
  {
    inout[i]*=N/sum;
  } /* done with scaling */

  double tolerance=1.0e-5;
  int iterlimit = N*N+0x42;
  for(i=0;i<iterlimit;i++)
  {
    // TODO: do two projections at the same time, in order to not put cols or rows in advantage
    row_projections(N,inout);
    double error=col_error(N,inout);
//     printf("iteration %d, col_error=%.7f\n",i,error);
    if(error<tolerance) return 0; // done
    col_projections(N,inout);
    error=row_error(N,inout);
//     printf("iteration %d, row_error=%.7f\n",i,error);
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

#define y_col_row(xx,a,b) xx[b*N+a]
#define y_row_col(xx,a,b) xx[a*N+b]


void loadrowerrors(int N,int*in,int*out)
{ /* put difference to probabilitysteps for each row in out */
  int i=0;
  int j=0;
  for(i=0;i<N;i++)
  {
    int sum=0;
    for(j=0;j<N;j++)
    {
      sum+=y_row_col(in,i,j);
    }
    out[i]=sum-probabilitysteps;
  }
  return;
}

void loadcolerrors(int N,int*in,int*out)
{ /* put difference to probabilitysteps for each col in out */
  int i=0;
  int j=0;
  for(i=0;i<N;i++)
  {
    int sum=0;
    for(j=0;j<N;j++)
    {
      sum+=y_col_row(in,i,j);
    }
    out[i]=sum-probabilitysteps;
  }
  return;
}

int summation(int N,int*in)
{
  int sum=0;
  int i=0;
  for(i=0;i<N;i++)
  {
    sum+=in[i];
  }
  return sum;
}

int abssum(int N,int*in)
{
  int sum=0;
  int i=0;
  for(i=0;i<N;i++)
  {
    sum+=abs(in[i]);
  }
  return sum;
}


int errorexchange_row(int N,double*data,int*inout,int*row_errors,int*col_errors)
{
  /* exchange the rounding errors between rows and columns, from row */
  int col_abssum=abssum(N,col_errors);
  int total_sum=summation(N,row_errors);
  int sign=1;
  if (total_sum>=0) sign=1; /* yes, even for ==0 */
  if (total_sum<0) sign=-1;
  if(total_sum==0 && col_abssum==0)
  {
    ; // TODO: only exchange between rows
    /* find pair of rows such that one is >0, one <0 */
    int i=0;
    int row_pos=-1;
    int row_neg=-1;
    for(i=0;i<N;i++)
    {
      if(row_errors[i]>0) row_pos=i;
      if(row_errors[i]<0) row_neg=i;
    }
    if(row_pos==-1 || row_neg==-1) return 1; /* error */
    /* select best column index for exchange*/
    double minval=2*probabilitysteps;
    int mod_index=0;
    for(i=0;i<N;i++)
    {
      if(x_row_col(row_pos,i)<=0) continue;
      double a_pos=probabilitysteps*y_row_col(data,row_pos,i);
      int newval_pos=y_row_col(inout,row_pos,i)-1;
      double a_neg=probabilitysteps*y_row_col(data,row_neg,i);
      int newval_neg=y_row_col(inout,row_neg,i)+1;
      double cur_value=fabs(a_neg-newval_neg)+fabs(a_pos-newval_pos);
      if(cur_value<minval)
      {
        minval=cur_value;
        mod_index=i;
      }
    }
    /* do the modification */
    x_row_col(row_pos,mod_index)-=1;
    x_row_col(row_pos,mod_index)+=1;
    row_errors[row_pos]-=1;
    row_errors[row_neg]+=1;
    return 0;
  }
  int maxindex=0;
  {
    /* get max (or min if sign==-1) index for row_errors */
    int maxval=0;
    int i=0;
    for(i=0;i<N;i++)
    {
      if(sign*row_errors[i]>=maxval)
      {
        maxval=sign*row_errors[i];
        maxindex=i;
      }
    }
  }
  /* select best index for rounding in other direction */
  int i=0;
  int mod_index=0;
  double minval=2.0*probabilitysteps;
  for(i=N-1;i>=0;i--)
  {
    if(sign==1 && x_row_col(maxindex,i)==0) continue; // cannot round down
    if(col_abssum==0 || sign*col_errors[i]>0) /* preferably same sign */
    {
      double a=probabilitysteps*y_row_col(data,maxindex,i);
      int newval=y_row_col(inout,maxindex,i)-sign;
      double cur_value=fabs(a-newval);
      if(cur_value<minval)
      {
        minval=cur_value;
        mod_index=i;
      }
    }
  }
  /* do the modification */
  x_row_col(maxindex,mod_index)-=sign;
  row_errors[maxindex]-=sign;
  col_errors[mod_index]-=sign;
  return 0;
}


int errorexchange_col(int N,double*data,int*inout,int*col_errors,int*row_errors)
{
  /* exchange the rounding errors between cols and rowumns, from col */
  int row_abssum=abssum(N,row_errors);
  int total_sum=summation(N,col_errors);
  int sign=1;
  if (total_sum>=0) sign=1; /* yes, even for ==0 */
  if (total_sum<0) sign=-1;
  if(total_sum==0 && row_abssum==0)
  {
    ; // TODO: only exchange between cols
    /* find pair of cols such that one is >0, one <0 */
    int i=0;
    int col_pos=-1;
    int col_neg=-1;
    for(i=0;i<N;i++)
    {
      if(col_errors[i]>0) col_pos=i;
      if(col_errors[i]<0) col_neg=i;
    }
    if(col_pos==-1 || col_neg==-1) return 1; /* error */
    /* select best rowumn index for exchange*/
    double minval=2*probabilitysteps;
    int mod_index=0;
    for(i=0;i<N;i++)
    {
      if(x_col_row(col_pos,i)<=0) continue;
      double a_pos=probabilitysteps*y_col_row(data,col_pos,i);
      int newval_pos=y_col_row(inout,col_pos,i)-1;
      double a_neg=probabilitysteps*y_col_row(data,col_neg,i);
      int newval_neg=y_col_row(inout,col_neg,i)+1;
      double cur_value=fabs(a_neg-newval_neg)+fabs(a_pos-newval_pos);
      if(cur_value<minval)
      {
        minval=cur_value;
        mod_index=i;
      }
    }
    /* do the modification */
    x_col_row(col_pos,mod_index)-=1;
    x_col_row(col_pos,mod_index)+=1;
    col_errors[col_pos]-=1;
    col_errors[col_neg]+=1;
    return 0;
  }
  int maxindex=0;
  {
    /* get max (or min if sign==-1) index for col_errors */
    int maxval=0;
    int i=0;
    for(i=0;i<N;i++)
    {
      if(sign*col_errors[i]>=maxval)
      {
        maxval=sign*col_errors[i];
        maxindex=i;
      }
    }
  }
  /* select best index for rounding in other direction */
  int i=0;
  int mod_index=0;
  double minval=2.0*probabilitysteps;
  for(i=N-1;i>=0;i--)
  {
    if(sign==1 && x_col_row(maxindex,i)==0) continue; // cannot round down
    if(row_abssum==0 || sign*row_errors[i]>0) /* preferably same sign */
    {
      double a=probabilitysteps*y_col_row(data,maxindex,i);
      int newval=y_col_row(inout,maxindex,i)-sign;
      double cur_value=fabs(a-newval);
      if(cur_value<minval)
      {
        minval=cur_value;
        mod_index=i;
      }
    }
  }
  /* do the modification */
  x_col_row(maxindex,mod_index)-=sign;
  col_errors[maxindex]-=sign;
  row_errors[mod_index]-=sign;
  return 0;
}


int roundprediction(int N,double*in,int*out)
{
  /* step 1: round every entry*/
  int i=0;
  for(i=0;i<N*N;i++)
  {
    out[i]=(int)round(in[i]*probabilitysteps);
  }

  /* step 2: calculate errors for each row and col */
  int row_errors[N];
  int col_errors[N];
  loadrowerrors(N,out,row_errors);
  loadcolerrors(N,out,col_errors);

  int iterationlimit=N*N*3+0x42;
//   int iterationlimit=0x20;
  for(i=0;i<iterationlimit;i++)
  {
    int col_abssum=abssum(N,col_errors);
    int row_abssum=abssum(N,row_errors);
    int max_abssum= col_abssum>row_abssum ? col_abssum:row_abssum;
    if(max_abssum==0) return 0; // we are done
//     printf("iteration %i, col_abssum=%d, row_abssum=%d\n",i,col_abssum,row_abssum);
    if(max_abssum==row_abssum)
    {
     errorexchange_row(N,in,out,row_errors,col_errors);
    }
    else
    {
     errorexchange_col(N,in,out,col_errors,row_errors);
    }
  }
  return -1;
}
