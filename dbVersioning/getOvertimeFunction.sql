drop function getOvertime;
create
    definer = root@`127.0.0.1` function getOvertime(callactivityId bigint) returns decimal(5, 2)
    reads sql data
    deterministic
BEGIN
    DECLARE shiftStartTime DECIMAL(10, 2);
    DECLARE shiftEndTime DECIMAL(10, 2);
    DECLARE activityDate DATE;
    DECLARE activityEngineerOvertimeAllowed BOOLEAN;
    DECLARE officeStartTime DECIMAL(10, 2);
    DECLARE officeEndTime DECIMAL(10, 2);
    declare submitAsOvertime boolean;
    declare isEngineerTravel boolean;
    SELECT caa_date,
           engineerOvertimeFlag = 'Y',
           CAST(
                       TIME_TO_SEC(caa_starttime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           CAST(
                       TIME_TO_SEC(caa_endtime) / (60 * 60) AS DECIMAL(10, 2)
               ),
           callactivity.submitAsOvertime,
           caa_callacttypeno = 22
    INTO
        activityDate,
        activityEngineerOvertimeAllowed,
        shiftStartTime,
        shiftEndTime,
        submitAsOvertime,
        isEngineerTravel
    FROM callactivity
             LEFT JOIN consultant
                       ON caa_consno = consultant.`cns_consno`
             LEFT JOIN callacttype
                       ON caa_callacttypeno = callacttype.`cat_callacttypeno`
    WHERE caa_callactivityno = callactivityId;

    if(not submitAsOvertime or not activityEngineerOvertimeAllowed) then
        return 0;
    end if;

    if(not isEngineerTravel) then
        RETURN shiftEndTime - shiftStartTime;
    end if;
    IF (weekday(activityDate) in (5,6)
        or isBankHoliday(activityDate)
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
    if(shiftStartTime >= officeStartTime and shiftStartTime < officeEndTime) then
        set shiftStartTime = officeEndTime;
    end if;

    if(shiftEndTime > officeStartTime and shiftEndTime < officeEndTime) then
        set shiftEndTime = officeStartTime;
    end if;

    if(officeEndTime < officeStartTime) then
        return 0;
    end if;
    return shiftEndTime - shiftStartTime;
END;
