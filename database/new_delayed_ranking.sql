
-- maybe test this script or read carefully

DROP FUNCTION IF EXISTS calc_final_score_b;
DROP FUNCTION IF EXISTS calc_average_score_b;
DROP FUNCTION IF EXISTS boehmipoints;
DROP PROCEDURE IF EXISTS updatePointsForGame;


DELIMITER //
CREATE FUNCTION calc_final_score_b (points int, games int)
  RETURNS int(11) DETERMINISTIC
BEGIN
  IF games<1 THEN
    RETURN (-1);
  END IF;
  RETURN (ROUND(10000*points / (games + 10))); -- resolution is times 10000
END //
DELIMITER ;

DELIMITER //
CREATE FUNCTION calc_average_score_b (points int,games int)
  RETURNS int(11) DETERMINISTIC
BEGIN
  IF games<1 THEN
    RETURN(0);
  END IF;
  RETURN (ROUND(1000000.0*points / (6.2*games)));
  -- this is only a scaled version of the average,
  -- such that it is displayed correctly without code changes in profile
END //
DELIMITER ;


DELIMITER //
CREATE FUNCTION boehmipoints (xplace int)
  RETURNS int(11) DETERMINISTIC
BEGIN
  CASE xplace
    WHEN 1 THEN
      RETURN(375);
    WHEN 2 THEN
      RETURN(225);
    WHEN 3 THEN
      RETURN(150);
    WHEN 4 THEN
      RETURN(100);
    WHEN 5 THEN
      RETURN(75);
    WHEN 6 THEN
      RETURN(50);
    WHEN 7 THEN
      RETURN(25);
    ELSE
      RETURN(0);
  END CASE;
  RETURN(0);
END //
DELIMITER ;




-- the following proc has been updated from the old version, using end_time instead of start_time for points

DELIMITER //
CREATE PROCEDURE updatePointsForGame -- actually we want to do more here
( IN x_idgame int(11))
BEGIN
  DECLARE b,a,c,d,e INT;
  DECLARE xplayer,xplace,games_week INT;
  DECLARE season_start datetime;
  DECLARE now,this_game_start,prev_game_start datetime;
  DECLARE cur1 CURSOR FOR SELECT player_idplayer,place FROM `game_has_player` WHERE game_idgame=x_idgame;
--   DECLARE CONTINUE HANDLER FOR NOT FOUND SET b=1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
      SELECT 1 INTO b FROM (SELECT 1) as sdfsdfsd;
    END;
  OPEN cur1;
  SELECT `end_time` INTO now FROM `game` WHERE idgame=x_idgame;
  SELECT `start_time` INTO this_game_start FROM `game` WHERE idgame=x_idgame;
--   CALL start_of_this_season(now,season_start);
  SET season_start=start_of_this_season(now);
  UPDATE game_has_player 
    SET start_time=this_game_start, end_time=now
    WHERE game_idgame=x_idgame; -- update start and end_time
-- starting loop over 10 players from the game
  SET b=0;
  mywhile: LOOP
    FETCH cur1 INTO xplayer,xplace;
    IF b=1 THEN
      LEAVE mywhile;
    END IF;
    IF now<'2017-02-01 00:00:01' THEN -- old ranking
      SELECT SUM(pointsforplace(place)) INTO d FROM (
        SELECT place
        FROM `game_has_player`
        WHERE player_idplayer=xplayer AND end_time>=season_start
        ORDER BY end_time DESC
        LIMIT 100 -- fuck limits
      ) as whydoesmysqlrequirethatiuseanaliashere; -- sum of points in the last 100 games
      SELECT COUNT(*) INTO c
        FROM `game_has_player`
        WHERE player_idplayer=xplayer AND end_time>=season_start;
      SELECT `games_seven_days` INTO games_week
        FROM `player_ranking` WHERE player_id=xplayer; -- temporary save #games of last 7 days
      UPDATE player_ranking
        SET `points_sum` = d , `season_games`=c,
          `average_score`=calc_average_score(d,c),
          `games_seven_days`=games_week+1,
          `final_score` = calc_final_score(d,c,games_week+1)
        WHERE player_id=xplayer; -- updatee everything
    END IF;
    IF now>'2017-02-01 00:00:00' THEN -- new ranking (delayed avg)
      SELECT COUNT(*),SUM(boehmipoints(place)) INTO c,d
        FROM `game_has_player`
        WHERE player_idplayer=xplayer AND end_time>=season_start
        ORDER BY end_time DESC; -- read points
      UPDATE player_ranking
        SET `points_sum` = d , `season_games`=c,
          `average_score`=calc_average_score_b(d,c),
          `final_score` = calc_final_score_b(d,c)
        WHERE player_id=xplayer;
    END IF;
  END LOOP mywhile;
-- ending loop
  CLOSE cur1;
  IF now<'2017-02-01 00:00:01' THEN -- old ranking only
    SET prev_game_start = now;
    SELECT start_time INTO prev_game_start
      FROM `game` WHERE `start_time` < now
      ORDER BY start_time DESC
      LIMIT 1;
    CALL update_activity_malus(prev_game_start,now); -- for updating malus for certain players
  END IF;
END //
DELIMITER ;


