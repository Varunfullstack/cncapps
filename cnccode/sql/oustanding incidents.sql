SELECT 
  cus_name AS `customer`,
  pro_problemno AS `requestID`,
  pro_date_raised,
  cns_name AS `assignedTo`,
  (SELECT 
    reason 
  FROM
    callactivity 
  WHERE caa_problemno = pro_problemno 
    AND caa_callacttypeno = 51) AS `description`,
    
  TIMEDIFF(NOW(),pro_date_raised ) / 24 /24 /24 /24 AS `openDays`,
  pro_total_activity_duration_hours AS `timeSpentHours` 
FROM
  problem 
  JOIN customer 
    ON cus_custno = pro_custno 
  JOIN consultant 
    ON pro_fixed_consno = cns_consno 
WHERE DATE(pro_date_raised) <=DATE(
    DATE_SUB(NOW(), INTERVAL 2 DAY)) 
    AND pro_status NOT IN ('F', 'C')
  
ORDER BY customer,
  pro_problemno 