#! /usr/bin/python
# python 2


import json
import time

print "simulation of forget ranking, but without weekly penalty"
time.sleep(2) # breathe and be patient
# TODO: weekly penalty

# coefficients!
forget_coef=0.04
points_coef=(15,9,6,4,3,2,1,0,0,0)
weakly_forget_penalty=0.6
# end coefficients


# import json database
print "reading files..."
player_file="playerlist.json"
games_file="gameslist.json"
game_db=json.loads(open(games_file,"r").read())
playerlist=json.loads(open(player_file,"r").read())
print "...done"

# note: format of game_db:
#   [{"id":gameid,"t":time,"d":[(playerid,place),...]},...]

def player_start_values(playerid):
  ret={}
  ret["id"]=playerid
  ret["score"]=0.0 # start
  ret["games"]=0 # not relevant
  ret["last_game"]=1 # 1970 - the beginning of time
  return ret

def player_new_score(oldscore,place):
  val=oldscore*(1.0-forget_coef)
  points_gain=points_coef[place-1]
  return val+points_gain


# init database
player_db={}
for x in playerlist:
  player_db[x]=player_start_values(x) # maybe .copy() ?


print "starting simulation of",len(game_db),"games..."
for game in game_db:
  # big loop over all the games

  for player,place in game["d"]:
    # small loop over all places

    player_db[player]["games"]+=1
    oldscore=player_db[player]["score"]
    if type(oldscore)==type(None):
      print oldscore,player,player_db[player]
    player_db[player]["score"]=player_new_score(oldscore,place)
    player_db[player]["last_game"]=game["t"]
    
print "...done"

print "result for player 1:", player_db[1]
