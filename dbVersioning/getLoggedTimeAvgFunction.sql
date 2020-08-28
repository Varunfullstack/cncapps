DROP FUNCTION IF EXISTS `getLoggedTimeAvg`#

CREATE
    DEFINER = `root`@`localhost` FUNCTION `getLoggedTimeAvg`(userID INTEGER, givenDate DATE, days INTEGER) RETURNS FLOAT
    reads sql data
BEGIN

    DECLARE result FLOAT;

    SELECT AVG(u.`loggedHours` + u.`cncLoggedHours`)
    INTO result
    FROM (SELECT *
          FROM user_time_log a
          WHERE a.userID = userID
            AND a.loggedDate <= givenDate
          ORDER BY a.loggedDate DESC
          LIMIT days) u;

    RETURN result;

END
#
GRANT EXECUTE ON FUNCTION getLoggedTimeAvg TO 'webuser'@'%'