UPDATE custitem SET declinedFlag = 'N' WHERE cui_itemno = 4111 AND cui_expiry_date > NOW();
UPDATE customer SET cus_sla_p1 = 1;
UPDATE customer SET cus_sla_p1 = 2;
UPDATE customer SET cus_sla_p1 = 4;
UPDATE customer SET cus_sla_p1 = 8;
UPDATE customer SET cus_sla_p5 = 0;
UPDATE `cncp1`.`item` SET `renewalTypeID` = '2' WHERE `itm_itemno` = '4111' AND`itm_manno` = '17' AND`itm_desc` = 'Pre-Pay Contract' AND`itm_stockcat` = 'R' AND`itm_itemtypeno` = '57' AND`itm_sstk_price` = '1000.00' AND`itm_sstk_cost` = '0.00' AND`itm_mstk_cost` = '0.00' AND`itm_serial_req` = 'Y' AND`itm_sstk_qty` = '-15.00' AND`itm_mstk_qty` = '0.00' AND`itm_discontinued` = 'N' AND`itm_unit_of_sale` = '' AND`itm_contno` = '844' AND`itm_servercare_flag` = 'N' AND`contractResponseTime` = '10' AND`notes` = 'CNC Pre-Pay Contract – pay as you go with no monthly fee but with no guaranteed response time.\r\n£70 per hour contract rate. 15 minute increments for remote support. \r\n• Minimal upfront cost.\r\n• Only pay for the time you use.\r\n• Requires an initial fee, which is debited in 15 minute\r\n intervals for telephone or remote support and a minimum\r\nof one hour plus travel for on-site support. \r\n• Reduces initial commitment costs.\r\n• This contract offers support on a ‘best endeavours’ basis.\r\n• We DO NOT encourage the use of this option for support \r\nof mission critical servers as we cannot provide a guaranteed\r\nfix and response time.  \r\n• Be aware that using Pre-Pay for your servers can yield relatively\r\nlarge expenditure for parts and labour if server failure occurs.' AND`renewalTypeID` = '0'; 
ALTER TABLE `cncp1`.`custitem` DROP COLUMN `cui_sla_p1`, DROP COLUMN `cui_sla_p2`, DROP COLUMN `cui_sla_p3`, DROP COLUMN `cui_sla_p4`, DROP COLUMN `cui_sla_p5`, DROP COLUMN `cui_live_flag`; 

UPDATE `cncp1`.`item` SET `renewalTypeID` = '5' WHERE `itm_desc` LIKE 'CNC Internet Business Account%'; 
UPDATE `cncp1`.`item` SET `renewalTypeID` = '5' WHERE `itm_desc` LIKE 'CNC Internet Virtual Server%'; 
/*
UPDATE custitem
JOIN item ON cui_itemno = itm_itemno
SET
	cui_sale_price = cui_sale_price /12,
	cui_cost_price = cui_cost_price/12,
	salePricePerMonth = cui_sale_price /12,
	costPricePerMonth = cui_cost_price/12
WHERE item.`renewalTypeID` = 5;
*/

ALTER TABLE `cncp1`.`renewaltype` CHANGE `description` `description` CHAR(50) CHARSET latin1 COLLATE latin1_swedish_ci NOT NULL, ADD COLUMN `allowSrLogging` CHAR(1) DEFAULT 'Y' NOT NULL AFTER `description`; 
UPDATE `cncp1`.`renewaltype` SET `allowSrLogging` = 'N' WHERE `renewalTypeID` = '4';
ALTER TABLE `cncp1`.`custitem` ADD COLUMN `hostingUserName` CHAR(50) NULL AFTER `cui_sla_response_hours`; 
ALTER TABLE `cncp1`.`custitem` ADD COLUMN `cui_internal_notes` TEXT NULL AFTER `hostingUserName`; 

ALTER TABLE `cncp1`.`activitycategory` CHANGE `description` `description` CHAR(50) CHARSET utf8 COLLATE utf8_general_ci NOT NULL, ADD COLUMN `allowSelection` CHAR(1) DEFAULT 'Y' NOT NULL AFTER `description`; 

INSERT INTO `cncp1`.`renewaltype` (`description`) VALUES ('Hosting');
INSERT INTO `cncp1`.`db_sequence` (`seq_name`, `nextid`) VALUES ('arecord', '100'); 
INSERT INTO `cncp1`.`db_sequence` (`seq_name`, `nextid`) VALUES ('password', '100'); 

UPDATE
	customer
SET
	cus_sla_p1 = 0.5,
	cus_sla_p2 = 1,
	cus_sla_p3 = 2,
	cus_sla_p4 = 4,
	cus_sla_p5 = 24
WHERE
	cus_custno IN (520,2554,4838);
	
UPDATE
	customer
SET
	cus_sla_p1 = 1,
	cus_sla_p2 = 2,
	cus_sla_p3 = 4,
	cus_sla_p4 = 8,
	cus_sla_p5 = 24
WHERE
	cus_custno IN (
		6008,
		1649,
		6074,
		2404,
		2172,
		5662,
		3474,
		6011,
		1965,
		2848,
		5348,
		3950,
		4977,
		6009
	);

UPDATE
	customer
SET
	cus_sla_p1 = 2,
	cus_sla_p2 = 4,
	cus_sla_p3 = 8,
	cus_sla_p4 = 16,
	cus_sla_p5 = 24
