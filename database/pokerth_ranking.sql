-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Erstellungszeit: 24. Nov 2016 um 15:49
-- Server Version: 5.5.53-0+deb8u1
-- PHP-Version: 5.6.27-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DELIMITER $$
--
-- Prozeduren
--
DROP PROCEDURE IF EXISTS `new_season`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `new_season`( )
BEGIN
  UPDATE `player_ranking` SET final_score=-1, points_sum=0,
     season_games=0, average_score=0,games_seven_days=0 -- everywhere
     WHERE 1;
END$$

DROP PROCEDURE IF EXISTS `updatePointsForGame`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `updatePointsForGame`( IN x_idgame int(11))
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
    SELECT COUNT(*),SUM(pointsforplace(place)) INTO c,d
      FROM `game_has_player`
      WHERE player_idplayer=xplayer AND start_time>=season_start
      ORDER BY start_time DESC
      LIMIT 100; -- LAST hundred games this season
    SELECT `games_seven_days` INTO games_week
      FROM `player_ranking` WHERE player_id=xplayer;
    UPDATE player_ranking
      SET `points_sum` = d , `season_games`=c,
        `average_score`=calc_average_score(d,c),
        `games_seven_days`=games_week+1,
        `final_score` = calc_final_score(d,c,games_week+1)
      WHERE player_id=xplayer;
  END LOOP mywhile;
