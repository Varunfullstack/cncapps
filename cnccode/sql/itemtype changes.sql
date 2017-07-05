# Item type changes

USE `cncp1`;

/* Alter table in target */
ALTER TABLE `custitem` 
	CHANGE `cui_contract_cuino` `cui_contract_cuino` INT(11)   NULL COMMENT 'Contract' AFTER `cui_man_contno`, 
	CHANGE `cui_serial` `cui_serial` VARCHAR(20)  COLLATE utf8_general_ci NULL COMMENT 'Serial No' AFTER `cui_contract_cuino`, 
	CHANGE `cui_cust_ref` `cui_cust_ref` VARCHAR(45)  COLLATE utf8_general_ci NULL COMMENT 'Server name' AFTER `cui_serial`, 
	CHANGE `cui_ordno` `cui_ordno` INT(11)   NULL AFTER `cui_cust_ref`, 
	CHANGE `cui_sale_price` `cui_sale_price` DECIMAL(12,2)   NULL AFTER `cui_ordno`, 
	CHANGE `cui_porno` `cui_porno` INT(11)   NULL AFTER `cui_sale_price`, 
	CHANGE `cui_pord_price` `cui_pord_price` DECIMAL(12,2)   NULL AFTER `cui_porno`, 
	CHANGE `cui_cost_price` `cui_cost_price` DECIMAL(12,2)   NULL AFTER `cui_pord_price`, 
	CHANGE `cui_users` `cui_users` SMALLINT(6)   NULL COMMENT 'Number of users' AFTER `cui_cost_price`, 
	CHANGE `cui_ord_date` `cui_ord_date` DATE   NULL AFTER `cui_users`, 
	CHANGE `cui_expiry_date` `cui_expiry_date` DATE   NULL AFTER `cui_ord_date`, 
	CHANGE `curGSCBalance` `curGSCBalance` DECIMAL(8,2)   NOT NULL DEFAULT '0.00' AFTER `cui_expiry_date`, 
	CHANGE `renewalStatus` `renewalStatus` CHAR(1)  COLLATE utf8_general_ci NULL DEFAULT 'N' COMMENT 'Is this required now we have the \"live\" flag' AFTER `curGSCBalance`, 
	CHANGE `renewalOrdheadID` `renewalOrdheadID` INT(11)   NULL DEFAULT '0' AFTER `renewalStatus`, 
	CHANGE `itemNotes` `itemNotes` TEXT  COLLATE utf8_general_ci NULL AFTER `renewalOrdheadID`, 
	ADD COLUMN `cui_sla_p1` INT(5)   NULL AFTER `itemNotes`, 
	ADD COLUMN `cui_sla_p2` INT(5)   NULL AFTER `cui_sla_p1`, 
	ADD COLUMN `cui_sla_p3` INT(5)   NULL AFTER `cui_sla_p2`, 
	ADD COLUMN `cui_sla_p4` INT(5)   NULL AFTER `cui_sla_p3`, 
	ADD COLUMN `cui_sla_p5` INT(5)   NULL AFTER `cui_sla_p4`, 
	ADD COLUMN `cui_live_flag` CHAR(1)  COLLATE latin1_swedish_ci NULL DEFAULT 'Y' COMMENT 'Is this contract live?' AFTER `cui_sla_p5`, 
	ADD COLUMN `cui_prepay_balance` DECIMAL(6,2)   NULL COMMENT 'On customer record at present' AFTER `cui_live_flag`, 
	ADD COLUMN `cui_sales_order_status` ENUM('','Q','I','C')  COLLATE latin1_swedish_ci NULL COMMENT 'Which type of sales order to create when billing' AFTER `cui_prepay_balance`, 
	ADD COLUMN `renewalDate` DATE   NULL AFTER `cui_sales_order_status`, 
	ADD COLUMN `customerName` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `renewalDate`, 
	ADD COLUMN `customerID` INT(11) UNSIGNED   NULL AFTER `customerName`, 
	ADD COLUMN `itemID` INT(11) UNSIGNED   NULL AFTER `customerID`, 
	ADD COLUMN `customerItemID` INT(11) UNSIGNED   NULL AFTER `itemID`, 
	ADD COLUMN `months` INT(10)   NULL AFTER `customerItemID`, 
	ADD COLUMN `ordheadID` CHAR(10)  COLLATE latin1_swedish_ci NULL AFTER `months`, 
	ADD COLUMN `broadbandServiceType` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `ordheadID`, 
	ADD COLUMN `broadbandServiceTypeID` INT(11)   NULL AFTER `broadbandServiceType`, 
	ADD COLUMN `adslPhone` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `broadbandServiceTypeID`, 
	ADD COLUMN `fee` DOUBLE   NULL COMMENT 'Cost per month' AFTER `adslPhone`, 
	ADD COLUMN `macCode` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `fee`, 
	ADD COLUMN `batchNo` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `macCode`, 
	ADD COLUMN `reference` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `batchNo`, 
	ADD COLUMN `defaultGateway` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `reference`, 
	ADD COLUMN `networkAddress` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `defaultGateway`, 
	ADD COLUMN `subnetMask` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `networkAddress`, 
	ADD COLUMN `routerIPAddress` TEXT  COLLATE latin1_swedish_ci NULL COMMENT 'one or more IP addresses' AFTER `subnetMask`, 
	ADD COLUMN `userName` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `routerIPAddress`, 
	ADD COLUMN `password` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `userName`, 
	ADD COLUMN `etaDate` DATE   NULL AFTER `password`, 
	ADD COLUMN `installationDate` DATE   NULL AFTER `etaDate`, 
	ADD COLUMN `costPerAnnum` DOUBLE   NULL COMMENT 'Sale price per annum' AFTER `installationDate`, 
	ADD COLUMN `salePricePerMonth` DECIMAL(6,2)   NULL COMMENT 'Sales Price Per Month' AFTER `costPerAnnum`, 
	ADD COLUMN `costPricePerMonth` DECIMAL(6,2)   NULL AFTER `salePricePerMonth`, 
	ADD COLUMN `ispID` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `costPricePerMonth`, 
	ADD COLUMN `requiresChangesFlag` VARCHAR(1)  COLLATE latin1_swedish_ci NULL AFTER `ispID`, 
	ADD COLUMN `dualBroadbandFlag` VARCHAR(1)  COLLATE latin1_swedish_ci NULL AFTER `requiresChangesFlag`, 
	ADD COLUMN `dnsCompany` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `dualBroadbandFlag`, 
	ADD COLUMN `ipCurrentNo` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `dnsCompany`, 
	ADD COLUMN `mx` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `ipCurrentNo`, 
	ADD COLUMN `secureServer` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `mx`, 
	ADD COLUMN `vpns` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `secureServer`, 
	ADD COLUMN `owa` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `vpns`, 
	ADD COLUMN `oma` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `owa`, 
	ADD COLUMN `remotePortal` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `oma`, 
	ADD COLUMN `smartHost` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `remotePortal`, 
	ADD COLUMN `preparationRecords` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `smartHost`, 
	ADD COLUMN `assignedTo` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `preparationRecords`, 
	ADD COLUMN `initialSpeedTest` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `assignedTo`, 
	ADD COLUMN `preMigrationNotes` LONGBLOB   NULL AFTER `initialSpeedTest`, 
	ADD COLUMN `postMigrationNotes` LONGBLOB   NULL AFTER `preMigrationNotes`, 
	ADD COLUMN `docsUpdatedAndChecksCompleted` VARCHAR(255)  COLLATE latin1_swedish_ci NULL AFTER `postMigrationNotes`, 
	ADD COLUMN `invoicePeriodMonths` INT(4)   NULL COMMENT 'Number of months between invoices' AFTER `docsUpdatedAndChecksCompleted`, 
	ADD COLUMN `totalInvoiceMonths` INT(11)   NULL COMMENT 'Number of months to add to install date to calculate next invoice due date' AFTER `invoicePeriodMonths`, 
	ADD COLUMN `declinedFlag` CHAR(1)  COLLATE latin1_swedish_ci NULL AFTER `totalInvoiceMonths`, 
	ADD COLUMN `hostingCompany` CHAR(100)  COLLATE latin1_swedish_ci NULL AFTER `declinedFlag`, 
	ADD COLUMN `osPlatform` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `hostingCompany`, 
	ADD COLUMN `domainNames` CHAR(100)  COLLATE latin1_swedish_ci NULL AFTER `osPlatform`, 
	ADD COLUMN `controlPanelUrl` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `domainNames`, 
	ADD COLUMN `ftpAddress` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `controlPanelUrl`, 
	ADD COLUMN `ftpUsername` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `ftpAddress`, 
	ADD COLUMN `wwwAddress` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `ftpUsername`, 
	ADD COLUMN `websiteDeveloper` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `wwwAddress`, 
	ADD COLUMN `dateGenerated` DATE   NULL DEFAULT '0000-00-00' AFTER `websiteDeveloper`, 
	ADD COLUMN `startDate` DATE   NULL AFTER `dateGenerated`, 
	ADD COLUMN `salePrice` DECIMAL(6,2)   NULL AFTER `startDate`, 
	ADD COLUMN `costPrice` DECIMAL(6,2)   NULL AFTER `salePrice`, 
	ADD COLUMN `qty` INT(3)   NULL AFTER `costPrice`, 
	ADD COLUMN `renQuotationTypeID` INT(11)   NULL AFTER `qty`, 
	ADD COLUMN `comment` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `renQuotationTypeID`, 
	ADD COLUMN `grantNumber` CHAR(50)  COLLATE latin1_swedish_ci NULL AFTER `comment`, 
	ADD COLUMN `notes` TEXT  COLLATE latin1_swedish_ci NULL AFTER `grantNumber`;