WHERE
	cus_custno IN (
		3183,
		2539,
		5190,
		1000,
		2315,
		1731,
		5402,
		1065,
		2186,
		1929,
		5978,
		5320,
		4053,
		2869,
		2343,
		1951,
		6032,
		2707,
		2424,
		2095,
		2923,
		1908,
		5206,
		1468,
		5148,
		6027,
		2524,
		2207,
		1702,
		1554,
		4767,
		3562,
		2166,
		1711,
		1548,
		2312,
		2389,
		2420,
		4523,
		1617,
		1841,
		6075,
		1545,
		1746,
		4452,
		6045,
		2305,
		2102,
		3832,
		2914,
		1478,
		1510,
		5007,
		1969,
		5910,
		1268,
		5151,
		2530
	);

UPDATE
	customer
SET
	cus_sla_p1 = 4,
	cus_sla_p2 = 8,
	cus_sla_p3 = 16,
	cus_sla_p4 = 20,
	cus_sla_p5 = 24
WHERE
	cus_custno IN (
		1644,
		2507,
		129,
		6014,
		2764,
		282,
		1647,
		5709,
		5349,
		4162,
		2141,
		1714,
		6001,
		2722,
		2916,
		2498,
		3861,
		85,
		2499,
		3908,
		4295,
		2621,
		1743,
		3292,
		1598,
		1633,
		3271,
		4763,
		4151,
		2533,
		961,
		503,
		820,
		767,
		2025
	);
	
UPDATE
	customer
SET
	cus_sla_p1 = 5,
	cus_sla_p2 = 10,
	cus_sla_p3 = 15,
	cus_sla_p4 = 24,
	cus_sla_p5 = 24
WHERE
	cus_custno IN (
		2020,
		4984,
		849,
		2214,
		2028,
		4564,
		4057,
		2488,
		3147,
		1255,
		5140,
		2226,
		1401,
		259,
		117,
		1939,
		5460,
		3092,
		2426,
		3232,
		2977,
		1685,
		2045,
		2375,
		134,
		203
	);
	
DELIMITER $$

USE `cncp1`$$

DROP VIEW IF EXISTS `auto_request_activitycategory`$$

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `auto_request_activitycategory` AS (
SELECT
  `activitycategory`.`activityCategoryID` AS `activityCategoryID`,
  `activitycategory`.`description`        AS `description`,
  `activitycategory`.`allowSelection`     AS `allowSelection`
FROM `activitycategory`)$$

DELIMITER ;	

DELIMITER $$

USE `cncp1`$$

DELIMITER $$

USE `cncp1`$$

DROP VIEW IF EXISTS `auto_request_contracts`$$

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `auto_request_contracts` AS 
SELECT
  `custitem`.`cui_cuino`      AS `contractCustomerItemID`,
  `custitem`.`cui_custno`     AS `customerID`,
  `customer`.`cus_name`       AS `customerName`,
  `renewaltype`.`description` AS `renewalType`,
  `address`.`add_postcode`    AS `postcode`,
  `item`.`itm_desc`           AS `description`,
  `custitem`.`adslPhone`      AS `adslPhone`,
  `custitem`.`routerIpAddress`	AS `routerIpAddress`
FROM ((((`custitem`
      JOIN `item`
        ON ((`custitem`.`cui_itemno` = `item`.`itm_itemno`)))
     JOIN `customer`
       ON ((`customer`.`cus_custno` = `custitem`.`cui_custno`)))
    JOIN `renewaltype`
      ON ((`renewaltype`.`renewalTypeID` = `item`.`renewalTypeID`)))
   JOIN `address`
     ON (((`address`.`add_siteno` = `custitem`.`cui_siteno`)
          AND (`address`.`add_custno` = `custitem`.`cui_custno`))))
WHERE ((`renewaltype`.`allowSrLogging` = _latin1'Y')
       AND (`custitem`.`declinedFlag` <> _latin1'Y'))
ORDER BY `renewaltype`.`description`,`item`.`itm_desc`$$

DELIMITER ;

DELIMITER $$

USE `cncp1`$$

DROP VIEW IF EXISTS `auto_request_rootcause`$$

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `auto_request_rootcause` AS (
SELECT
  `rootcause`.`rtc_rootcauseno` AS `rootCauseID`,
  `rootcause`.`rtc_desc`        AS `description`
FROM `rootcause`)$$

DELIMITER ;

GRANT SELECT ON `cncp1`.`auto_request_activitycategory` TO 'autorequest'@'%'; 
GRANT SELECT ON `cncp1`.`auto_request_contracts` TO 'autorequest'@'%'; 
GRANT SELECT ON `cncp1`.`auto_request_rootcause` TO 'autorequest'@'%'; 

ALTER TABLE `cncp1`.`callactivity` DROP COLUMN `caa_contract_cuino`, DROP INDEX `contract`;

ALTER TABLE `cncp1`.`automated_request` ADD COLUMN `rootCauseID` INT(11) NULL AFTER `attachmentFileLengthBytes`, ADD COLUMN `contractCustomerItemID` INT(11) NULL AFTER `rootCauseID`, ADD COLUMN `activityCategoryID` INT(11) NULL AFTER `contractCustomerItemID`; 
