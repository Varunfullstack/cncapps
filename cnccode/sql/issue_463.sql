DELIMITER $$

USE `cncp1`$$

DROP VIEW IF EXISTS `service_activity_search`$$

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `service_activity_search` AS (
SELECT
  `callactivity`.`caa_callactivityno` AS `caa_callactivityno`,
  `callactivity`.`caa_siteno`         AS `caa_siteno`,
  `callactivity`.`caa_contno`         AS `caa_contno`,
  `callactivity`.`caa_item`           AS `caa_item`,
  `callactivity`.`caa_callacttypeno`  AS `caa_callacttypeno`,
  `callactivity`.`activityCategoryID` AS `activityCategoryID`,
  `callacttype`.`cat_desc`            AS `cat_desc`,
  `callactivity`.`projectID`          AS `projectID`,
  `callactivity`.`caa_problemno`      AS `caa_problemno`,
  `callactivity`.`caa_consno`         AS `caa_consno`,
  `consultant`.`cns_name`             AS `cns_name`,
  `callactivity`.`caa_date`           AS `caa_date`,
  `callactivity`.`caa_starttime`      AS `caa_starttime`,
  `callactivity`.`caa_endtime`        AS `caa_endtime`,
  `callactivity`.`caa_status`         AS `caa_status`,
  `callactivity`.`reason`             AS `reason`,
  `callactivity`.`internalNotes`      AS `internalNotes`,
  `callactivity`.`curValue`           AS `curValue`,
  `callactivity`.`statementYearMonth` AS `statementYearMonth`,
  `project`.`description`             AS `project`,
  (TIME_TO_SEC(`callactivity`.`caa_endtime`) - TIME_TO_SEC(`callactivity`.`caa_starttime`)) AS `durationHours`,
  `activityitem`.`itm_sstk_price`     AS `itm_sstk_price`,
  `callactivity`.`caa_custno`         AS `caa_custno`,
  `customer`.`cus_name`               AS `cus_name`,
  `address`.`add_postcode`            AS `add_postcode`,
  CONCAT(`contact`.`con_first_name`,_utf8' ',`contact`.`con_last_name`) AS `CONCAT(
    con_first_name,
    ' ',
    con_last_name
  )`,
  `callactivity`.`caa_cuino`          AS `caa_cuino`,
  `problem`.`pro_contract_cuino`      AS `pro_contract_cuino`,
  IFNULL(`contractitem`.`itm_desc`,_utf8'T & M') AS `contract`,
  `callactivity`.`caa_under_contract` AS `caa_under_contract`,
  `callacttype`.`cat_allow_exp_flag`  AS `cat_allow_exp_flag`,
  `callacttype`.`allowSCRFlag`        AS `allowSCRFlag`,
  `activitycategory`.`description`    AS `activityCategory`
FROM ((((((((((((((`callactivity`
                JOIN `customer`
                  ON ((`callactivity`.`caa_custno` = `customer`.`cus_custno`)))
               LEFT JOIN `callacttype`
                 ON ((`callactivity`.`caa_callacttypeno` = `callacttype`.`cat_callacttypeno`)))
              LEFT JOIN `custitem`
                ON ((`callactivity`.`caa_cuino` = `custitem`.`cui_cuino`)))
             LEFT JOIN `project`
               ON ((`project`.`projectID` = `callactivity`.`projectID`)))
            LEFT JOIN `problem`
              ON ((`problem`.`pro_problemno` = `callactivity`.`caa_problemno`)))
           LEFT JOIN `item`
             ON ((`custitem`.`cui_itemno` = `item`.`itm_itemno`)))
          LEFT JOIN `item` `activityitem`
            ON ((`callacttype`.`cat_itemno` = `activityitem`.`itm_itemno`)))
         LEFT JOIN `custitem` `contract`
           ON ((`problem`.`pro_contract_cuino` = `contract`.`cui_cuino`)))
        LEFT JOIN `consultant`
          ON ((`callactivity`.`caa_consno` = `consultant`.`cns_consno`)))
       LEFT JOIN `address`
         ON (((`callactivity`.`caa_custno` = `address`.`add_custno`)
              AND (`callactivity`.`caa_siteno` = `address`.`add_siteno`))))
      LEFT JOIN `contact`
        ON ((`callactivity`.`caa_contno` = `contact`.`con_contno`)))
     LEFT JOIN `activitycategory`
       ON ((`activitycategory`.`activityCategoryID` = `callactivity`.`activityCategoryID`)))
    LEFT JOIN `item` `contractitem`
      ON ((`contract`.`cui_itemno` = `contractitem`.`itm_itemno`)))
   LEFT JOIN `contract` `warranty`
     ON ((`custitem`.`cui_man_contno` = `warranty`.`cnt_contno`)))
WHERE (`problem`.`pro_date_raised` >= (NOW() - INTERVAL 1 MONTH))
ORDER BY `callactivity`.`caa_starttime` DESC,`callactivity`.`caa_consno`)$$

