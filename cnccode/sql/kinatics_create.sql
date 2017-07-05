/*
SQLyog Ultimate v10.41 
MySQL - 5.1.50-community-log : Database - cncp1
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`cncp1` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `cncp1`;

/*Table structure for table `activitycategory` */

DROP TABLE IF EXISTS `activitycategory`;

CREATE TABLE `activitycategory` (
  `activityCategoryID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `allowSelection` char(1) DEFAULT 'Y' COMMENT 'Can this be selected when creating/editing?',
  PRIMARY KEY (`activityCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;

/*Table structure for table `address` */

DROP TABLE IF EXISTS `address`;

CREATE TABLE `address` (
  `add_custno` int(11) NOT NULL DEFAULT '0',
  `add_add1` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `add_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `add_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `add_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `add_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `add_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `add_inv_contno` int(11) DEFAULT NULL,
  `add_del_contno` int(11) DEFAULT NULL,
  `add_debtor_code` char(10) CHARACTER SET utf8 DEFAULT NULL,
  `add_siteno` smallint(6) NOT NULL DEFAULT '0',
  `add_sage_ref` char(6) CHARACTER SET utf8 DEFAULT NULL,
  `add_phone` char(40) CHARACTER SET utf8 DEFAULT NULL,
  `add_max_travel_hours` decimal(5,2) NOT NULL DEFAULT '1.50',
  `add_active_flag` char(1) DEFAULT 'Y',
  PRIMARY KEY (`add_custno`,`add_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `answer` */

DROP TABLE IF EXISTS `answer`;

CREATE TABLE `answer` (
  `ans_answerno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ans_questionno` int(11) unsigned NOT NULL,
  `ans_problemno` int(11) NOT NULL,
  `ans_answer` text NOT NULL,
  `ans_name` char(100) NOT NULL,
  `ans_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ans_answerno`)
) ENGINE=MyISAM AUTO_INCREMENT=8613 DEFAULT CHARSET=latin1;

/*Table structure for table `answertype` */

DROP TABLE IF EXISTS `answertype`;

CREATE TABLE `answertype` (
  `ant_answertypeno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ant_desc` char(50) NOT NULL,
  PRIMARY KEY (`ant_answertypeno`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Table structure for table `arecords` */

DROP TABLE IF EXISTS `arecords`;

CREATE TABLE `arecords` (
  `are_arecordsno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `are_custitem` int(11) unsigned NOT NULL COMMENT 'The domain record',
  `are_desc` char(60) NOT NULL,
  `are_destination_ip` char(100) NOT NULL,
  `are_function` char(100) NOT NULL,
  PRIMARY KEY (`are_arecordsno`),
  KEY `are_custitem` (`are_custitem`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `automated_request` */

DROP TABLE IF EXISTS `automated_request`;

CREATE TABLE `automated_request` (
  `automatedRequestID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key (leave blank when INSERTing)',
  `customerID` int(11) unsigned DEFAULT NULL COMMENT 'Optional',
  `serviceRequestID` int(11) DEFAULT NULL,
  `postcode` char(10) DEFAULT NULL COMMENT 'Optional',
  `senderEmailAddress` char(100) NOT NULL COMMENT 'Required',
  `textBody` longtext NOT NULL COMMENT 'Required',
  `htmlBody` longtext COMMENT 'Optional (if exists then used instead of textBody)',
  `priority` enum('1','2','3','4','5') DEFAULT '5' COMMENT '1 - 5',
  `sendEmail` enum('A','N','S') DEFAULT 'A' COMMENT 'Always, Never, Skip work commenced email',
  `serverGuardFlag` enum('Y','N') DEFAULT 'N' COMMENT 'Y/N',
  `importedFlag` enum('Y','N') DEFAULT 'N' COMMENT 'Was this row imported successfully? Y/N',
  `attachment` longblob COMMENT 'Optional attachment',
  `attachmentFilename` char(50) DEFAULT NULL COMMENT 'Attachment name (required for attachment)',
  `attachmentMimeType` char(30) DEFAULT NULL COMMENT 'Attachment MIME type. e.g. application/pdf',
  `attachmentFileLengthBytes` int(11) DEFAULT NULL COMMENT 'Length in bytes',
  `monitorName` char(100) DEFAULT NULL COMMENT 'name of the monitor, to allow tracking of the failure and success',
  `monitorAgentName` char(100) DEFAULT NULL COMMENT 'This is the computer that the monitor has failed against.',
  `monitorStatus` char(1) DEFAULT NULL COMMENT 'Success or Failure',
  PRIMARY KEY (`automatedRequestID`)
) ENGINE=MyISAM AUTO_INCREMENT=11570 DEFAULT CHARSET=latin1;

/*Table structure for table `broadbandservicetype` */

DROP TABLE IF EXISTS `broadbandservicetype`;

CREATE TABLE `broadbandservicetype` (
  `broadbandServiceTypeID` int(11) unsigned NOT NULL,
  `description` char(50) NOT NULL,
  PRIMARY KEY (`broadbandServiceTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `callactengineer` */

DROP TABLE IF EXISTS `callactengineer`;

CREATE TABLE `callactengineer` (
  `cae_callactengno` int(11) NOT NULL DEFAULT '0',
  `cae_callactivityno` int(11) NOT NULL DEFAULT '0',
  `cae_item` smallint(6) NOT NULL DEFAULT '0',
  `cae_consno` int(11) NOT NULL DEFAULT '0',
  `cae_expn_exp_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cae_ot_exp_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `caeix_1` (`cae_callactengno`),
  KEY `caeix_2` (`cae_consno`,`cae_callactivityno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `callactivity` */

DROP TABLE IF EXISTS `callactivity`;

CREATE TABLE `callactivity` (
  `caa_callactivityno` int(11) NOT NULL DEFAULT '0',
  `caa_siteno` int(11) DEFAULT NULL,
  `caa_contno` int(11) NOT NULL DEFAULT '0',
  `caa_item` smallint(6) DEFAULT NULL,
  `caa_callacttypeno` int(11) NOT NULL DEFAULT '0',
  `activityCategoryID` int(10) unsigned DEFAULT NULL,
  `projectID` int(11) NOT NULL DEFAULT '0',
  `caa_problemno` int(11) unsigned NOT NULL DEFAULT '0',
  `caa_date` date NOT NULL DEFAULT '0000-00-00',
  `caa_starttime` varchar(5) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `caa_endtime` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `caa_status` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `caa_expexport_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `reason` text CHARACTER SET utf8,
  `internalNotes` mediumtext,
  `curValue` decimal(6,2) NOT NULL DEFAULT '0.00',
  `statementYearMonth` varchar(7) CHARACTER SET utf8 DEFAULT NULL,
  `caa_custno` int(11) unsigned NOT NULL DEFAULT '0',
  `caa_cuino` int(11) unsigned NOT NULL DEFAULT '0',
  `caa_contract_cuino` int(11) unsigned DEFAULT NULL,
  `caa_under_contract` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `caa_authorised` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `caa_consno` int(6) unsigned NOT NULL DEFAULT '0',
  `caa_ot_exp_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  `caa_completed_consno` int(6) unsigned NOT NULL DEFAULT '0',
  `caa_completed_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `caa_serverguard` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  `caa_parent_callactivityno` int(11) DEFAULT NULL,
  `caa_awaiting_customer_response_flag` char(1) DEFAULT 'N' COMMENT 'If so then exclude this from time duration calculations',
  `caa_class` char(1) DEFAULT NULL COMMENT '[W]orking, [I]nformational, [O]ther',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `caa_logging_error_flag` char(1) DEFAULT 'N' COMMENT 'Was there an error when logging this activity',
  UNIQUE KEY `ix354_1` (`caa_callactivityno`),
  KEY `contract` (`caa_contract_cuino`),
  KEY `thread` (`caa_problemno`),
  KEY `caa_custno` (`caa_custno`),
  KEY `caa_date` (`caa_date`,`caa_starttime`),
  KEY `NewIndex1` (`caa_date`,`caa_callacttypeno`),
  KEY `activityCategoryID` (`activityCategoryID`),
  KEY `contno` (`caa_contno`),
  FULLTEXT KEY `details_full` (`reason`),
  FULLTEXT KEY `internalNotes_full` (`internalNotes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `callactivity_archive` */

DROP TABLE IF EXISTS `callactivity_archive`;

CREATE TABLE `callactivity_archive` (
  `caa_callactivityno` int(11) NOT NULL DEFAULT '0',
  `caa_siteno` int(11) DEFAULT NULL,
  `caa_contno` int(11) NOT NULL DEFAULT '0',
  `caa_item` smallint(6) DEFAULT NULL,
  `caa_callacttypeno` int(11) NOT NULL DEFAULT '0',
  `activityCategoryID` int(10) unsigned DEFAULT NULL,
  `projectID` int(11) NOT NULL DEFAULT '0',
  `caa_problemno` int(11) unsigned NOT NULL DEFAULT '0',
  `caa_date` date NOT NULL DEFAULT '0000-00-00',
  `caa_starttime` varchar(5) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `caa_endtime` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `caa_status` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `caa_expexport_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `reason` text CHARACTER SET utf8,
  `internalNotes` mediumtext,
  `curValue` decimal(6,2) NOT NULL DEFAULT '0.00',
  `statementYearMonth` varchar(7) CHARACTER SET utf8 DEFAULT NULL,
  `caa_custno` int(11) unsigned NOT NULL DEFAULT '0',
  `caa_cuino` int(11) unsigned NOT NULL DEFAULT '0',
  `caa_contract_cuino` int(11) unsigned DEFAULT NULL,
  `caa_under_contract` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `caa_authorised` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `caa_consno` int(6) unsigned NOT NULL DEFAULT '0',
  `caa_ot_exp_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `caa_completed_consno` int(6) unsigned NOT NULL DEFAULT '0',
  `caa_completed_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `caa_serverguard` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  `caa_parent_callactivityno` int(11) DEFAULT NULL,
  `caa_awaiting_customer_response_flag` char(1) DEFAULT 'N' COMMENT 'If so then exclude this from time duration calculations',
  `caa_class` char(1) DEFAULT NULL COMMENT '[W]orking, [I]nformational, [O]ther',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `caa_logging_error_flag` char(1) DEFAULT 'N' COMMENT 'Was there an error when logging this activity'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `callacttype` */

DROP TABLE IF EXISTS `callacttype`;

CREATE TABLE `callacttype` (
  `cat_callacttypeno` int(11) NOT NULL DEFAULT '0',
  `cat_desc` char(60) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `cat_ooh_multiplier` decimal(5,2) DEFAULT NULL,
  `cat_itemno` int(11) DEFAULT NULL,
  `cat_min_hours` decimal(5,2) DEFAULT NULL,
  `cat_max_hours` decimal(5,2) DEFAULT NULL,
  `cat_req_check_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cat_allow_exp_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cat_problem_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cat_action_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cat_resolve_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cat_r_problem_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cat_r_action_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cat_r_resolve_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `allowSCRFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  `curValueFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  `customerEmailFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'Y',
  `travelFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N',
  `activeFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'Y' COMMENT 'Is this activity type in use?',
  `showNotChargeableFlag` char(1) NOT NULL DEFAULT 'Y' COMMENT 'On customer activity emails, show if not chargeable',
  `engineerOvertimeFlag` char(1) NOT NULL DEFAULT 'Y',
  `cat_on_site_flag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Is this on site',
  UNIQUE KEY `ix358_1` (`cat_callacttypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `calldocument` */

DROP TABLE IF EXISTS `calldocument`;

CREATE TABLE `calldocument` (
  `callDocumentID` int(11) NOT NULL DEFAULT '0',
  `problemID` int(11) NOT NULL DEFAULT '0',
  `callActivityID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `filename` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `file` longblob NOT NULL,
  `fileMIMEType` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `fileLength` int(11) NOT NULL DEFAULT '0',
  `createDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `createUserID` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `XPKDocument` (`callDocumentID`),
  KEY `problemID` (`problemID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `consultant` */

DROP TABLE IF EXISTS `consultant`;

CREATE TABLE `consultant` (
  `cns_consno` int(11) NOT NULL DEFAULT '0',
  `cns_manager` smallint(6) DEFAULT NULL,
  `cns_name` varchar(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `cns_salutation` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `cns_add1` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `cns_add2` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `cns_add3` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `cns_town` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `cns_county` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `cns_postcode` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `cns_logname` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `cns_employee_no` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `cns_petrol_rate` decimal(5,2) DEFAULT NULL,
  `cns_hourly_pay_rate` decimal(5,2) DEFAULT '25.00',
  `cns_password` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `cns_perms` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `signatureFilename` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `jobTitle` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `firstName` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `lastName` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `activeFlag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `weekdayOvertimeFlag` char(1) DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `cns_helpdesk_flag` char(1) DEFAULT NULL,
  UNIQUE KEY `ixcns_1` (`cns_consno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `contact` */

DROP TABLE IF EXISTS `contact`;

CREATE TABLE `contact` (
  `con_contno` int(11) NOT NULL DEFAULT '0',
  `con_siteno` smallint(6) DEFAULT NULL,
  `con_custno` int(11) DEFAULT NULL,
  `con_suppno` int(11) DEFAULT NULL,
  `con_title` char(10) CHARACTER SET utf8 DEFAULT NULL,
  `con_position` char(50) DEFAULT NULL,
  `con_last_name` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `con_first_name` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `con_email` char(60) CHARACTER SET utf8 DEFAULT NULL,
  `con_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `con_mobile_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `con_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailshot` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_accounts_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `con_statement_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `con_discontinued` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag1` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag2` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag3` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag4` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag5` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag6` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag7` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag8` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag9` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag10` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_notes` char(200) DEFAULT NULL,
  `con_portal_password` char(10) DEFAULT NULL,
  `con_failed_login_count` int(3) DEFAULT NULL,
  `con_work_started_email_flag` char(1) DEFAULT 'Y',
  `con_auto_close_email_flag` char(1) DEFAULT 'Y',
  UNIQUE KEY `ix_con2` (`con_contno`),
  KEY `ixcon_1` (`con_custno`,`con_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `contact_bu` */

DROP TABLE IF EXISTS `contact_bu`;

CREATE TABLE `contact_bu` (
  `con_contno` int(11) NOT NULL DEFAULT '0',
  `con_siteno` smallint(6) DEFAULT NULL,
  `con_custno` int(11) DEFAULT NULL,
  `con_suppno` int(11) DEFAULT NULL,
  `con_title` char(10) CHARACTER SET utf8 DEFAULT NULL,
  `con_position` char(50) DEFAULT NULL,
  `con_last_name` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `con_first_name` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `con_email` char(60) CHARACTER SET utf8 DEFAULT NULL,
  `con_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `con_mobile_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `con_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailshot` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_accounts_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `con_statement_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `con_discontinued` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag1` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag2` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag3` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag4` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag5` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag6` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag7` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag8` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag9` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_mailflag10` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `con_notes` char(200) DEFAULT NULL,
  `con_portal_password` char(10) DEFAULT NULL,
  `con_failed_login_count` int(2) DEFAULT NULL,
  UNIQUE KEY `ix_con2` (`con_contno`),
  KEY `ixcon_1` (`con_custno`,`con_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `contract` */

DROP TABLE IF EXISTS `contract`;

CREATE TABLE `contract` (
  `cnt_contno` int(11) NOT NULL DEFAULT '0',
  `cnt_desc` char(60) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `cnt_years` smallint(6) DEFAULT NULL,
  `cnt_manno` int(11) DEFAULT NULL,
  UNIQUE KEY `ixcnt_1` (`cnt_contno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `custitem` */

DROP TABLE IF EXISTS `custitem`;

CREATE TABLE `custitem` (
  `cui_cuino` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cui_custno` int(11) NOT NULL DEFAULT '0',
  `cui_siteno` smallint(6) NOT NULL DEFAULT '0',
  `cui_itemno` int(11) NOT NULL DEFAULT '0',
  `cui_man_contno` smallint(6) DEFAULT NULL,
  `cui_contract_cuino` int(11) DEFAULT NULL COMMENT 'Contract',
  `cui_serial` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Serial No',
  `cui_cust_ref` varchar(45) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Server name',
  `cui_ordno` int(11) DEFAULT NULL,
  `cui_sale_price` decimal(12,2) DEFAULT NULL,
  `cui_porno` int(11) DEFAULT NULL,
  `cui_pord_price` decimal(12,2) DEFAULT NULL,
  `cui_cost_price` decimal(12,2) DEFAULT NULL,
  `cui_users` smallint(6) DEFAULT NULL COMMENT 'Number of users',
  `cui_ord_date` date DEFAULT NULL,
  `cui_expiry_date` date DEFAULT NULL,
  `curGSCBalance` decimal(8,2) NOT NULL DEFAULT '0.00',
  `renewalStatus` char(1) CHARACTER SET utf8 DEFAULT 'N' COMMENT 'Is this required now we have the "live" flag',
  `renewalOrdheadID` int(11) DEFAULT '0',
  `itemNotes` text CHARACTER SET utf8,
  `cui_sla_p1` int(5) DEFAULT NULL,
  `cui_sla_p2` int(5) DEFAULT NULL,
  `cui_sla_p3` int(5) DEFAULT NULL,
  `cui_sla_p4` int(5) DEFAULT NULL,
  `cui_sla_p5` int(5) DEFAULT NULL,
  `cui_live_flag` char(1) DEFAULT 'Y' COMMENT 'Is this contract live?',
  `cui_prepay_balance` decimal(6,2) DEFAULT NULL COMMENT 'On customer record at present',
  `cui_sales_order_status` enum('','Q','I','C') DEFAULT NULL COMMENT 'Which type of sales order to create when billing',
  `renewalDate` date DEFAULT NULL,
  `itemID` int(11) unsigned DEFAULT NULL,
  `months` int(10) DEFAULT NULL,
  `ordheadID` char(10) DEFAULT NULL,
  `broadbandServiceType` char(50) DEFAULT NULL,
  `broadbandServiceTypeID` int(11) DEFAULT NULL,
  `adslPhone` varchar(255) DEFAULT NULL,
  `fee` double DEFAULT NULL COMMENT 'Cost per month',
  `macCode` varchar(255) DEFAULT NULL,
  `batchNo` char(50) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `defaultGateway` char(50) DEFAULT NULL,
  `networkAddress` char(50) DEFAULT NULL,
  `subnetMask` char(50) DEFAULT NULL,
  `routerIPAddress` text COMMENT 'one or more IP addresses',
  `userName` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `etaDate` date DEFAULT NULL,
  `installationDate` date DEFAULT NULL,
  `costPerAnnum` double DEFAULT NULL COMMENT 'Sale price per annum',
  `salePricePerMonth` decimal(6,2) DEFAULT NULL COMMENT 'Sales Price Per Month',
  `costPricePerMonth` decimal(6,2) DEFAULT NULL,
  `ispID` varchar(255) DEFAULT NULL,
  `requiresChangesFlag` varchar(1) DEFAULT NULL,
  `dualBroadbandFlag` varchar(1) DEFAULT NULL,
  `dnsCompany` varchar(255) DEFAULT NULL,
  `ipCurrentNo` char(50) DEFAULT NULL,
  `mx` varchar(255) DEFAULT NULL,
  `secureServer` varchar(255) DEFAULT NULL,
  `vpns` varchar(255) DEFAULT NULL,
  `owa` varchar(255) DEFAULT NULL,
  `oma` varchar(255) DEFAULT NULL,
  `remotePortal` varchar(255) DEFAULT NULL,
  `smartHost` varchar(255) DEFAULT NULL,
  `preparationRecords` varchar(255) DEFAULT NULL,
  `assignedTo` varchar(255) DEFAULT NULL,
  `initialSpeedTest` varchar(255) DEFAULT NULL,
  `preMigrationNotes` longblob,
  `postMigrationNotes` longblob,
  `docsUpdatedAndChecksCompleted` varchar(255) DEFAULT NULL,
  `invoicePeriodMonths` int(4) DEFAULT NULL COMMENT 'Number of months between invoices',
  `totalInvoiceMonths` int(11) DEFAULT NULL COMMENT 'Number of months to add to install date to calculate next invoice due date',
  `declinedFlag` char(1) DEFAULT NULL,
  `hostingCompany` char(100) DEFAULT NULL,
  `osPlatform` char(50) DEFAULT NULL,
  `domainNames` char(100) DEFAULT NULL,
  `controlPanelUrl` char(50) DEFAULT NULL,
  `ftpAddress` char(50) DEFAULT NULL,
  `ftpUsername` char(50) DEFAULT NULL,
  `wwwAddress` char(50) DEFAULT NULL,
  `websiteDeveloper` char(50) DEFAULT NULL,
  `dateGenerated` date DEFAULT '0000-00-00',
  `startDate` date DEFAULT NULL,
  `salePrice` decimal(6,2) DEFAULT NULL,
  `costPrice` decimal(6,2) DEFAULT NULL,
  `qty` int(3) DEFAULT NULL,
  `renQuotationTypeID` int(11) DEFAULT NULL,
  `comment` char(50) DEFAULT NULL,
  `grantNumber` char(50) DEFAULT NULL,
  `notes` text,
  `cui_consno` smallint(6) DEFAULT NULL,
  `cui_ctactno` smallint(6) DEFAULT NULL,
  `cui_desp_date` date DEFAULT NULL,
  `cui_pord_date` date DEFAULT NULL,
  `cui_ref_cust` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `cui_sla_response_hours` int(3) DEFAULT NULL,
  UNIQUE KEY `ixcui_1` (`cui_cuino`),
  KEY `ix_cui2` (`cui_custno`,`cui_siteno`,`cui_cuino`),
  KEY `renewalOrdheadID` (`renewalOrdheadID`),
  KEY `cui_itemno` (`cui_itemno`)
) ENGINE=MyISAM AUTO_INCREMENT=38714 DEFAULT CHARSET=latin1;

/*Table structure for table `customer` */

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `cus_custno` int(11) NOT NULL DEFAULT '0',
  `cus_name` varchar(35) CHARACTER SET utf8 DEFAULT NULL,
  `cus_reg_no` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `cus_inv_siteno` smallint(6) DEFAULT NULL,
  `cus_del_siteno` smallint(6) DEFAULT NULL,
  `cus_mailshot` varchar(2) CHARACTER SET utf8 DEFAULT NULL,
  `cus_create_date` date DEFAULT NULL,
  `cus_referred` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cus_pcx` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cus_ctypeno` int(11) DEFAULT NULL,
  `cus_prospect` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cus_others_email_main_flag` char(1) DEFAULT 'Y',
  `cus_work_started_email_main_flag` char(1) DEFAULT 'Y' COMMENT 'Should work stared emails be sent from service system?',
  `cus_auto_close_email_main_flag` char(1) DEFAULT 'Y' COMMENT 'Email main contact?',
  `cus_became_customer_date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date became a customer',
  `cus_dropped_customer_date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date dropped as customer',
  `cus_leadstatusno` int(11) unsigned DEFAULT NULL,
  `gscTopUpAmount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `modifyDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `noOfPCs` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '0',
  `noOfServers` smallint(3) NOT NULL DEFAULT '0',
  `noOfSites` tinyint(4) NOT NULL DEFAULT '1',
  `comments` text,
  `reviewDate` date DEFAULT NULL,
  `reviewTime` char(5) DEFAULT NULL,
  `reviewAction` char(100) DEFAULT NULL,
  `reviewUserID` int(11) DEFAULT NULL,
  `modifyUserID` int(11) DEFAULT NULL,
  `cus_sectorno` int(11) unsigned DEFAULT NULL,
  `cus_tech_notes` char(100) DEFAULT NULL COMMENT 'Notes to appear on all request screens',
  `cus_special_attention_flag` char(1) DEFAULT 'N',
  `cus_special_attention_end_date` date DEFAULT NULL,
  `cus_support_24_hour_flag` char(1) DEFAULT 'N',
  `cus_sla_p1` int(5) DEFAULT NULL,
  `cus_sla_p2` int(5) DEFAULT NULL,
  `cus_sla_p3` int(5) DEFAULT NULL,
  `cus_sla_p4` int(5) DEFAULT NULL,
  `cus_sla_p5` int(5) DEFAULT NULL,
  UNIQUE KEY `ixcus_1` (`cus_custno`),
  KEY `cus_sectorno` (`cus_sectorno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `customeritemdocument` */

DROP TABLE IF EXISTS `customeritemdocument`;

CREATE TABLE `customeritemdocument` (
  `customerItemDocumentID` int(11) NOT NULL DEFAULT '0',
  `customerItemID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `filename` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `file` longblob NOT NULL,
  `fileMIMEType` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `fileLength` int(11) NOT NULL DEFAULT '0',
  `createDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `createUserID` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `XPKCustomerItemDocument` (`customerItemDocumentID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `customernote` */

DROP TABLE IF EXISTS `customernote`;

CREATE TABLE `customernote` (
  `cno_customernoteno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cno_custno` int(11) NOT NULL,
  `cno_created` datetime DEFAULT NULL,
  `cno_modified` datetime DEFAULT NULL,
  `cno_modified_consno` int(11) unsigned NOT NULL,
  `cno_details` text,
  `cno_created_consno` int(11) unsigned NOT NULL,
  `cno_ordno` int(11) unsigned DEFAULT NULL COMMENT 'Sales Order',
  PRIMARY KEY (`cno_customernoteno`)
) ENGINE=MyISAM AUTO_INCREMENT=11721 DEFAULT CHARSET=latin1;

/*Table structure for table `customerproblem` */

DROP TABLE IF EXISTS `customerproblem`;

CREATE TABLE `customerproblem` (
  `cpr_customerproblemno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cpr_custno` int(11) unsigned DEFAULT NULL,
  `cpr_siteno` int(3) DEFAULT '99',
  `cpr_contno` int(11) unsigned NOT NULL,
  `cpr_date` datetime DEFAULT NULL,
  `cpr_priority` int(11) DEFAULT NULL,
  `cpr_reason` text,
  `cpr_internal_notes` text,
  `cpr_serverguard_flag` char(1) DEFAULT 'N',
  `cpr_source` enum('C','S') DEFAULT 'S',
  `cpr_problemno` int(11) unsigned NOT NULL,
  `cpr_update_existing_request` tinyint(1) DEFAULT '1',
  `cpr_send_email` char(1) DEFAULT NULL,
  PRIMARY KEY (`cpr_customerproblemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `custype` */

DROP TABLE IF EXISTS `custype`;

CREATE TABLE `custype` (
  `cty_ctypeno` smallint(6) NOT NULL DEFAULT '0',
  `cty_desc` char(40) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `date_xref` */

DROP TABLE IF EXISTS `date_xref`;

CREATE TABLE `date_xref` (
  `date_field` date NOT NULL,
  `is_bank_holiday` char(1) DEFAULT 'N',
  PRIMARY KEY (`date_field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `db_sequence` */

DROP TABLE IF EXISTS `db_sequence`;

CREATE TABLE `db_sequence` (
  `seq_name` varchar(127) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `nextid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`seq_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `delivery` */

DROP TABLE IF EXISTS `delivery`;

CREATE TABLE `delivery` (
  `del_delno` int(11) NOT NULL DEFAULT '0',
  `del_desc` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `del_send_note` char(1) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `ix141_1` (`del_delno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `deliverynote` */

DROP TABLE IF EXISTS `deliverynote`;

CREATE TABLE `deliverynote` (
  `deliveryNoteID` int(11) NOT NULL DEFAULT '0',
  `ordheadID` int(11) NOT NULL DEFAULT '0',
  `noteNo` tinyint(4) NOT NULL DEFAULT '0',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`deliveryNoteID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `deshead` */

DROP TABLE IF EXISTS `deshead`;

CREATE TABLE `deshead` (
  `deh_desno` int(11) NOT NULL DEFAULT '0',
  `deh_ordno` smallint(6) NOT NULL DEFAULT '0',
  `deh_custno` smallint(6) NOT NULL DEFAULT '0',
  `deh_invno` int(11) DEFAULT NULL,
  `deh_ref_cust` char(23) CHARACTER SET utf8 DEFAULT NULL,
  `deh_ref_ecc` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `deh_method` char(12) CHARACTER SET utf8 DEFAULT NULL,
  `deh_date` date DEFAULT NULL,
  UNIQUE KEY `ix130_1` (`deh_desno`),
  KEY `ix132_5` (`deh_ref_cust`),
  KEY `ix135_6` (`deh_ref_ecc`),
  KEY `ixdeh_1` (`deh_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `desline` */

DROP TABLE IF EXISTS `desline`;

CREATE TABLE `desline` (
  `del_desno` smallint(6) NOT NULL DEFAULT '0',
  `del_line_no` smallint(6) NOT NULL DEFAULT '0',
  `del_ord_line_no` smallint(6) NOT NULL DEFAULT '0',
  `del_qty` decimal(7,2) DEFAULT NULL,
  KEY `ix131_1` (`del_desno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `domain_import` */

DROP TABLE IF EXISTS `domain_import`;

CREATE TABLE `domain_import` (
  `domain` char(100) DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `invoicePeriodMonths` int(3) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `expense` */

DROP TABLE IF EXISTS `expense`;

CREATE TABLE `expense` (
  `exp_expenseno` int(11) NOT NULL DEFAULT '0',
  `exp_callactivityno` int(11) NOT NULL DEFAULT '0',
  `exp_expensetypeno` int(11) NOT NULL DEFAULT '0',
  `exp_mileage` int(11) DEFAULT NULL,
  `exp_value` decimal(8,2) NOT NULL DEFAULT '0.00',
  `exp_vat_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `exp_exported_flag` char(1) DEFAULT 'N' COMMENT 'indicates whether this expense has been exported to the file',
  UNIQUE KEY `ix402_1` (`exp_expenseno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `expensetype` */

DROP TABLE IF EXISTS `expensetype`;

CREATE TABLE `expensetype` (
  `ext_expensetypeno` int(11) NOT NULL DEFAULT '0',
  `ext_desc` char(80) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `ext_mileage_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `ext_vat_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `ix401_1` (`ext_expensetypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `further_action` */

DROP TABLE IF EXISTS `further_action`;

CREATE TABLE `further_action` (
  `furtherActionID` int(6) unsigned NOT NULL DEFAULT '0',
  `description` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `emailAddress` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `requireDate` char(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  `emailBody` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`furtherActionID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `future_action` */

DROP TABLE IF EXISTS `future_action`;

CREATE TABLE `future_action` (
  `futureActionID` int(11) unsigned NOT NULL DEFAULT '0',
  `furtherActionID` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `callActivityID` varchar(11) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `engineerName` char(50) DEFAULT NULL,
  `dateCreated` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `headert` */

DROP TABLE IF EXISTS `headert`;

CREATE TABLE `headert` (
  `headerID` tinyint(4) NOT NULL DEFAULT '0',
  `hed_name` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `hed_add1` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `hed_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `hed_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `hed_town` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_county` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `hed_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `hed_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `hed_goods_contact` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `hed_sstk_suppno` smallint(6) DEFAULT NULL,
  `hed_mstk_suppno` smallint(6) DEFAULT NULL,
  `hed_std_vatcode` char(2) CHARACTER SET utf8 DEFAULT NULL,
  `hed_car_stockcat` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_next_porno` smallint(6) DEFAULT NULL,
  `hed_next_itemno` smallint(6) DEFAULT NULL,
  `hed_next_invno` int(11) DEFAULT NULL,
  `hed_sstk_locno` smallint(6) DEFAULT NULL,
  `hed_mstk_locno` smallint(6) DEFAULT NULL,
  `hed_ecc_ass_locno` smallint(6) DEFAULT NULL,
  `hed_ecc_op_locno` smallint(6) DEFAULT NULL,
  `hed_invoice_prt` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_porder_prt` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_plaser_prt` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_llaser_prt` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_system_prt` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_audit_prt` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_bill_starttime` char(5) CHARACTER SET utf8 DEFAULT NULL,
  `hed_bill_endtime` char(5) CHARACTER SET utf8 DEFAULT NULL,
  `hed_hd_starttime` char(5) DEFAULT NULL,
  `hed_hd_endtime` char(5) DEFAULT NULL,
  `hed_pro_starttime` char(5) DEFAULT NULL,
  `hed_pro_endtime` char(5) DEFAULT NULL,
  `hed_gensup_itemno` int(11) DEFAULT NULL,
  `hed_portal_pin` char(5) NOT NULL,
  `hed_next_schedno` int(11) DEFAULT NULL,
  `hed_ot_adjust_hour` decimal(5,2) DEFAULT NULL,
  `hed_mailflg1_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg2_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg3_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg4_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg5_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg6_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg7_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg8_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg9_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg10_def` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg1_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg2_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg3_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg4_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg5_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg6_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg7_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg8_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg9_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_mailflg10_desc` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `hed_helpdesk_problems` text COMMENT 'Daily helpdesk problems',
  `hed_helpdesk_os_count` int(5) DEFAULT NULL,
  `hed_helpdesk_os_service_desk_count` int(5) DEFAULT NULL,
  `hed_helpdesk_os_servercare_count` int(5) DEFAULT NULL,
  `hed_helpdesk_os_prepay_count` int(5) DEFAULT NULL,
  `hed_helpdesk_os_escalation_count` int(5) DEFAULT NULL,
  `hed_helpdesk_os_cust_response_count` int(5) DEFAULT NULL,
  `hed_hourly_labour_cost` decimal(5,2) DEFAULT NULL COMMENT 'Cost to CNC',
  `hed_portal_24_hour_pin` char(5) NOT NULL,
  PRIMARY KEY (`headerID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `invhead` */

DROP TABLE IF EXISTS `invhead`;

CREATE TABLE `invhead` (
  `inh_invno` int(11) NOT NULL DEFAULT '0',
  `inh_custno` int(11) NOT NULL DEFAULT '0',
  `inh_siteno` smallint(6) DEFAULT NULL,
  `inh_ordno` int(11) DEFAULT NULL,
  `inh_type` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `inh_add1` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `inh_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `inh_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `inh_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `inh_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `inh_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `inh_contno` int(11) DEFAULT NULL,
  `inh_contact` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `inh_salutation` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `inh_pay_method` char(30) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `paymentTermsID` tinyint(4) DEFAULT NULL,
  `inh_vat_code` char(2) CHARACTER SET utf8 DEFAULT NULL,
  `inh_vat_rate` decimal(4,2) DEFAULT NULL,
  `inh_ref_ecc` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `inh_ref_cust` char(23) CHARACTER SET utf8 DEFAULT NULL,
  `inh_debtor_code` char(10) CHARACTER SET utf8 DEFAULT NULL,
  `inh_source` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `inh_vat_only` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `inh_date_printed` date DEFAULT NULL COMMENT 'PDF file of the invoice',
  `inh_pdf_file` longblob,
  UNIQUE KEY `ix115_1` (`inh_invno`),
  KEY `ixinh_2` (`inh_ordno`),
  KEY `inh_date_printed` (`inh_date_printed`),
  KEY `custno` (`inh_custno`),
  KEY `contno` (`inh_contno`),
  KEY `paymentTerms` (`paymentTermsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `invline` */

DROP TABLE IF EXISTS `invline`;

CREATE TABLE `invline` (
  `inl_invno` int(11) DEFAULT NULL,
  `inl_line_no` smallint(6) DEFAULT NULL,
  `inl_ord_line_no` smallint(6) DEFAULT NULL,
  `inl_line_type` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `inl_itemno` int(11) DEFAULT NULL,
  `inl_desc` char(45) CHARACTER SET utf8 DEFAULT NULL,
  `inl_qty` decimal(7,2) DEFAULT NULL,
  `inl_unit_price` decimal(7,2) DEFAULT NULL,
  `inl_cost_price` decimal(7,2) DEFAULT NULL,
  `inl_stockcat` char(1) CHARACTER SET utf8 DEFAULT NULL,
  KEY `ixinl_1` (`inl_invno`),
  KEY `inl_itemno` (`inl_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `item` */

DROP TABLE IF EXISTS `item`;

CREATE TABLE `item` (
  `itm_itemno` int(11) NOT NULL DEFAULT '0',
  `itm_manno` smallint(6) DEFAULT NULL,
  `itm_desc` varchar(45) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `itm_stockcat` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `itm_itemtypeno` int(11) DEFAULT NULL,
  `itm_sstk_price` decimal(12,2) DEFAULT NULL,
  `itm_sstk_cost` decimal(12,2) DEFAULT NULL,
  `itm_mstk_cost` decimal(12,2) DEFAULT NULL,
  `itm_serial_req` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `itm_sstk_qty` decimal(5,2) DEFAULT NULL,
  `itm_mstk_qty` decimal(5,2) DEFAULT NULL,
  `itm_discontinued` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `itm_unit_of_sale` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `itm_contno` int(11) DEFAULT NULL,
  `itm_servercare_flag` char(1) DEFAULT '0' COMMENT 'is this a servercare contract?',
  `contractResponseTime` int(4) unsigned DEFAULT '8',
  `notes` text CHARACTER SET utf8,
  `renewalTypeID` int(11) unsigned DEFAULT NULL,
  UNIQUE KEY `ixitm_1` (`itm_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `itemtype` */

DROP TABLE IF EXISTS `itemtype`;

CREATE TABLE `itemtype` (
  `ity_itemtypeno` int(11) NOT NULL DEFAULT '0',
  `ity_desc` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `ity_stockcat` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`ity_itemtypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `leadstatus` */

DROP TABLE IF EXISTS `leadstatus`;

CREATE TABLE `leadstatus` (
  `lst_leadstatusno` int(11) unsigned NOT NULL,
  `lst_desc` char(50) DEFAULT NULL,
  PRIMARY KEY (`lst_leadstatusno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `mail_queue` */

DROP TABLE IF EXISTS `mail_queue`;

CREATE TABLE `mail_queue` (
  `id` bigint(20) NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `time_to_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sent_time` datetime DEFAULT NULL,
  `id_user` bigint(20) NOT NULL DEFAULT '0',
  `ip` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT 'unknown',
  `sender` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `recipient` varchar(300) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `headers` text CHARACTER SET utf8 NOT NULL,
  `body` longtext CHARACTER SET utf8 NOT NULL,
  `try_sent` tinyint(4) NOT NULL DEFAULT '0',
  `delete_after_send` tinyint(1) NOT NULL DEFAULT '1',
  `is_sending` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `time_to_send` (`time_to_send`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `mail_queue_seq` */

DROP TABLE IF EXISTS `mail_queue_seq`;

CREATE TABLE `mail_queue_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=252467 DEFAULT CHARSET=latin1;

/*Table structure for table `mailshot_table` */

DROP TABLE IF EXISTS `mailshot_table`;

CREATE TABLE `mailshot_table` (
  `keyfield` char(50) DEFAULT NULL,
  `hits` int(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `manufact` */

DROP TABLE IF EXISTS `manufact`;

CREATE TABLE `manufact` (
  `man_manno` int(11) NOT NULL DEFAULT '0',
  `man_name` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `man_disc_rate` decimal(4,2) DEFAULT NULL,
  UNIQUE KEY `ixman_1` (`man_manno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `notepad` */

DROP TABLE IF EXISTS `notepad`;

CREATE TABLE `notepad` (
  `not_type` varchar(3) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `not_key` int(11) NOT NULL DEFAULT '0',
  `not_line` tinyint(4) NOT NULL DEFAULT '0',
  `not_text` varchar(76) CHARACTER SET utf8 DEFAULT NULL,
  KEY `ix_not1` (`not_type`,`not_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ordhead` */

DROP TABLE IF EXISTS `ordhead`;

CREATE TABLE `ordhead` (
  `odh_ordno` int(11) NOT NULL DEFAULT '0',
  `odh_custno` int(11) DEFAULT NULL,
  `odh_type` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_part_invoice` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_date` date DEFAULT NULL,
  `odh_req_date` date DEFAULT NULL,
  `odh_prom_date` date DEFAULT NULL,
  `odh_expect_date` date DEFAULT NULL,
  `odh_ref_ecc` char(100) CHARACTER SET utf8 DEFAULT NULL,
  `odh_ref_cust` char(23) CHARACTER SET utf8 DEFAULT NULL,
  `odh_vat_code` char(2) CHARACTER SET utf8 DEFAULT NULL,
  `odh_vat_rate` decimal(4,2) DEFAULT NULL,
  `odh_inv_siteno` smallint(6) DEFAULT NULL,
  `odh_inv_add1` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_contno` int(11) DEFAULT NULL,
  `odh_inv_contact` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_salutation` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_sphone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_email` char(60) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_siteno` smallint(6) DEFAULT NULL,
  `odh_del_add1` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_contno` int(11) DEFAULT NULL,
  `odh_del_contact` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_salutation` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_sphone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_email` char(60) CHARACTER SET utf8 DEFAULT NULL,
  `odh_debtor_code` char(10) CHARACTER SET utf8 DEFAULT NULL,
  `odh_wip` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_consno` smallint(6) DEFAULT NULL,
  `odh_pay_method` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `paymentTermsID` tinyint(4) DEFAULT NULL,
  `odh_add_item` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_callno` int(11) DEFAULT NULL,
  `odh_quotation_subject` char(50) DEFAULT NULL,
  `odh_quotation_introduction` char(200) DEFAULT NULL,
  `updatedTime` datetime DEFAULT NULL,
  `odh_service_request_custitemno` int(11) DEFAULT NULL,
  `odh_service_request_text` text,
  `odh_service_request_bill_to_sales_order` char(1) DEFAULT NULL,
  `odh_service_request_priority` int(11) DEFAULT NULL,
  `odh_problemno` int(11) DEFAULT NULL COMMENT 'The service request generated from this order',
  UNIQUE KEY `ixodh_1` (`odh_ordno`),
  KEY `ixodh_2` (`odh_type`),
  KEY `ixodh_3` (`odh_date`),
  KEY `ixodh_4` (`odh_custno`),
  KEY `ixodh_5` (`odh_ref_ecc`),
  KEY `ixodh_6` (`odh_ref_cust`),
  KEY `ixodh_7` (`odh_custno`,`odh_type`),
  KEY `ixodh_10` (`odh_custno`,`odh_date`),
  KEY `paymentTerms` (`paymentTermsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ordhead_sco` */

DROP TABLE IF EXISTS `ordhead_sco`;

CREATE TABLE `ordhead_sco` (
  `odh_ordno` int(11) NOT NULL DEFAULT '0',
  `odh_custno` int(11) DEFAULT NULL,
  `odh_type` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_part_invoice` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_date` date DEFAULT NULL,
  `odh_req_date` date DEFAULT NULL,
  `odh_prom_date` date DEFAULT NULL,
  `odh_expect_date` date DEFAULT NULL,
  `odh_ref_ecc` char(100) CHARACTER SET utf8 DEFAULT NULL,
  `odh_ref_cust` char(23) CHARACTER SET utf8 DEFAULT NULL,
  `odh_vat_code` char(2) CHARACTER SET utf8 DEFAULT NULL,
  `odh_vat_rate` decimal(4,2) DEFAULT NULL,
  `odh_inv_siteno` smallint(6) DEFAULT NULL,
  `odh_inv_add1` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_contno` int(11) DEFAULT NULL,
  `odh_inv_contact` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_salutation` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_sphone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_inv_email` char(60) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_siteno` smallint(6) DEFAULT NULL,
  `odh_del_add1` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_contno` int(11) DEFAULT NULL,
  `odh_del_contact` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_salutation` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_sphone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `odh_del_email` char(60) CHARACTER SET utf8 DEFAULT NULL,
  `odh_debtor_code` char(10) CHARACTER SET utf8 DEFAULT NULL,
  `odh_wip` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_consno` smallint(6) DEFAULT NULL,
  `odh_pay_method` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `odh_add_item` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_callno` int(11) DEFAULT NULL,
  `updatedTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `newOrdheadID` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `ixodh_1` (`odh_ordno`),
  KEY `new_ordheadid` (`newOrdheadID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ordline` */

DROP TABLE IF EXISTS `ordline`;

CREATE TABLE `ordline` (
  `odl_type` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odl_ordno` int(11) NOT NULL DEFAULT '0',
  `odl_item_no` smallint(6) NOT NULL DEFAULT '0',
  `odl_custno` int(11) DEFAULT NULL,
  `odl_itemno` int(11) DEFAULT NULL,
  `odl_stockcat` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odl_desc` char(45) CHARACTER SET utf8 DEFAULT NULL,
  `odl_qty_ord` decimal(7,2) DEFAULT NULL,
  `odl_qty_desp` decimal(7,2) DEFAULT NULL,
  `odl_qty_last_desp` decimal(7,2) DEFAULT NULL,
  `odl_suppno` smallint(6) DEFAULT NULL,
  `odl_d_unit` decimal(7,2) DEFAULT NULL,
  `odl_d_total` decimal(7,2) DEFAULT NULL,
  `odl_e_unit` decimal(7,2) DEFAULT NULL,
  `odl_e_total` decimal(7,2) DEFAULT NULL,
  `odl_renewal_cuino` int(11) unsigned DEFAULT NULL,
  KEY `idxodl_2` (`odl_suppno`,`odl_desc`),
  KEY `idodl_3` (`odl_custno`,`odl_desc`),
  KEY `odl_itemno` (`odl_itemno`),
  KEY `ix_ordno` (`odl_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ordline_sco` */

DROP TABLE IF EXISTS `ordline_sco`;

CREATE TABLE `ordline_sco` (
  `odl_type` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odl_ordno` int(11) DEFAULT NULL,
  `odl_item_no` smallint(6) DEFAULT NULL,
  `odl_custno` int(11) DEFAULT NULL,
  `odl_itemno` int(11) DEFAULT NULL,
  `odl_stockcat` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odl_desc` char(45) CHARACTER SET utf8 DEFAULT NULL,
  `odl_qty_ord` decimal(7,2) DEFAULT NULL,
  `odl_qty_desp` decimal(7,2) DEFAULT NULL,
  `odl_qty_last_desp` decimal(7,2) DEFAULT NULL,
  `odl_suppno` smallint(6) DEFAULT NULL,
  `odl_d_unit` decimal(7,2) DEFAULT NULL,
  `odl_d_total` decimal(7,2) DEFAULT NULL,
  `odl_e_unit` decimal(7,2) DEFAULT NULL,
  `odl_e_total` decimal(7,2) DEFAULT NULL,
  `processedFlag` tinyint(1) NOT NULL DEFAULT '0',
  KEY `ixodl_1` (`odl_ordno`),
  KEY `odl_itemno` (`odl_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `page_view` */

DROP TABLE IF EXISTS `page_view`;

CREATE TABLE `page_view` (
  `page_view_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `script_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `display_fields` longtext CHARACTER SET utf8,
  `order_by` longtext CHARACTER SET utf8,
  `filters` longtext CHARACTER SET utf8,
  `created_by_user_id` int(11) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by_user_id` int(11) NOT NULL DEFAULT '0',
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`page_view_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `created_by_user_id` (`created_by_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Table structure for table `paymentterms` */

DROP TABLE IF EXISTS `paymentterms`;

CREATE TABLE `paymentterms` (
  `paymentTermsID` int(11) NOT NULL DEFAULT '0',
  `description` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `days` tinyint(4) NOT NULL DEFAULT '0',
  `generateInvoiceFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `automaticInvoiceFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`paymentTermsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `paymeth` */

DROP TABLE IF EXISTS `paymeth`;

CREATE TABLE `paymeth` (
  `pay_payno` int(11) NOT NULL DEFAULT '0',
  `pay_desc` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `pay_card` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `pay_cardno` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `pay_exp_date` date DEFAULT NULL,
  `pay_consno` smallint(6) DEFAULT NULL,
  `automaticInvoiceFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'Y',
  UNIQUE KEY `ix178_1` (`pay_payno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `pinline` */

DROP TABLE IF EXISTS `pinline`;

CREATE TABLE `pinline` (
  `pin_pinno` int(11) NOT NULL DEFAULT '0',
  `pin_type` char(2) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `pin_ac_ref` int(11) NOT NULL DEFAULT '0',
  `pin_nom_ref` int(11) NOT NULL DEFAULT '0',
  `pin_dept` int(11) NOT NULL DEFAULT '0',
  `pin_date` date NOT NULL DEFAULT '0000-00-00',
  `pin_ref` char(30) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `pin_details` char(20) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `pin_net_amnt` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pin_tax_code` char(2) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `pin_tax_amnt` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pin_printed` char(1) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`pin_pinno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `porhead` */

DROP TABLE IF EXISTS `porhead`;

CREATE TABLE `porhead` (
  `poh_porno` int(11) NOT NULL DEFAULT '0',
  `poh_type` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `poh_suppno` int(11) NOT NULL DEFAULT '0',
  `poh_contno` int(11) DEFAULT NULL,
  `poh_date` date DEFAULT NULL,
  `poh_ordno` int(11) DEFAULT NULL,
  `poh_supp_ref` char(30) CHARACTER SET utf8 DEFAULT NULL,
  `poh_direct_del` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `poh_payno` smallint(6) DEFAULT NULL,
  `poh_invoices` char(60) CHARACTER SET utf8 DEFAULT NULL,
  `poh_printed` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `poh_consno` smallint(6) DEFAULT NULL,
  `poh_vat_code` char(2) CHARACTER SET utf8 DEFAULT NULL,
  `poh_vat_rate` decimal(5,2) DEFAULT NULL,
  `poh_locno` smallint(6) DEFAULT NULL,
  `poh_ord_consno` smallint(6) DEFAULT NULL,
  `poh_ord_date` date DEFAULT NULL,
  UNIQUE KEY `ix149_1` (`poh_porno`),
  KEY `ixpoh_1` (`poh_ordno`),
  KEY `ixpoh_2` (`poh_suppno`,`poh_type`),
  KEY `ixpoh_3` (`poh_type`),
  KEY `poh_date` (`poh_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `porline` */

DROP TABLE IF EXISTS `porline`;

CREATE TABLE `porline` (
  `pol_porno` int(11) NOT NULL DEFAULT '0',
  `pol_lineno` smallint(6) NOT NULL DEFAULT '0',
  `pol_itemno` int(11) DEFAULT NULL,
  `pol_qty_ord` decimal(12,2) DEFAULT NULL,
  `pol_qty_rec` decimal(12,2) DEFAULT NULL,
  `pol_qty_inv` decimal(12,6) DEFAULT NULL,
  `pol_cost` decimal(12,2) DEFAULT NULL,
  `pol_stockcat` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `pol_exp_date` date DEFAULT NULL,
  KEY `ixpol_1` (`pol_porno`),
  KEY `pol_itemno` (`pol_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `prepaystatement` */

DROP TABLE IF EXISTS `prepaystatement`;

CREATE TABLE `prepaystatement` (
  `pre_prepayno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pre_custno` int(11) unsigned DEFAULT NULL,
  `pre_date` date DEFAULT NULL,
  `pre_balance` decimal(8,2) DEFAULT NULL,
  `pre_file` longblob,
  PRIMARY KEY (`pre_prepayno`)
) ENGINE=MyISAM AUTO_INCREMENT=1452 DEFAULT CHARSET=latin1;

/*Table structure for table `prizewinner` */

DROP TABLE IF EXISTS `prizewinner`;

CREATE TABLE `prizewinner` (
  `prz_prizewinnerno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prz_yearmonth` char(7) NOT NULL COMMENT 'YYYY-MM',
  `prz_contno` int(11) NOT NULL COMMENT 'Customer contact that woin',
  `prz_approved_flag` char(1) DEFAULT 'N' COMMENT 'has the prize been approved?',
  `prz_survey_name` char(50) DEFAULT NULL COMMENT 'name entered on the survey',
  PRIMARY KEY (`prz_prizewinnerno`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

/*Table structure for table `problem` */

DROP TABLE IF EXISTS `problem`;

CREATE TABLE `problem` (
  `pro_problemno` int(11) unsigned NOT NULL DEFAULT '0',
  `pro_custno` int(11) NOT NULL DEFAULT '0',
  `pro_priority` int(3) DEFAULT NULL,
  `pro_consno` int(11) DEFAULT NULL COMMENT 'User to whom the problem is allocated. zero indicaates Unallocated',
  `pro_status` enum('I','P','F','C') DEFAULT 'I' COMMENT 'I=Initial P=In Progress F=Fixed C=Completed',
  `pro_date_raised` datetime DEFAULT NULL COMMENT 'Date the problem was first raised in the system',
  `pro_responded_hours` decimal(7,2) DEFAULT '0.00' COMMENT 'duration in hours until response',
  `pro_fixed_consno` int(11) DEFAULT NULL COMMENT 'user that fixed problem',
  `pro_fixed_date` datetime DEFAULT NULL,
  `pro_working_hours` decimal(7,2) DEFAULT '0.00' COMMENT 'Number of working hours since problem first raised',
  `pro_sent_sla_alert_flag` char(1) DEFAULT 'N' COMMENT 'Set to show SLA alert email has been sent',
  `pro_internal_notes` mediumtext COMMENT 'Internal CNC Notes',
  `pro_completion_alert_count` int(1) DEFAULT '0' COMMENT 'How many completion alerts have been sent to the customer',
  `pro_complete_date` date DEFAULT NULL COMMENT 'Date when request to be completed either manually or automatically',
  `pro_email_option` enum('A','N','S') DEFAULT 'A' COMMENT 'A=Always, N=Never or S=Skip work commenced email',
  `pro_alarm_date` date DEFAULT NULL,
  `pro_alarm_time` char(5) DEFAULT NULL,
  `pro_total_activity_duration_hours` decimal(7,2) DEFAULT NULL,
  `pro_chargeable_activity_duration_hours` decimal(7,2) DEFAULT NULL,
  `pro_sla_response_hours` decimal(12,2) DEFAULT NULL,
  `pro_contract_cuino` int(11) DEFAULT NULL,
  `pro_escalated_flag` char(1) DEFAULT 'N' COMMENT 'Was this escalated',
  `pro_escalated_consno` int(11) unsigned DEFAULT NULL COMMENT 'Engineer that escalated',
  `pro_reopened_flag` char(1) DEFAULT 'N' COMMENT 'Was this reopened',
  `pro_contno` int(11) unsigned DEFAULT NULL,
  `activityCategoryID` int(11) unsigned DEFAULT NULL,
  `pro_technician_weighting` enum('1','2','3','4','5') DEFAULT '1' COMMENT 'Priority for engineer dashboard. Not used in any other context.',
  `pro_rejected_consno` int(11) unsigned DEFAULT NULL COMMENT 'User that last rejected request',
  `pro_do_next_flag` char(1) DEFAULT 'N' COMMENT 'Indicates the engineer should work on this request next',
  `pro_rootcauseno` int(11) unsigned DEFAULT NULL COMMENT 'FK to rootcause table',
  `pro_working_hours_alert_sent_flag` char(1) DEFAULT 'N' COMMENT 'Has an alert been sent when number of elapsed hours exceeds system limit?',
  `pro_awaiting_customer_response_flag` char(1) DEFAULT 'N' COMMENT 'Are we waiting for customer action?',
  `pro_working_hours_calculated_to_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'This is an optomisisation used by BUProblemSLA',
  `pro_manager_comment` text,
  `pro_breach_comment` text,
  `pro_message_to_sales` text,
  `pro_monitor_name` char(100) DEFAULT NULL,
  `pro_monitor_agent_name` char(100) DEFAULT NULL,
  PRIMARY KEY (`pro_problemno`),
  KEY `pro_status_consno` (`pro_status`,`pro_consno`),
  FULLTEXT KEY `pro_internal_notes` (`pro_internal_notes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `project` */

DROP TABLE IF EXISTS `project`;

CREATE TABLE `project` (
  `projectID` int(11) NOT NULL DEFAULT '0',
  `customerID` int(11) NOT NULL DEFAULT '0',
  `description` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `startDate` date DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `notes` text,
  UNIQUE KEY `ixcallt_1` (`projectID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `question` */

DROP TABLE IF EXISTS `question`;

CREATE TABLE `question` (
  `que_questionno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `que_questionnaireno` int(11) unsigned DEFAULT NULL,
  `que_desc` char(100) NOT NULL COMMENT 'Description',
  `que_answertypeno` enum('1','2','3') DEFAULT NULL COMMENT 'Type of answer. 1=',
  `que_active_flag` char(1) DEFAULT 'Y' COMMENT 'Question is active?',
  `que_weight` int(3) DEFAULT '0' COMMENT 'Weighting to control display order on screen',
  `que_required_flag` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`que_questionno`)
) ENGINE=MyISAM AUTO_INCREMENT=3060 DEFAULT CHARSET=latin1;

/*Table structure for table `question_type` */

DROP TABLE IF EXISTS `question_type`;

CREATE TABLE `question_type` (
  `qut_questiontypeno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `qut_desc` char(50) NOT NULL,
  PRIMARY KEY (`qut_questiontypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `questionnaire` */

DROP TABLE IF EXISTS `questionnaire`;

CREATE TABLE `questionnaire` (
  `qur_questionnaireno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `qur_desc` char(50) NOT NULL,
  `qur_intro` text NOT NULL,
  `qur_thank_you` text NOT NULL,
  `qur_rating_1_desc` char(50) NOT NULL,
  `qur_rating_5_desc` char(50) NOT NULL,
  PRIMARY KEY (`qur_questionnaireno`)
) ENGINE=MyISAM AUTO_INCREMENT=92012 DEFAULT CHARSET=latin1;

/*Table structure for table `quotation` */

DROP TABLE IF EXISTS `quotation`;

CREATE TABLE `quotation` (
  `quotationID` int(11) NOT NULL DEFAULT '0',
  `ordheadID` int(11) NOT NULL DEFAULT '0',
  `versionNo` int(11) NOT NULL DEFAULT '0',
  `sentDateTime` datetime DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `salutation` char(200) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `emailSubject` char(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `fileExtension` char(5) CHARACTER SET utf8 DEFAULT NULL,
  `documentType` char(20) DEFAULT NULL,
  PRIMARY KEY (`quotationID`),
  UNIQUE KEY `ordhead_ix1` (`ordheadID`,`versionNo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ras` */

DROP TABLE IF EXISTS `ras`;

CREATE TABLE `ras` (
  `ID` double NOT NULL AUTO_INCREMENT,
  `Cust no` double DEFAULT NULL,
  `theCust` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `theDom` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `AdminName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `AdminPass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `cncadminname` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `cncadminpass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con1Type` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con1Number` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con1user` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con1pass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con2Type` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con2Number` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con2user` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Con2Pass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server1` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server1PRIVIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server1PUBIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server1ControlApp` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server1AppUser` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server1AppPass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server2` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server2PRIVIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server2PUBIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server2ControlApp` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server2AppUser` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server2AppPass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server3` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server3PRIVIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server3PUBIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server3ControlApp` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server3AppUser` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server3AppPass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server4` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server4PRIVIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server4PUBIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server4ControlApp` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server4AppUser` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server4AppPass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server5` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server5PRIVIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server5PUBIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server5ControlApp` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server5AppUser` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server5AppPass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server6` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server6PRIVIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server6PUBIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server6ControlApp` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server6AppUser` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Server6AppPass` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `GatewayLANIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `GatewayWANIP` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `WANSubnet` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `GatewayMake` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `GatewayUsername` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `GatewayPassword` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `Notes` longtext CHARACTER SET utf8,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=395 DEFAULT CHARSET=latin1;

/*Table structure for table `renbroadband` */

DROP TABLE IF EXISTS `renbroadband`;

CREATE TABLE `renbroadband` (
  `renBroadbandID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `renewalDate` date DEFAULT NULL,
  `customerName` char(50) DEFAULT NULL,
  `customerID` int(11) unsigned DEFAULT NULL,
  `itemID` int(11) unsigned DEFAULT NULL,
  `customerItemID` int(11) unsigned DEFAULT NULL,
  `months` int(10) DEFAULT NULL,
  `ordheadID` char(10) DEFAULT NULL,
  `broadbandServiceType` char(50) DEFAULT NULL,
  `broadbandServiceTypeID` int(11) DEFAULT NULL,
  `adslPhone` varchar(255) DEFAULT NULL,
  `fee` double DEFAULT NULL COMMENT 'Cost per month',
  `macCode` varchar(255) DEFAULT NULL,
  `batchNo` char(50) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `defaultGateway` char(50) DEFAULT NULL,
  `networkAddress` char(50) DEFAULT NULL,
  `subnetMask` char(50) DEFAULT NULL,
  `routerIPAddress` text COMMENT 'one or more IP addresses',
  `userName` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `etaDate` date DEFAULT NULL,
  `installationDate` date DEFAULT NULL,
  `costPerAnnum` double DEFAULT NULL COMMENT 'Sale price per annum',
  `salePricePerMonth` decimal(6,2) DEFAULT NULL COMMENT 'Sales Price Per Month',
  `costPricePerMonth` decimal(6,2) DEFAULT NULL,
  `ispID` varchar(255) DEFAULT NULL,
  `requiresChangesFlag` varchar(1) DEFAULT NULL,
  `dualBroadbandFlag` varchar(1) DEFAULT NULL,
  `dnsCompany` varchar(255) DEFAULT NULL,
  `ipCurrentNo` char(50) DEFAULT NULL,
  `mx` varchar(255) DEFAULT NULL,
  `secureServer` varchar(255) DEFAULT NULL,
  `vpns` varchar(255) DEFAULT NULL,
  `owa` varchar(255) DEFAULT NULL,
  `oma` varchar(255) DEFAULT NULL,
  `remotePortal` varchar(255) DEFAULT NULL,
  `smartHost` varchar(255) DEFAULT NULL,
  `preparationRecords` varchar(255) DEFAULT NULL,
  `assignedTo` varchar(255) DEFAULT NULL,
  `initialSpeedTest` varchar(255) DEFAULT NULL,
  `preMigrationNotes` longblob,
  `postMigrationNotes` longblob,
  `docsUpdatedAndChecksCompleted` varchar(255) DEFAULT NULL,
  `invoicePeriodMonths` int(4) DEFAULT NULL COMMENT 'Number of months between invoices',
  `totalInvoiceMonths` int(11) DEFAULT NULL COMMENT 'Number of months to add to install date to calculate next invoice due date',
  `declinedFlag` char(1) DEFAULT NULL,
  PRIMARY KEY (`renBroadbandID`)
) ENGINE=MyISAM AUTO_INCREMENT=331 DEFAULT CHARSET=utf8;

/*Table structure for table `rencontract` */

DROP TABLE IF EXISTS `rencontract`;

CREATE TABLE `rencontract` (
  `renContractID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customerItemID` int(11) DEFAULT NULL,
  `installationDate` date DEFAULT NULL,
  `invoicePeriodMonths` int(3) unsigned DEFAULT NULL,
  `declinedFlag` char(1) DEFAULT 'N',
  `totalInvoiceMonths` int(5) DEFAULT NULL,
  `notes` char(45) DEFAULT NULL,
  `hostingCompany` char(100) DEFAULT NULL,
  `password` char(50) DEFAULT NULL,
  `osPlatform` char(50) DEFAULT NULL,
  `domainNames` char(100) DEFAULT NULL,
  `controlPanelUrl` char(50) DEFAULT NULL,
  `ftpAddress` char(50) DEFAULT NULL,
  `ftpUsername` char(50) DEFAULT NULL,
  `wwwAddress` char(50) DEFAULT NULL,
  `websiteDeveloper` char(50) DEFAULT NULL,
  PRIMARY KEY (`renContractID`)
) ENGINE=MyISAM AUTO_INCREMENT=2226 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `rendomain` */

DROP TABLE IF EXISTS `rendomain`;

CREATE TABLE `rendomain` (
  `renDomainID` int(11) unsigned NOT NULL,
  `customerItemID` int(11) NOT NULL,
  `installationDate` date NOT NULL,
  `invoicePeriodMonths` int(3) unsigned DEFAULT NULL,
  `declinedFlag` char(1) DEFAULT 'N',
  `totalInvoiceMonths` int(5) DEFAULT NULL,
  `notes` char(45) DEFAULT NULL,
  `dateGenerated` date DEFAULT '0000-00-00',
  PRIMARY KEY (`renDomainID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `renewaltype` */

DROP TABLE IF EXISTS `renewaltype`;

CREATE TABLE `renewaltype` (
  `renewalTypeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` char(50) DEFAULT NULL,
  `allowSrLogging` char(1) DEFAULT 'Y',
  PRIMARY KEY (`renewalTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `renquotation` */

DROP TABLE IF EXISTS `renquotation`;

CREATE TABLE `renquotation` (
  `renQuotationID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customerItemID` int(11) DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `declinedFlag` char(1) DEFAULT 'N',
  `salePrice` decimal(6,2) DEFAULT NULL,
  `costPrice` decimal(6,2) DEFAULT NULL,
  `qty` int(3) DEFAULT NULL,
  `renQuotationTypeID` int(11) DEFAULT NULL,
  `comment` char(50) DEFAULT NULL,
  `grantNumber` char(50) DEFAULT NULL,
  `dateGenerated` date DEFAULT '0000-00-00' COMMENT 'This is the date that the quotation was generated from the renewal record',
  PRIMARY KEY (`renQuotationID`)
) ENGINE=MyISAM AUTO_INCREMENT=703 DEFAULT CHARSET=latin1;

/*Table structure for table `renquotationtype` */

DROP TABLE IF EXISTS `renquotationtype`;

CREATE TABLE `renquotationtype` (
  `renQuotationTypeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` char(50) NOT NULL,
  `addInstallationCharge` char(1) DEFAULT 'Y',
  PRIMARY KEY (`renQuotationTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*Table structure for table `rootcause` */

DROP TABLE IF EXISTS `rootcause`;

CREATE TABLE `rootcause` (
  `rtc_rootcauseno` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `rtc_desc` char(50) NOT NULL COMMENT 'Short description',
  `rtc_long_desc` char(100) NOT NULL COMMENT 'Long description',
  PRIMARY KEY (`rtc_rootcauseno`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

/*Table structure for table `salesrequest` */

DROP TABLE IF EXISTS `salesrequest`;

CREATE TABLE `salesrequest` (
  `srq_salesrequestno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `srq_ordno` int(11) unsigned NOT NULL,
  `srq_text` text,
  `srq_contractcuino` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`srq_salesrequestno`),
  KEY `srq_ordno` (`srq_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `sector` */

DROP TABLE IF EXISTS `sector`;

CREATE TABLE `sector` (
  `sec_sectorno` int(11) unsigned NOT NULL,
  `sec_desc` char(50) DEFAULT NULL,
  PRIMARY KEY (`sec_sectorno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `securityapp` */

DROP TABLE IF EXISTS `securityapp`;

CREATE TABLE `securityapp` (
  `securityAppID` int(11) NOT NULL DEFAULT '0',
  `description` char(50) NOT NULL DEFAULT '',
  `backupFlag` char(1) DEFAULT NULL,
  `emailAVFlag` char(1) DEFAULT NULL,
  `serverAVFlag` char(1) DEFAULT NULL,
  PRIMARY KEY (`securityAppID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `service request review_csv` */

DROP TABLE IF EXISTS `service request review_csv`;

CREATE TABLE `service request review_csv` (
  `F1` int(10) NOT NULL,
  `F2` text,
  PRIMARY KEY (`F1`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `servicedeskreport` */

DROP TABLE IF EXISTS `servicedeskreport`;

CREATE TABLE `servicedeskreport` (
  `sdr_servicedeskreportno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sdr_year_month` char(6) NOT NULL,
  `sdr_calls_received` int(3) DEFAULT NULL,
  `sdr_calls_overflowed` int(3) DEFAULT NULL,
  `sdr_calls_helpdesk` int(3) DEFAULT NULL,
  `sdr_calls_answer_seconds` int(3) DEFAULT NULL,
  `sdr_calls_abandoned` int(3) DEFAULT NULL,
  `sdr_meeting_results` text,
  `sdr_staff_issues` text,
  `sdr_staff_holiday_days` int(3) DEFAULT NULL,
  `sdr_staff_sick_days` int(3) DEFAULT NULL,
  `sdr_training` text,
  `sdr_any_other_business` text,
  PRIMARY KEY (`sdr_servicedeskreportno`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `sid` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `name` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `val` text CHARACTER SET utf8,
  `changed` varchar(14) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`name`,`sid`),
  KEY `changed` (`changed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `staffavailable` */

DROP TABLE IF EXISTS `staffavailable`;

CREATE TABLE `staffavailable` (
  `staffAvailableID` int(11) unsigned NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `am` decimal(2,1) NOT NULL DEFAULT '0.5' COMMENT 'available in the morning',
  `pm` decimal(2,1) NOT NULL DEFAULT '0.5' COMMENT 'available in the afternoon',
  PRIMARY KEY (`staffAvailableID`)
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 COMMENT='Indicates whether an engineer is avalable for a given date';

/*Table structure for table `standardtext` */

DROP TABLE IF EXISTS `standardtext`;

CREATE TABLE `standardtext` (
  `stt_standardtextno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stt_sort_order` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Order of appearance in drop-downs',
  `stt_desc` char(50) NOT NULL COMMENT 'Text for drop-downs etc',
  `stt_text` text NOT NULL COMMENT 'Content to be pasted',
  `stt_standardtexttypeno` int(11) NOT NULL COMMENT 'Type of text',
  PRIMARY KEY (`stt_standardtextno`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `standardtexttype` */

DROP TABLE IF EXISTS `standardtexttype`;

CREATE TABLE `standardtexttype` (
  `sty_standardtexttypeno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sty_desc` char(50) NOT NULL,
  PRIMARY KEY (`sty_standardtexttypeno`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Table structure for table `stockcat` */

DROP TABLE IF EXISTS `stockcat`;

CREATE TABLE `stockcat` (
  `stc_stockcat` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `stc_desc` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `stc_sal_nom` char(6) CHARACTER SET utf8 DEFAULT NULL,
  `stc_pur_cust` char(6) CHARACTER SET utf8 DEFAULT NULL,
  `stc_pur_sales_stk` char(6) CHARACTER SET utf8 DEFAULT NULL,
  `stc_pur_maint_stk` char(6) CHARACTER SET utf8 DEFAULT NULL,
  `stc_pur_ecc_asset` char(6) CHARACTER SET utf8 DEFAULT NULL,
  `stc_pur_ecc_oper` char(6) CHARACTER SET utf8 DEFAULT NULL,
  `stc_serial_req` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `stc_post_movement` char(1) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `ixstc_1` (`stc_stockcat`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `supplier` */

DROP TABLE IF EXISTS `supplier`;

CREATE TABLE `supplier` (
  `sup_suppno` int(11) NOT NULL DEFAULT '0',
  `sup_name` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `sup_contact` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `sup_add1` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `sup_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `sup_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `sup_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `sup_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `sup_phone` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `sup_fax` char(20) CHARACTER SET utf8 DEFAULT NULL,
  `sup_web_site_url` char(100) CHARACTER SET utf8 DEFAULT NULL,
  `sup_pay_method` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `sup_credit_limit` decimal(10,2) DEFAULT NULL,
  `sup_approval` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `sup_scopeno` smallint(6) DEFAULT NULL,
  `sup_payno` smallint(6) DEFAULT NULL,
  `sup_date_quest` date DEFAULT NULL,
  `sup_contno` int(11) DEFAULT NULL,
  `sup_cnc_accno` char(20) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`sup_suppno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `userext` */

DROP TABLE IF EXISTS `userext`;

CREATE TABLE `userext` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `signatureFilename` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `jobTitle` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `firstName` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `lastName` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `activeFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `vat` */

DROP TABLE IF EXISTS `vat`;

CREATE TABLE `vat` (
  `vat_rate_t0` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t1` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t2` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t3` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t4` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t5` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t6` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t7` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t8` decimal(4,2) NOT NULL DEFAULT '0.00',
  `vat_rate_t9` decimal(4,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
