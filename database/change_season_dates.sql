
DROP FUNCTION IF EXISTS `start_of_this_season`;

DELIMITER //
CREATE FUNCTION start_of_this_season ( now datetime)
 RETURNS datetime DETERMINISTIC
BEGIN
 DECLARE month,year varchar(10);
 SET month = DATE_FORMAT(now,"%m");
 IF month<4 THEN
  SET year = DATE_FORMAT(now,"%Y");
  IF year = 2017 AND month>1 THEN
    RETURN( DATE_FORMAT(now,"%Y-02-01 00:00:00"));
  END IF;
  RETURN( DATE_FORMAT(now,"%Y-01-01 00:00:00"));
--    RETURN (DATE_FORMAT(DATE_SUB(now,INTERVAL 99 DAY),"%Y-10-01 00:00:00"));
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
END //
DELIMITER ;