DELIMITER ;

DELIMITER $$

USE `cncp1`$$

DROP VIEW IF EXISTS `service_request_report`$$

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `service_request_report` AS (
SELECT
  `problem`.`pro_problemno`                       AS `ServiceRequestID`,
  DATE_FORMAT(`problem`.`pro_date_raised`,_utf8'%Y-%m-%d') AS `RaisedDate`,
  DATE_FORMAT(`problem`.`pro_date_raised`,_utf8'%H:%i') AS `RaisedTime`,
  DATE_FORMAT(`problem`.`pro_fixed_date`,_utf8'%Y-%m-%d') AS `FixedDate`,
  DATE_FORMAT(`problem`.`pro_fixed_date`,_utf8'%H:%i') AS `FixedTime`,
  (SELECT
     MAX(`callactivity`.`created`) AS `MAX(created)`
   FROM `callactivity`
   WHERE (`callactivity`.`caa_problemno` = `problem`.`pro_problemno`)) AS `lastUpdated`,
  (SELECT
     MIN(`callactivity`.`created`) AS `MIN(created)`
   FROM `callactivity`
   WHERE (`callactivity`.`caa_problemno` = `problem`.`pro_problemno`)) AS `created`,
  `customer`.`cus_name`                           AS `Customer`,
  CONCAT(`contact`.`con_first_name`,_utf8' ',`contact`.`con_last_name`) AS `Contact`,
  `problem`.`pro_priority`                        AS `Priority`,
  `activitycategory`.`description`                AS `Category`,
  `rootcause`.`rtc_desc`                          AS `RootCause`,
  CONCAT(_utf8'Ref-',`problem`.`pro_problemno`)   AS `CallReference`,
  `problem`.`pro_total_activity_duration_hours`   AS `TotalHours`,
  `problem`.`pro_responded_hours`                 AS `ResponseHours`,
  `problem`.`pro_sla_response_hours`              AS `MinContractResponseHours`,
  _utf8''                                         AS `DiffResponseContract`,
  `problem`.`pro_working_hours`                   AS `FixHours`,
  `consultant`.`cns_name`                         AS `FixEngineer`,
  CONCAT(IFNULL(`item`.`itm_desc`,_utf8''),_utf8' ',IFNULL(`address`.`add_postcode`,_utf8''),_utf8' ',CONVERT(IFNULL(`custitem`.`adslPhone`,_latin1'') USING utf8)) AS `Contract`,
  `problem`.`pro_awaiting_customer_response_flag` AS `AwaitingCustomer`,
  `problem`.`pro_manager_comment`                 AS `ManagerComment`
FROM ((((((((`problem`
          LEFT JOIN `customer`
            ON ((`customer`.`cus_custno` = `problem`.`pro_custno`)))
         LEFT JOIN `contact`
           ON ((`contact`.`con_contno` = `problem`.`pro_contno`)))
        LEFT JOIN `activitycategory`
          ON ((`problem`.`activityCategoryID` = `activitycategory`.`activityCategoryID`)))
       LEFT JOIN `rootcause`
         ON ((`problem`.`pro_rootcauseno` = `rootcause`.`rtc_rootcauseno`)))
      LEFT JOIN `consultant`
        ON ((`consultant`.`cns_consno` = `problem`.`pro_fixed_consno`)))
     LEFT JOIN `custitem`
       ON ((`custitem`.`cui_cuino` = `problem`.`pro_contract_cuino`)))
    LEFT JOIN `address`
      ON (((`address`.`add_custno` = `custitem`.`cui_custno`)
           AND (`address`.`add_siteno` = `custitem`.`cui_siteno`))))
   LEFT JOIN `item`
     ON ((`item`.`itm_itemno` = `custitem`.`cui_itemno`)))
WHERE (`problem`.`pro_date_raised` >= (NOW() - INTERVAL 1 MONTH))
ORDER BY `problem`.`pro_date_raised`)$$

DELIMITER ;