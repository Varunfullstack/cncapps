DROP FUNCTION IF EXISTS `getOpenHours` #

CREATE
    DEFINER = `root` @`::1` FUNCTION `getOpenHours`(serviceRequestID INTEGER) RETURNS FLOAT READS SQL DATA DETERMINISTIC
BEGIN

    DECLARE initialStartDate DATE;
    DECLARE initialStartTime TIME;
    DECLARE fixedEndDate DATE;
    DECLARE fixedEndTime TIME;
    DECLARE totalSeconds bigint DEFAULT 0;
    DECLARE currentDate DATE;
    DECLARE currentStartTime TIME;
    DECLARE currentEndTime TIME;
    DECLARE dayOfTheWeek INTEGER;
    DECLARE openBusinessHour TIME;
    DECLARE closeBusinessHour TIME;
    SELECT `hed_bill_starttime`,
           hed_bill_endtime
    INTO openBusinessHour,
        closeBusinessHour
    FROM headert
    LIMIT 1;
    SELECT callactivity.`caa_starttime`,
           callactivity.`caa_date`
    INTO initialStartTime,
        initialStartDate
    FROM callactivity
    WHERE callactivity.`caa_problemno` = serviceRequestID
      AND callactivity.`caa_callacttypeno` = 51;
    SELECT callactivity.`caa_endtime`,
           callactivity.`caa_date`
    INTO fixedEndTime,
        fixedEndDate
    FROM callactivity
    WHERE callactivity.`caa_problemno` = serviceRequestID
      AND callactivity.`caa_callacttypeno` = 57
    ORDER BY caa_date DESC
    LIMIT 1;

    if (fixedEndDate is null) then
        return null;
    end if;
    SET currentDate = initialStartDate;
    SET currentStartTime = initialStartTime;
    IF currentStartTime < openBusinessHour THEN
        SET currentStartTime = openBusinessHour;
    END IF;
    SET currentEndTime = closeBusinessHour;
    REPEAT
        SET dayOfTheWeek = DAYOFWEEK(currentDate);
        IF (
                dayOfTheWeek <> 1
                AND dayOfTheWeek <> 7
                AND NOT isBankHoliday(currentDate)
                and currentStartTime < closeBusinessHour
            )
        THEN
            IF (
                    currentDate = fixedEndDate
                    AND fixedEndTime < closeBusinessHour
                )
            THEN
                SET currentEndTime = fixedEndTime;
            END IF;
            -- here we have the correct startTime, endTime and date...so we should add the seconds to the counter
            SET totalSeconds = totalSeconds + TIME_TO_SEC(
                    TIMEDIFF(
                            currentEndTime,
                            currentStartTime
                        )
                );
        END IF;
        SET currentDate = DATE_ADD(currentDate, INTERVAL 1 DAY);
        SET currentStartTime = openBusinessHour;
        SET currentEndTime = closeBusinessHour;
    UNTIL currentDate > fixedEndDate
        END REPEAT;
    RETURN totalSeconds / 60 / 60;
END #
GRANT EXECUTE ON FUNCTION getLoggedTimeTotal TO 'webuser'@'%'