-- MySQL dump 10.13  Distrib 5.5.23, for Win32 (x86)
--
-- Host: localhost    Database: cncapps
-- ------------------------------------------------------
-- Server version	5.5.23-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activitycategory`
--

DROP TABLE IF EXISTS `activitycategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activitycategory` (
  `activityCategoryID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` char(50) CHARACTER SET utf8 NOT NULL,
  `allowSelection` char(1) NOT NULL DEFAULT 'Y' COMMENT 'Can this be selected by users in drop-downs?',
  PRIMARY KEY (`activityCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=latin1 COMMENT='Call activity categrories';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `address`
--

DROP TABLE IF EXISTS `address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address` (
  `add_custno` int(11) NOT NULL DEFAULT '0' COMMENT 'PK to customer',
  `add_add1` char(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Address lines',
  `add_add2` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `add_add3` char(35) CHARACTER SET utf8 DEFAULT NULL,
  `add_town` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `add_county` char(25) CHARACTER SET utf8 DEFAULT NULL,
  `add_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL,
  `add_inv_contno` int(11) DEFAULT NULL COMMENT 'PK to Default Invoice contact',
  `add_del_contno` int(11) DEFAULT NULL COMMENT 'PK to Default delivery contact',
  `add_debtor_code` char(10) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Used by Sage accounts',
  `add_siteno` smallint(6) NOT NULL DEFAULT '0',
  `add_sage_ref` char(6) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Sage accounts reference',
  `add_phone` char(40) CHARACTER SET utf8 DEFAULT NULL,
  `add_max_travel_hours` decimal(5,2) NOT NULL DEFAULT '1.50' COMMENT 'Maximum travel hours to this site from CNC',
  `add_active_flag` char(1) DEFAULT 'Y' COMMENT 'Active site',
  PRIMARY KEY (`add_custno`,`add_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Customer Sites';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `answer`
--

DROP TABLE IF EXISTS `answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answer` (
  `ans_answerno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ans_questionno` int(11) unsigned NOT NULL COMMENT 'PK to question',
  `ans_problemno` int(11) NOT NULL COMMENT 'PK to related Service Request',
  `ans_answer` text NOT NULL COMMENT 'Text of the answer',
  `ans_name` char(100) NOT NULL COMMENT 'Name of client contact',
  `ans_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date/time',
  PRIMARY KEY (`ans_answerno`)
) ENGINE=MyISAM AUTO_INCREMENT=16357 DEFAULT CHARSET=latin1 COMMENT='Quesionairre answers';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `answertype`
--

DROP TABLE IF EXISTS `answertype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `answertype` (
  `ant_answertypeno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ant_desc` char(50) NOT NULL,
  PRIMARY KEY (`ant_answertypeno`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='Quesionairre answer type. e.g. Yes/No, freetext';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arecord`
--

DROP TABLE IF EXISTS `arecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arecord` (
  `are_arecordno` int(11) unsigned NOT NULL,
  `are_custitemno` int(11) unsigned NOT NULL COMMENT 'PK to Internet contract',
  `are_type` char(60) NOT NULL COMMENT 'Type of record. e.g. MX',
  `are_name` char(60) NOT NULL,
  `are_destination_ip` char(100) NOT NULL COMMENT 'IP address',
  `are_function` char(100) NOT NULL,
  PRIMARY KEY (`are_arecordno`),
  KEY `are_custitem` (`are_custitemno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Internet A-Records for client';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_trail`
--

DROP TABLE IF EXISTS `audit_trail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_trail` (
  `auditTrailId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tableName` char(50) NOT NULL COMMENT 'table affected',
  `primaryKey` int(11) NOT NULL COMMENT 'PK value of row affected',
  `colName` char(50) NOT NULL COMMENT 'field name',
  `oldValue` longtext COMMENT 'old value',
  `newValue` longtext COMMENT 'new value',
  `userID` int(11) DEFAULT NULL COMMENT 'user',
  `modifyDate` datetime DEFAULT NULL,
  PRIMARY KEY (`auditTrailId`)
) ENGINE=MyISAM AUTO_INCREMENT=9440 DEFAULT CHARSET=latin1 COMMENT='Audit trail of changes to other database fields by users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auto_request_activitycategory`
--

DROP TABLE IF EXISTS `auto_request_activitycategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_request_activitycategory` (
  `activityCategoryID` int(10) unsigned DEFAULT NULL,
  `description` char(50) DEFAULT NULL,
  `allowSelection` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auto_request_contracts`
--

DROP TABLE IF EXISTS `auto_request_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_request_contracts` (
  `contractCustomerItemID` int(11) unsigned DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `customerName` varchar(35) DEFAULT NULL,
  `renewalType` char(50) DEFAULT NULL,
  `postcode` char(15) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `adslPhone` varchar(255) DEFAULT NULL,
  `routerIpAddress` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auto_request_rootcause`
--

DROP TABLE IF EXISTS `auto_request_rootcause`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_request_rootcause` (
  `rootCauseID` int(11) unsigned DEFAULT NULL,
  `description` char(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `automated_request`
--

DROP TABLE IF EXISTS `automated_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `attachment` char(1) DEFAULT NULL COMMENT 'Is there an attachment?',
  `attachmentFilename` char(255) DEFAULT NULL COMMENT 'Attachment name (required for attachment)',
  `attachmentMimeType` char(100) DEFAULT NULL COMMENT 'Attachment MIME type. e.g. application/pdf',
  `rootCauseID` int(11) DEFAULT NULL,
  `contractCustomerItemID` int(11) DEFAULT NULL,
  `activityCategoryID` int(11) DEFAULT NULL,
  `monitorName` char(100) DEFAULT NULL COMMENT 'name of the monitor, to allow tracking of the failure and success',
  `monitorAgentName` char(100) DEFAULT NULL COMMENT 'This is the computer that the monitor has failed against.',
  `monitorStatus` char(1) DEFAULT NULL COMMENT 'Success or Failure',
  `importErrorFound` enum('Y','N') DEFAULT 'N' COMMENT 'Has the import process already found this error',
  `importDateTime` datetime DEFAULT NULL COMMENT 'Date this row was updated into the CNC datbase',
  `createDateTime` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date this row was created in this table',
  `subjectLine` char(255) DEFAULT NULL COMMENT 'Subject line of email',
  `queueNo` int(3) DEFAULT '1' COMMENT 'Queue number for SR',
  PRIMARY KEY (`automatedRequestID`),
  KEY `senderEmailAddress` (`senderEmailAddress`)
) ENGINE=MyISAM AUTO_INCREMENT=449058 DEFAULT CHARSET=latin1 COMMENT='Import of automated requests from external source';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `base_password`
--

DROP TABLE IF EXISTS `base_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `base_password` (
  `basePasswordID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `passwordString` char(10) NOT NULL COMMENT 'Password base string',
  PRIMARY KEY (`basePasswordID`)
) ENGINE=MyISAM AUTO_INCREMENT=298 DEFAULT CHARSET=latin1 COMMENT='A list of base words from which passwords will be generated';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `broadbandservicetype`
--

DROP TABLE IF EXISTS `broadbandservicetype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `broadbandservicetype` (
  `broadbandServiceTypeID` int(11) unsigned NOT NULL,
  `description` char(50) NOT NULL,
  PRIMARY KEY (`broadbandServiceTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='OBSOLETE - to be dropped when no more refs from application';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `callactengineer`
--

DROP TABLE IF EXISTS `callactengineer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callactengineer` (
  `cae_callactengno` int(11) NOT NULL DEFAULT '0',
  `cae_callactivityno` int(11) NOT NULL DEFAULT '0',
  `cae_item` smallint(6) NOT NULL DEFAULT '0',
  `cae_consno` int(11) NOT NULL DEFAULT '0',
  `cae_expn_exp_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `cae_ot_exp_flag` char(1) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `caeix_1` (`cae_callactengno`),
  KEY `caeix_2` (`cae_consno`,`cae_callactivityno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='OBSOLETE to be removed when refs from application removed';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `callactivity`
--

DROP TABLE IF EXISTS `callactivity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callactivity` (
  `caa_callactivityno` int(11) NOT NULL DEFAULT '0',
  `caa_siteno` int(11) DEFAULT NULL COMMENT 'Site for activity. FK to address.add_siteno',
  `caa_contno` int(11) NOT NULL DEFAULT '0' COMMENT 'Customer Contact. FK to contact.con_contno',
  `caa_item` smallint(6) DEFAULT NULL COMMENT 'OBSOLETE',
  `caa_callacttypeno` int(11) NOT NULL DEFAULT '0' COMMENT 'Activity Type. FK to callacttype.cat_callacttypeno',
  `projectID` int(11) NOT NULL DEFAULT '0' COMMENT 'Project. FK to project.projectID',
  `caa_problemno` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Service Request. FK to problem.problemno',
  `caa_date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date of activity',
  `caa_date_yearmonth` char(6) DEFAULT NULL COMMENT 'For speed of reporting',
  `caa_starttime` varchar(5) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Start Time HH:SS',
  `caa_endtime` varchar(5) CHARACTER SET utf8 DEFAULT NULL COMMENT 'End Time HH:SS',
  `caa_status` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Status [O]pen, [C]hecked, [A]uthorised',
  `caa_expexport_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Staff expenses processed Y/N',
  `reason` text CHARACTER SET utf8 COMMENT 'Details',
  `internalNotes` mediumtext COMMENT 'OBSOLETE: on Service Request now',
  `curValue` decimal(6,2) NOT NULL DEFAULT '0.00' COMMENT 'Value in ┬ú for (used for PrePay)',
  `statementYearMonth` varchar(7) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE?',
  `caa_custno` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Customer. FK to customer.cus_custno',
  `caa_cuino` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'OBSOLETE',
  `caa_under_contract` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'OBSOLETE',
  `caa_authorised` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'T&M processed Y/N',
  `caa_consno` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'User. FK to consultant.cns_consno',
  `caa_ot_exp_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N' COMMENT 'Overtime processed',
  `caa_completed_consno` int(6) unsigned NOT NULL DEFAULT '0' COMMENT 'User that completed activity. . FK to consultant.cns_consno',
  `caa_completed_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Completed date',
  `caa_serverguard` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N' COMMENT 'Serverguard? Y/N',
  `caa_parent_callactivityno` int(11) DEFAULT NULL COMMENT 'OBSOLETE',
  `caa_awaiting_customer_response_flag` char(1) DEFAULT 'N' COMMENT 'If so then exclude this from time duration calculations',
  `caa_class` char(1) DEFAULT NULL COMMENT '[W]orking, [I]nformational, [O]ther',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date/time created on database',
  `caa_logging_error_flag` char(1) DEFAULT 'N' COMMENT 'Was there an error when logging this activity',
  `escalationID` int(11) unsigned DEFAULT NULL COMMENT 'FK to Escalation table',
  `escalationAcceptedFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Was the escalation request accepted?',
  `caa_hide_from_customer_flag` char(1) DEFAULT 'N' COMMENT 'Hide this activity from customer',
  `caa_secondsite_error_server` char(50) DEFAULT NULL COMMENT 'When 2ndSite error activity, this is the server name',
  `caa_secondsite_error_cuino` int(11) unsigned NOT NULL COMMENT 'When 2ndSite error, item no',
  UNIQUE KEY `ix354_1` (`caa_callactivityno`),
  KEY `caa_custno` (`caa_custno`),
  KEY `contno` (`caa_contno`),
  KEY `caa_date_yearmonth` (`caa_date_yearmonth`),
  KEY `caa_callacttypeno` (`caa_callacttypeno`),
  KEY `problem_callacttypeno` (`caa_problemno`,`caa_callacttypeno`),
  KEY `date_starttime_consno` (`caa_date`,`caa_starttime`,`caa_consno`),
  KEY `date_callacttypeno` (`caa_date`,`caa_callacttypeno`),
  FULLTEXT KEY `details_full` (`reason`),
  FULLTEXT KEY `internalNotes_full` (`internalNotes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Service request activity';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_insert_callactivity` BEFORE INSERT ON `callactivity` 
    FOR EACH ROW BEGIN
	SET NEW.caa_date_yearmonth = DATE_FORMAT( NEW.caa_date , '%Y%m');
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_update_callactivity` BEFORE UPDATE ON `callactivity` 
    FOR EACH ROW BEGIN
	SET NEW.caa_date_yearmonth = DATE_FORMAT( NEW.caa_date , '%Y%m');
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `callactivity_archive`
--

DROP TABLE IF EXISTS `callactivity_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='OBSOLETE  remove reference in BUActivity line 3100 & delete';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `callacttype`
--

DROP TABLE IF EXISTS `callacttype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callacttype` (
  `cat_callacttypeno` int(11) NOT NULL DEFAULT '0',
  `cat_desc` char(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Description',
  `cat_ooh_multiplier` decimal(5,2) DEFAULT NULL COMMENT 'Cost Multiplier to apply when activity is Out of Office Hours',
  `cat_itemno` int(11) DEFAULT NULL COMMENT 'PK to underlying item',
  `cat_min_hours` decimal(5,2) DEFAULT NULL COMMENT 'Minumum charge hours',
  `cat_max_hours` decimal(5,2) DEFAULT NULL COMMENT 'Maximum charge hours',
  `cat_req_check_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Requires manual checking (especially for T&M)',
  `cat_allow_exp_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Users may log expense claims against this',
  `cat_problem_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `cat_action_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `cat_resolve_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `cat_r_problem_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `cat_r_action_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `cat_r_resolve_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `allowSCRFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N' COMMENT 'Allow On-site activity',
  `curValueFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N' COMMENT 'Currency value only (used for prepay topup)',
  `customerEmailFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'Y' COMMENT 'Send Emails to customer',
  `travelFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'N' COMMENT 'Travel type of activity',
  `activeFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT 'Y' COMMENT 'Is this activity type in use?',
  `showNotChargeableFlag` char(1) NOT NULL DEFAULT 'Y' COMMENT 'On customer activity emails, show if not chargeable',
  `engineerOvertimeFlag` char(1) NOT NULL DEFAULT 'Y' COMMENT 'Allow overtime against this activity',
  `cat_on_site_flag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Is this on site',
  `cat_portal_display_flag` char(1) NOT NULL DEFAULT 'Y' COMMENT 'SHould activity be displayed on portal',
  UNIQUE KEY `ix358_1` (`cat_callacttypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Service Request Activity type';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calldocument`
--

DROP TABLE IF EXISTS `calldocument`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calldocument` (
  `callDocumentID` int(11) NOT NULL DEFAULT '0',
  `problemID` int(11) NOT NULL DEFAULT '0' COMMENT 'PK Service Request',
  `callActivityID` int(11) NOT NULL DEFAULT '0' COMMENT 'OBSOLETE',
  `description` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Description of file',
  `filename` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'File name',
  `file` longblob NOT NULL COMMENT 'File contents (binary)',
  `fileMIMEType` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'File Mime type',
  `fileLength` int(11) NOT NULL DEFAULT '0' COMMENT 'Length Bytes',
  `createDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Create date',
  `createUserID` int(11) NOT NULL DEFAULT '0' COMMENT 'Create user',
  UNIQUE KEY `XPKDocument` (`callDocumentID`),
  KEY `problemID` (`problemID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Files uploaded to Service Requests';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultant`
--

DROP TABLE IF EXISTS `consultant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant` (
  `cns_consno` int(11) NOT NULL DEFAULT '0',
  `cns_manager` smallint(6) DEFAULT NULL COMMENT 'PK to manager user',
  `cns_name` varchar(35) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Name',
  `cns_salutation` varchar(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Salutation',
  `cns_add1` varchar(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Address line 1',
  `cns_add2` varchar(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Address line 2',
  `cns_add3` varchar(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Address line 3',
  `cns_town` varchar(25) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Town',
  `cns_county` varchar(25) CHARACTER SET utf8 DEFAULT NULL COMMENT 'County',
  `cns_postcode` varchar(15) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Postcode',
  `cns_logname` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Login username',
  `cns_employee_no` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Employee number',
  `cns_petrol_rate` decimal(5,2) DEFAULT NULL COMMENT 'Petrol mileage rate',
  `cns_hourly_pay_rate` decimal(5,2) DEFAULT '25.00' COMMENT 'Hourly pay',
  `cns_perms` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Permission Groups',
  `signatureFilename` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'NAme of signature file',
  `jobTitle` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Job title',
  `firstName` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'First name',
  `lastName` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Last Name',
  `activeFlag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Active user?',
  `weekdayOvertimeFlag` char(1) DEFAULT NULL COMMENT 'Gets overtime during week days?',
  `customerID` int(11) DEFAULT NULL COMMENT 'OBSOLETE',
  `cns_helpdesk_flag` char(1) DEFAULT NULL COMMENT 'Helpdesk technician',
  `teamID` int(11) unsigned NOT NULL COMMENT 'Team of user',
  `receiveSdManagerEmailFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Recieve SD Manager Emails',
  `changePriorityFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Can change SR priority',
  `appearInQueueFlag` char(1) NOT NULL DEFAULT 'Y' COMMENT 'Does this user appear in the dashboard queues?',
  `standardDayHours` decimal(4,2) unsigned NOT NULL COMMENT 'Number of hours in user''s standard day',
  `changeApproverFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Allowed to approve changes in Change Control process',
  UNIQUE KEY `ixcns_1` (`cns_consno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `con_contno` int(11) NOT NULL DEFAULT '0',
  `con_siteno` smallint(6) DEFAULT NULL COMMENT 'Default Site FK (only if Customer)',
  `con_custno` int(11) DEFAULT NULL COMMENT 'Customer PK (optional)',
  `con_suppno` int(11) DEFAULT NULL COMMENT 'Supplier PK (optional)',
  `con_title` char(10) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Title',
  `con_position` char(50) DEFAULT NULL COMMENT 'Position',
  `con_last_name` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Last name',
  `con_first_name` char(25) CHARACTER SET utf8 DEFAULT NULL COMMENT 'First name',
  `con_email` char(60) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Email address',
  `con_phone` char(25) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Phone number',
  `con_mobile_phone` char(25) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Mobile phone',
  `con_fax` char(25) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Fax',
  `con_mailshot` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Send Mailshots?',
  `con_accounts_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Is member of Accounts?',
  `con_statement_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Should receive PrePay statements?',
  `con_discontinued` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Is no longer active?',
  `con_mailflag1` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag2` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag3` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag4` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag5` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag6` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag7` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag8` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag9` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_mailflag10` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Refer to header table for meaning',
  `con_notes` char(200) DEFAULT NULL COMMENT 'Notes',
  `con_portal_password` char(10) DEFAULT NULL COMMENT 'Customer portal password',
  `con_failed_login_count` int(3) DEFAULT NULL COMMENT 'Failed portal log in count',
  `con_work_started_email_flag` char(1) DEFAULT 'Y' COMMENT 'Send Request Work Started email?',
  `con_auto_close_email_flag` char(1) DEFAULT 'Y' COMMENT 'Send Request automatically closed email?',
  UNIQUE KEY `ix_con2` (`con_contno`),
  KEY `ixcon_1` (`con_custno`,`con_siteno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Customer/Supplier Contacts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract`
--

DROP TABLE IF EXISTS `contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contract` (
  `cnt_contno` int(11) NOT NULL DEFAULT '0',
  `cnt_desc` char(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Description',
  `cnt_years` smallint(6) DEFAULT NULL COMMENT 'Years of cover',
  `cnt_manno` int(11) DEFAULT NULL COMMENT 'FK to manufacturer',
  UNIQUE KEY `ixcnt_1` (`cnt_contno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Manufacurer product warrany';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custitem`
--

DROP TABLE IF EXISTS `custitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custitem` (
  `cui_cuino` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cui_custno` int(11) NOT NULL DEFAULT '0' COMMENT 'PK Customer',
  `cui_siteno` smallint(6) NOT NULL DEFAULT '0' COMMENT 'PK Site',
  `cui_itemno` int(11) NOT NULL DEFAULT '0' COMMENT 'PK Underlying Item',
  `cui_man_contno` smallint(6) DEFAULT NULL COMMENT 'PK Manufacturer Warranty',
  `cui_contract_cuino` int(11) DEFAULT NULL COMMENT 'Contract',
  `cui_serial` varchar(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Serial No',
  `cui_cust_ref` varchar(45) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Server name',
  `cui_ordno` int(11) DEFAULT NULL COMMENT 'PK Sales Order',
  `cui_sale_price` decimal(12,2) DEFAULT NULL COMMENT 'Sale price',
  `cui_porno` int(11) DEFAULT NULL COMMENT 'PK Purchase Order',
  `cui_pord_price` decimal(12,2) DEFAULT NULL COMMENT 'Purchase price',
  `cui_cost_price` decimal(12,2) DEFAULT NULL COMMENT 'Cost price',
  `cui_users` smallint(6) DEFAULT NULL COMMENT 'Number of users',
  `cui_ord_date` date DEFAULT NULL COMMENT 'OBSOLETE',
  `cui_expiry_date` date DEFAULT NULL COMMENT 'Contract expiry date',
  `curGSCBalance` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Prepay balance',
  `renewalStatus` char(1) CHARACTER SET utf8 DEFAULT 'R' COMMENT 'Contract Renewal status [D]eclined, [R]enewed',
  `renewalOrdheadID` int(11) DEFAULT '0' COMMENT 'PK Sales order of renewal',
  `itemNotes` text CHARACTER SET utf8 COMMENT 'Contract Details (used on printed contract)',
  `cui_prepay_balance` decimal(6,2) DEFAULT NULL COMMENT 'OBSOLETE',
  `cui_sales_order_status` enum('','Q','I','C') DEFAULT NULL COMMENT 'Which type of sales order to create when billing',
  `renewalDate` date DEFAULT NULL COMMENT 'Date renewed',
  `customerName` char(50) DEFAULT NULL COMMENT 'Customer Name (useage to be verified)',
  `customerID` int(11) unsigned DEFAULT NULL COMMENT 'PK Customer',
  `itemID` int(11) unsigned DEFAULT NULL COMMENT 'PK Underlying Item',
  `customerItemID` int(11) unsigned DEFAULT NULL COMMENT 'OBSOLETE',
  `months` int(10) DEFAULT NULL COMMENT 'Contract months already  billed',
  `ordheadID` char(10) DEFAULT NULL COMMENT 'OBSOLETE',
  `broadbandServiceType` char(50) DEFAULT NULL COMMENT 'OBSOLETE',
  `broadbandServiceTypeID` int(11) DEFAULT NULL COMMENT 'OBSOLETE',
  `adslPhone` varchar(255) DEFAULT NULL COMMENT 'Internet service contract phone line',
  `fee` double DEFAULT NULL COMMENT 'Contract cost per month',
  `macCode` varchar(255) DEFAULT NULL COMMENT 'Internet MAC code',
  `batchNo` char(50) DEFAULT NULL COMMENT 'OBSOLETE',
  `reference` varchar(255) DEFAULT NULL COMMENT 'OSOLETE',
  `defaultGateway` char(50) DEFAULT NULL COMMENT 'Hosting domain name',
  `networkAddress` char(50) DEFAULT NULL COMMENT 'Internet network address',
  `subnetMask` char(50) DEFAULT NULL COMMENT 'Internet sub-net mask',
  `routerIPAddress` text COMMENT 'Internet IP addresses',
  `userName` varchar(255) DEFAULT NULL COMMENT 'Hosting/internet username',
  `password` varchar(255) DEFAULT NULL COMMENT 'Hosting/Internet password',
  `etaDate` date DEFAULT NULL COMMENT 'Internet install ETA date',
  `installationDate` date DEFAULT NULL COMMENT 'Internet actual install date',
  `costPerAnnum` double DEFAULT NULL COMMENT 'Sale price per annum',
  `salePricePerMonth` decimal(6,2) DEFAULT NULL COMMENT 'Sales Price Per Month',
  `costPricePerMonth` decimal(6,2) DEFAULT NULL COMMENT 'Cost per month',
  `ispID` varchar(255) DEFAULT NULL COMMENT 'Internet ISP',
  `requiresChangesFlag` varchar(1) DEFAULT NULL COMMENT 'OBSOLETE',
  `dualBroadbandFlag` varchar(1) DEFAULT NULL COMMENT 'Internet Dual Broadband?',
  `dnsCompany` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `ipCurrentNo` char(50) DEFAULT NULL COMMENT 'OBSOLETE',
  `mx` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `secureServer` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `vpns` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `owa` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `oma` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `remotePortal` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `smartHost` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `preparationRecords` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `assignedTo` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `initialSpeedTest` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `preMigrationNotes` longblob COMMENT 'OBSOLETE',
  `postMigrationNotes` longblob COMMENT 'OBSOLETE',
  `docsUpdatedAndChecksCompleted` varchar(255) DEFAULT NULL COMMENT 'OBSOLETE',
  `invoicePeriodMonths` int(4) DEFAULT NULL COMMENT 'Number of months between invoices',
  `totalInvoiceMonths` int(11) DEFAULT NULL COMMENT 'Number of months to add to install date to calculate next invoice due date',
  `declinedFlag` char(1) DEFAULT NULL COMMENT 'Contract declied by customer?',
  `hostingCompany` char(100) DEFAULT NULL COMMENT 'Hosting provider',
  `osPlatform` char(50) DEFAULT NULL COMMENT 'Hosting server OS',
  `domainNames` char(100) DEFAULT NULL COMMENT 'OBSOLETE',
  `controlPanelUrl` char(50) DEFAULT NULL COMMENT 'Hosting control panel URL',
  `ftpAddress` char(50) DEFAULT NULL COMMENT 'Hosting FTP server',
  `ftpUsername` char(50) DEFAULT NULL COMMENT 'Hosting FTP username',
  `wwwAddress` char(50) DEFAULT NULL COMMENT 'OBSOLETE',
  `websiteDeveloper` char(50) DEFAULT NULL COMMENT 'OBSOLETE',
  `dateGenerated` date DEFAULT '0000-00-00' COMMENT 'Contract date of last Sales Order/Invoice',
  `startDate` date DEFAULT NULL COMMENT 'Contract start of billing date',
  `salePrice` decimal(6,2) DEFAULT NULL COMMENT 'Sale price for billing',
  `costPrice` decimal(6,2) DEFAULT NULL COMMENT 'Cost price',
  `qty` int(3) DEFAULT NULL COMMENT 'Quotation renewal unit qty (e.g. Antivirus users)',
  `renQuotationTypeID` int(11) DEFAULT NULL COMMENT 'PK Quotation contract type',
  `comment` char(50) DEFAULT NULL COMMENT 'Contract internal comments',
  `grantNumber` char(50) DEFAULT NULL COMMENT 'Quotation renewal Details field',
  `notes` text COMMENT 'Quotation renewal Notes (seen by customer)',
  `cui_consno` smallint(6) DEFAULT NULL COMMENT 'User who originaly despatched sales order',
  `cui_ctactno` smallint(6) DEFAULT NULL COMMENT 'Contact who ordered contract',
  `cui_desp_date` date DEFAULT NULL COMMENT 'Sales order despatch date',
  `cui_pord_date` date DEFAULT NULL COMMENT 'Purchase order date',
  `cui_ref_cust` varchar(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Sales order Cust ref',
  `cui_sla_response_hours` int(3) DEFAULT NULL COMMENT 'OBSOLETE',
  `hostingUserName` char(50) DEFAULT NULL COMMENT 'Hosting username',
  `cui_internal_notes` text COMMENT 'Contract internal notes',
  `bandwidthAllowance` char(10) DEFAULT NULL COMMENT 'Broadband Bandwidth Allowance',
  `secondsiteLocationPath` char(150) DEFAULT NULL COMMENT 'Network path to image',
  `secondsiteStorageUsedGb` int(3) DEFAULT NULL COMMENT 'Storage used in Mb',
  `secondsiteServerDriveLetters` char(100) DEFAULT NULL COMMENT 'Comma-sep list of drives',
  `secondsiteValidationSuspendUntilDate` date DEFAULT NULL COMMENT 'Suspend 2nd site validation until this date',
  `secondsiteSuspendedByUserID` int(11) DEFAULT '0' COMMENT 'User that last suspended',
  `secondsiteSuspendedDate` date DEFAULT NULL COMMENT 'Date last suspended',
  `secondsiteImageDelayDays` int(3) DEFAULT NULL COMMENT 'Delay image date check by this many days',
  `secondsiteImageDelayUserID` int(11) DEFAULT NULL COMMENT 'User that last set image delay',
  `secondsiteImageDelayDate` date DEFAULT NULL COMMENT 'Date that image delay was last set on',
  `secondsiteLocalExcludeFlag` char(1) DEFAULT 'N' COMMENT 'Exclude local server from 2ndSite checks',
  `autoGenerateContractInvoice` char(1) DEFAULT 'N' COMMENT 'Indicates if Contract Renewal automatically generates an invoice or just a sales order',
  UNIQUE KEY `ixcui_1` (`cui_cuino`),
  KEY `ix_cui2` (`cui_custno`,`cui_siteno`,`cui_cuino`),
  KEY `renewalOrdheadID` (`renewalOrdheadID`),
  KEY `cui_itemno` (`cui_itemno`),
  KEY `cui_custno` (`cui_custno`,`renewalStatus`,`declinedFlag`)
) ENGINE=MyISAM AUTO_INCREMENT=49281 DEFAULT CHARSET=latin1 COMMENT='Product or service owned by customer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custitem_contract`
--

DROP TABLE IF EXISTS `custitem_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custitem_contract` (
  `cic_custitemcontractno` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cic_cuino` int(10) unsigned NOT NULL,
  `cic_contractcuino` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cic_custitemcontractno`),
  UNIQUE KEY `cic_cuino` (`cic_cuino`,`cic_contractcuino`)
) ENGINE=MyISAM AUTO_INCREMENT=2672 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer` (
  `cus_custno` int(11) NOT NULL DEFAULT '0',
  `cus_name` varchar(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Name',
  `cus_reg_no` varchar(10) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Company Registraction Number',
  `cus_inv_siteno` smallint(6) DEFAULT NULL COMMENT 'Default invoice site',
  `cus_del_siteno` smallint(6) DEFAULT NULL COMMENT 'Default delivery site',
  `cus_mailshot` varchar(2) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Send mailshots to contacts?',
  `cus_create_date` date DEFAULT NULL COMMENT 'Customer first created',
  `cus_referred` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Referred status?',
  `cus_pcx` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `cus_ctypeno` int(11) DEFAULT NULL COMMENT 'How this customer was obtained',
  `cus_prospect` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Is this a prospect?',
  `cus_others_email_main_flag` char(1) DEFAULT 'Y' COMMENT 'OBSOLETE',
  `cus_work_started_email_main_flag` char(1) DEFAULT 'Y' COMMENT 'Should work stared emails be sent from service system?',
  `cus_auto_close_email_main_flag` char(1) DEFAULT 'Y' COMMENT 'Email main contact?',
  `cus_became_customer_date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date became a customer',
  `cus_dropped_customer_date` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date dropped as customer',
  `cus_leadstatusno` int(11) unsigned DEFAULT NULL COMMENT 'PK prospect lead status',
  `gscTopUpAmount` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Amount for PrePay top-ups',
  `modifyDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Modified datetime',
  `noOfPCs` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '0' COMMENT 'Prospect: Number of PCs',
  `noOfServers` smallint(3) NOT NULL DEFAULT '0' COMMENT 'Prospect: Number of Servers',
  `noOfSites` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Prospect: Number of sites',
  `comments` text COMMENT 'Comments',
  `reviewDate` date DEFAULT NULL COMMENT 'Prospect next review',
  `reviewTime` char(5) DEFAULT NULL COMMENT 'Prospect next review',
  `reviewAction` longtext COMMENT 'What to do on next review',
  `reviewUserID` int(11) DEFAULT NULL COMMENT 'Who should review',
  `modifyUserID` int(11) DEFAULT NULL COMMENT 'Record modified by',
  `cus_sectorno` int(11) unsigned DEFAULT NULL COMMENT 'Organisation business sector',
  `cus_tech_notes` char(100) DEFAULT NULL COMMENT 'Notes to appear on all request screens',
  `cus_special_attention_flag` char(1) DEFAULT 'N' COMMENT 'Customer is special attention',
  `cus_special_attention_end_date` date DEFAULT NULL COMMENT 'Special attention period end',
  `cus_support_24_hour_flag` char(1) DEFAULT 'N' COMMENT 'Support is 24 hours',
  `cus_sla_p1` decimal(4,1) DEFAULT NULL COMMENT 'Support P1 response hours',
  `cus_sla_p2` decimal(4,1) DEFAULT NULL COMMENT 'Support P2 response hours',
  `cus_sla_p3` decimal(4,1) DEFAULT NULL COMMENT 'Support P3 response hours',
  `cus_sla_p4` decimal(4,1) DEFAULT NULL COMMENT 'Support P4 response hours',
  `cus_sla_p5` decimal(4,1) DEFAULT NULL COMMENT 'Support P5 response hours',
  `cus_send_contract_email` char(100) DEFAULT '' COMMENT 'If set then send an email of conttracts to email address',
  `cus_send_tandc_email` char(100) DEFAULT NULL COMMENT 'If set then send T&C email to portal user',
  `cus_last_review_meeting_date` date DEFAULT NULL COMMENT 'Date of last review meeting',
  `cus_review_meeting_frequency_months` int(3) DEFAULT '6' COMMENT 'Frequency of review meetings',
  `cus_review_meeting_email_sent_flag` char(1) DEFAULT 'N' COMMENT 'Has a meeting reminder been sent?',
  `cus_account_manager_consno` int(11) unsigned DEFAULT NULL COMMENT 'Account manager user',
  UNIQUE KEY `ixcus_1` (`cus_custno`),
  KEY `cus_sectorno` (`cus_sectorno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customeritemdocument`
--

DROP TABLE IF EXISTS `customeritemdocument`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='OBSOLETE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customernote`
--

DROP TABLE IF EXISTS `customernote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customernote` (
  `cno_customernoteno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cno_custno` int(11) NOT NULL COMMENT 'Customer',
  `cno_created` datetime DEFAULT NULL COMMENT 'Created date',
  `cno_modified` datetime DEFAULT NULL COMMENT 'Modified date',
  `cno_modified_consno` int(11) unsigned NOT NULL COMMENT 'Modified by',
  `cno_details` text COMMENT 'Details of action',
  `cno_created_consno` int(11) unsigned NOT NULL COMMENT 'Created by',
  `cno_ordno` int(11) unsigned DEFAULT NULL COMMENT 'Sales Order',
  PRIMARY KEY (`cno_customernoteno`)
) ENGINE=MyISAM AUTO_INCREMENT=33249 DEFAULT CHARSET=latin1 COMMENT='Sales activity for prospects';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customerproblem`
--

DROP TABLE IF EXISTS `customerproblem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=28972 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custype`
--

DROP TABLE IF EXISTS `custype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custype` (
  `cty_ctypeno` smallint(6) NOT NULL DEFAULT '0',
  `cty_desc` char(40) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `date_xref`
--

DROP TABLE IF EXISTS `date_xref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_xref` (
  `date_field` date NOT NULL,
  `is_bank_holiday` char(1) DEFAULT 'N',
  PRIMARY KEY (`date_field`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Bank holiday dates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_sequence`
--

DROP TABLE IF EXISTS `db_sequence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_sequence` (
  `seq_name` varchar(127) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'table name',
  `nextid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Next PK value',
  PRIMARY KEY (`seq_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Source of internal database IDs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `delivery`
--

DROP TABLE IF EXISTS `delivery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery` (
  `del_delno` int(11) NOT NULL DEFAULT '0',
  `del_desc` char(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Delivery description',
  `del_send_note` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Send note?',
  UNIQUE KEY `ix141_1` (`del_delno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Delivery methods for sales orders';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deliverynote`
--

DROP TABLE IF EXISTS `deliverynote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliverynote` (
  `deliveryNoteID` int(11) NOT NULL DEFAULT '0',
  `ordheadID` int(11) NOT NULL DEFAULT '0' COMMENT 'Sales Order',
  `noteNo` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Delivery note sequence no (starts at 1)',
  `dateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date/time shipped',
  PRIMARY KEY (`deliveryNoteID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Sales order delivery note';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deshead`
--

DROP TABLE IF EXISTS `deshead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deshead` (
  `deh_desno` int(11) NOT NULL DEFAULT '0',
  `deh_ordno` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Sales order',
  `deh_custno` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Customer',
  `deh_invno` int(11) DEFAULT NULL COMMENT 'Sales Invoice',
  `deh_ref_cust` char(23) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Customer Ref',
  `deh_ref_ecc` char(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'CNC Ref',
  `deh_method` char(12) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Delivery Method',
  `deh_date` date DEFAULT NULL COMMENT 'Despatch date',
  UNIQUE KEY `ix130_1` (`deh_desno`),
  KEY `ix132_5` (`deh_ref_cust`),
  KEY `ix135_6` (`deh_ref_ecc`),
  KEY `ixdeh_1` (`deh_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Sales order despatches';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `desline`
--

DROP TABLE IF EXISTS `desline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `desline` (
  `del_desno` smallint(6) NOT NULL DEFAULT '0',
  `del_line_no` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Despatch sequence no',
  `del_ord_line_no` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Sales order line no',
  `del_qty` decimal(7,2) DEFAULT NULL COMMENT 'Qty despatched now',
  KEY `ix131_1` (`del_desno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `domain_import`
--

DROP TABLE IF EXISTS `domain_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domain_import` (
  `domain` char(100) DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `customerID` int(11) DEFAULT NULL,
  `invoicePeriodMonths` int(3) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='OBSOLETE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `escalation`
--

DROP TABLE IF EXISTS `escalation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation` (
  `escalationID` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',
  `description` char(50) NOT NULL COMMENT 'Escalation description',
  `activeFlag` char(1) NOT NULL DEFAULT 'Y' COMMENT 'Allowed for selection',
  PRIMARY KEY (`escalationID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expense`
--

DROP TABLE IF EXISTS `expense`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense` (
  `exp_expenseno` int(11) NOT NULL DEFAULT '0',
  `exp_callactivityno` int(11) NOT NULL DEFAULT '0' COMMENT 'Service request activity',
  `exp_expensetypeno` int(11) NOT NULL DEFAULT '0' COMMENT 'FK to expense type',
  `exp_mileage` int(11) DEFAULT NULL COMMENT 'Miles claimed',
  `exp_value` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Value claimed',
  `exp_vat_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'VAT included?',
  `exp_exported_flag` char(1) DEFAULT 'N' COMMENT 'indicates whether this expense has been exported to the file',
  UNIQUE KEY `ix402_1` (`exp_expenseno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expensetype`
--

DROP TABLE IF EXISTS `expensetype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expensetype` (
  `ext_expensetypeno` int(11) NOT NULL DEFAULT '0',
  `ext_desc` char(80) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Description',
  `ext_mileage_flag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Is this mileage?',
  `ext_vat_flag` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Include VAT by default?',
  UNIQUE KEY `ix401_1` (`ext_expensetypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Expense claim type';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `externalitem`
--

DROP TABLE IF EXISTS `externalitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `externalitem` (
  `externalitemID` int(11) unsigned NOT NULL,
  `customerID` int(11) unsigned NOT NULL COMMENT 'customer',
  `itemTypeID` int(11) NOT NULL COMMENT 'category of item',
  `description` char(50) NOT NULL COMMENT 'description of item',
  `notes` longtext COMMENT 'Notes about this item',
  `licenceRenewalDate` date DEFAULT NULL COMMENT 'Date item is due for renewal',
  PRIMARY KEY (`externalitemID`),
  KEY `itemTypeID` (`itemTypeID`),
  KEY `customerID` (`customerID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `further_action`
--

DROP TABLE IF EXISTS `further_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `further_action` (
  `furtherActionID` int(6) unsigned NOT NULL DEFAULT '0',
  `description` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `emailAddress` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `requireDate` char(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  `emailBody` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`furtherActionID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='OBSOLETE. Remove application refs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `future_action`
--

DROP TABLE IF EXISTS `future_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `future_action` (
  `futureActionID` int(11) unsigned NOT NULL DEFAULT '0',
  `furtherActionID` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `callActivityID` varchar(11) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `engineerName` char(50) DEFAULT NULL,
  `dateCreated` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='OBSOLETE: Remove application refs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `headert`
--

DROP TABLE IF EXISTS `headert`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `headert` (
  `headerID` tinyint(4) NOT NULL DEFAULT '0',
  `hed_name` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Company name',
  `hed_add1` char(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Address1',
  `hed_add2` char(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Add2',
  `hed_add3` char(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Add3',
  `hed_town` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Town',
  `hed_county` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'County',
  `hed_postcode` char(15) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Postcode',
  `hed_phone` char(20) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Phone',
  `hed_fax` char(20) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Fax',
  `hed_goods_contact` char(35) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Goods In Contact',
  `hed_sstk_suppno` smallint(6) DEFAULT NULL COMMENT 'FK to Sales Stock Supplier',
  `hed_mstk_suppno` smallint(6) DEFAULT NULL COMMENT 'PK to Manufacturing Stock Supplier',
  `hed_std_vatcode` char(2) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Standard VAT code',
  `hed_car_stockcat` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_next_porno` smallint(6) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_next_itemno` smallint(6) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_next_invno` int(11) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_sstk_locno` smallint(6) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_mstk_locno` smallint(6) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_ecc_ass_locno` smallint(6) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_ecc_op_locno` smallint(6) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_invoice_prt` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_porder_prt` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_plaser_prt` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_llaser_prt` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_system_prt` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_audit_prt` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_bill_starttime` char(5) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Billing start time',
  `hed_bill_endtime` char(5) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Billing end time HH:MM',
  `hed_hd_starttime` char(5) DEFAULT NULL COMMENT 'Help Desk Start time',
  `hed_hd_endtime` char(5) DEFAULT NULL COMMENT 'Help Desk end time',
  `hed_pro_starttime` char(5) DEFAULT NULL COMMENT 'Project work start time',
  `hed_pro_endtime` char(5) DEFAULT NULL COMMENT 'Project work end time',
  `hed_gensup_itemno` int(11) DEFAULT NULL COMMENT 'FK to Pre-pay Contract item',
  `hed_portal_pin` char(5) NOT NULL COMMENT 'Customer Portal PIN',
  `hed_next_schedno` int(11) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_ot_adjust_hour` decimal(5,2) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_mailflg1_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg2_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg3_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg4_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg5_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg6_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg7_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg8_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg9_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg10_def` char(1) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Default Value',
  `hed_mailflg1_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg2_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg3_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg4_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg5_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg6_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg7_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg8_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg9_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_mailflg10_desc` char(30) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Contact Flag Description',
  `hed_helpdesk_problems` text COMMENT 'Daily helpdesk problems',
  `hed_helpdesk_os_count` int(5) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_helpdesk_os_service_desk_count` int(5) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_helpdesk_os_servercare_count` int(5) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_helpdesk_os_prepay_count` int(5) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_helpdesk_os_escalation_count` int(5) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_helpdesk_os_cust_response_count` int(5) DEFAULT NULL COMMENT 'OBSOLETE',
  `hed_hourly_labour_cost` decimal(5,2) DEFAULT NULL COMMENT 'Cost to CNC',
  `hed_portal_24_hour_pin` char(5) NOT NULL COMMENT '24 hour support PIN',
  `hed_high_activity_alert_count` int(3) DEFAULT NULL COMMENT 'Number of activities per day per request triggers an alert email',
  `hed_priority_1_desc` char(50) NOT NULL COMMENT 'Priority 1 description',
  `hed_priority_2_desc` char(50) NOT NULL COMMENT 'Priority 2 description',
  `hed_priority_3_desc` char(50) NOT NULL,
  `hed_priority_4_desc` char(50) NOT NULL,
  `hed_priority_5_desc` char(50) NOT NULL,
  `hed_sr_import_attachment_directory` char(100) NOT NULL COMMENT 'Network path to SR import email attachments',
  `hed_allowed_client_ip_pattern` char(100) NOT NULL COMMENT 'regx pattern for allowed client IP',
  `hed_hd_team_limit_hours` decimal(5,2) NOT NULL COMMENT 'Default time limit for solving problems by helpdesk team',
  `hed_es_team_limit_hours` decimal(5,2) NOT NULL COMMENT 'Default time limit for escalations team to solve SR',
  `hed_im_team_limit_hours` decimal(5,2) NOT NULL COMMENT 'Default time linit for implementations team to solve SR',
  `hed_hd_team_max_pause_count` int(1) unsigned NOT NULL COMMENT 'Maximum no of times activity may be paused by member of SD team',
  `hed_hd_team_pause_seconds` int(3) unsigned NOT NULL COMMENT 'Length of activity pause in seconds',
  `hed_hd_team_target_log_percentage` int(3) unsigned NOT NULL COMMENT 'Help Desk team target percentage of time to be logged per day',
  `hed_es_team_target_log_percentage` int(3) unsigned NOT NULL COMMENT 'Escalations team target percentage of time to be logged per day',
  `hed_im_team_target_log_percentage` int(3) NOT NULL COMMENT 'Implementation team target % of time to be logged per day',
  `hed_hd_team_target_sla_percentage` int(3) unsigned NOT NULL COMMENT 'HD team target response within SLA percentage',
  `hed_es_team_target_sla_percentage` int(3) unsigned NOT NULL COMMENT 'ES team target response within SLA percentage',
  `hed_im_team_target_sla_percentage` int(3) unsigned NOT NULL,
  `hed_hd_team_target_fix_hours` int(3) unsigned NOT NULL COMMENT 'HD team target fix hours',
  `hed_es_team_target_fix_hours` int(3) unsigned NOT NULL COMMENT 'ES team target fix hours',
  `hed_im_team_target_fix_hours` int(3) unsigned NOT NULL,
  `hed_hd_team_target_fix_qty_per_month` int(3) unsigned NOT NULL COMMENT 'HD team target fix qty',
  `hed_es_team_target_fix_qty_per_month` int(3) unsigned NOT NULL COMMENT 'ES team target fix qty',
  `hed_im_team_target_fix_qty_per_month` int(3) unsigned NOT NULL,
  `hed_sr_autocomplete_threshold_hours` decimal(5,2) unsigned NOT NULL COMMENT 'Hours under which SRs get automatically completed',
  `hed_sr_prompt_contract_threshold_hours` decimal(5,2) unsigned NOT NULL COMMENT 'Hours under which to prompt for a contract when fixing SRs above P3',
  `hed_remote_support_warn_hours` decimal(5,2) unsigned NOT NULL COMMENT 'Hours over which on remote support activity to warn user',
  `hed_customer_contact_warn_hours` decimal(5,2) unsigned NOT NULL COMMENT 'Hours over which on remote customer contact activity to warn user',
  PRIMARY KEY (`headerID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='System header';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invhead`
--

DROP TABLE IF EXISTS `invhead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invhead` (
  `inh_invno` int(11) NOT NULL DEFAULT '0',
  `inh_custno` int(11) NOT NULL DEFAULT '0' COMMENT 'FK to customer',
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
  `inh_date_printed_yearmonth` char(6) DEFAULT NULL,
  `inh_pdf_file` longblob,
  UNIQUE KEY `ix115_1` (`inh_invno`),
  KEY `ixinh_2` (`inh_ordno`),
  KEY `inh_date_printed` (`inh_date_printed`),
  KEY `custno` (`inh_custno`),
  KEY `contno` (`inh_contno`),
  KEY `paymentTerms` (`paymentTermsID`),
  KEY `inh_date_printed_yearmonth` (`inh_date_printed_yearmonth`),
  KEY `inh_type` (`inh_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Invoice header';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `invhead_date_yearmonth_insert` BEFORE INSERT ON `invhead` 
    FOR EACH ROW BEGIN
	SET NEW.inh_date_printed_yearmonth = DATE_FORMAT( NEW.inh_date_printed , '%Y%m');
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `invhead_date_yearmonth_update` BEFORE UPDATE ON `invhead` 
    FOR EACH ROW BEGIN
	SET NEW.inh_date_printed_yearmonth = DATE_FORMAT( NEW.inh_date_printed , '%Y%m');
    END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `invline`
--

DROP TABLE IF EXISTS `invline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `inl_itemno` (`inl_itemno`),
  KEY `inl_line_type` (`inl_line_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  UNIQUE KEY `ixitm_1` (`itm_itemno`),
  KEY `itm_itemtypeno` (`itm_itemtypeno`),
  KEY `renewalTypeID` (`renewalTypeID`),
  FULLTEXT KEY `itm_desc` (`itm_desc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemtype`
--

DROP TABLE IF EXISTS `itemtype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemtype` (
  `ity_itemtypeno` int(11) NOT NULL DEFAULT '0',
  `ity_desc` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `ity_stockcat` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`ity_itemtypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leadstatus`
--

DROP TABLE IF EXISTS `leadstatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leadstatus` (
  `lst_leadstatusno` int(11) unsigned NOT NULL,
  `lst_desc` char(50) DEFAULT NULL,
  PRIMARY KEY (`lst_leadstatusno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail_queue`
--

DROP TABLE IF EXISTS `mail_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `time_started_sending` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `time_to_send` (`time_to_send`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail_queue_seq`
--

DROP TABLE IF EXISTS `mail_queue_seq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_queue_seq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1191057 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mailshot_table`
--

DROP TABLE IF EXISTS `mailshot_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mailshot_table` (
  `keyfield` char(50) DEFAULT NULL,
  `hits` int(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `main_contacts`
--

DROP TABLE IF EXISTS `main_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `main_contacts` (
  `ContactFirstName` char(25) DEFAULT NULL,
  `ContactLastName` char(35) DEFAULT NULL,
  `ContactEmail` char(60) DEFAULT NULL,
  `CustomerName` varchar(35) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `manufact`
--

DROP TABLE IF EXISTS `manufact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manufact` (
  `man_manno` int(11) NOT NULL DEFAULT '0',
  `man_name` char(35) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `man_disc_rate` decimal(4,2) DEFAULT NULL,
  UNIQUE KEY `ixman_1` (`man_manno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notepad`
--

DROP TABLE IF EXISTS `notepad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notepad` (
  `not_type` varchar(3) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `not_key` int(11) NOT NULL DEFAULT '0',
  `not_line` tinyint(4) NOT NULL DEFAULT '0',
  `not_text` varchar(76) CHARACTER SET utf8 DEFAULT NULL,
  KEY `ix_not1` (`not_type`,`not_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='OBSOLETE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordhead`
--

DROP TABLE IF EXISTS `ordhead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ordhead` (
  `odh_ordno` int(11) NOT NULL DEFAULT '0',
  `odh_custno` int(11) DEFAULT NULL,
  `odh_type` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_part_invoice` char(1) CHARACTER SET utf8 DEFAULT NULL,
  `odh_date` date DEFAULT NULL,
  `odh_req_date` date DEFAULT NULL,
  `odh_prom_date` date DEFAULT NULL,
  `odh_expect_date` date DEFAULT NULL,
  `odh_quotation_ordno` int(11) DEFAULT NULL,
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
  `odh_quotation_create_date` date DEFAULT NULL COMMENT 'Date that original quotation created',
  UNIQUE KEY `ixodh_1` (`odh_ordno`),
  KEY `ixodh_2` (`odh_type`),
  KEY `ixodh_3` (`odh_date`),
  KEY `ixodh_4` (`odh_custno`),
  KEY `ixodh_5` (`odh_quotation_ordno`),
  KEY `ixodh_6` (`odh_ref_cust`),
  KEY `ixodh_7` (`odh_custno`,`odh_type`),
  KEY `ixodh_10` (`odh_custno`,`odh_date`),
  KEY `paymentTerms` (`paymentTermsID`),
  KEY `quotationCreateDate` (`odh_quotation_create_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordhead_sco`
--

DROP TABLE IF EXISTS `ordhead_sco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordline`
--

DROP TABLE IF EXISTS `ordline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ordline` (
  `odl_ordlineno` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK so that phpMyAdmin can edit rows',
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
  PRIMARY KEY (`odl_ordlineno`),
  KEY `idxodl_2` (`odl_suppno`,`odl_desc`),
  KEY `idodl_3` (`odl_custno`,`odl_desc`),
  KEY `odl_itemno` (`odl_itemno`),
  KEY `ix_ordno` (`odl_ordno`),
  FULLTEXT KEY `odl_desc_fulltext` (`odl_desc`)
) ENGINE=MyISAM AUTO_INCREMENT=418514 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ordline_sco`
--

DROP TABLE IF EXISTS `ordline_sco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_view`
--

DROP TABLE IF EXISTS `page_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password`
--

DROP TABLE IF EXISTS `password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password` (
  `pas_passwordno` int(11) unsigned NOT NULL,
  `pas_custno` int(11) unsigned NOT NULL,
  `pas_username` char(100) DEFAULT NULL,
  `pas_service` mediumtext,
  `pas_password` mediumtext,
  `pas_notes` mediumtext,
  PRIMARY KEY (`pas_passwordno`),
  KEY `pas_custno` (`pas_custno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paymentterms`
--

DROP TABLE IF EXISTS `paymentterms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentterms` (
  `paymentTermsID` int(11) NOT NULL DEFAULT '0',
  `description` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `days` tinyint(4) NOT NULL DEFAULT '0',
  `generateInvoiceFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `automaticInvoiceFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`paymentTermsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paymeth`
--

DROP TABLE IF EXISTS `paymeth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pinline`
--

DROP TABLE IF EXISTS `pinline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `porhead`
--

DROP TABLE IF EXISTS `porhead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `porline`
--

DROP TABLE IF EXISTS `porline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `pol_itemno` (`pol_itemno`),
  KEY `pol_porno` (`pol_porno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `portal_customer_document`
--

DROP TABLE IF EXISTS `portal_customer_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_customer_document` (
  `portalCustomerDocumentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customerID` int(11) NOT NULL,
  `description` char(100) NOT NULL COMMENT 'Description of the document',
  `filename` char(100) NOT NULL COMMENT 'Name of file',
  `file` longblob NOT NULL COMMENT 'The binary',
  `fileMimeType` char(50) NOT NULL COMMENT 'Mime type for display',
  `startersFormFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Starters form',
  `leaversFormFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Leavers form?',
  `mainContactOnlyFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'View by Main Contact only?',
  `createdDate` datetime NOT NULL,
  `createdUserID` int(11) NOT NULL,
  PRIMARY KEY (`portalCustomerDocumentID`),
  KEY `customerID` (`customerID`)
) ENGINE=MyISAM AUTO_INCREMENT=529 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `portal_document`
--

DROP TABLE IF EXISTS `portal_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_document` (
  `portalDocumentID` int(10) unsigned NOT NULL DEFAULT '0',
  `description` char(100) NOT NULL COMMENT 'Description of the document',
  `filename` char(100) NOT NULL COMMENT 'Name of file',
  `file` longblob NOT NULL COMMENT 'The binary',
  `fileMimeType` char(50) NOT NULL COMMENT 'Mime type for display',
  `mainContactOnlyFlag` char(1) NOT NULL DEFAULT 'N' COMMENT 'View by Main Contact only?',
  `requiresAcceptanceFlag` char(1) DEFAULT 'N' COMMENT 'Requires acceptance by main contact?',
  `createdDate` datetime NOT NULL,
  `createdUserID` int(11) NOT NULL,
  PRIMARY KEY (`portalDocumentID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `portal_document_acceptance`
--

DROP TABLE IF EXISTS `portal_document_acceptance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portal_document_acceptance` (
  `portalDocumentAcceptanceID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `portalDocumentID` int(10) unsigned NOT NULL,
  `customerID` int(11) NOT NULL COMMENT 'Customer that accepted',
  `acceptedDate` datetime DEFAULT NULL COMMENT 'Date accepted',
  `acceptedByContactID` int(11) DEFAULT NULL COMMENT 'Person that accepted',
  PRIMARY KEY (`portalDocumentAcceptanceID`)
) ENGINE=MyISAM AUTO_INCREMENT=253 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prepaystatement`
--

DROP TABLE IF EXISTS `prepaystatement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prepaystatement` (
  `pre_prepayno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pre_custno` int(11) unsigned DEFAULT NULL,
  `pre_date` date DEFAULT NULL,
  `pre_balance` decimal(8,2) DEFAULT NULL,
  `pre_file` longblob,
  PRIMARY KEY (`pre_prepayno`)
) ENGINE=MyISAM AUTO_INCREMENT=5346 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prizewinner`
--

DROP TABLE IF EXISTS `prizewinner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prizewinner` (
  `prz_prizewinnerno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `prz_yearmonth` char(7) NOT NULL COMMENT 'YYYY-MM',
  `prz_contno` int(11) NOT NULL COMMENT 'Customer contact that woin',
  `prz_approved_flag` char(1) DEFAULT 'N' COMMENT 'has the prize been approved?',
  `prz_survey_name` char(50) DEFAULT NULL COMMENT 'name entered on the survey',
  PRIMARY KEY (`prz_prizewinnerno`)
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `problem`
--

DROP TABLE IF EXISTS `problem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problem` (
  `pro_problemno` int(11) unsigned NOT NULL DEFAULT '0',
  `pro_custno` int(11) NOT NULL DEFAULT '0' COMMENT 'Customer FK to customer.cus_custno',
  `pro_priority` int(3) DEFAULT NULL COMMENT '1 to 5',
  `pro_consno` int(11) DEFAULT NULL COMMENT 'Allocated User. FK to consultant.cns_consno',
  `pro_status` enum('I','P','F','C') DEFAULT 'I' COMMENT 'I=Initial P=In Progress F=Fixed C=Completed',
  `pro_date_raised` datetime DEFAULT NULL COMMENT 'Date the problem was first raised in the system',
  `pro_responded_hours` decimal(7,2) DEFAULT '0.00' COMMENT 'duration in hours until response',
  `pro_fixed_consno` int(11) DEFAULT NULL COMMENT 'User that fixed problem. FK to consultant.cns_consno',
  `pro_fixed_date` datetime DEFAULT NULL COMMENT 'Dat/time fixed',
  `pro_working_hours` decimal(7,2) DEFAULT '0.00' COMMENT 'Number of working hours since problem first raised',
  `pro_sent_sla_alert_flag` char(1) DEFAULT 'N' COMMENT 'Set to show SLA alert email has been sent',
  `pro_internal_notes` mediumtext COMMENT 'Internal CNC Notes',
  `pro_completion_alert_count` int(1) DEFAULT '0' COMMENT 'How many completion alerts have been sent to the customer',
  `pro_complete_date` date DEFAULT NULL COMMENT 'Date when request to be completed either manually or automatically',
  `pro_email_option` enum('A','N','S') DEFAULT 'A' COMMENT 'A=Always, N=Never or S=Skip work commenced email',
  `pro_hide_from_customer_flag` char(1) DEFAULT 'N' COMMENT 'Hide all SR activity from customer',
  `pro_alarm_date` date DEFAULT '0000-00-00' COMMENT 'Future alarm date',
  `pro_alarm_time` char(5) DEFAULT NULL COMMENT 'Future alarm time',
  `pro_total_activity_duration_hours` decimal(7,2) DEFAULT NULL COMMENT 'Combined duration of all Activities',
  `pro_total_travel_activity_duration_hours` decimal(7,2) DEFAULT NULL COMMENT 'Combined duration of Travel activity',
  `pro_chargeable_activity_duration_hours` decimal(7,2) DEFAULT NULL COMMENT 'Combined duration of chargeable Activities',
  `pro_sla_response_hours` decimal(12,2) DEFAULT NULL COMMENT 'Elapsed hours from raised to responded',
  `pro_contract_cuino` int(11) DEFAULT NULL COMMENT 'Contract. FK to custitem.cui_cuino',
  `pro_escalated_flag` char(1) DEFAULT 'N' COMMENT 'Was this escalated',
  `pro_escalated_consno` int(11) unsigned DEFAULT NULL COMMENT 'User that escalated. FK to consultant.cns_consno',
  `pro_reopened_flag` char(1) DEFAULT 'N' COMMENT 'Was this reopened',
  `pro_contno` int(11) unsigned DEFAULT NULL COMMENT 'Customer Contact that raised Request. FK to contact.con_contno',
  `activityCategoryID` int(11) unsigned DEFAULT NULL COMMENT 'Activity Category. FK to activitycategory.act_activityCategoryID',
  `pro_technician_weighting` enum('1','2','3','4','5') DEFAULT '1' COMMENT 'Priority for engineer dashboard. Not used in any other context.',
  `pro_rejected_consno` int(11) unsigned DEFAULT NULL COMMENT 'User that last rejected request. FK to consultant.cns_consno',
  `pro_do_next_flag` char(1) DEFAULT 'N' COMMENT 'Indicates the engineer should work on this request next',
  `pro_rootcauseno` int(11) unsigned DEFAULT NULL COMMENT 'Root Cause of problem. FK to rootcause.rtc_rootcauseno',
  `pro_working_hours_alert_sent_flag` char(1) DEFAULT 'N' COMMENT 'Has an alert been sent when number of elapsed hours exceeds system limit?',
  `pro_awaiting_customer_response_flag` char(1) DEFAULT 'N' COMMENT 'Are we waiting for customer action?',
  `pro_working_hours_calculated_to_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'This is an optomisisation used by BUProblemSLA',
  `pro_manager_comment` text COMMENT 'Manager''s comments (only visible to helpdesk manager)',
  `pro_breach_comment` text COMMENT 'Comment about breach of SLA',
  `pro_message_to_sales` text COMMENT 'Message sent to sales',
  `pro_monitor_name` char(100) DEFAULT NULL COMMENT 'Automatic import Monitor Name',
  `pro_monitor_agent_name` char(100) DEFAULT NULL COMMENT 'Automatic import Monitor Agent Name',
  `pro_projectno` int(11) unsigned DEFAULT NULL COMMENT 'Project',
  `pro_linked_ordno` int(11) unsigned DEFAULT NULL COMMENT 'Related Sales Order',
  `pro_critical_flag` char(1) NOT NULL DEFAULT 'N' COMMENT 'Critical Service Request',
  `pro_queue_no` int(2) DEFAULT '1' COMMENT 'Queue number',
  `pro_hd_limit_minutes` decimal(8,3) NOT NULL COMMENT 'HD team time remaining',
  `pro_es_remain_hours` decimal(8,3) NOT NULL COMMENT 'Escalation team time remaining',
  `pro_im_remain_hours` decimal(8,3) NOT NULL COMMENT 'Implementations team time remaining',
  `pro_hd_time_alert_flag` char(1) NOT NULL DEFAULT 'N' COMMENT 'HD team time alert has been displayed',
  `pro_es_time_alert_flag` char(1) DEFAULT 'N' COMMENT 'ES team time alert has been displayed',
  `pro_im_time_alert_flag` char(1) DEFAULT 'N' COMMENT 'IM team time alert flag has been displayed',
  `pro_hd_pause_count` int(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of times HD pause button has been clicked',
  `pro_management_review_reason` text NOT NULL COMMENT 'Reason for management review',
  `pro_started_consno` int(5) unsigned DEFAULT NULL COMMENT 'User that started work on SR',
  PRIMARY KEY (`pro_problemno`),
  KEY `pro_status_consno` (`pro_status`,`pro_consno`),
  KEY `pro_monitor_name` (`pro_monitor_name`,`pro_monitor_agent_name`),
  KEY `pro_linked_ordno` (`pro_linked_ordno`),
  KEY `custno_status` (`pro_custno`,`pro_status`),
  KEY `pro_date_raised` (`pro_date_raised`),
  KEY `pro_fixed_date` (`pro_fixed_date`),
  KEY `pro_critical_flag` (`pro_critical_flag`),
  KEY `pro_queue_no` (`pro_queue_no`),
  KEY `pro_alarm_date` (`pro_alarm_date`,`pro_alarm_time`),
  FULLTEXT KEY `pro_internal_notes` (`pro_internal_notes`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='Service Request';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `projectID` int(11) NOT NULL DEFAULT '0',
  `customerID` int(11) NOT NULL DEFAULT '0',
  `description` char(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `startDate` date DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `notes` text,
  UNIQUE KEY `ixcallt_1` (`projectID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question` (
  `que_questionno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `que_questionnaireno` int(11) unsigned DEFAULT NULL,
  `que_desc` char(130) NOT NULL COMMENT 'Description',
  `que_answertypeno` int(11) DEFAULT NULL COMMENT 'Type of answer. 1=',
  `que_active_flag` char(1) DEFAULT 'Y' COMMENT 'Question is active?',
  `que_weight` int(3) DEFAULT '0' COMMENT 'Weighting to control display order on screen',
  `que_required_flag` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`que_questionno`)
) ENGINE=MyISAM AUTO_INCREMENT=3200 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_type`
--

DROP TABLE IF EXISTS `question_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_type` (
  `qut_questiontypeno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `qut_desc` char(50) NOT NULL,
  PRIMARY KEY (`qut_questiontypeno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaire`
--

DROP TABLE IF EXISTS `questionnaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questionnaire` (
  `qur_questionnaireno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `qur_desc` char(50) NOT NULL,
  `qur_intro` text NOT NULL,
  `qur_thank_you` text NOT NULL,
  `qur_rating_1_desc` char(50) NOT NULL,
  `qur_rating_5_desc` char(50) NOT NULL,
  `qur_name_required` char(1) NOT NULL DEFAULT 'Y' COMMENT 'Is a name required when completing questionnaire?',
  PRIMARY KEY (`qur_questionnaireno`)
) ENGINE=MyISAM AUTO_INCREMENT=92012 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue_history`
--

DROP TABLE IF EXISTS `queue_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue_history` (
  `queueHistoryID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `queueName` char(10) NOT NULL COMMENT 'Name of queue',
  `srCount` int(10) NOT NULL DEFAULT '0' COMMENT 'How many SRs in queue',
  `time` datetime NOT NULL COMMENT 'Date and time',
  PRIMARY KEY (`queueHistoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quotation`
--

DROP TABLE IF EXISTS `quotation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ras`
--

DROP TABLE IF EXISTS `ras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `renbroadband`
--

DROP TABLE IF EXISTS `renbroadband`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=331 DEFAULT CHARSET=utf8 COMMENT='OBSOLETE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rencontract`
--

DROP TABLE IF EXISTS `rencontract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=2226 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='OBSOLETE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rendomain`
--

DROP TABLE IF EXISTS `rendomain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='OBSOLETE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `renewaltype`
--

DROP TABLE IF EXISTS `renewaltype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `renewaltype` (
  `renewalTypeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` char(50) NOT NULL,
  `allowSrLogging` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`renewalTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `renquotation`
--

DROP TABLE IF EXISTS `renquotation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=703 DEFAULT CHARSET=latin1 COMMENT='OBSOLETE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `renquotationtype`
--

DROP TABLE IF EXISTS `renquotationtype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `renquotationtype` (
  `renQuotationTypeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` char(50) NOT NULL,
  `addInstallationCharge` char(1) DEFAULT 'Y',
  PRIMARY KEY (`renQuotationTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rootcause`
--

DROP TABLE IF EXISTS `rootcause`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rootcause` (
  `rtc_rootcauseno` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `rtc_desc` char(50) NOT NULL COMMENT 'Short description',
  `rtc_long_desc` char(100) NOT NULL COMMENT 'Long description',
  PRIMARY KEY (`rtc_rootcauseno`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=latin1 COMMENT='Root cause of a Service Request problem';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorder_document`
--

DROP TABLE IF EXISTS `salesorder_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorder_document` (
  `salesOrderDocumentID` int(10) unsigned NOT NULL DEFAULT '0',
  `ordheadID` int(11) NOT NULL,
  `description` char(100) NOT NULL COMMENT 'Description of the document',
  `filename` char(100) NOT NULL COMMENT 'Name of file',
  `file` longblob NOT NULL COMMENT 'The binary',
  `fileMimeType` char(50) NOT NULL COMMENT 'Mime type for display',
  `createdDate` datetime NOT NULL,
  `createdUserID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesrequest`
--

DROP TABLE IF EXISTS `salesrequest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesrequest` (
  `srq_salesrequestno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `srq_ordno` int(11) unsigned NOT NULL,
  `srq_text` text,
  `srq_contractcuino` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`srq_salesrequestno`),
  KEY `srq_ordno` (`srq_ordno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `secondsite_image`
--

DROP TABLE IF EXISTS `secondsite_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secondsite_image` (
  `secondsiteImageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customerItemID` int(10) unsigned NOT NULL,
  `imageName` char(100) NOT NULL COMMENT 'e.g. disk label C',
  `imagePath` char(255) DEFAULT NULL COMMENT 'Path to last image file',
  `status` char(20) DEFAULT NULL COMMENT 'Status when last checked',
  `imageTime` datetime DEFAULT NULL COMMENT 'Date/time of image',
  PRIMARY KEY (`secondsiteImageID`)
) ENGINE=MyISAM AUTO_INCREMENT=1113 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sector`
--

DROP TABLE IF EXISTS `sector`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sector` (
  `sec_sectorno` int(11) unsigned NOT NULL,
  `sec_desc` char(50) DEFAULT NULL,
  PRIMARY KEY (`sec_sectorno`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `securityapp`
--

DROP TABLE IF EXISTS `securityapp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securityapp` (
  `securityAppID` int(11) NOT NULL DEFAULT '0',
  `description` char(50) NOT NULL DEFAULT '',
  `backupFlag` char(1) DEFAULT NULL,
  `emailAVFlag` char(1) DEFAULT NULL,
  `serverAVFlag` char(1) DEFAULT NULL,
  PRIMARY KEY (`securityAppID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service request review_csv`
--

DROP TABLE IF EXISTS `service request review_csv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service request review_csv` (
  `F1` int(10) NOT NULL,
  `F2` text,
  PRIMARY KEY (`F1`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servicedeskreport`
--

DROP TABLE IF EXISTS `servicedeskreport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `sid` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `name` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `val` text CHARACTER SET utf8,
  `changed` varchar(14) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`name`,`sid`),
  KEY `changed` (`changed`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staffavailable`
--

DROP TABLE IF EXISTS `staffavailable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staffavailable` (
  `staffAvailableID` int(11) unsigned NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `am` decimal(2,1) NOT NULL DEFAULT '0.5' COMMENT 'available in the morning',
  `pm` decimal(2,1) NOT NULL DEFAULT '0.5' COMMENT 'available in the afternoon',
  PRIMARY KEY (`staffAvailableID`)
) ENGINE=MyISAM AUTO_INCREMENT=92 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 COMMENT='Indicates whether an engineer is avalable for a given date';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `standardtext`
--

DROP TABLE IF EXISTS `standardtext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `standardtext` (
  `stt_standardtextno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stt_sort_order` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Order of appearance in drop-downs',
  `stt_desc` char(50) NOT NULL COMMENT 'Text for drop-downs etc',
  `stt_text` text NOT NULL COMMENT 'Content to be pasted',
  `stt_standardtexttypeno` int(11) NOT NULL COMMENT 'Type of text',
  PRIMARY KEY (`stt_standardtextno`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `standardtexttype`
--

DROP TABLE IF EXISTS `standardtexttype`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `standardtexttype` (
  `sty_standardtexttypeno` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sty_desc` char(50) NOT NULL,
  PRIMARY KEY (`sty_standardtexttypeno`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcat`
--

DROP TABLE IF EXISTS `stockcat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team`
--

DROP TABLE IF EXISTS `team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team` (
  `teamID` int(11) unsigned NOT NULL COMMENT 'PK field',
  `name` char(50) NOT NULL COMMENT 'Team name',
  `teamRoleID` int(11) unsigned NOT NULL COMMENT 'FK to teamrole table',
  `level` int(5) NOT NULL COMMENT 'Level',
  `activeFlag` char(1) NOT NULL DEFAULT 'Y' COMMENT 'Whether this team is active',
  PRIMARY KEY (`teamID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team_performance`
--

DROP TABLE IF EXISTS `team_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_performance` (
  `teamPerformanceID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) unsigned NOT NULL,
  `month` int(2) unsigned NOT NULL,
  `hdTeamTargetSlaPercentage` decimal(5,2) unsigned NOT NULL COMMENT 'HD team target response within SLA percentage',
  `hdTeamTargetFixHours` int(3) unsigned NOT NULL COMMENT 'HD team target fix hours',
  `hdTeamTargetFixQtyPerMonth` int(3) unsigned NOT NULL COMMENT 'HD team target fix qty',
  `hdTeamActualSlaPercentage` decimal(5,2) unsigned NOT NULL COMMENT 'HD team actual response within SLA percentage',
  `hdTeamActualFixHours` int(3) unsigned NOT NULL COMMENT 'HD team actual fix hours',
  `hdTeamActualFixQtyPerMonth` int(3) unsigned NOT NULL COMMENT 'HD team actual fix qty',
  `esTeamTargetSlaPercentage` decimal(5,2) unsigned NOT NULL COMMENT 'ES team target response within SLA percentage',
  `esTeamTargetFixHours` int(3) unsigned NOT NULL COMMENT 'ES team target fix hours',
  `esTeamTargetFixQtyPerMonth` int(3) unsigned NOT NULL COMMENT 'ES team target fix qty',
  `esTeamActualSlaPercentage` decimal(5,2) unsigned NOT NULL COMMENT 'ES team actual response within SLA percentage',
  `esTeamActualFixHours` int(3) unsigned NOT NULL COMMENT 'ES team actual fix hours',
  `esTeamActualFixQtyPerMonth` int(3) unsigned NOT NULL COMMENT 'ES team actual fix qty',
  `imTeamTargetSlaPercentage` decimal(5,2) unsigned NOT NULL,
  `imTeamTargetFixHours` int(3) unsigned NOT NULL,
  `imTeamTargetFixQtyPerMonth` int(3) unsigned NOT NULL,
  `imTeamActualSlaPercentage` decimal(5,2) unsigned NOT NULL,
  `imTeamActualFixHours` int(3) unsigned NOT NULL,
  `imTeamActualFixQtyPerMonth` int(3) unsigned NOT NULL,
  PRIMARY KEY (`teamPerformanceID`),
  UNIQUE KEY `teamID` (`year`,`month`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team_role`
--

DROP TABLE IF EXISTS `team_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_role` (
  `teamRoleID` int(11) unsigned NOT NULL COMMENT 'PK',
  `name` char(50) NOT NULL COMMENT 'Role description',
  PRIMARY KEY (`teamRoleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_table`
--

DROP TABLE IF EXISTS `temp_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_table` (
  `id` int(10) unsigned NOT NULL,
  `value` char(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `time_breach`
--

DROP TABLE IF EXISTS `time_breach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `time_breach` (
  `timeBreachID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned NOT NULL COMMENT 'Person that breached',
  `breachDate` datetime NOT NULL COMMENT 'Date and time breached',
  `breachCount` int(3) NOT NULL COMMENT 'Number of breaches of this SR on this Date',
  PRIMARY KEY (`timeBreachID`),
  UNIQUE KEY `userID_breachDate` (`userID`,`breachDate`)
) ENGINE=MyISAM AUTO_INCREMENT=2964 DEFAULT CHARSET=latin1 COMMENT='Breach of time allocated to SR by person';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `time_granted`
--

DROP TABLE IF EXISTS `time_granted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `time_granted` (
  `timeGrantedID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned NOT NULL COMMENT 'Person granted to',
  `hours` decimal(7,2) unsigned NOT NULL COMMENT 'Additional hours granted',
  `grantedDate` date NOT NULL COMMENT 'Date granted',
  PRIMARY KEY (`timeGrantedID`),
  UNIQUE KEY `userID_grantedDate` (`userID`,`grantedDate`)
) ENGINE=MyISAM AUTO_INCREMENT=2061 DEFAULT CHARSET=latin1 COMMENT='Additional time granted to users to work on SRs by date';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_time_log`
--

DROP TABLE IF EXISTS `user_time_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_time_log` (
  `userTimeLogID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) unsigned NOT NULL,
  `teamLevel` int(1) unsigned NOT NULL COMMENT 'team that user was in',
  `loggedDate` date DEFAULT NULL COMMENT 'date time logged',
  `loggedHours` decimal(6,2) unsigned NOT NULL COMMENT 'hours logged',
  `dayHours` decimal(6,2) DEFAULT NULL COMMENT 'work hours for date',
  `startedTime` time NOT NULL COMMENT 'time started work - use for daily running performance',
  PRIMARY KEY (`userTimeLogID`),
  UNIQUE KEY `userID` (`userID`,`loggedDate`)
) ENGINE=MyISAM AUTO_INCREMENT=10169 DEFAULT CHARSET=latin1 COMMENT='Records hours worked per user per day';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userext`
--

DROP TABLE IF EXISTS `userext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userext` (
  `userID` int(11) NOT NULL DEFAULT '0',
  `signatureFilename` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `jobTitle` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `firstName` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `lastName` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `activeFlag` char(1) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vat`
--

DROP TABLE IF EXISTS `vat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-05-19 16:13:58