/* Alter table in target */
ALTER TABLE `problem` 
	ADD COLUMN `pro_message_to_sales` TEXT  COLLATE latin1_swedish_ci NULL AFTER `pro_breach_comment`;

UPDATE
custitem ci
JOIN renbroadband rb ON rb.`customerItemID` = ci.cui_cuino
SET
	ci.`renewalDate` = rb.`renewalDate`,
	ci.`itemID` = rb.`itemID`,
	ci.`months` = rb.`months`,
	ci.`ordheadID` = rb.`ordheadID`,
	ci.`broadbandServiceType` = rb.`broadbandServiceType`,
	ci.`broadbandServiceTypeID` = rb.`broadbandServiceTypeID`,
	ci.`adslPhone` = rb.`adslPhone`,
	ci.`fee` = rb.`fee`,
	ci.`macCode` = rb.`macCode`,
	ci.`batchNo` = rb.`batchNo`,
	ci.`reference` = rb.`reference`,
	ci.`defaultGateway` = rb.`defaultGateway`,
	ci.`networkAddress` = rb.`networkAddress`,
	ci.`subnetMask` = rb.`subnetMask`,
	ci.`routerIPAddress` = rb.`routerIPAddress`,
	ci.`userName` = rb.`userName`,
	ci.`password` = rb.`password`,
	ci.`etaDate` = rb.`etaDate`,
	ci.`installationDate` = rb.`installationDate`,
	ci.`costPerAnnum` = rb.`costPerAnnum`,
	ci.`salePricePerMonth` = rb.`salePricePerMonth`,
	ci.`costPricePerMonth` = rb.`costPricePerMonth`,
	ci.`ispID` = rb.`ispID`,
	ci.`requiresChangesFlag` = rb.`requiresChangesFlag`,
	ci.`dualBroadbandFlag` = rb.`dualBroadbandFlag`,
	ci.`dnsCompany` = rb.`dnsCompany`,
	ci.`ipCurrentNo` = rb.`ipCurrentNo`,
	ci.`mx` = rb.`mx`,
	ci.`secureServer` = rb.`secureServer`,
	ci.`vpns` = rb.`vpns`,
	ci.`owa` = rb.`owa`,
	ci.`oma` = rb.`oma`,
	ci.`remotePortal` = rb.`remotePortal`,
	ci.`smartHost` = rb.`smartHost`,
	ci.`preparationRecords` = rb.`preparationRecords`,
	ci.`assignedTo` = rb.`assignedTo`,
	ci.`initialSpeedTest` = rb.`initialSpeedTest`,
	ci.`preMigrationNotes` = rb.`preMigrationNotes`,
	ci.`postMigrationNotes` = rb.`postMigrationNotes`,
	ci.`docsUpdatedAndChecksCompleted` = rb.`docsUpdatedAndChecksCompleted`,
	ci.`invoicePeriodMonths` = rb.`invoicePeriodMonths`,
	ci.`totalInvoiceMonths` = rb.`totalInvoiceMonths`,
	ci.`declinedFlag` = rb.`declinedFlag`;
	

