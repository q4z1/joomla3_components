
-- NOTE: do not execute this sql script yet!!


DROP FUNCTION IF EXISTS calc_final_score_b;
DROP FUNCTION IF EXISTS boehmipoints;

-- TODO: updatePointsForGame: switch at exact date

DELIMITER //
CREATE FUNCTION calc_final_score_b (points int, games int)
  RETURNS int(11) DETERMINISTIC
BEGIN
  IF games<1 THEN
    RETURN (-1);
  END IF;
  RETURN ROUND(1000*points / (games + 10)); -- resolution is times 1000
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