-- ending loop
  CLOSE cur1;
  -- TODO: update 7-day-malus
  SET prev_game_start = now;
  SELECT start_time INTO prev_game_start
    FROM `game` WHERE `start_time` < now  -- TODO: what if NULL
    ORDER BY start_time DESC
    LIMIT 1;
  CALL update_activity_malus(prev_game_start,now); -- TODO: this is majority of calculating time :(
  -- TODO: figure out why ...
  -- TODO: set in_ranking_calculation
END$$

DROP PROCEDURE IF EXISTS `update_activity_malus`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_activity_malus`( IN time_start datetime, IN time_end datetime)
BEGIN
  DECLARE a,b,xplayer INT;
  DECLARE week_ago datetime; -- considering season_start
  DECLARE cur2 CURSOR FOR SELECT DISTINCT player_idplayer FROM `game_has_player` -- select distinct
    WHERE start_time>=DATE_SUB(time_start,INTERVAL 7 DAY) 
      AND start_time<=DATE_SUB(time_end, INTERVAL 7 DAY);
--   DECLARE CONTINUE HANDLER FOR NOT FOUND SET b=1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
      SELECT 1 INTO b FROM (SELECT 1) as sdfsd;
    END;
  SET week_ago = GREATEST(start_of_this_season(time_end),DATE_SUB(time_end,INTERVAL 7 DAY));
  OPEN cur2;
  myloop: LOOP
    FETCH cur2 INTO xplayer;
    -- TODO
    IF b=1 THEN
      LEAVE myloop;
    END IF;
    SELECT COUNT(*) INTO a FROM game_has_player
      WHERE player_idplayer=xplayer AND start_time>=week_ago;
    IF a>6 THEN
      UPDATE player_ranking SET `games_seven_days`=a
        WHERE player_id=xplayer;
    END IF;
    IF a<7 THEN -- not bug free
      UPDATE player_ranking 
        SET `games_seven_days`=a, 
        `final_score` = calc_final_score(`points_sum`,`season_games`,a)
        WHERE player_id=xplayer;
    END IF;
  END LOOP myloop;
  CLOSE cur2;
END$$

--
-- Funktionen
--
DROP FUNCTION IF EXISTS `calc_average_score`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `calc_average_score`(points int, games int) RETURNS int(11)
    DETERMINISTIC
BEGIN
  IF games<100 THEN
    RETURN(ROUND((points*(1000*1000.0/6.2))/games)); -- average 6.2 points per game
  END IF;
  RETURN(ROUND(points*10000.0/6.2)); -- assuming 100 games
END$$

DROP FUNCTION IF EXISTS `calc_final_score`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `calc_final_score`(points int, games int, lastweek int) RETURNS int(11)
    DETERMINISTIC
BEGIN
  DECLARE inactivity_malus INT; -- in percent
  DECLARE xscore INT;
  SET inactivity_malus=0;
  SET xscore=calc_average_score(points,games);
  SET xscore=ROUND(xscore*(10000.0+LEAST(500,games))/10000.0); -- small game bonus , TODO: max 500 games
  IF games<1 THEN
    RETURN(-1);
  END IF;
  IF games<30 THEN
    SET xscore=ROUND(xscore*games/30.0); -- first 30-day malus
  END IF;
  IF lastweek<6 THEN
--     SET inactivity_malus=5*(6-lastweek); -- appareantly this is wrong
    SET inactivity_malus=5*(10-lastweek); --
  END IF;
  RETURN(ROUND(xscore *(100.0-inactivity_malus)/100.0));
END$$

DROP FUNCTION IF EXISTS `pointsforplace`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `pointsforplace`( xplace int) RETURNS int(11)
    DETERMINISTIC
BEGIN
    CASE xplace
      WHEN 1 THEN
        RETURN(24);
      WHEN 2 THEN
        RETURN(16);
      WHEN 3 THEN
        RETURN(10);
      WHEN 4 THEN
        RETURN(6);
      WHEN 5 THEN
        RETURN(3);
      WHEN 6 THEN
         RETURN(2);
      WHEN 7 THEN
        RETURN(1);
      ELSE
        RETURN(0);
    END CASE;
    RETURN(0);
END$$

DROP FUNCTION IF EXISTS `rank`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `rank`(xfinal int,xgames int,xid int) RETURNS int(11)
    DETERMINISTIC
BEGIN
  DECLARE res int;
  SELECT (
    SELECT COUNT(*) FROM player_ranking 
      WHERE final_score>xfinal ) + (
    SELECT COUNT(*) FROM player_ranking
      WHERE final_score=xfinal AND season_games>xgames )+(
    SELECT COUNT(*) FROM player_ranking
      WHERE final_score=xfinal AND season_games=xgames
      AND player_id<xid)+1 INTO res;
 RETURN(res);
END$$

DROP FUNCTION IF EXISTS `start_of_this_season`$$
CREATE DEFINER=`root`@`localhost` FUNCTION `start_of_this_season`(now datetime) RETURNS datetime
    DETERMINISTIC
BEGIN
 DECLARE month varchar(10);
 SET month = DATE_FORMAT(now,"%m");
 IF month<4 THEN
  RETURN( DATE_FORMAT(now,"%Y-01-01 00:00:00"));
 END IF;
 IF month>3 AND month<7 THEN
--    SET season_start = DATE_FORMAT(now,"%Y-04-01");
   RETURN( DATE_FORMAT(now,"%Y-04-01 00:00:00"));
 END IF;
 IF month>6 AND month<10 THEN
--    SET season_start = DATE_FORMAT(now,"%Y-07-01");
   RETURN( DATE_FORMAT(now,"%Y-07-01 00:00:00"));
 END IF;
 IF month>9 THEN
--    SET season_start = DATE_FORMAT(now,"%Y-10-01");
   RETURN( DATE_FORMAT(now,"%Y-10-01 00:00:00"));
 END IF;
 RETURN(now); -- ERROR
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `game`
--

DROP TABLE IF EXISTS `game`;
CREATE TABLE IF NOT EXISTS `game` (
`idgame` int(13) NOT NULL,
  `name` varchar(64) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `game_has_player`
--

DROP TABLE IF EXISTS `game_has_player`;
CREATE TABLE IF NOT EXISTS `game_has_player` (
  `game_idgame` int(13) NOT NULL,
  `player_idplayer` int(11) NOT NULL,
  `place` int(4) NOT NULL,
  `start_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `player_ranking`
--

DROP TABLE IF EXISTS `player_ranking`;
CREATE TABLE IF NOT EXISTS `player_ranking` (
  `player_id` int(11) NOT NULL,
  `final_score` int(11) NOT NULL DEFAULT '-1',
  `username` varchar(64) NOT NULL,
  `points_sum` int(11) NOT NULL DEFAULT '0',
  `season_games` int(11) NOT NULL DEFAULT '0',
  `average_score` int(13) NOT NULL DEFAULT '0',
  `games_seven_days` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A view to see the simulated real world earnings and the efficiency.
-- The simulated real world earnings are calculated just like real world
-- SNGs:
--  1st         place: +4
--  2nd         place: +2
--  3rd         place: +1
--  4th to 10th place: -1
--
-- The efficiency is just the average earnings per game: earnings / games.
--
CREATE VIEW money_list AS
SELECT a.`player_idplayer`,
  (SELECT COUNT(*) FROM `game_has_player` WHERE `player_idplayer` = a.`player_idplayer` AND `place` = 1) * 4 +
  (SELECT COUNT(*) FROM `game_has_player` WHERE `player_idplayer` = a.`player_idplayer` AND `place` = 2) * 2 +
  (SELECT COUNT(*) FROM `game_has_player` WHERE `player_idplayer` = a.`player_idplayer` AND `place` = 3) -
  (SELECT COUNT(*) FROM `game_has_player` WHERE `player_idplayer` = a.`player_idplayer` AND `place` > 3) AS earnings,
  (SELECT COUNT(DISTINCT `game_idgame`) FROM `game_has_player` WHERE `player_idplayer` = a.`player_idplayer`) AS games,
  (SELECT earnings / games) AS efficiency
FROM `game_has_player` a
GROUP BY `player_idplayer`;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `game`
--
ALTER TABLE `game`
 ADD PRIMARY KEY (`idgame`), ADD KEY `start_time` (`start_time`);

--
-- Indizes für die Tabelle `game_has_player`
--
ALTER TABLE `game_has_player`
 ADD KEY `start_time` (`start_time`), ADD KEY `player_idplayer` (`player_idplayer`,`start_time`,`place`), ADD KEY `game_idgame` (`game_idgame`), ADD KEY `player_idplayer_2` (`player_idplayer`,`place`);

--
-- Indizes für die Tabelle `player_ranking`
--
ALTER TABLE `player_ranking`
 ADD PRIMARY KEY (`player_id`), ADD KEY `final_score` (`final_score`), ADD KEY `final_score_2` (`final_score`,`season_games`,`player_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `game`
--
ALTER TABLE `game`
MODIFY `idgame` int(13) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=0;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
