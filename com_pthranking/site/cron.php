<?php
require(__DIR__ . "/defines.php");

$processUser = posix_getpwuid(posix_geteuid());
if($processUser['name'] != "root"){
    die("Access not allowed!\n");    
}

$table_prefix = date("Y") . "_" . ceil(date("m")/3) . "_";
// exception for beta phase 2016/2017
if(date("Y") == 2017 && date("m") < 4 && date("d") < 31){
    $table_prefix = "2016-4_";
}

$tables = array(
  "player",
  "player_ranking",
  "game",
  "game_has_player",
);

$db_name = RDB_DB;
$db_name .= "_test"; // @XXX: debug testing 1st - REMOVE in productive mode!

$db = new mysqli(RDB_HOST, RDB_USER, RDB_PASS, $db_name);
if ($db->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    exit();
}

$db->query("DELETE FROM `game_has_player` WHERE `game_idgame` = 0;"); // might not be needed anymore as the wrong dat issue with "0000-00-00 00:00:00" happened during adding the end_time field

// CREATE TABLE newtable LIKE oldtable; 
// INSERT newtable SELECT * FROM oldtable;
foreach($tables as $table){
    $db->query("CREATE TABLE `".$table_prefix.$table."` LIKE `".$table."`;");
    $db->query("INSERT `".$table_prefix.$table."` SELECT * FROM `".$table."`;");
    if($table != "player" && $table != "player_ranking"){
        $db->query("TRUNCATE `".$table."`;");
    }
    elseif($table == "player_ranking"){
        $db->query("UPDATE `".$table."` SET final_score = -1, points_sum = 0, season_games = 0, average_score = 0, games_seven_days = 0;");
    }
}

// finally truncate suspended_usernames
$db->query("TRUNCATE `suspended_usernames`;");

die("season reset done!\n");