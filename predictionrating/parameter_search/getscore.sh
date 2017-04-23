

# inputs:
#  prefix (e.g. forget)
#  sample_limit
#  predict_start
# coefficient vector

set -e

echo $0

if [ $0 == "getscore.sh" ]
then
  cd ..
elif [ $0 != "parameter_search/getscore.sh" ]
then
  echo "error: started in wrong path"
  exit 1
fi

# TODO: dont always recompile


echo "working directory:" $(pwd)

#step 1: compile with coefficient vector
cd $1
echo "compiling..."
bash compile.sh $4

# step 2: call prediction_rating.py with parameters

cd ..
echo "calculating predictions..."
python prediction_rating.py $1 $2 $3 > tmp1
mv tmp1 parameter_search/tmp1
cd parameter_search

# step 3: grep in output for result
grep ^result tmp1 > tmp2
cat tmp2 | tail -c +9 > tmp3
RESULT=$(cat tmp3)

re='^-?[0-9]+([.][0-9]+)?$'
if ! [[ $RESULT =~ $re ]]
then
  echo "error: result not recognised as a float"
  exit 3
fi

# step 4: write result in relevant file

echo "$2 $3 $4 $RESULT" >> $1.data

# rm tmp1
rm tmp2
rm tmp3
