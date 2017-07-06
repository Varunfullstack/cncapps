/*
SQLyog Ultimate v11.01 (64 bit)
MySQL - 5.0.67-community-nt : Database - cncp1
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
  `activityCategoryID` int(10) unsigned NOT NULL auto_increment,
  `description` char(50) character set utf8 NOT NULL,
  `allowSelection` char(1) NOT NULL default 'Y',
  PRIMARY KEY  (`activityCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;

/*Table structure for table `address` */

DROP TABLE IF EXISTS `address`;

CREATE TABLE `address` (
  `add_custno` int(11) NOT NULL default '0',
  `add_add1` char(35) character set utf8 default NULL,
  `add_add2` char(35) character set utf8 default NULL,
  `add_add3` char(35) character set utf8 default NULL,
  `add_town` char(25) character set utf8 default NULL,
  `add_county` char(25) character set utf8 default NULL,
  `add_postcode` char(15) character set utf8 default NULL,
  `add_inv_contno` int(11) default NULL,
  `add_del_contno` int(11) default NULL,
  `add_debtor_code` char(10) character set utf8 default NULL,
  `add_siteno` smallint(6) NOT NULL default '0',
  `add_sage_ref` char(6) character set utf8 default NULL,
  `add_phone` char(40) character set utf8 default NULL,
  `add_max_travel_hours` decimal(5,2) NOT NULL default '1.50',
  `add_active_flag` char(1) default 'Y',
  PRIMARY KEY  (`add_custno`,`add_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `answer` */

DROP TABLE IF EXISTS `answer`;

CREATE TABLE `answer` (
  `ans_answerno` int(11) unsigned NOT NULL auto_increment,
  `ans_questionno` int(11) unsigned NOT NULL,
  `ans_problemno` int(11) NOT NULL,
  `ans_answer` text NOT NULL,
  `ans_name` char(100) NOT NULL,
  `ans_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ans_answerno`)
) ENGINE=MyISAM AUTO_INCREMENT=9270 DEFAULT CHARSET=latin1;

/*Table structure for table `answertype` */

DROP TABLE IF EXISTS `answertype`;

CREATE TABLE `answertype` (
  `ant_answertypeno` int(11) unsigned NOT NULL auto_increment,
  `ant_desc` char(50) NOT NULL,
  PRIMARY KEY  (`ant_answertypeno`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Table structure for table `arecord` */

DROP TABLE IF EXISTS `arecord`;

CREATE TABLE `arecord` (
  `are_arecordno` int(11) unsigned NOT NULL,
  `are_custitemno` int(11) unsigned NOT NULL COMMENT 'The domain record',
  `are_type` char(60) NOT NULL,
  `are_name` char(60) NOT NULL,
  `are_destination_ip` char(100) NOT NULL,
  `are_function` char(100) NOT NULL,
  PRIMARY KEY  (`are_arecordno`),
  KEY `are_custitem` (`are_custitemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `audit_trail` */

DROP TABLE IF EXISTS `audit_trail`;

CREATE TABLE `audit_trail` (
  `auditTrailId` int(11) unsigned NOT NULL auto_increment,
  `tableName` char(50) NOT NULL,
  `primaryKey` int(11) NOT NULL,
  `colName` char(50) NOT NULL,
  `oldValue` longtext,
  `newValue` longtext,
  `userID` int(11) default NULL,
  `modifyDate` datetime default NULL,
  PRIMARY KEY  (`auditTrailId`)
) ENGINE=MyISAM AUTO_INCREMENT=638 DEFAULT CHARSET=latin1;

/*Table structure for table `automated_request` */

DROP TABLE IF EXISTS `automated_request`;

CREATE TABLE `automated_request` (
  `automatedRequestID` int(11) unsigned NOT NULL auto_increment COMMENT 'Primary Key (leave blank when INSERTing)',
  `customerID` int(11) unsigned default NULL COMMENT 'Optional',
  `serviceRequestID` int(11) default NULL,
  `postcode` char(10) default NULL COMMENT 'Optional',
  `senderEmailAddress` char(100) NOT NULL COMMENT 'Required',
  `textBody` longtext NOT NULL COMMENT 'Required',
  `htmlBody` longtext COMMENT 'Optional (if exists then used instead of textBody)',
  `priority` enum('1','2','3','4','5') default '5' COMMENT '1 - 5',
  `sendEmail` enum('A','N','S') default 'A' COMMENT 'Always, Never, Skip work commenced email',
  `serverGuardFlag` enum('Y','N') default 'N' COMMENT 'Y/N',
  `importedFlag` enum('Y','N') default 'N' COMMENT 'Was this row imported successfully? Y/N',
  `attachment` longblob COMMENT 'Optional attachment',
  `attachmentFilename` char(50) default NULL COMMENT 'Attachment name (required for attachment)',
  `attachmentMimeType` char(30) default NULL COMMENT 'Attachment MIME type. e.g. application/pdf',
  `attachmentFileLengthBytes` int(11) default NULL COMMENT 'Length in bytes',
  `rootCauseID` int(11) default NULL,
  `contractCustomerItemID` int(11) default NULL,
  `activityCategoryID` int(11) default NULL,
  `monitorName` char(100) default NULL COMMENT 'name of the monitor, to allow tracking of the failure and success',
  `monitorAgentName` char(100) default NULL COMMENT 'This is the computer that the monitor has failed against.',
  `monitorStatus` char(1) default NULL COMMENT 'Success or Failure',
  `importErrorFound` enum('Y','N') default 'N' COMMENT 'Has the import process already found this error',
  PRIMARY KEY  (`automatedRequestID`),
  KEY `senderEmailAddress` (`senderEmailAddress`)
) ENGINE=MyISAM AUTO_INCREMENT=55094 DEFAULT CHARSET=latin1;

/*Table structure for table `broadbandservicetype` */

DROP TABLE IF EXISTS `broadbandservicetype`;

CREATE TABLE `broadbandservicetype` (
  `broadbandServiceTypeID` int(11) unsigned NOT NULL,
  `description` char(50) NOT NULL,
  PRIMARY KEY  (`broadbandServiceTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `callactengineer` */

DROP TABLE IF EXISTS `callactengineer`;

CREATE TABLE `callactengineer` (
  `cae_callactengno` int(11) NOT NULL default '0',
  `cae_callactivityno` int(11) NOT NULL default '0',
  `cae_item` smallint(6) NOT NULL default '0',
  `cae_consno` int(11) NOT NULL default '0',
  `cae_expn_exp_flag` char(1) character set utf8 default NULL,
  `cae_ot_exp_flag` char(1) character set utf8 default NULL,
  UNIQUE KEY `caeix_1` (`cae_callactengno`),
  KEY `caeix_2` (`cae_consno`,`cae_callactivityno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `callactivity` */

DROP TABLE IF EXISTS `callactivity`;

CREATE TABLE `callactivity` (
  `caa_callactivityno` int(11) NOT NULL default '0',
  `caa_siteno` int(11) default NULL,
  `caa_contno` int(11) NOT NULL default '0',
  `caa_item` smallint(6) default NULL,
  `caa_callacttypeno` int(11) NOT NULL default '0',
  `activityCategoryID` int(10) unsigned default NULL,
  `projectID` int(11) NOT NULL default '0',
  `caa_problemno` int(11) unsigned NOT NULL default '0',
  `caa_date` date NOT NULL default '0000-00-00',
  `caa_starttime` varchar(5) character set utf8 NOT NULL default '',
  `caa_endtime` varchar(5) character set utf8 default NULL,
  `caa_status` char(1) character set utf8 default NULL,
  `caa_expexport_flag` char(1) character set utf8 default NULL,
  `reason` text character set utf8,
  `internalNotes` mediumtext,
  `curValue` decimal(6,2) NOT NULL default '0.00',
  `statementYearMonth` varchar(7) character set utf8 default NULL,
  `caa_custno` int(11) unsigned NOT NULL default '0',
  `caa_cuino` int(11) unsigned NOT NULL default '0',
  `caa_under_contract` char(1) character set utf8 NOT NULL default '',
  `caa_authorised` char(1) character set utf8 NOT NULL default '',
  `caa_consno` int(6) unsigned NOT NULL default '0',
  `caa_ot_exp_flag` char(1) character set utf8 NOT NULL default 'N',
  `caa_completed_consno` int(6) unsigned NOT NULL default '0',
  `caa_completed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `caa_serverguard` char(1) character set utf8 NOT NULL default 'N',
  `caa_parent_callactivityno` int(11) default NULL,
  `caa_awaiting_customer_response_flag` char(1) default 'N' COMMENT 'If so then exclude this from time duration calculations',
  `caa_class` char(1) default NULL COMMENT '[W]orking, [I]nformational, [O]ther',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `caa_logging_error_flag` char(1) default 'N' COMMENT 'Was there an error when logging this activity',
  UNIQUE KEY `ix354_1` (`caa_callactivityno`),
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
  `caa_callactivityno` int(11) NOT NULL default '0',
  `caa_siteno` int(11) default NULL,
  `caa_contno` int(11) NOT NULL default '0',
  `caa_item` smallint(6) default NULL,
  `caa_callacttypeno` int(11) NOT NULL default '0',
  `activityCategoryID` int(10) unsigned default NULL,
  `projectID` int(11) NOT NULL default '0',
  `caa_problemno` int(11) unsigned NOT NULL default '0',
  `caa_date` date NOT NULL default '0000-00-00',
  `caa_starttime` varchar(5) character set utf8 NOT NULL default '',
  `caa_endtime` varchar(5) character set utf8 default NULL,
  `caa_status` char(1) character set utf8 default NULL,
  `caa_expexport_flag` char(1) character set utf8 default NULL,
  `reason` text character set utf8,
  `internalNotes` mediumtext,
  `curValue` decimal(6,2) NOT NULL default '0.00',
  `statementYearMonth` varchar(7) character set utf8 default NULL,
  `caa_custno` int(11) unsigned NOT NULL default '0',
  `caa_cuino` int(11) unsigned NOT NULL default '0',
  `caa_contract_cuino` int(11) unsigned default NULL,
  `caa_under_contract` char(1) character set utf8 NOT NULL default '',
  `caa_authorised` char(1) character set utf8 NOT NULL default '',
  `caa_consno` int(6) unsigned NOT NULL default '0',
  `caa_ot_exp_flag` char(1) character set utf8 NOT NULL default '',
  `caa_completed_consno` int(6) unsigned NOT NULL default '0',
  `caa_completed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `caa_serverguard` char(1) character set utf8 NOT NULL default 'N',
  `caa_parent_callactivityno` int(11) default NULL,
  `caa_awaiting_customer_response_flag` char(1) default 'N' COMMENT 'If so then exclude this from time duration calculations',
  `caa_class` char(1) default NULL COMMENT '[W]orking, [I]nformational, [O]ther',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `caa_logging_error_flag` char(1) default 'N' COMMENT 'Was there an error when logging this activity'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `callacttype` */

DROP TABLE IF EXISTS `callacttype`;

CREATE TABLE `callacttype` (
  `cat_callacttypeno` int(11) NOT NULL default '0',
  `cat_desc` char(60) character set utf8 NOT NULL default '',
  `cat_ooh_multiplier` decimal(5,2) default NULL,
  `cat_itemno` int(11) default NULL,
  `cat_min_hours` decimal(5,2) default NULL,
  `cat_max_hours` decimal(5,2) default NULL,
  `cat_req_check_flag` char(1) character set utf8 default NULL,
  `cat_allow_exp_flag` char(1) character set utf8 default NULL,
  `cat_problem_flag` char(1) character set utf8 default NULL,
  `cat_action_flag` char(1) character set utf8 default NULL,
  `cat_resolve_flag` char(1) character set utf8 default NULL,
  `cat_r_problem_flag` char(1) character set utf8 default NULL,
  `cat_r_action_flag` char(1) character set utf8 default NULL,
  `cat_r_resolve_flag` char(1) character set utf8 default NULL,
  `allowSCRFlag` char(1) character set utf8 NOT NULL default 'N',
  `curValueFlag` char(1) character set utf8 NOT NULL default 'N',
  `customerEmailFlag` char(1) character set utf8 NOT NULL default 'Y',
  `travelFlag` char(1) character set utf8 NOT NULL default 'N',
  `activeFlag` char(1) character set utf8 NOT NULL default 'Y' COMMENT 'Is this activity type in use?',
  `showNotChargeableFlag` char(1) NOT NULL default 'Y' COMMENT 'On customer activity emails, show if not chargeable',
  `engineerOvertimeFlag` char(1) NOT NULL default 'Y',
  `cat_on_site_flag` char(1) NOT NULL default 'N' COMMENT 'Is this on site',
  UNIQUE KEY `ix358_1` (`cat_callacttypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `calldocument` */

DROP TABLE IF EXISTS `calldocument`;

CREATE TABLE `calldocument` (
  `callDocumentID` int(11) NOT NULL default '0',
  `problemID` int(11) NOT NULL default '0',
  `callActivityID` int(11) NOT NULL default '0',
  `description` varchar(50) character set utf8 NOT NULL default '',
  `filename` varchar(100) character set utf8 NOT NULL default '',
  `file` longblob NOT NULL,
  `fileMIMEType` varchar(100) character set utf8 NOT NULL default '',
  `fileLength` int(11) NOT NULL default '0',
  `createDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `createUserID` int(11) NOT NULL default '0',
  UNIQUE KEY `XPKDocument` (`callDocumentID`),
  KEY `problemID` (`problemID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `consultant` */

DROP TABLE IF EXISTS `consultant`;

CREATE TABLE `consultant` (
  `cns_consno` int(11) NOT NULL default '0',
  `cns_manager` smallint(6) default NULL,
  `cns_name` varchar(35) character set utf8 NOT NULL default '',
  `cns_salutation` varchar(35) character set utf8 default NULL,
  `cns_add1` varchar(35) character set utf8 default NULL,
  `cns_add2` varchar(35) character set utf8 default NULL,
  `cns_add3` varchar(35) character set utf8 default NULL,
  `cns_town` varchar(25) character set utf8 default NULL,
  `cns_county` varchar(25) character set utf8 default NULL,
  `cns_postcode` varchar(15) character set utf8 default NULL,
  `cns_logname` varchar(20) character set utf8 default NULL,
  `cns_employee_no` varchar(20) character set utf8 default NULL,
  `cns_petrol_rate` decimal(5,2) default NULL,
  `cns_hourly_pay_rate` decimal(5,2) default '25.00',
  `cns_password` varchar(50) character set utf8 default NULL,
  `cns_perms` varchar(100) character set utf8 default NULL,
  `signatureFilename` varchar(50) character set utf8 default NULL,
  `jobTitle` varchar(50) character set utf8 default NULL,
  `firstName` varchar(50) character set utf8 default NULL,
  `lastName` varchar(50) character set utf8 default NULL,
  `activeFlag` char(1) character set utf8 default NULL,
  `weekdayOvertimeFlag` char(1) default NULL,
  `customerID` int(11) default NULL,
  `cns_helpdesk_flag` char(1) default NULL,
  UNIQUE KEY `ixcns_1` (`cns_consno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `contact` */

DROP TABLE IF EXISTS `contact`;

CREATE TABLE `contact` (
  `con_contno` int(11) NOT NULL default '0',
  `con_siteno` smallint(6) default NULL,
  `con_custno` int(11) default NULL,
  `con_suppno` int(11) default NULL,
  `con_title` char(10) character set utf8 default NULL,
  `con_position` char(50) default NULL,
  `con_last_name` char(35) character set utf8 NOT NULL default '',
  `con_first_name` char(25) character set utf8 default NULL,
  `con_email` char(60) character set utf8 default NULL,
  `con_phone` char(20) character set utf8 default NULL,
  `con_mobile_phone` char(20) character set utf8 default NULL,
  `con_fax` char(20) character set utf8 default NULL,
  `con_mailshot` char(1) character set utf8 default NULL,
  `con_accounts_flag` char(1) character set utf8 NOT NULL default '',
  `con_statement_flag` char(1) character set utf8 NOT NULL default '',
  `con_discontinued` char(1) character set utf8 default NULL,
  `con_mailflag1` char(1) character set utf8 default NULL,
  `con_mailflag2` char(1) character set utf8 default NULL,
  `con_mailflag3` char(1) character set utf8 default NULL,
  `con_mailflag4` char(1) character set utf8 default NULL,
  `con_mailflag5` char(1) character set utf8 default NULL,
  `con_mailflag6` char(1) character set utf8 default NULL,
  `con_mailflag7` char(1) character set utf8 default NULL,
  `con_mailflag8` char(1) character set utf8 default NULL,
  `con_mailflag9` char(1) character set utf8 default NULL,
  `con_mailflag10` char(1) character set utf8 default NULL,
  `con_notes` char(200) default NULL,
  `con_portal_password` char(10) default NULL,
  `con_failed_login_count` int(3) default NULL,
  `con_work_started_email_flag` char(1) default 'Y',
  `con_auto_close_email_flag` char(1) default 'Y',
  UNIQUE KEY `ix_con2` (`con_contno`),
  KEY `ixcon_1` (`con_custno`,`con_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `contact_bu` */

DROP TABLE IF EXISTS `contact_bu`;

CREATE TABLE `contact_bu` (
  `con_contno` int(11) NOT NULL default '0',
  `con_siteno` smallint(6) default NULL,
  `con_custno` int(11) default NULL,
  `con_suppno` int(11) default NULL,
  `con_title` char(10) character set utf8 default NULL,
  `con_position` char(50) default NULL,
  `con_last_name` char(35) character set utf8 NOT NULL default '',
  `con_first_name` char(25) character set utf8 default NULL,
  `con_email` char(60) character set utf8 default NULL,
  `con_phone` char(20) character set utf8 default NULL,
  `con_mobile_phone` char(20) character set utf8 default NULL,
  `con_fax` char(20) character set utf8 default NULL,
  `con_mailshot` char(1) character set utf8 default NULL,
  `con_accounts_flag` char(1) character set utf8 NOT NULL default '',
  `con_statement_flag` char(1) character set utf8 NOT NULL default '',
  `con_discontinued` char(1) character set utf8 default NULL,
  `con_mailflag1` char(1) character set utf8 default NULL,
  `con_mailflag2` char(1) character set utf8 default NULL,
  `con_mailflag3` char(1) character set utf8 default NULL,
  `con_mailflag4` char(1) character set utf8 default NULL,
  `con_mailflag5` char(1) character set utf8 default NULL,
  `con_mailflag6` char(1) character set utf8 default NULL,
  `con_mailflag7` char(1) character set utf8 default NULL,
  `con_mailflag8` char(1) character set utf8 default NULL,
  `con_mailflag9` char(1) character set utf8 default NULL,
  `con_mailflag10` char(1) character set utf8 default NULL,
  `con_notes` char(200) default NULL,
  `con_portal_password` char(10) default NULL,
  `con_failed_login_count` int(2) default NULL,
  UNIQUE KEY `ix_con2` (`con_contno`),
  KEY `ixcon_1` (`con_custno`,`con_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `contract` */

DROP TABLE IF EXISTS `contract`;

CREATE TABLE `contract` (
  `cnt_contno` int(11) NOT NULL default '0',
  `cnt_desc` char(60) character set utf8 NOT NULL default '',
  `cnt_years` smallint(6) default NULL,
  `cnt_manno` int(11) default NULL,
  UNIQUE KEY `ixcnt_1` (`cnt_contno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `custitem` */

DROP TABLE IF EXISTS `custitem`;

CREATE TABLE `custitem` (
  `cui_cuino` int(11) unsigned NOT NULL auto_increment,
  `cui_custno` int(11) NOT NULL default '0',
  `cui_siteno` smallint(6) NOT NULL default '0',
  `cui_itemno` int(11) NOT NULL default '0',
  `cui_man_contno` smallint(6) default NULL,
  `cui_contract_cuino` int(11) default NULL COMMENT 'Contract',
  `cui_serial` varchar(20) character set utf8 default NULL COMMENT 'Serial No',
  `cui_cust_ref` varchar(45) character set utf8 default NULL COMMENT 'Server name',
  `cui_ordno` int(11) default NULL,
  `cui_sale_price` decimal(12,2) default NULL,
  `cui_porno` int(11) default NULL,
  `cui_pord_price` decimal(12,2) default NULL,
  `cui_cost_price` decimal(12,2) default NULL,
  `cui_users` smallint(6) default NULL COMMENT 'Number of users',
  `cui_ord_date` date default NULL,
  `cui_expiry_date` date default NULL,
  `curGSCBalance` decimal(8,2) NOT NULL default '0.00',
  `renewalStatus` char(1) character set utf8 default 'N' COMMENT 'Is this required now we have the "live" flag',
  `renewalOrdheadID` int(11) default '0',
  `itemNotes` text character set utf8,
  `cui_prepay_balance` decimal(6,2) default NULL COMMENT 'On customer record at present',
  `cui_sales_order_status` enum('','Q','I','C') default NULL COMMENT 'Which type of sales order to create when billing',
  `renewalDate` date default NULL,
  `customerName` char(50) default NULL,
  `customerID` int(11) unsigned default NULL,
  `itemID` int(11) unsigned default NULL,
  `customerItemID` int(11) unsigned default NULL,
  `months` int(10) default NULL,
  `ordheadID` char(10) default NULL,
  `broadbandServiceType` char(50) default NULL,
  `broadbandServiceTypeID` int(11) default NULL,
  `adslPhone` varchar(255) default NULL,
  `fee` double default NULL COMMENT 'Cost per month',
  `macCode` varchar(255) default NULL,
  `batchNo` char(50) default NULL,
  `reference` varchar(255) default NULL,
  `defaultGateway` char(50) default NULL,
  `networkAddress` char(50) default NULL,
  `subnetMask` char(50) default NULL,
  `routerIPAddress` text COMMENT 'one or more IP addresses',
  `userName` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `etaDate` date default NULL,
  `installationDate` date default NULL,
  `costPerAnnum` double default NULL COMMENT 'Sale price per annum',
  `salePricePerMonth` decimal(6,2) default NULL COMMENT 'Sales Price Per Month',
  `costPricePerMonth` decimal(6,2) default NULL,
  `ispID` varchar(255) default NULL,
  `requiresChangesFlag` varchar(1) default NULL,
  `dualBroadbandFlag` varchar(1) default NULL,
  `dnsCompany` varchar(255) default NULL,
  `ipCurrentNo` char(50) default NULL,
  `mx` varchar(255) default NULL,
  `secureServer` varchar(255) default NULL,
  `vpns` varchar(255) default NULL,
  `owa` varchar(255) default NULL,
  `oma` varchar(255) default NULL,
  `remotePortal` varchar(255) default NULL,
  `smartHost` varchar(255) default NULL,
  `preparationRecords` varchar(255) default NULL,
  `assignedTo` varchar(255) default NULL,
  `initialSpeedTest` varchar(255) default NULL,
  `preMigrationNotes` longblob,
  `postMigrationNotes` longblob,
  `docsUpdatedAndChecksCompleted` varchar(255) default NULL,
  `invoicePeriodMonths` int(4) default NULL COMMENT 'Number of months between invoices',
  `totalInvoiceMonths` int(11) default NULL COMMENT 'Number of months to add to install date to calculate next invoice due date',
  `declinedFlag` char(1) default NULL,
  `hostingCompany` char(100) default NULL,
  `osPlatform` char(50) default NULL,
  `domainNames` char(100) default NULL,
  `controlPanelUrl` char(50) default NULL,
  `ftpAddress` char(50) default NULL,
  `ftpUsername` char(50) default NULL,
  `wwwAddress` char(50) default NULL,
  `websiteDeveloper` char(50) default NULL,
  `dateGenerated` date default '0000-00-00',
  `startDate` date default NULL,
  `salePrice` decimal(6,2) default NULL,
  `costPrice` decimal(6,2) default NULL,
  `qty` int(3) default NULL,
  `renQuotationTypeID` int(11) default NULL,
  `comment` char(50) default NULL,
  `grantNumber` char(50) default NULL,
  `notes` text,
  `cui_consno` smallint(6) default NULL,
  `cui_ctactno` smallint(6) default NULL,
  `cui_desp_date` date default NULL,
  `cui_pord_date` date default NULL,
  `cui_ref_cust` varchar(35) character set utf8 default NULL,
  `cui_sla_response_hours` int(3) default NULL,
  `hostingUserName` char(50) default NULL,
  `cui_internal_notes` text,
  UNIQUE KEY `ixcui_1` (`cui_cuino`),
  KEY `ix_cui2` (`cui_custno`,`cui_siteno`,`cui_cuino`),
  KEY `renewalOrdheadID` (`renewalOrdheadID`),
  KEY `cui_itemno` (`cui_itemno`)
) ENGINE=MyISAM AUTO_INCREMENT=39303 DEFAULT CHARSET=latin1;

/*Table structure for table `customer` */

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `cus_custno` int(11) NOT NULL default '0',
  `cus_name` varchar(35) character set utf8 default NULL,
  `cus_reg_no` varchar(10) character set utf8 default NULL,
  `cus_inv_siteno` smallint(6) default NULL,
  `cus_del_siteno` smallint(6) default NULL,
  `cus_mailshot` varchar(2) character set utf8 default NULL,
  `cus_create_date` date default NULL,
  `cus_referred` char(1) character set utf8 default NULL,
  `cus_pcx` char(1) character set utf8 default NULL,
  `cus_ctypeno` int(11) default NULL,
  `cus_prospect` char(1) character set utf8 default NULL,
  `cus_others_email_main_flag` char(1) default 'Y',
  `cus_work_started_email_main_flag` char(1) default 'Y' COMMENT 'Should work stared emails be sent from service system?',
  `cus_auto_close_email_main_flag` char(1) default 'Y' COMMENT 'Email main contact?',
  `cus_became_customer_date` date NOT NULL default '0000-00-00' COMMENT 'Date became a customer',
  `cus_dropped_customer_date` date NOT NULL default '0000-00-00' COMMENT 'Date dropped as customer',
  `cus_leadstatusno` int(11) unsigned default NULL,
  `gscTopUpAmount` decimal(8,2) NOT NULL default '0.00',
  `modifyDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `noOfPCs` varchar(10) character set utf8 NOT NULL default '0',
  `noOfServers` smallint(3) NOT NULL default '0',
  `noOfSites` tinyint(4) NOT NULL default '1',
  `comments` text,
  `reviewDate` date default NULL,
  `reviewTime` char(5) default NULL,
  `reviewAction` char(100) default NULL,
  `reviewUserID` int(11) default NULL,
  `modifyUserID` int(11) default NULL,
  `cus_sectorno` int(11) unsigned default NULL,
  `cus_tech_notes` char(100) default NULL COMMENT 'Notes to appear on all request screens',
  `cus_special_attention_flag` char(1) default 'N',
  `cus_special_attention_end_date` date default NULL,
  `cus_support_24_hour_flag` char(1) default 'N',
  `cus_sla_p1` int(5) default NULL,
  `cus_sla_p2` int(5) default NULL,
  `cus_sla_p3` int(5) default NULL,
  `cus_sla_p4` int(5) default NULL,
  `cus_sla_p5` int(5) default NULL,
  UNIQUE KEY `ixcus_1` (`cus_custno`),
  KEY `cus_sectorno` (`cus_sectorno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `customer_password` */

DROP TABLE IF EXISTS `customer_password`;

CREATE TABLE `customer_password` (
  `cpw_customerpasswordno` int(11) unsigned NOT NULL auto_increment,
  `cpw_custno` int(11) unsigned NOT NULL,
  `cpw_username` char(50) NOT NULL,
  `cpw_service` char(100) NOT NULL,
  `cpw_password` char(20) NOT NULL,
  PRIMARY KEY  (`cpw_customerpasswordno`),
  KEY `cpw_custno` (`cpw_custno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `customeritemdocument` */

DROP TABLE IF EXISTS `customeritemdocument`;

CREATE TABLE `customeritemdocument` (
  `customerItemDocumentID` int(11) NOT NULL default '0',
  `customerItemID` int(11) NOT NULL default '0',
  `description` varchar(50) character set utf8 NOT NULL default '',
  `filename` varchar(100) character set utf8 NOT NULL default '',
  `file` longblob NOT NULL,
  `fileMIMEType` varchar(100) character set utf8 NOT NULL default '',
  `fileLength` int(11) NOT NULL default '0',
  `createDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `createUserID` int(11) NOT NULL default '0',
  UNIQUE KEY `XPKCustomerItemDocument` (`customerItemDocumentID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `customernote` */

DROP TABLE IF EXISTS `customernote`;

CREATE TABLE `customernote` (
  `cno_customernoteno` int(11) unsigned NOT NULL auto_increment,
  `cno_custno` int(11) NOT NULL,
  `cno_created` datetime default NULL,
  `cno_modified` datetime default NULL,
  `cno_modified_consno` int(11) unsigned NOT NULL,
  `cno_details` text,
  `cno_created_consno` int(11) unsigned NOT NULL,
  `cno_ordno` int(11) unsigned default NULL COMMENT 'Sales Order',
  PRIMARY KEY  (`cno_customernoteno`)
) ENGINE=MyISAM AUTO_INCREMENT=13437 DEFAULT CHARSET=latin1;

/*Table structure for table `customerproblem` */

DROP TABLE IF EXISTS `customerproblem`;

CREATE TABLE `customerproblem` (
  `cpr_customerproblemno` int(11) unsigned NOT NULL auto_increment,
  `cpr_custno` int(11) unsigned default NULL,
  `cpr_siteno` int(3) default '99',
  `cpr_contno` int(11) unsigned NOT NULL,
  `cpr_date` datetime default NULL,
  `cpr_priority` int(11) default NULL,
  `cpr_reason` text,
  `cpr_internal_notes` text,
  `cpr_serverguard_flag` char(1) default 'N',
  `cpr_source` enum('C','S') default 'S',
  `cpr_problemno` int(11) unsigned NOT NULL,
  `cpr_update_existing_request` tinyint(1) default '1',
  `cpr_send_email` char(1) default NULL,
  PRIMARY KEY  (`cpr_customerproblemno`)
) ENGINE=MyISAM AUTO_INCREMENT=19106 DEFAULT CHARSET=latin1;

/*Table structure for table `custype` */

DROP TABLE IF EXISTS `custype`;

CREATE TABLE `custype` (
  `cty_ctypeno` smallint(6) NOT NULL default '0',
  `cty_desc` char(40) character set utf8 NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `date_xref` */

DROP TABLE IF EXISTS `date_xref`;

CREATE TABLE `date_xref` (
  `date_field` date NOT NULL,
  `is_bank_holiday` char(1) default 'N',
  PRIMARY KEY  (`date_field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `db_sequence` */

DROP TABLE IF EXISTS `db_sequence`;

CREATE TABLE `db_sequence` (
  `seq_name` varchar(127) character set utf8 NOT NULL default '',
  `nextid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`seq_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `delivery` */

DROP TABLE IF EXISTS `delivery`;

CREATE TABLE `delivery` (
  `del_delno` int(11) NOT NULL default '0',
  `del_desc` char(35) character set utf8 default NULL,
  `del_send_note` char(1) character set utf8 default NULL,
  UNIQUE KEY `ix141_1` (`del_delno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `deliverynote` */

DROP TABLE IF EXISTS `deliverynote`;

CREATE TABLE `deliverynote` (
  `deliveryNoteID` int(11) NOT NULL default '0',
  `ordheadID` int(11) NOT NULL default '0',
  `noteNo` tinyint(4) NOT NULL default '0',
  `dateTime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`deliveryNoteID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `deshead` */

DROP TABLE IF EXISTS `deshead`;

CREATE TABLE `deshead` (
  `deh_desno` int(11) NOT NULL default '0',
  `deh_ordno` smallint(6) NOT NULL default '0',
  `deh_custno` smallint(6) NOT NULL default '0',
  `deh_invno` int(11) default NULL,
  `deh_ref_cust` char(23) character set utf8 default NULL,
  `deh_ref_ecc` char(35) character set utf8 default NULL,
  `deh_method` char(12) character set utf8 default NULL,
  `deh_date` date default NULL,
  UNIQUE KEY `ix130_1` (`deh_desno`),
  KEY `ix132_5` (`deh_ref_cust`),
  KEY `ix135_6` (`deh_ref_ecc`),
  KEY `ixdeh_1` (`deh_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `desline` */

DROP TABLE IF EXISTS `desline`;

CREATE TABLE `desline` (
  `del_desno` smallint(6) NOT NULL default '0',
  `del_line_no` smallint(6) NOT NULL default '0',
  `del_ord_line_no` smallint(6) NOT NULL default '0',
  `del_qty` decimal(7,2) default NULL,
  KEY `ix131_1` (`del_desno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `domain_import` */

DROP TABLE IF EXISTS `domain_import`;

CREATE TABLE `domain_import` (
  `domain` char(100) default NULL,
  `expiryDate` date default NULL,
  `customerID` int(11) default NULL,
  `invoicePeriodMonths` int(3) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `expense` */

DROP TABLE IF EXISTS `expense`;

CREATE TABLE `expense` (
  `exp_expenseno` int(11) NOT NULL default '0',
  `exp_callactivityno` int(11) NOT NULL default '0',
  `exp_expensetypeno` int(11) NOT NULL default '0',
  `exp_mileage` int(11) default NULL,
  `exp_value` decimal(8,2) NOT NULL default '0.00',
  `exp_vat_flag` char(1) character set utf8 NOT NULL default '',
  `exp_exported_flag` char(1) default 'N' COMMENT 'indicates whether this expense has been exported to the file',
  UNIQUE KEY `ix402_1` (`exp_expenseno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `expensetype` */

DROP TABLE IF EXISTS `expensetype`;

CREATE TABLE `expensetype` (
  `ext_expensetypeno` int(11) NOT NULL default '0',
  `ext_desc` char(80) character set utf8 NOT NULL default '',
  `ext_mileage_flag` char(1) character set utf8 NOT NULL default '',
  `ext_vat_flag` char(1) character set utf8 default NULL,
  UNIQUE KEY `ix401_1` (`ext_expensetypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `further_action` */

DROP TABLE IF EXISTS `further_action`;

CREATE TABLE `further_action` (
  `furtherActionID` int(6) unsigned NOT NULL default '0',
  `description` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `emailAddress` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `requireDate` char(1) character set utf8 collate utf8_unicode_ci NOT NULL default 'Y',
  `emailBody` text character set utf8 collate utf8_unicode_ci,
  PRIMARY KEY  (`furtherActionID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `future_action` */

DROP TABLE IF EXISTS `future_action`;

CREATE TABLE `future_action` (
  `futureActionID` int(11) unsigned NOT NULL default '0',
  `furtherActionID` int(11) NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `callActivityID` varchar(11) character set utf8 NOT NULL default '',
  `engineerName` char(50) default NULL,
  `dateCreated` date default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `headert` */

DROP TABLE IF EXISTS `headert`;

CREATE TABLE `headert` (
  `headerID` tinyint(4) NOT NULL default '0',
  `hed_name` char(35) character set utf8 NOT NULL default '',
  `hed_add1` char(35) character set utf8 default NULL,
  `hed_add2` char(35) character set utf8 default NULL,
  `hed_add3` char(35) character set utf8 default NULL,
  `hed_town` char(30) character set utf8 default NULL,
  `hed_county` char(30) character set utf8 default NULL,
  `hed_postcode` char(15) character set utf8 default NULL,
  `hed_phone` char(20) character set utf8 default NULL,
  `hed_fax` char(20) character set utf8 default NULL,
  `hed_goods_contact` char(35) character set utf8 default NULL,
  `hed_sstk_suppno` smallint(6) default NULL,
  `hed_mstk_suppno` smallint(6) default NULL,
  `hed_std_vatcode` char(2) character set utf8 default NULL,
  `hed_car_stockcat` char(1) character set utf8 default NULL,
  `hed_next_porno` smallint(6) default NULL,
  `hed_next_itemno` smallint(6) default NULL,
  `hed_next_invno` int(11) default NULL,
  `hed_sstk_locno` smallint(6) default NULL,
  `hed_mstk_locno` smallint(6) default NULL,
  `hed_ecc_ass_locno` smallint(6) default NULL,
  `hed_ecc_op_locno` smallint(6) default NULL,
  `hed_invoice_prt` char(30) character set utf8 default NULL,
  `hed_porder_prt` char(30) character set utf8 default NULL,
  `hed_plaser_prt` char(30) character set utf8 default NULL,
  `hed_llaser_prt` char(30) character set utf8 default NULL,
  `hed_system_prt` char(30) character set utf8 default NULL,
  `hed_audit_prt` char(30) character set utf8 default NULL,
  `hed_bill_starttime` char(5) character set utf8 default NULL,
  `hed_bill_endtime` char(5) character set utf8 default NULL,
  `hed_hd_starttime` char(5) default NULL,
  `hed_hd_endtime` char(5) default NULL,
  `hed_pro_starttime` char(5) default NULL,
  `hed_pro_endtime` char(5) default NULL,
  `hed_gensup_itemno` int(11) default NULL,
  `hed_portal_pin` char(5) NOT NULL,
  `hed_next_schedno` int(11) default NULL,
  `hed_ot_adjust_hour` decimal(5,2) default NULL,
  `hed_mailflg1_def` char(1) character set utf8 default NULL,
  `hed_mailflg2_def` char(1) character set utf8 default NULL,
  `hed_mailflg3_def` char(1) character set utf8 default NULL,
  `hed_mailflg4_def` char(1) character set utf8 default NULL,
  `hed_mailflg5_def` char(1) character set utf8 default NULL,
  `hed_mailflg6_def` char(1) character set utf8 default NULL,
  `hed_mailflg7_def` char(1) character set utf8 default NULL,
  `hed_mailflg8_def` char(1) character set utf8 default NULL,
  `hed_mailflg9_def` char(1) character set utf8 default NULL,
  `hed_mailflg10_def` char(1) character set utf8 default NULL,
  `hed_mailflg1_desc` char(30) character set utf8 default NULL,
  `hed_mailflg2_desc` char(30) character set utf8 default NULL,
  `hed_mailflg3_desc` char(30) character set utf8 default NULL,
  `hed_mailflg4_desc` char(30) character set utf8 default NULL,
  `hed_mailflg5_desc` char(30) character set utf8 default NULL,
  `hed_mailflg6_desc` char(30) character set utf8 default NULL,
  `hed_mailflg7_desc` char(30) character set utf8 default NULL,
  `hed_mailflg8_desc` char(30) character set utf8 default NULL,
  `hed_mailflg9_desc` char(30) character set utf8 default NULL,
  `hed_mailflg10_desc` char(30) character set utf8 default NULL,
  `hed_helpdesk_problems` text COMMENT 'Daily helpdesk problems',
  `hed_helpdesk_os_count` int(5) default NULL,
  `hed_helpdesk_os_service_desk_count` int(5) default NULL,
  `hed_helpdesk_os_servercare_count` int(5) default NULL,
  `hed_helpdesk_os_prepay_count` int(5) default NULL,
  `hed_helpdesk_os_escalation_count` int(5) default NULL,
  `hed_helpdesk_os_cust_response_count` int(5) default NULL,
  `hed_hourly_labour_cost` decimal(5,2) default NULL COMMENT 'Cost to CNC',
  `hed_portal_24_hour_pin` char(5) NOT NULL,
  PRIMARY KEY  (`headerID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `invhead` */

DROP TABLE IF EXISTS `invhead`;

CREATE TABLE `invhead` (
  `inh_invno` int(11) NOT NULL default '0',
  `inh_custno` int(11) NOT NULL default '0',
  `inh_siteno` smallint(6) default NULL,
  `inh_ordno` int(11) default NULL,
  `inh_type` char(1) character set utf8 NOT NULL default '',
  `inh_add1` char(35) character set utf8 NOT NULL default '',
  `inh_add2` char(35) character set utf8 default NULL,
  `inh_add3` char(35) character set utf8 default NULL,
  `inh_town` char(25) character set utf8 default NULL,
  `inh_county` char(25) character set utf8 default NULL,
  `inh_postcode` char(15) character set utf8 default NULL,
  `inh_contno` int(11) default NULL,
  `inh_contact` char(25) character set utf8 default NULL,
  `inh_salutation` char(25) character set utf8 default NULL,
  `inh_pay_method` char(30) character set utf8 NOT NULL default '',
  `paymentTermsID` tinyint(4) default NULL,
  `inh_vat_code` char(2) character set utf8 default NULL,
  `inh_vat_rate` decimal(4,2) default NULL,
  `inh_ref_ecc` char(35) character set utf8 default NULL,
  `inh_ref_cust` char(23) character set utf8 default NULL,
  `inh_debtor_code` char(10) character set utf8 default NULL,
  `inh_source` char(1) character set utf8 default NULL,
  `inh_vat_only` char(1) character set utf8 default NULL,
  `inh_date_printed` date default NULL COMMENT 'PDF file of the invoice',
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
  `inl_invno` int(11) default NULL,
  `inl_line_no` smallint(6) default NULL,
  `inl_ord_line_no` smallint(6) default NULL,
  `inl_line_type` char(1) character set utf8 default NULL,
  `inl_itemno` int(11) default NULL,
  `inl_desc` char(45) character set utf8 default NULL,
  `inl_qty` decimal(7,2) default NULL,
  `inl_unit_price` decimal(7,2) default NULL,
  `inl_cost_price` decimal(7,2) default NULL,
  `inl_stockcat` char(1) character set utf8 default NULL,
  KEY `ixinl_1` (`inl_invno`),
  KEY `inl_itemno` (`inl_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `item` */

DROP TABLE IF EXISTS `item`;

CREATE TABLE `item` (
  `itm_itemno` int(11) NOT NULL default '0',
  `itm_manno` smallint(6) default NULL,
  `itm_desc` varchar(45) character set utf8 NOT NULL default '',
  `itm_stockcat` char(1) character set utf8 NOT NULL default '',
  `itm_itemtypeno` int(11) default NULL,
  `itm_sstk_price` decimal(12,2) default NULL,
  `itm_sstk_cost` decimal(12,2) default NULL,
  `itm_mstk_cost` decimal(12,2) default NULL,
  `itm_serial_req` char(1) character set utf8 default NULL,
  `itm_sstk_qty` decimal(5,2) default NULL,
  `itm_mstk_qty` decimal(5,2) default NULL,
  `itm_discontinued` char(1) character set utf8 default NULL,
  `itm_unit_of_sale` varchar(20) character set utf8 default NULL,
  `itm_contno` int(11) default NULL,
  `itm_servercare_flag` char(1) default '0' COMMENT 'is this a servercare contract?',
  `contractResponseTime` int(4) unsigned default '8',
  `notes` text character set utf8,
  `renewalTypeID` int(11) unsigned default NULL,
  UNIQUE KEY `ixitm_1` (`itm_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `itemtype` */

DROP TABLE IF EXISTS `itemtype`;

CREATE TABLE `itemtype` (
  `ity_itemtypeno` int(11) NOT NULL default '0',
  `ity_desc` char(50) character set utf8 NOT NULL default '',
  `ity_stockcat` char(1) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`ity_itemtypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `leadstatus` */

DROP TABLE IF EXISTS `leadstatus`;

CREATE TABLE `leadstatus` (
  `lst_leadstatusno` int(11) unsigned NOT NULL,
  `lst_desc` char(50) default NULL,
  PRIMARY KEY  (`lst_leadstatusno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `mail_queue` */

DROP TABLE IF EXISTS `mail_queue`;

CREATE TABLE `mail_queue` (
  `id` bigint(20) NOT NULL default '0',
  `create_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `time_to_send` datetime NOT NULL default '0000-00-00 00:00:00',
  `sent_time` datetime default NULL,
  `id_user` bigint(20) NOT NULL default '0',
  `ip` varchar(20) character set utf8 NOT NULL default 'unknown',
  `sender` varchar(100) character set utf8 NOT NULL default '',
  `recipient` varchar(300) character set utf8 NOT NULL default '',
  `headers` text character set utf8 NOT NULL,
  `body` longtext character set utf8 NOT NULL,
  `try_sent` tinyint(4) NOT NULL default '0',
  `delete_after_send` tinyint(1) NOT NULL default '1',
  `is_sending` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `time_to_send` (`time_to_send`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `mail_queue_seq` */

DROP TABLE IF EXISTS `mail_queue_seq`;

CREATE TABLE `mail_queue_seq` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=302151 DEFAULT CHARSET=latin1;

/*Table structure for table `mailshot_table` */

DROP TABLE IF EXISTS `mailshot_table`;

CREATE TABLE `mailshot_table` (
  `keyfield` char(50) default NULL,
  `hits` int(5) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `manufact` */

DROP TABLE IF EXISTS `manufact`;

CREATE TABLE `manufact` (
  `man_manno` int(11) NOT NULL default '0',
  `man_name` char(35) character set utf8 NOT NULL default '',
  `man_disc_rate` decimal(4,2) default NULL,
  UNIQUE KEY `ixman_1` (`man_manno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `notepad` */

DROP TABLE IF EXISTS `notepad`;

CREATE TABLE `notepad` (
  `not_type` varchar(3) character set utf8 NOT NULL default '',
  `not_key` int(11) NOT NULL default '0',
  `not_line` tinyint(4) NOT NULL default '0',
  `not_text` varchar(76) character set utf8 default NULL,
  KEY `ix_not1` (`not_type`,`not_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ordhead` */

DROP TABLE IF EXISTS `ordhead`;

CREATE TABLE `ordhead` (
  `odh_ordno` int(11) NOT NULL default '0',
  `odh_custno` int(11) default NULL,
  `odh_type` char(1) character set utf8 default NULL,
  `odh_part_invoice` char(1) character set utf8 default NULL,
  `odh_date` date default NULL,
  `odh_req_date` date default NULL,
  `odh_prom_date` date default NULL,
  `odh_expect_date` date default NULL,
  `odh_ref_ecc` char(100) character set utf8 default NULL,
  `odh_ref_cust` char(23) character set utf8 default NULL,
  `odh_vat_code` char(2) character set utf8 default NULL,
  `odh_vat_rate` decimal(4,2) default NULL,
  `odh_inv_siteno` smallint(6) default NULL,
  `odh_inv_add1` char(35) character set utf8 default NULL,
  `odh_inv_add2` char(35) character set utf8 default NULL,
  `odh_inv_add3` char(35) character set utf8 default NULL,
  `odh_inv_town` char(25) character set utf8 default NULL,
  `odh_inv_county` char(25) character set utf8 default NULL,
  `odh_inv_postcode` char(15) character set utf8 default NULL,
  `odh_inv_contno` int(11) default NULL,
  `odh_inv_contact` char(25) character set utf8 default NULL,
  `odh_inv_salutation` char(15) character set utf8 default NULL,
  `odh_inv_phone` char(20) character set utf8 default NULL,
  `odh_inv_fax` char(20) character set utf8 default NULL,
  `odh_inv_sphone` char(20) character set utf8 default NULL,
  `odh_inv_email` char(60) character set utf8 default NULL,
  `odh_del_siteno` smallint(6) default NULL,
  `odh_del_add1` char(35) character set utf8 default NULL,
  `odh_del_add2` char(35) character set utf8 default NULL,
  `odh_del_add3` char(35) character set utf8 default NULL,
  `odh_del_town` char(25) character set utf8 default NULL,
  `odh_del_county` char(25) character set utf8 default NULL,
  `odh_del_postcode` char(15) character set utf8 default NULL,
  `odh_del_contno` int(11) default NULL,
  `odh_del_contact` char(25) character set utf8 default NULL,
  `odh_del_salutation` char(15) character set utf8 default NULL,
  `odh_del_phone` char(20) character set utf8 default NULL,
  `odh_del_fax` char(20) character set utf8 default NULL,
  `odh_del_sphone` char(20) character set utf8 default NULL,
  `odh_del_email` char(60) character set utf8 default NULL,
  `odh_debtor_code` char(10) character set utf8 default NULL,
  `odh_wip` char(1) character set utf8 default NULL,
  `odh_consno` smallint(6) default NULL,
  `odh_pay_method` char(30) character set utf8 default NULL,
  `paymentTermsID` tinyint(4) default NULL,
  `odh_add_item` char(1) character set utf8 default NULL,
  `odh_callno` int(11) default NULL,
  `odh_quotation_subject` char(50) default NULL,
  `odh_quotation_introduction` char(200) default NULL,
  `updatedTime` datetime default NULL,
  `odh_service_request_custitemno` int(11) default NULL,
  `odh_service_request_text` text,
  `odh_service_request_bill_to_sales_order` char(1) default NULL,
  `odh_service_request_priority` int(11) default NULL,
  `odh_problemno` int(11) default NULL COMMENT 'The service request generated from this order',
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
  `odh_ordno` int(11) NOT NULL default '0',
  `odh_custno` int(11) default NULL,
  `odh_type` char(1) character set utf8 default NULL,
  `odh_part_invoice` char(1) character set utf8 default NULL,
  `odh_date` date default NULL,
  `odh_req_date` date default NULL,
  `odh_prom_date` date default NULL,
  `odh_expect_date` date default NULL,
  `odh_ref_ecc` char(100) character set utf8 default NULL,
  `odh_ref_cust` char(23) character set utf8 default NULL,
  `odh_vat_code` char(2) character set utf8 default NULL,
  `odh_vat_rate` decimal(4,2) default NULL,
  `odh_inv_siteno` smallint(6) default NULL,
  `odh_inv_add1` char(35) character set utf8 default NULL,
  `odh_inv_add2` char(35) character set utf8 default NULL,
  `odh_inv_add3` char(35) character set utf8 default NULL,
  `odh_inv_town` char(25) character set utf8 default NULL,
  `odh_inv_county` char(25) character set utf8 default NULL,
  `odh_inv_postcode` char(15) character set utf8 default NULL,
  `odh_inv_contno` int(11) default NULL,
  `odh_inv_contact` char(25) character set utf8 default NULL,
  `odh_inv_salutation` char(15) character set utf8 default NULL,
  `odh_inv_phone` char(20) character set utf8 default NULL,
  `odh_inv_sphone` char(20) character set utf8 default NULL,
  `odh_inv_fax` char(20) character set utf8 default NULL,
  `odh_inv_email` char(60) character set utf8 default NULL,
  `odh_del_siteno` smallint(6) default NULL,
  `odh_del_add1` char(35) character set utf8 default NULL,
  `odh_del_add2` char(35) character set utf8 default NULL,
  `odh_del_add3` char(35) character set utf8 default NULL,
  `odh_del_town` char(25) character set utf8 default NULL,
  `odh_del_county` char(25) character set utf8 default NULL,
  `odh_del_postcode` char(15) character set utf8 default NULL,
  `odh_del_contno` int(11) default NULL,
  `odh_del_contact` char(25) character set utf8 default NULL,
  `odh_del_salutation` char(15) character set utf8 default NULL,
  `odh_del_phone` char(20) character set utf8 default NULL,
  `odh_del_sphone` char(20) character set utf8 default NULL,
  `odh_del_fax` char(20) character set utf8 default NULL,
  `odh_del_email` char(60) character set utf8 default NULL,
  `odh_debtor_code` char(10) character set utf8 default NULL,
  `odh_wip` char(1) character set utf8 default NULL,
  `odh_consno` smallint(6) default NULL,
  `odh_pay_method` char(30) character set utf8 default NULL,
  `odh_add_item` char(1) character set utf8 default NULL,
  `odh_callno` int(11) default NULL,
  `updatedTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `newOrdheadID` int(11) NOT NULL default '0',
  UNIQUE KEY `ixodh_1` (`odh_ordno`),
  KEY `new_ordheadid` (`newOrdheadID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ordline` */

DROP TABLE IF EXISTS `ordline`;

CREATE TABLE `ordline` (
  `odl_type` char(1) character set utf8 default NULL,
  `odl_ordno` int(11) NOT NULL default '0',
  `odl_item_no` smallint(6) NOT NULL default '0',
  `odl_custno` int(11) default NULL,
  `odl_itemno` int(11) default NULL,
  `odl_stockcat` char(1) character set utf8 default NULL,
  `odl_desc` char(45) character set utf8 default NULL,
  `odl_qty_ord` decimal(7,2) default NULL,
  `odl_qty_desp` decimal(7,2) default NULL,
  `odl_qty_last_desp` decimal(7,2) default NULL,
  `odl_suppno` smallint(6) default NULL,
  `odl_d_unit` decimal(7,2) default NULL,
  `odl_d_total` decimal(7,2) default NULL,
  `odl_e_unit` decimal(7,2) default NULL,
  `odl_e_total` decimal(7,2) default NULL,
  `odl_renewal_cuino` int(11) unsigned default NULL,
  KEY `idxodl_2` (`odl_suppno`,`odl_desc`),
  KEY `idodl_3` (`odl_custno`,`odl_desc`),
  KEY `odl_itemno` (`odl_itemno`),
  KEY `ix_ordno` (`odl_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ordline_sco` */

DROP TABLE IF EXISTS `ordline_sco`;

CREATE TABLE `ordline_sco` (
  `odl_type` char(1) character set utf8 default NULL,
  `odl_ordno` int(11) default NULL,
  `odl_item_no` smallint(6) default NULL,
  `odl_custno` int(11) default NULL,
  `odl_itemno` int(11) default NULL,
  `odl_stockcat` char(1) character set utf8 default NULL,
  `odl_desc` char(45) character set utf8 default NULL,
  `odl_qty_ord` decimal(7,2) default NULL,
  `odl_qty_desp` decimal(7,2) default NULL,
  `odl_qty_last_desp` decimal(7,2) default NULL,
  `odl_suppno` smallint(6) default NULL,
  `odl_d_unit` decimal(7,2) default NULL,
  `odl_d_total` decimal(7,2) default NULL,
  `odl_e_unit` decimal(7,2) default NULL,
  `odl_e_total` decimal(7,2) default NULL,
  `processedFlag` tinyint(1) NOT NULL default '0',
  KEY `ixodl_1` (`odl_ordno`),
  KEY `odl_itemno` (`odl_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `page_view` */

DROP TABLE IF EXISTS `page_view`;

CREATE TABLE `page_view` (
  `page_view_id` int(11) unsigned NOT NULL auto_increment,
  `script_name` varchar(50) character set utf8 NOT NULL default '',
  `name` varchar(50) character set utf8 NOT NULL default '',
  `display_fields` longtext character set utf8,
  `order_by` longtext character set utf8,
  `filters` longtext character set utf8,
  `created_by_user_id` int(11) NOT NULL default '0',
  `created_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified_by_user_id` int(11) NOT NULL default '0',
  `modified_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`page_view_id`),
  KEY `modified_by_user_id` (`modified_by_user_id`),
  KEY `created_by_user_id` (`created_by_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Table structure for table `password` */

DROP TABLE IF EXISTS `password`;

CREATE TABLE `password` (
  `pas_passwordno` int(11) unsigned NOT NULL,
  `pas_custno` int(11) unsigned NOT NULL,
  `pas_username` char(50) NOT NULL,
  `pas_service` char(60) NOT NULL,
  `pas_password` char(30) NOT NULL,
  `pas_notes` char(200) default NULL,
  PRIMARY KEY  (`pas_passwordno`),
  KEY `pas_custno` (`pas_custno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `paymentterms` */

DROP TABLE IF EXISTS `paymentterms`;

CREATE TABLE `paymentterms` (
  `paymentTermsID` int(11) NOT NULL default '0',
  `description` char(50) character set utf8 NOT NULL default '',
  `days` tinyint(4) NOT NULL default '0',
  `generateInvoiceFlag` char(1) character set utf8 NOT NULL default '',
  `automaticInvoiceFlag` char(1) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`paymentTermsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `paymeth` */

DROP TABLE IF EXISTS `paymeth`;

CREATE TABLE `paymeth` (
  `pay_payno` int(11) NOT NULL default '0',
  `pay_desc` char(35) character set utf8 NOT NULL default '',
  `pay_card` char(1) character set utf8 NOT NULL default '',
  `pay_cardno` char(30) character set utf8 default NULL,
  `pay_exp_date` date default NULL,
  `pay_consno` smallint(6) default NULL,
  `automaticInvoiceFlag` char(1) character set utf8 NOT NULL default 'Y',
  UNIQUE KEY `ix178_1` (`pay_payno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `pinline` */

DROP TABLE IF EXISTS `pinline`;

CREATE TABLE `pinline` (
  `pin_pinno` int(11) NOT NULL default '0',
  `pin_type` char(2) character set utf8 NOT NULL default '',
  `pin_ac_ref` int(11) NOT NULL default '0',
  `pin_nom_ref` int(11) NOT NULL default '0',
  `pin_dept` int(11) NOT NULL default '0',
  `pin_date` date NOT NULL default '0000-00-00',
  `pin_ref` char(30) character set utf8 NOT NULL default '',
  `pin_details` char(20) character set utf8 NOT NULL default '',
  `pin_net_amnt` decimal(12,2) NOT NULL default '0.00',
  `pin_tax_code` char(2) character set utf8 NOT NULL default '',
  `pin_tax_amnt` decimal(12,2) NOT NULL default '0.00',
  `pin_printed` char(1) character set utf8 default NULL,
  PRIMARY KEY  (`pin_pinno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `porhead` */

DROP TABLE IF EXISTS `porhead`;

CREATE TABLE `porhead` (
  `poh_porno` int(11) NOT NULL default '0',
  `poh_type` char(1) character set utf8 default NULL,
  `poh_suppno` int(11) NOT NULL default '0',
  `poh_contno` int(11) default NULL,
  `poh_date` date default NULL,
  `poh_ordno` int(11) default NULL,
  `poh_supp_ref` char(30) character set utf8 default NULL,
  `poh_direct_del` char(1) character set utf8 default NULL,
  `poh_payno` smallint(6) default NULL,
  `poh_invoices` char(60) character set utf8 default NULL,
  `poh_printed` char(1) character set utf8 default NULL,
  `poh_consno` smallint(6) default NULL,
  `poh_vat_code` char(2) character set utf8 default NULL,
  `poh_vat_rate` decimal(5,2) default NULL,
  `poh_locno` smallint(6) default NULL,
  `poh_ord_consno` smallint(6) default NULL,
  `poh_ord_date` date default NULL,
  UNIQUE KEY `ix149_1` (`poh_porno`),
  KEY `ixpoh_1` (`poh_ordno`),
  KEY `ixpoh_2` (`poh_suppno`,`poh_type`),
  KEY `ixpoh_3` (`poh_type`),
  KEY `poh_date` (`poh_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `porline` */

DROP TABLE IF EXISTS `porline`;

CREATE TABLE `porline` (
  `pol_porno` int(11) NOT NULL default '0',
  `pol_lineno` smallint(6) NOT NULL default '0',
  `pol_itemno` int(11) default NULL,
  `pol_qty_ord` decimal(12,2) default NULL,
  `pol_qty_rec` decimal(12,2) default NULL,
  `pol_qty_inv` decimal(12,6) default NULL,
  `pol_cost` decimal(12,2) default NULL,
  `pol_stockcat` char(1) character set utf8 default NULL,
  `pol_exp_date` date default NULL,
  KEY `ixpol_1` (`pol_porno`),
  KEY `pol_itemno` (`pol_itemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `prepaystatement` */

DROP TABLE IF EXISTS `prepaystatement`;

CREATE TABLE `prepaystatement` (
  `pre_prepayno` int(11) unsigned NOT NULL auto_increment,
  `pre_custno` int(11) unsigned default NULL,
  `pre_date` date default NULL,
  `pre_balance` decimal(8,2) default NULL,
  `pre_file` longblob,
  PRIMARY KEY  (`pre_prepayno`)
) ENGINE=MyISAM AUTO_INCREMENT=1833 DEFAULT CHARSET=latin1;

/*Table structure for table `prizewinner` */

DROP TABLE IF EXISTS `prizewinner`;

CREATE TABLE `prizewinner` (
  `prz_prizewinnerno` int(11) unsigned NOT NULL auto_increment,
  `prz_yearmonth` char(7) NOT NULL COMMENT 'YYYY-MM',
  `prz_contno` int(11) NOT NULL COMMENT 'Customer contact that woin',
  `prz_approved_flag` char(1) default 'N' COMMENT 'has the prize been approved?',
  `prz_survey_name` char(50) default NULL COMMENT 'name entered on the survey',
  PRIMARY KEY  (`prz_prizewinnerno`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

/*Table structure for table `problem` */

DROP TABLE IF EXISTS `problem`;

CREATE TABLE `problem` (
  `pro_problemno` int(11) unsigned NOT NULL default '0',
  `pro_custno` int(11) NOT NULL default '0',
  `pro_priority` int(3) default NULL,
  `pro_consno` int(11) default NULL COMMENT 'User to whom the problem is allocated. zero indicaates Unallocated',
  `pro_status` enum('I','P','F','C') default 'I' COMMENT 'I=Initial P=In Progress F=Fixed C=Completed',
  `pro_date_raised` datetime default NULL COMMENT 'Date the problem was first raised in the system',
  `pro_responded_hours` decimal(7,2) default '0.00' COMMENT 'duration in hours until response',
  `pro_fixed_consno` int(11) default NULL COMMENT 'user that fixed problem',
  `pro_fixed_date` datetime default NULL,
  `pro_working_hours` decimal(7,2) default '0.00' COMMENT 'Number of working hours since problem first raised',
  `pro_sent_sla_alert_flag` char(1) default 'N' COMMENT 'Set to show SLA alert email has been sent',
  `pro_internal_notes` mediumtext COMMENT 'Internal CNC Notes',
  `pro_completion_alert_count` int(1) default '0' COMMENT 'How many completion alerts have been sent to the customer',
  `pro_complete_date` date default NULL COMMENT 'Date when request to be completed either manually or automatically',
  `pro_email_option` enum('A','N','S') default 'A' COMMENT 'A=Always, N=Never or S=Skip work commenced email',
  `pro_alarm_date` date default NULL,
  `pro_alarm_time` char(5) default NULL,
  `pro_total_activity_duration_hours` decimal(7,2) default NULL,
  `pro_chargeable_activity_duration_hours` decimal(7,2) default NULL,
  `pro_sla_response_hours` decimal(12,2) default NULL,
  `pro_contract_cuino` int(11) default NULL,
  `pro_escalated_flag` char(1) default 'N' COMMENT 'Was this escalated',
  `pro_escalated_consno` int(11) unsigned default NULL COMMENT 'Engineer that escalated',
  `pro_reopened_flag` char(1) default 'N' COMMENT 'Was this reopened',
  `pro_contno` int(11) unsigned default NULL,
  `activityCategoryID` int(11) unsigned default NULL,
  `pro_technician_weighting` enum('1','2','3','4','5') default '1' COMMENT 'Priority for engineer dashboard. Not used in any other context.',
  `pro_rejected_consno` int(11) unsigned default NULL COMMENT 'User that last rejected request',
  `pro_do_next_flag` char(1) default 'N' COMMENT 'Indicates the engineer should work on this request next',
  `pro_rootcauseno` int(11) unsigned default NULL COMMENT 'FK to rootcause table',
  `pro_working_hours_alert_sent_flag` char(1) default 'N' COMMENT 'Has an alert been sent when number of elapsed hours exceeds system limit?',
  `pro_awaiting_customer_response_flag` char(1) default 'N' COMMENT 'Are we waiting for customer action?',
  `pro_working_hours_calculated_to_time` datetime default '0000-00-00 00:00:00' COMMENT 'This is an optomisisation used by BUProblemSLA',
  `pro_manager_comment` text,
  `pro_breach_comment` text,
  `pro_message_to_sales` text,
  `pro_monitor_name` char(100) default NULL,
  `pro_monitor_agent_name` char(100) default NULL,
  PRIMARY KEY  (`pro_problemno`),
  KEY `pro_status_consno` (`pro_status`,`pro_consno`),
  KEY `pro_monitor_name` (`pro_monitor_name`,`pro_monitor_agent_name`),
  FULLTEXT KEY `pro_internal_notes` (`pro_internal_notes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `project` */

DROP TABLE IF EXISTS `project`;

CREATE TABLE `project` (
  `projectID` int(11) NOT NULL default '0',
  `customerID` int(11) NOT NULL default '0',
  `description` char(50) character set utf8 NOT NULL default '',
  `startDate` date default NULL,
  `expiryDate` date default NULL,
  `notes` text,
  UNIQUE KEY `ixcallt_1` (`projectID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `question` */

DROP TABLE IF EXISTS `question`;

CREATE TABLE `question` (
  `que_questionno` int(11) unsigned NOT NULL auto_increment,
  `que_questionnaireno` int(11) unsigned default NULL,
  `que_desc` char(100) NOT NULL COMMENT 'Description',
  `que_answertypeno` enum('1','2','3') default NULL COMMENT 'Type of answer. 1=',
  `que_active_flag` char(1) default 'Y' COMMENT 'Question is active?',
  `que_weight` int(3) default '0' COMMENT 'Weighting to control display order on screen',
  `que_required_flag` char(1) NOT NULL default 'Y',
  PRIMARY KEY  (`que_questionno`)
) ENGINE=MyISAM AUTO_INCREMENT=3061 DEFAULT CHARSET=latin1;

/*Table structure for table `question_type` */

DROP TABLE IF EXISTS `question_type`;

CREATE TABLE `question_type` (
  `qut_questiontypeno` int(11) unsigned NOT NULL auto_increment,
  `qut_desc` char(50) NOT NULL,
  PRIMARY KEY  (`qut_questiontypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `questionnaire` */

DROP TABLE IF EXISTS `questionnaire`;

CREATE TABLE `questionnaire` (
  `qur_questionnaireno` int(11) unsigned NOT NULL auto_increment,
  `qur_desc` char(50) NOT NULL,
  `qur_intro` text NOT NULL,
  `qur_thank_you` text NOT NULL,
  `qur_rating_1_desc` char(50) NOT NULL,
  `qur_rating_5_desc` char(50) NOT NULL,
  PRIMARY KEY  (`qur_questionnaireno`)
) ENGINE=MyISAM AUTO_INCREMENT=92012 DEFAULT CHARSET=latin1;

/*Table structure for table `quotation` */

DROP TABLE IF EXISTS `quotation`;

CREATE TABLE `quotation` (
  `quotationID` int(11) NOT NULL default '0',
  `ordheadID` int(11) NOT NULL default '0',
  `versionNo` int(11) NOT NULL default '0',
  `sentDateTime` datetime default NULL,
  `userID` int(11) NOT NULL default '0',
  `salutation` char(200) character set utf8 NOT NULL default '',
  `emailSubject` char(100) character set utf8 NOT NULL default '',
  `fileExtension` char(5) character set utf8 default NULL,
  `documentType` char(20) default NULL,
  PRIMARY KEY  (`quotationID`),
  UNIQUE KEY `ordhead_ix1` (`ordheadID`,`versionNo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `ras` */

DROP TABLE IF EXISTS `ras`;

CREATE TABLE `ras` (
  `ID` double NOT NULL auto_increment,
  `Cust no` double default NULL,
  `theCust` varchar(255) character set utf8 default NULL,
  `theDom` varchar(255) character set utf8 default NULL,
  `AdminName` varchar(255) character set utf8 default NULL,
  `AdminPass` varchar(255) character set utf8 default NULL,
  `cncadminname` varchar(255) character set utf8 default NULL,
  `cncadminpass` varchar(255) character set utf8 default NULL,
  `Con1Type` varchar(255) character set utf8 default NULL,
  `Con1Number` varchar(255) character set utf8 default NULL,
  `Con1user` varchar(255) character set utf8 default NULL,
  `Con1pass` varchar(255) character set utf8 default NULL,
  `Con2Type` varchar(255) character set utf8 default NULL,
  `Con2Number` varchar(255) character set utf8 default NULL,
  `Con2user` varchar(255) character set utf8 default NULL,
  `Con2Pass` varchar(255) character set utf8 default NULL,
  `Server1` varchar(255) character set utf8 default NULL,
  `Server1PRIVIP` varchar(255) character set utf8 default NULL,
  `Server1PUBIP` varchar(255) character set utf8 default NULL,
  `Server1ControlApp` varchar(255) character set utf8 default NULL,
  `Server1AppUser` varchar(255) character set utf8 default NULL,
  `Server1AppPass` varchar(255) character set utf8 default NULL,
  `Server2` varchar(255) character set utf8 default NULL,
  `Server2PRIVIP` varchar(255) character set utf8 default NULL,
  `Server2PUBIP` varchar(255) character set utf8 default NULL,
  `Server2ControlApp` varchar(255) character set utf8 default NULL,
  `Server2AppUser` varchar(255) character set utf8 default NULL,
  `Server2AppPass` varchar(255) character set utf8 default NULL,
  `Server3` varchar(255) character set utf8 default NULL,
  `Server3PRIVIP` varchar(255) character set utf8 default NULL,
  `Server3PUBIP` varchar(255) character set utf8 default NULL,
  `Server3ControlApp` varchar(255) character set utf8 default NULL,
  `Server3AppUser` varchar(255) character set utf8 default NULL,
  `Server3AppPass` varchar(255) character set utf8 default NULL,
  `Server4` varchar(255) character set utf8 default NULL,
  `Server4PRIVIP` varchar(255) character set utf8 default NULL,
  `Server4PUBIP` varchar(255) character set utf8 default NULL,
  `Server4ControlApp` varchar(255) character set utf8 default NULL,
  `Server4AppUser` varchar(255) character set utf8 default NULL,
  `Server4AppPass` varchar(255) character set utf8 default NULL,
  `Server5` varchar(255) character set utf8 default NULL,
  `Server5PRIVIP` varchar(255) character set utf8 default NULL,
  `Server5PUBIP` varchar(255) character set utf8 default NULL,
  `Server5ControlApp` varchar(255) character set utf8 default NULL,
  `Server5AppUser` varchar(255) character set utf8 default NULL,
  `Server5AppPass` varchar(255) character set utf8 default NULL,
  `Server6` varchar(255) character set utf8 default NULL,
  `Server6PRIVIP` varchar(255) character set utf8 default NULL,
  `Server6PUBIP` varchar(255) character set utf8 default NULL,
  `Server6ControlApp` varchar(255) character set utf8 default NULL,
  `Server6AppUser` varchar(255) character set utf8 default NULL,
  `Server6AppPass` varchar(255) character set utf8 default NULL,
  `GatewayLANIP` varchar(255) character set utf8 default NULL,
  `GatewayWANIP` varchar(255) character set utf8 default NULL,
  `WANSubnet` varchar(255) character set utf8 default NULL,
  `GatewayMake` varchar(255) character set utf8 default NULL,
  `GatewayUsername` varchar(255) character set utf8 default NULL,
  `GatewayPassword` varchar(255) character set utf8 default NULL,
  `Notes` longtext character set utf8,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=395 DEFAULT CHARSET=latin1;

/*Table structure for table `renbroadband` */

DROP TABLE IF EXISTS `renbroadband`;

CREATE TABLE `renbroadband` (
  `renBroadbandID` int(11) unsigned NOT NULL auto_increment,
  `renewalDate` date default NULL,
  `customerName` char(50) default NULL,
  `customerID` int(11) unsigned default NULL,
  `itemID` int(11) unsigned default NULL,
  `customerItemID` int(11) unsigned default NULL,
  `months` int(10) default NULL,
  `ordheadID` char(10) default NULL,
  `broadbandServiceType` char(50) default NULL,
  `broadbandServiceTypeID` int(11) default NULL,
  `adslPhone` varchar(255) default NULL,
  `fee` double default NULL COMMENT 'Cost per month',
  `macCode` varchar(255) default NULL,
  `batchNo` char(50) default NULL,
  `reference` varchar(255) default NULL,
  `defaultGateway` char(50) default NULL,
  `networkAddress` char(50) default NULL,
  `subnetMask` char(50) default NULL,
  `routerIPAddress` text COMMENT 'one or more IP addresses',
  `userName` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `etaDate` date default NULL,
  `installationDate` date default NULL,
  `costPerAnnum` double default NULL COMMENT 'Sale price per annum',
  `salePricePerMonth` decimal(6,2) default NULL COMMENT 'Sales Price Per Month',
  `costPricePerMonth` decimal(6,2) default NULL,
  `ispID` varchar(255) default NULL,
  `requiresChangesFlag` varchar(1) default NULL,
  `dualBroadbandFlag` varchar(1) default NULL,
  `dnsCompany` varchar(255) default NULL,
  `ipCurrentNo` char(50) default NULL,
  `mx` varchar(255) default NULL,
  `secureServer` varchar(255) default NULL,
  `vpns` varchar(255) default NULL,
  `owa` varchar(255) default NULL,
  `oma` varchar(255) default NULL,
  `remotePortal` varchar(255) default NULL,
  `smartHost` varchar(255) default NULL,
  `preparationRecords` varchar(255) default NULL,
  `assignedTo` varchar(255) default NULL,
  `initialSpeedTest` varchar(255) default NULL,
  `preMigrationNotes` longblob,
  `postMigrationNotes` longblob,
  `docsUpdatedAndChecksCompleted` varchar(255) default NULL,
  `invoicePeriodMonths` int(4) default NULL COMMENT 'Number of months between invoices',
  `totalInvoiceMonths` int(11) default NULL COMMENT 'Number of months to add to install date to calculate next invoice due date',
  `declinedFlag` char(1) default NULL,
  PRIMARY KEY  (`renBroadbandID`)
) ENGINE=MyISAM AUTO_INCREMENT=331 DEFAULT CHARSET=utf8;

/*Table structure for table `rencontract` */

DROP TABLE IF EXISTS `rencontract`;

CREATE TABLE `rencontract` (
  `renContractID` int(11) unsigned NOT NULL auto_increment,
  `customerItemID` int(11) default NULL,
  `installationDate` date default NULL,
  `invoicePeriodMonths` int(3) unsigned default NULL,
  `declinedFlag` char(1) default 'N',
  `totalInvoiceMonths` int(5) default NULL,
  `notes` char(45) default NULL,
  `hostingCompany` char(100) default NULL,
  `password` char(50) default NULL,
  `osPlatform` char(50) default NULL,
  `domainNames` char(100) default NULL,
  `controlPanelUrl` char(50) default NULL,
  `ftpAddress` char(50) default NULL,
  `ftpUsername` char(50) default NULL,
  `wwwAddress` char(50) default NULL,
  `websiteDeveloper` char(50) default NULL,
  PRIMARY KEY  (`renContractID`)
) ENGINE=MyISAM AUTO_INCREMENT=2226 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `rendomain` */

DROP TABLE IF EXISTS `rendomain`;

CREATE TABLE `rendomain` (
  `renDomainID` int(11) unsigned NOT NULL,
  `customerItemID` int(11) NOT NULL,
  `installationDate` date NOT NULL,
  `invoicePeriodMonths` int(3) unsigned default NULL,
  `declinedFlag` char(1) default 'N',
  `totalInvoiceMonths` int(5) default NULL,
  `notes` char(45) default NULL,
  `dateGenerated` date default '0000-00-00',
  PRIMARY KEY  (`renDomainID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `renewaltype` */

DROP TABLE IF EXISTS `renewaltype`;

CREATE TABLE `renewaltype` (
  `renewalTypeID` int(11) unsigned NOT NULL auto_increment,
  `description` char(50) NOT NULL,
  `allowSrLogging` char(1) NOT NULL default 'Y',
  PRIMARY KEY  (`renewalTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `renquotation` */

DROP TABLE IF EXISTS `renquotation`;

CREATE TABLE `renquotation` (
  `renQuotationID` int(11) unsigned NOT NULL auto_increment,
  `customerItemID` int(11) default NULL,
  `startDate` date default NULL,
  `declinedFlag` char(1) default 'N',
  `salePrice` decimal(6,2) default NULL,
  `costPrice` decimal(6,2) default NULL,
  `qty` int(3) default NULL,
  `renQuotationTypeID` int(11) default NULL,
  `comment` char(50) default NULL,
  `grantNumber` char(50) default NULL,
  `dateGenerated` date default '0000-00-00' COMMENT 'This is the date that the quotation was generated from the renewal record',
  PRIMARY KEY  (`renQuotationID`)
) ENGINE=MyISAM AUTO_INCREMENT=703 DEFAULT CHARSET=latin1;

/*Table structure for table `renquotationtype` */

DROP TABLE IF EXISTS `renquotationtype`;

CREATE TABLE `renquotationtype` (
  `renQuotationTypeID` int(11) unsigned NOT NULL auto_increment,
  `description` char(50) NOT NULL,
  `addInstallationCharge` char(1) default 'Y',
  PRIMARY KEY  (`renQuotationTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

/*Table structure for table `rootcause` */

DROP TABLE IF EXISTS `rootcause`;

CREATE TABLE `rootcause` (
  `rtc_rootcauseno` int(11) unsigned NOT NULL auto_increment COMMENT 'ID',
  `rtc_desc` char(50) NOT NULL COMMENT 'Short description',
  `rtc_long_desc` char(100) NOT NULL COMMENT 'Long description',
  PRIMARY KEY  (`rtc_rootcauseno`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

/*Table structure for table `salesrequest` */

DROP TABLE IF EXISTS `salesrequest`;

CREATE TABLE `salesrequest` (
  `srq_salesrequestno` int(11) unsigned NOT NULL auto_increment,
  `srq_ordno` int(11) unsigned NOT NULL,
  `srq_text` text,
  `srq_contractcuino` int(11) unsigned default NULL,
  PRIMARY KEY  (`srq_salesrequestno`),
  KEY `srq_ordno` (`srq_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `sector` */

DROP TABLE IF EXISTS `sector`;

CREATE TABLE `sector` (
  `sec_sectorno` int(11) unsigned NOT NULL,
  `sec_desc` char(50) default NULL,
  PRIMARY KEY  (`sec_sectorno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `securityapp` */

DROP TABLE IF EXISTS `securityapp`;

CREATE TABLE `securityapp` (
  `securityAppID` int(11) NOT NULL default '0',
  `description` char(50) NOT NULL default '',
  `backupFlag` char(1) default NULL,
  `emailAVFlag` char(1) default NULL,
  `serverAVFlag` char(1) default NULL,
  PRIMARY KEY  (`securityAppID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `service request review_csv` */

DROP TABLE IF EXISTS `service request review_csv`;

CREATE TABLE `service request review_csv` (
  `F1` int(10) NOT NULL,
  `F2` text,
  PRIMARY KEY  (`F1`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `servicedeskreport` */

DROP TABLE IF EXISTS `servicedeskreport`;

CREATE TABLE `servicedeskreport` (
  `sdr_servicedeskreportno` int(11) unsigned NOT NULL auto_increment,
  `sdr_year_month` char(6) NOT NULL,
  `sdr_calls_received` int(3) default NULL,
  `sdr_calls_overflowed` int(3) default NULL,
  `sdr_calls_helpdesk` int(3) default NULL,
  `sdr_calls_answer_seconds` int(3) default NULL,
  `sdr_calls_abandoned` int(3) default NULL,
  `sdr_meeting_results` text,
  `sdr_staff_issues` text,
  `sdr_staff_holiday_days` int(3) default NULL,
  `sdr_staff_sick_days` int(3) default NULL,
  `sdr_training` text,
  `sdr_any_other_business` text,
  PRIMARY KEY  (`sdr_servicedeskreportno`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `sid` varchar(32) character set utf8 NOT NULL default '',
  `name` varchar(32) character set utf8 NOT NULL default '',
  `val` text character set utf8,
  `changed` varchar(14) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`name`,`sid`),
  KEY `changed` (`changed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `staffavailable` */

DROP TABLE IF EXISTS `staffavailable`;

CREATE TABLE `staffavailable` (
  `staffAvailableID` int(11) unsigned NOT NULL,
  `userID` int(11) default NULL,
  `date` date NOT NULL,
  `am` decimal(2,1) NOT NULL default '0.5' COMMENT 'available in the morning',
  `pm` decimal(2,1) NOT NULL default '0.5' COMMENT 'available in the afternoon',
  PRIMARY KEY  (`staffAvailableID`)
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 COMMENT='Indicates whether an engineer is avalable for a given date';

/*Table structure for table `standardtext` */

DROP TABLE IF EXISTS `standardtext`;

CREATE TABLE `standardtext` (
  `stt_standardtextno` int(11) unsigned NOT NULL auto_increment,
  `stt_sort_order` int(1) unsigned NOT NULL default '1' COMMENT 'Order of appearance in drop-downs',
  `stt_desc` char(50) NOT NULL COMMENT 'Text for drop-downs etc',
  `stt_text` text NOT NULL COMMENT 'Content to be pasted',
  `stt_standardtexttypeno` int(11) NOT NULL COMMENT 'Type of text',
  PRIMARY KEY  (`stt_standardtextno`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*Table structure for table `standardtexttype` */

DROP TABLE IF EXISTS `standardtexttype`;

CREATE TABLE `standardtexttype` (
  `sty_standardtexttypeno` int(11) unsigned NOT NULL auto_increment,
  `sty_desc` char(50) NOT NULL,
  PRIMARY KEY  (`sty_standardtexttypeno`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Table structure for table `stockcat` */

DROP TABLE IF EXISTS `stockcat`;

CREATE TABLE `stockcat` (
  `stc_stockcat` char(1) character set utf8 NOT NULL default '',
  `stc_desc` char(25) character set utf8 default NULL,
  `stc_sal_nom` char(6) character set utf8 default NULL,
  `stc_pur_cust` char(6) character set utf8 default NULL,
  `stc_pur_sales_stk` char(6) character set utf8 default NULL,
  `stc_pur_maint_stk` char(6) character set utf8 default NULL,
  `stc_pur_ecc_asset` char(6) character set utf8 default NULL,
  `stc_pur_ecc_oper` char(6) character set utf8 default NULL,
  `stc_serial_req` char(1) character set utf8 default NULL,
  `stc_post_movement` char(1) character set utf8 default NULL,
  UNIQUE KEY `ixstc_1` (`stc_stockcat`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `supplier` */

DROP TABLE IF EXISTS `supplier`;

CREATE TABLE `supplier` (
  `sup_suppno` int(11) NOT NULL default '0',
  `sup_name` char(35) character set utf8 default NULL,
  `sup_contact` char(35) character set utf8 default NULL,
  `sup_add1` char(35) character set utf8 default NULL,
  `sup_add2` char(35) character set utf8 default NULL,
  `sup_town` char(25) character set utf8 default NULL,
  `sup_county` char(25) character set utf8 default NULL,
  `sup_postcode` char(15) character set utf8 default NULL,
  `sup_phone` char(20) character set utf8 default NULL,
  `sup_fax` char(20) character set utf8 default NULL,
  `sup_web_site_url` char(100) character set utf8 default NULL,
  `sup_pay_method` char(15) character set utf8 default NULL,
  `sup_credit_limit` decimal(10,2) default NULL,
  `sup_approval` char(1) character set utf8 default NULL,
  `sup_scopeno` smallint(6) default NULL,
  `sup_payno` smallint(6) default NULL,
  `sup_date_quest` date default NULL,
  `sup_contno` int(11) default NULL,
  `sup_cnc_accno` char(20) character set utf8 default NULL,
  PRIMARY KEY  (`sup_suppno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `userext` */

DROP TABLE IF EXISTS `userext`;

CREATE TABLE `userext` (
  `userID` int(11) NOT NULL default '0',
  `signatureFilename` varchar(50) character set utf8 NOT NULL default '',
  `jobTitle` varchar(50) character set utf8 NOT NULL default '',
  `firstName` varchar(50) character set utf8 NOT NULL default '',
  `lastName` varchar(50) character set utf8 NOT NULL default '',
  `activeFlag` char(1) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `vat` */

DROP TABLE IF EXISTS `vat`;

CREATE TABLE `vat` (
  `vat_rate_t0` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t1` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t2` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t3` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t4` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t5` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t6` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t7` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t8` decimal(4,2) NOT NULL default '0.00',
  `vat_rate_t9` decimal(4,2) NOT NULL default '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `auto_request_activitycategory` */

DROP TABLE IF EXISTS `auto_request_activitycategory`;

/*!50001 DROP VIEW IF EXISTS `auto_request_activitycategory` */;
/*!50001 DROP TABLE IF EXISTS `auto_request_activitycategory` */;

/*!50001 CREATE TABLE  `auto_request_activitycategory`(
 `activityCategoryID` int(10) unsigned ,
 `description` char(50) ,
 `allowSelection` char(1) 
)*/;

/*Table structure for table `auto_request_contracts` */

DROP TABLE IF EXISTS `auto_request_contracts`;

/*!50001 DROP VIEW IF EXISTS `auto_request_contracts` */;
/*!50001 DROP TABLE IF EXISTS `auto_request_contracts` */;

/*!50001 CREATE TABLE  `auto_request_contracts`(
 `contractCustomerItemID` int(11) unsigned ,
 `customerID` int(11) ,
 `customerName` varchar(35) ,
 `renewalType` char(50) ,
 `postcode` char(15) ,
 `description` varchar(45) ,
 `adslPhone` varchar(255) ,
 `routerIpAddress` text 
)*/;

/*Table structure for table `auto_request_rootcause` */

DROP TABLE IF EXISTS `auto_request_rootcause`;

/*!50001 DROP VIEW IF EXISTS `auto_request_rootcause` */;
/*!50001 DROP TABLE IF EXISTS `auto_request_rootcause` */;

/*!50001 CREATE TABLE  `auto_request_rootcause`(
 `rootCauseID` int(11) unsigned ,
 `description` char(50) 
)*/;

/*Table structure for table `main_contacts` */

DROP TABLE IF EXISTS `main_contacts`;

/*!50001 DROP VIEW IF EXISTS `main_contacts` */;
/*!50001 DROP TABLE IF EXISTS `main_contacts` */;

/*!50001 CREATE TABLE  `main_contacts`(
 `ContactFirstName` char(25) ,
 `ContactLastName` char(35) ,
 `ContactEmail` char(60) ,
 `CustomerName` varchar(35) ,
 `CustomerID` int(11) 
)*/;

/*View structure for view auto_request_activitycategory */

/*!50001 DROP TABLE IF EXISTS `auto_request_activitycategory` */;
/*!50001 DROP VIEW IF EXISTS `auto_request_activitycategory` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `auto_request_activitycategory` AS (select `activitycategory`.`activityCategoryID` AS `activityCategoryID`,`activitycategory`.`description` AS `description`,`activitycategory`.`allowSelection` AS `allowSelection` from `activitycategory`) */;

/*View structure for view auto_request_contracts */

/*!50001 DROP TABLE IF EXISTS `auto_request_contracts` */;
/*!50001 DROP VIEW IF EXISTS `auto_request_contracts` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `auto_request_contracts` AS select `custitem`.`cui_cuino` AS `contractCustomerItemID`,`custitem`.`cui_custno` AS `customerID`,`customer`.`cus_name` AS `customerName`,`renewaltype`.`description` AS `renewalType`,`address`.`add_postcode` AS `postcode`,`item`.`itm_desc` AS `description`,`custitem`.`adslPhone` AS `adslPhone`,`custitem`.`routerIPAddress` AS `routerIpAddress` from ((((`custitem` join `item` on((`custitem`.`cui_itemno` = `item`.`itm_itemno`))) join `customer` on((`customer`.`cus_custno` = `custitem`.`cui_custno`))) join `renewaltype` on((`renewaltype`.`renewalTypeID` = `item`.`renewalTypeID`))) join `address` on(((`address`.`add_siteno` = `custitem`.`cui_siteno`) and (`address`.`add_custno` = `custitem`.`cui_custno`)))) where ((`renewaltype`.`allowSrLogging` = _latin1'Y') and (`custitem`.`declinedFlag` <> _latin1'Y')) order by `renewaltype`.`description`,`item`.`itm_desc` */;

/*View structure for view auto_request_rootcause */

/*!50001 DROP TABLE IF EXISTS `auto_request_rootcause` */;
/*!50001 DROP VIEW IF EXISTS `auto_request_rootcause` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `auto_request_rootcause` AS (select `rootcause`.`rtc_rootcauseno` AS `rootCauseID`,`rootcause`.`rtc_desc` AS `description` from `rootcause`) */;

/*View structure for view main_contacts */

/*!50001 DROP TABLE IF EXISTS `main_contacts` */;
/*!50001 DROP VIEW IF EXISTS `main_contacts` */;

/*!50001 CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `main_contacts` AS (select `contact`.`con_first_name` AS `ContactFirstName`,`contact`.`con_last_name` AS `ContactLastName`,`contact`.`con_email` AS `ContactEmail`,`customer`.`cus_name` AS `CustomerName`,`contact`.`con_custno` AS `CustomerID` from (`contact` join `customer` on((`customer`.`cus_custno` = `contact`.`con_custno`))) where (`contact`.`con_mailflag10` = _utf8'Y')) */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
