DROP FUNCTION IF EXISTS `getOvertime`#

CREATE DEFINER=`root`@`127.0.0.1` FUNCTION `getOvertime`(callactivityId BIGINT) RETURNS DECIMAL(5,2)
    READS SQL DATA
BEGIN
    DECLARE shiftStartTime DECIMAL(10, 2);
    DECLARE shiftEndTime DECIMAL(10, 2);
    DECLARE isWeekOvertimeAllowed BOOLEAN;
    DECLARE activityWeekday INT;
    DECLARE activityEngineerOvertimeAllowed BOOLEAN;
    DECLARE overtime DECIMAL(10, 2);
    DECLARE officeStartTime DECIMAL(10, 2);
    DECLARE officeEndTime DECIMAL(10, 2);
    declare override boolean;
    SELECT weekdayOvertimeFlag = 'Y',
           WEEKDAY(caa_date),
           engineerOvertimeFlag = 'Y',
           CAST(
                       TIME_TO_SEC(caa_starttime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           CAST(
                       TIME_TO_SEC(caa_endtime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           submitAsOvertime
    INTO isWeekOvertimeAllowed,
        activityWeekday,
        activityEngineerOvertimeAllowed,
        shiftStartTime,
        shiftEndTime,
        override
    FROM callactivity
             LEFT JOIN consultant
                       ON caa_consno = consultant.`cns_consno`
             LEFT JOIN callacttype
                       ON caa_callacttypeno = callacttype.`cat_callacttypeno`
    WHERE caa_callactivityno = callactivityId;



    IF (
                activityWeekday = 5
            OR activityWeekday = 6
            or override
        )
    THEN
        RETURN shiftEndTime - shiftStartTime;
    END IF;
    SELECT CAST(
                       TIME_TO_SEC(overtimeStartTime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           CAST(
                       TIME_TO_SEC(overtimeEndTime) / (60 * 60) AS DECIMAL(10, 2)
               )
    INTO officeStartTime,
        officeEndTime
    FROM headert;

    SET overtime = 0;
    IF (shiftStartTime < officeStartTime) THEN
        IF (shiftEndTime < officeStartTime) THEN
            SET overtime = shiftEndTime - shiftStartTime;
        ELSE
            SET overtime = officeStartTime - shiftStartTime;
        END IF;

    END IF;
    IF (shiftEndTime > officeEndTime) THEN
        IF (shiftStartTime > officeEndTime) THEN
            SET overtime = overtime + (shiftEndTime - shiftStartTime);
        ELSE
            SET overtime = overtime + (shiftEndTime - officeEndTime);
        END IF;
    END IF;
    RETURN overtime;
END #

