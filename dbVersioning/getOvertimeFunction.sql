DROP FUNCTION IF EXISTS `getOvertime`#

CREATE DEFINER=`root`@`127.0.0.1` FUNCTION `getOvertime`(callactivityId BIGINT) RETURNS DECIMAL(5,2)
    READS SQL DATA
BEGIN
    DECLARE projectStartTime DECIMAL(10, 2);
    DECLARE projectEndTime DECIMAL(10, 2);
    DECLARE helpdeskStartTime DECIMAL(10, 2);
    DECLARE helpdeskEndTime DECIMAL(10, 2);
    DECLARE shiftStartTime DECIMAL(10, 2);
    DECLARE shiftEndTime DECIMAL(10, 2);
    DECLARE isHelpdeskUser BOOLEAN;
    DECLARE isWeekOvertimeAllowed BOOLEAN;
    DECLARE activityWeekday INT;
    DECLARE activityEngineerOvertimeAllowed BOOLEAN;
    DECLARE overtime DECIMAL(10, 2);
    DECLARE officeStartTime DECIMAL(10, 2);
    DECLARE officeEndTime DECIMAL(10, 2);
    SELECT weekdayOvertimeFlag = 'Y',
           cns_helpdesk_flag = 'Y',
           WEEKDAY(caa_date),
           engineerOvertimeFlag = 'Y',
           CAST(
                       TIME_TO_SEC(caa_starttime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           CAST(
                       TIME_TO_SEC(caa_endtime) / (60 * 60) AS DECIMAL(10, 2)
               )
    INTO isWeekOvertimeAllowed,
        isHelpdeskUser,
        activityWeekday,
        activityEngineerOvertimeAllowed,
        shiftStartTime,
        shiftEndTime
    FROM callactivity
             LEFT JOIN consultant
                       ON caa_consno = consultant.`cns_consno`
             LEFT JOIN callacttype
                       ON caa_callacttypeno = callacttype.`cat_callacttypeno`
    WHERE caa_callactivityno = callactivityId;
    IF (
                activityWeekday = 5
            OR activityWeekday = 6
        )
    THEN
        RETURN shiftEndTime - shiftStartTime;
    END IF;
    SELECT CAST(
                       TIME_TO_SEC(hed_hd_starttime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           CAST(
                       TIME_TO_SEC(hed_hd_endtime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           CAST(
                       TIME_TO_SEC(hed_pro_starttime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           CAST(
                       TIME_TO_SEC(hed_pro_endtime) / (60 * 60) AS DECIMAL(10, 2)
               )
    INTO helpdeskStartTime,
        helpdeskEndTime,
        projectStartTime,
        projectEndTime
    FROM headert;

    SET overtime = 0;
    IF (isHelpdeskUser)
    THEN
        SET officeStartTime = helpdeskStartTime;
        SET officeEndTime = helpdeskEndTime;
        IF (shiftStartTime < officeStartTime)
        THEN
            IF (shiftEndTime < officeStartTime) THEN
                SET overtime = shiftEndTime - shiftStartTime;
            ELSE
                SET overtime = officeStartTime - shiftStartTime;
            END IF;
        END IF;

        IF (shiftEndTime > officeEndTime) THEN
            IF (shiftStartTime >= officeEndTime) THEN
                SET overtime = overtime + (shiftEndTime - shiftStartTime);
            END IF;
        END IF;
        RETURN overtime;
    END IF;

    SET officeStartTime = projectStartTime;
    SET officeEndTime = projectEndTime;
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
            SET overtime = overtime + (shiftEndTime - officeStartTime);
        END IF;
    END IF;
    RETURN overtime;
END #

