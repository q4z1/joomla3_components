#! /usr/bin/python

# python 2

import sys
from subprocess import Popen, PIPE
import json
import random
import time

DATASIZE=24
probabilitysteps=1000

prefix="forget_ranking"
predict_binary=prefix+"/predict"
game_binary=prefix+"/game"

# NOTE: no inactivity penalty

print "reading database files..."
player_file="playerlist.json"
games_file="gameslist.json"
game_db=json.loads(open(games_file,"r").read())
playerlist=json.loads(open(player_file,"r").read())
print "...done"

# TODO: maybe compilation

# note: format of game_db:
#   [{"id":gameid,"t":time,"d":[(playerid,place),...]},...]

def player_start_data(): 
  return [0]*DATASIZE
# TODO: init binary

#init database
player_db={}
for x in playerlist:
  player_db[x]=player_start_data() # maybe .copy() ?

total_prediction_error=0

def output2list(text):
  if type(text)!=type(""):
    text=str(text)
  ret=[]
  for x in text.split(" "):
    try:
      y=int(x)
      ret.append(y)
    except:
      continue
  return ret

max_game_error=probabilitysteps**2* (2*10**2)

def game_prediction_error(pred,res):
  pred=output2list(pred)
  # pred ist list of ints now
  N=len(res)
  if(len(pred)!=N*N):
    print "error:", len(pred),len(res)
    return max_game_error # bug!
  # part 1: evaluate winners
  win_error=0
  for i in xrange(N):
    prob=pred[i*N]
    r=0
    if res[i][1]==1: r=probabilitysteps
    win_error+=(r-prob)**2
  place_error=0
  for place in xrange(N): # actually place-1
    for i in xrange(N):
      prob=pred[i*N+place]
      r=0
      if res[i][1]==place+1: r=probabilitysteps
      place_error+=(r-prob)**2
  return N*win_error+place_error

sample_limit=9999 # limit number of games to be analyzed
# predict_start=1000
predict_start=0
counter=0

p_pred=Popen(["./"+predict_binary],stdout=PIPE,stdin=PIPE)
p_game=Popen(["./"+game_binary],stdout=PIPE,stdin=PIPE)

for game in game_db:
  # big loop over all the games
  if counter>=sample_limit: break
  if counter%200==0 and counter>0:
    print counter,"games calculated"
  counter+=1

  N=len(game["d"])
  t=game["t"]
  predict_input=[N,t]
  game_input=[N,t]
  # gather data
  for player,place in game["d"]:
    predict_input+=player_db[player]
    game_input+=[place]+player_db[player]
  if counter>predict_start:
    predict_input=" ".join(map(str,predict_input))+"\n"
#     p_pred=Popen(["./"+predict_binary],stdout=PIPE,stdin=PIPE)
    p_pred.stdin.write(buffer(predict_input))
    p_pred.stdin.flush()
    predict_output=p_pred.stdout.readline()
#     predict_output,err=p_pred.communicate(buffer(predict_input))
    total_prediction_error+=game_prediction_error(predict_output,game["d"])
  game_input=" ".join(map(str,game_input))+"\n"
#   p=Popen(["./"+game_binary],stdout=PIPE,stdin=PIPE)
  p_game.stdin.write(buffer(game_input))
  p_game.stdin.flush()
  game_output=p_game.stdout.readline()
#   game_output,err=p.communicate(buffer(game_input))
  game_output=output2list(game_output)
  for x in xrange(N):
    player=game["d"][x][0]
    player_db[player]=game_output[DATASIZE*x:DATASIZE*(x+1)] # new DATA

average_prediction_error=float(total_prediction_error)/(counter-predict_start)
normalized_prediction_error=average_prediction_error/(probabilitysteps**2)
print total_prediction_error,normalized_prediction_error
