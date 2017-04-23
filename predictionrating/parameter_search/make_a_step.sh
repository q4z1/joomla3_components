#! /usr/bin/bash


# TODO: check $1 input parameter


# step 0: create lockfile, check if pid, etc.
# note: $$ (slightly different: $BASHPID) contain pid

# if file exists
if [ -a lockfile ] 
  then
  OLDPID=$(cat lockfile)
  if pidof bash | grep $OLDPID
    then
    exit 1 # bash instance still alive
  fi
fi
echo $$ > lockfile

# step 1: compile nextparams.hs, if not exists


if [ -a nextparams.out ]
then
  NOTHINGVAR=0
else
  ghc -O3 nextparams.hs -o nextparams.out
  if [ $? -eq 0 ]
  then
    echo "compiling complete"
    strip nextparams.out
    rm nextparams.hi
    rm nextparams.o
  else
    rm lockfile
    exit 2 # could not compile
  fi
fi

# step2: run nextparam
NP=$(./nextparams.out $1)

echo $NP

if [ "$NP" == "done" ]
then
  echo "----------------------------------"
  echo "----------------------------------"
  echo "DONE"
  rm lockfile
  exit 16
fi

bash getscore.sh $1 $NP

GSRC=$?
if [ $GSRC -ne 0 ]
then
  echo "error $GSRC in getscore.sh"
  exit 3
fi


# final step: done
rm lockfile
