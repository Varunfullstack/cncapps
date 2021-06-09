DROP FUNCTION IF EXISTS `getLoggedTimeTotal`#

CREATE
    DEFINER = `root`@`localhost` FUNCTION `getLoggedTimeTotal`(userID INTEGER, givenDate DATE, days INTEGER)
    RETURNS FLOAT
    reads sql data
BEGIN

    DECLARE result FLOAT;

    SELECT sum(u.`loggedHours` + u.`cncLoggedHours`)
    INTO result
    FROM (SELECT *
          FROM user_time_log a
          WHERE a.userID = userID
            AND a.loggedDate <= givenDate
            and not isBankHoliday(loggedDate)
          ORDER BY a.loggedDate DESC
          LIMIT days) u;

    RETURN result;

END
#
GRANT EXECUTE ON FUNCTION getLoggedTimeTotal TO 'webuser'@'%'