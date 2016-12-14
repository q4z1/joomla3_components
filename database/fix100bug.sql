
DROP FUNCTION IF EXISTS recalc;
DROP PROCEDURE IF EXISTS fixthehundredgamebug_a;
DROP PROCEDURE IF EXISTS updatePointsForGame;



DELIMITER //
CREATE FUNCTION recalc (xplayer int,now datetime) -- recalculate points for a player
RETURNS int DETERMINISTIC
BEGIN
  DECLARE c INT;
  DECLARE d INT;
  DECLARE games_week INT;
  DECLARE season_start, week_ago datetime;
  SET season_start=start_of_this_season(now); -- start of this season
  SET week_ago = GREATEST(season_start,DATE_SUB(now,INTERVAL 7 DAY)); -- starting point for 7 days rule
  SELECT COUNT(*) INTO games_week FROM game_has_player
      WHERE player_idplayer=xplayer AND start_time>=week_ago; -- count games for 7 days rule
  SELECT SUM(pointsforplace(place)) INTO d FROM (
    SELECT place
    FROM `game_has_player`
    WHERE player_idplayer=xplayer AND start_time>=season_start
    ORDER BY start_time DESC
    LIMIT 100 -- fuck limits
  ) as whydoesmysqlrequirethatiuseanaliashere; -- selects sum of points for last 100 games
  SELECT COUNT(*) INTO c
    FROM `game_has_player`
    WHERE player_idplayer=xplayer AND start_time>=season_start; -- selects number of games (this season)
  UPDATE player_ranking
    SET `points_sum` = d , `season_games`=c,
      `average_score`=calc_average_score(d,c),
      `games_seven_days`=games_week,
      `final_score` = calc_final_score(d,c,games_week) -- update new stuff
    WHERE player_id=xplayer;
  RETURN(0); -- not important
END //
DELIMITER ;


DELIMITER // 
CREATE PROCEDURE fixthehundredgamebug_a () -- call this one to call recalc for all players >= 100 games
BEGIN
  DECLARE c,b,xplayer INT;
  DECLARE now datetime;
  DECLARE cur3 CURSOR FOR SELECT player_id
     FROM player_ranking
     WHERE season_games>=100; -- loop over all relevant players
  DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
      SELECT 1 INTO b FROM (SELECT 1) as sdfsd;
    END;
--   SET season_start='2018-01-01'; -- supernoob test data
  SELECT MAX(start_time) INTO now FROM game; -- our definition of "now" - for now
  OPEN cur3;
  myloop_a: LOOP
    FETCH cur3 INTO xplayer;
    IF b=1 THEN
        LEAVE myloop_a; -- exit loop
    END IF;
    SET c = recalc(xplayer,now); -- return value not needed,
  END LOOP myloop_a;
  CLOSE cur3;
END //
DELIMITER ;


-- the following proc has been updated from the old version, using proper sums/limits

DELIMITER //
CREATE PROCEDURE updatePointsForGame -- actually we want to do more here
( IN x_idgame int(11))
BEGIN
  DECLARE b,a,c,d INT;
  DECLARE xplayer,xplace,games_week INT;
  DECLARE season_start datetime;
  DECLARE now,prev_game_start datetime;
  DECLARE cur1 CURSOR FOR SELECT player_idplayer,place FROM `game_has_player` WHERE game_idgame=x_idgame;
--   DECLARE CONTINUE HANDLER FOR NOT FOUND SET b=1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
      SELECT 1 INTO b FROM (SELECT 1) as sdfsdfsd;
    END;
  OPEN cur1;
  SELECT `start_time` INTO now FROM `game` WHERE idgame=x_idgame;
--   CALL start_of_this_season(now,season_start);
  SET season_start=start_of_this_season(now);
  UPDATE game_has_player SET start_time=now WHERE game_idgame=x_idgame;
-- starting loop over 10 players from the game
  SET b=0;
  mywhile: LOOP
    FETCH cur1 INTO xplayer,xplace;
    IF b=1 THEN
      LEAVE mywhile;
    END IF;
    SELECT SUM(pointsforplace(place)) INTO d FROM (
      SELECT place
      FROM `game_has_player`
      WHERE player_idplayer=xplayer AND start_time>=season_start
      ORDER BY start_time DESC
      LIMIT 100 -- fuck limits
    ) as whydoesmysqlrequirethatiuseanaliashere; -- sum of points in the last 100 games
    SELECT COUNT(*) INTO c
      FROM `game_has_player`
      WHERE player_idplayer=xplayer AND start_time>=season_start;
    SELECT `games_seven_days` INTO games_week
      FROM `player_ranking` WHERE player_id=xplayer; -- temporary save #games of last 7 days
    UPDATE player_ranking
      SET `points_sum` = d , `season_games`=c,
        `average_score`=calc_average_score(d,c),
        `games_seven_days`=games_week+1,
        `final_score` = calc_final_score(d,c,games_week+1)
      WHERE player_id=xplayer; -- updatee everything
  END LOOP mywhile;
-- ending loop
  CLOSE cur1;
  SET prev_game_start = now;
  SELECT start_time INTO prev_game_start
    FROM `game` WHERE `start_time` < now
    ORDER BY start_time DESC
    LIMIT 1;
  CALL update_activity_malus(prev_game_start,now); -- for updating malus for certain players
END //
DELIMITER ;



CALL fixthehundredgamebug_a(); -- TODO: comment this after a one-time use :)
