#! /usr/bin/python

# python 2
import time
import json

sqlfile="game_has_player.sql"
jsonfile="gameslist.json"
jsonfile_p="playerlist.json"

print "read sql file..."
try:
  f=open(sqlfile,"r")
except:
  print "error: file",sqlfile,"not found"
  exit(1)

sqllines=f.read().split("\n")
f.close()
print "... done"


# step 1: convert into integer quadruples
# (gameid, playerid, place, unixtime)

print "converting sql data entries..."

def convert_sql_line(line):
  entries=line.split(",")
  if len(entries)<4: return False # error
  gameid=int(entries[0][1:])
  playerid=int(entries[1].strip())
  place=int(entries[2].strip())
  if entries[3].strip()=="NULL)":
    unixtime=1
    return (gameid,playerid,place,unixtime)
  if entries[3].count("'")<2: return False # error
  timestr=entries[3].split("'")[1]
  try:
    timetuple=time.strptime(timestr,"%Y-%m-%d %H:%M:%S")
  except:
    return False # error
  unixtime=int(time.mktime(timetuple))
  return (gameid,playerid,place,unixtime)

quad_db=[]
mode="not_yet"
for line in sqllines:
  if line[:6]=="INSERT" and mode=="not_yet":
    mode="read_entries"
    continue
  if line[:6]=="INSERT" and mode=="read_entries":
    continue
  if mode=="read_entries" and len(line)<3:
    mode="done_reading"
    continue
  if mode=="read_entries":
    q=convert_sql_line(line)
    if q==False:
      print "error while interpreting data",q,line
      exit(2)
    quad_db.append(q)
print "... done"    

print "ordering by games"
game_dict={} # dictionary
# structure: game[gameid]={"id":gameid,"t":time,"d":[(playerid,place),...]}
players=set()
game_time_pairs=[]


for entry in quad_db:
  gameid=entry[0]
  playerid=entry[1]
  place=entry[2]
  unixtime=entry[3]
  if gameid not in game_dict:
    game_dict[gameid]={"d":[],"t":unixtime,"id":gameid}
    game_time_pairs.append((gameid,unixtime))
  game_dict[gameid]["d"].append((playerid,place))
  players.add(playerid)

players=sorted(list(players))

game_time_pairs.sort(key=lambda x:x[1]) # sort by time
games_list=[]
for x in game_time_pairs:
  games_list.append(game_dict[x[0]])

print "... done"
print "json dumping and writing to file..."
output_gl=json.dumps(games_list)
output_p=json.dumps(players)

open(jsonfile,"w").write(output_gl)
open(jsonfile_p,"w").write(output_p)
print "...done"
  
