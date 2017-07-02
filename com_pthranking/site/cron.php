<?php
require(__DIR__ . "/defines.php");

$processUser = posix_getpwuid(posix_geteuid());
if($processUser['name'] != "root"){
    die("Access not allowed!\n");    
}


// start calculating table prefix

// get current unix time stampt
$now = time();

// // if you want to test the formula for $table_prefix on different days and times, use
// $now = strtotime("2017-09-30 04:21:00");
// // note that this will be in the default time zone


function get_season_prefix($timepoint)
{
  // warning! the result of this function can depend on the time zone
  // this function returns the season prefix
  // for the current season at a specified point in time

  // get season number from 1,2,3,4
  // jan,feb,mar -> 1, apr,may,jun -> 2, jul,aug,sep -> 3, oct,nov,dec -> 4
  $season_number = (int) ceil((date("m",$timepoint)-0.5) / 3 );

  // get year
  $year_number = (int) date("Y",$timepoint);

  // exception for beta phase 2016/2017
  if($year_number == 2017 && date("m",$timepoint) == 1){
      return "2016-4_";
  }
  return $year_number . "-" . $season_number . "_";
}


// get table prefix in an hour
$new_table_prefix = get_season_prefix(strtotime("+1 hour",$now));

// get old table prefix 24 hours ago
$old_table_prefix = get_season_prefix(strtotime("-24 hour",$now));

// only allow this script to execute if old_ and new_ are different
// this means we are at most 24 hours late and at most 1 hour early for a season reset
if($new_table_prefix == $old_table_prefix)
{
  printf("Errormessage: cron script not allowed at this time\n");
  exit();
}

// finally, the desired $table_prefix for this script is the one of the old season!
$table_prefix = $old_table_prefix;

// end calculating table prefix


$tables = array(
  "player",
  "player_ranking",
  "game",
  "game_has_player",
);

$db_name = RDB_DB;
//$db_name .= "_test"; // @XXX: debug testing 1st - REMOVE in productive mode!

$db = new mysqli(RDB_HOST, RDB_USER, RDB_PASS, $db_name);
if ($db->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    exit();
}

$result = $db->query("DELETE FROM `game_has_player` WHERE `game_idgame` = 0;"); // might not be needed anymore as the wrong date issue with "0000-00-00 00:00:00" happened during adding the end_time field
if(!$result){
    printf("Errormessage: %s\n", $db->error);
    exit();
}

// CREATE TABLE newtable LIKE oldtable; 
// INSERT newtable SELECT * FROM oldtable;
foreach($tables as $table){
    $result = $db->query("CREATE TABLE `".$table_prefix.$table."` LIKE `".$table."`;");
    if(!$result){
        printf("Errormessage: %s\n", $db->error);
        exit();
    }
    $result = $db->query("INSERT `".$table_prefix.$table."` SELECT * FROM `".$table."`;");
    if(!$result){
        printf("Errormessage: %s\n", $db->error);
        exit();
    }
    if($table != "player" && $table != "player_ranking" && $table != "game"){
        $result = $db->query("TRUNCATE `".$table."`;");
        if(!$result){
            printf("Errormessage: %s\n", $db->error);
            exit();
        }
    }
    elseif($table == "player_ranking"){
        $result = $db->query("UPDATE `".$table."` SET final_score = -1, points_sum = 0, season_games = 0, average_score = 0, games_seven_days = 0;");
        if(!$result){
            printf("Errormessage: %s\n", $db->error);
            exit();
        }
    }
}
// empty game table except open games
$start_limit = date('Y-m-d H:i:s', time()-(3600*8));
$result = $db->query("DELETE FROM `game` WHERE end_time IS NOT NULL OR start_time < '".$start_limit."';");
if(!$result){
    printf("Errormessage: %s\n", $db->error);
    exit();
}

// finally truncate suspended_usernames
$result = $db->query("TRUNCATE `suspended_usernames`;");
if(!$result){
    printf("Errormessage: %s\n", $db->error);
    exit();
}
die("season reset done!\n");
