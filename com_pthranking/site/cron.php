<?php
require(__DIR__ . "/defines.php");

$processUser = posix_getpwuid(posix_geteuid());
if($processUser['name'] != "root"){
    die("Access not allowed!\n");    
}

$table_prefix = date("Y") . "-" . ceil(date("m")/3) . "_";
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
    // @XXX: temporary no truncate of game_has_player until there is a better solution
    if($table != "player" && $table != "player_ranking" && $table != "game" && $table != "game_has_player"){
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