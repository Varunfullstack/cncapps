drop function IF EXISTS getToLogHours #
CREATE DEFINER =`root`@`localhost` FUNCTION `getToLogHours`(userID INTEGER, givenDate DATE, days INTEGER)
  RETURNS FLOAT
  BEGIN

    DECLARE result FLOAT;

    SELECT SUM(u.`dayHours`)
    INTO result
    FROM
      (SELECT *
       FROM
         user_time_log a
       WHERE a.userID = userID
             AND a.loggedDate <= givenDate
       ORDER BY a.loggedDate DESC
       LIMIT days) u;

    RETURN result;
  end
  #
    GRANT EXECUTE ON FUNCTION getToLogHours TO 'webuser'@'%'
