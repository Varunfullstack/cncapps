DROP FUNCTION IF EXISTS `getContractExpiryDate`#
CREATE
    DEFINER = `root`@`::1` FUNCTION `getContractExpiryDate`(installDate DATE,
                                                            initialContractLength INT) RETURNS DATE
    NO SQL
    DETERMINISTIC
BEGIN
    DECLARE firstExpiryDate DATE;
    DECLARE expiryDay INTEGER;
    DECLARE expiryMonth INTEGER;
    DECLARE expiryYear INTEGER;
    DECLARE nextExpiryDate DATE;
    DECLARE monthDifference INT;
    DECLARE yearDifference INT;
    DECLARE dayDifference INT;
    DECLARE expiryDate DATE;

    SET initialContractLength = IFNULL(initialContractLength, 12);

    SET firstExpiryDate = DATE_ADD(installDate, INTERVAL initialContractLength MONTH);

    SET expiryDay = DAY(installDate);

    SET expiryMonth = MONTH(installDate);

    SET expiryYear = YEAR(CURRENT_DATE);
    IF expiryMonth < MONTH(CURRENT_DATE) OR
       expiryMonth = MONTH(CURRENT_DATE) AND expiryDay < DAY(CURRENT_DATE) THEN
        SET expiryYear = expiryYear + 1;
    END IF;


    SET nextExpiryDate = STR_TO_DATE(CONCAT(expiryYear, '-', expiryMonth, '-', expiryDay), '%Y-%m-%d');
    IF firstExpiryDate > nextExpiryDate THEN
        SET nextExpiryDate = firstExpiryDate;
    END IF;


    SET monthDifference = TIMESTAMPDIFF(MONTH, nextExpiryDate, CURRENT_DATE);

    SET yearDifference = TIMESTAMPDIFF(YEAR, nextExpiryDate, CURRENT_DATE);

    SET dayDifference = TIMESTAMPDIFF(DAY, nextExpiryDate, CURRENT_DATE);

    SET expiryDate = nextExpiryDate;

    IF NOT yearDifference AND monthDifference < 3 THEN
        SET expiryDate = DATE_ADD(expiryDate, INTERVAL 1 YEAR);
    END IF;
    RETURN expiryDate;

END;
    #
    GRANT EXECUTE ON FUNCTION getLoggedTimeAvg TO 'webuser'@'%'