UPDATE
custitem ci
JOIN rencontract rc ON rc.`customerItemID` = ci.cui_cuino
SET
             ci.`installationDate`=rc.`installationDate`,
             ci.`invoicePeriodMonths`=rc.`invoicePeriodMonths`,
             ci.`declinedFlag`=rc.`declinedFlag`,
             ci.`totalInvoiceMonths`=rc.`totalInvoiceMonths`,
             ci.`notes`=rc.`notes`,
             ci.`hostingCompany`=rc.`hostingCompany`,
             ci.`password`=rc.`password`,
             ci.`osPlatform`=rc.`osPlatform`,
             ci.`domainNames`=rc.`domainNames`,
             ci.`controlPanelUrl`=rc.`controlPanelUrl`,
             ci.`ftpAddress`=rc.`ftpAddress`,
             ci.`ftpUsername`=rc.`ftpUsername`,
             ci.`wwwAddress`=rc.`wwwAddress`,
             ci.`websiteDeveloper`=rc.`websiteDeveloper`;
             
             
UPDATE
custitem ci
JOIN rendomain rd ON rd.`customerItemID` = ci.cui_cuino
SET
             ci.`installationDate` = rd.`installationDate`,
             ci.`invoicePeriodMonths` = rd.`invoicePeriodMonths`,
             ci.`declinedFlag` = rd.`declinedFlag`,
             ci.`totalInvoiceMonths` = rd.`totalInvoiceMonths`,
             ci.`notes` = rd.`notes`,
             ci.`dateGenerated` = ci.`dateGenerated`;

UPDATE
custitem ci
JOIN renquotation rq ON rq.`customerItemID` = ci.cui_cuino
SET
             ci.`startDate` = rq.`startDate`,
             ci.`declinedFlag` = rq.`declinedFlag`,
             ci.`salePrice` = rq.`salePrice`,
             ci.`costPrice` = rq.`costPrice`,
             ci.`qty` = rq.`qty`,
             ci.`renQuotationTypeID` = rq.`renQuotationTypeID`,
             ci.`comment` = rq.`comment`,
             ci.`grantNumber` = rq.`grantNumber`,
             ci.`dateGenerated` = rq.`dateGenerated`;
             
