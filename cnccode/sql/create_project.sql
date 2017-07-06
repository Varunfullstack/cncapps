/*
SQLyog Ultimate v9.63 
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

/*Table structure for table `project` */

DROP TABLE IF EXISTS `project`;

CREATE TABLE `project` (
  `projectID` int(11) NOT NULL default '0',
  `customerID` int(11) NOT NULL default '0',
  `description` char(50) character set utf8 NOT NULL default '',
  `expiryDate` date default NULL,
  UNIQUE KEY `ixcallt_1` (`projectID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `project` */

insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (1,2166,'Windows 2003 R2 Server Project','2007-03-23');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (2,1969,'Office Relocation Project','2007-03-23');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (3,2530,'SBS 2003 Server Project','2007-04-02');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (5,3292,'Office Relocation 2007','2007-04-06');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21250,1823,'Holland Project','2007-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21251,2315,'Fibre Cabling','2008-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21252,1841,'London Office','2007-04-20');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21253,2166,'Office Relocation 2007','2007-05-11');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21254,2764,'SBS 2003 Server','2007-06-14');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21255,997,'London File Server','2007-06-22');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21256,2359,'New York Office','2007-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21257,2315,'SPA Office Relocation','2007-10-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21258,1746,'SBS 2003 Upgrade','2007-10-08');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21259,1545,'Arvins & Grover Integration','2007-07-25');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21260,2693,'System Upgrade 2007','2007-09-26');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21261,1468,'SBS 2003 Server','2007-06-19');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21262,2025,'Bath Office Relocation','2007-06-22');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21263,2025,'London Office relocation','2007-09-18');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21264,2539,'SBS 2003','2007-08-06');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21265,2028,'Exchange 2003','2007-09-04');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21266,1939,'Server Relocation 2007','2007-07-06');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21267,4057,'SBS 2003','2007-08-28');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21268,1065,'Arvins & Grover Integration','2007-06-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21269,1478,'SBS 2003 Upgrade','2007-09-21');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21270,4337,'Initial Works','2007-09-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21271,2166,'Off-Site Server','2007-09-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21272,1731,'DFS Project','2007-11-05');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21273,2524,'SBS 2003','2008-01-09');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21274,1554,'SBS 2003','2007-10-25');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21275,1951,'SBS 2003','2007-10-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21276,2178,'Office Relocation 2007','2007-10-12');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21277,3474,'System Upgrades Project 2007','2008-02-21');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21278,2498,'Office Relocation 2007','2007-11-12');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21279,4478,'SBS 2003','2007-12-21');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21280,997,'ADSL Migrations 2007','2007-11-16');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21281,997,'Leeds Server 2007','2007-11-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21282,997,'Truro office','2007-11-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21283,1401,'SBS 2003','2008-01-04');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21284,1908,'Off-Site DR','2007-12-07');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21285,2102,'SBS 2003','2008-01-09');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21286,2916,'SBS 2003','2008-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21287,1617,'Server Integration 2008','2008-01-18');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21288,2172,'Move to Windows Servers - 2008','2008-02-05');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21289,520,'Shuttleworth Server 2008','2008-03-20');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21290,2722,'Office Relocation 2008','2008-04-10');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21292,2315,'SSL-VPN Remote Access','2008-03-19');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21293,823,'Office Relocation 2008','2008-03-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21294,4053,'Wireless Link','2008-03-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21295,1823,'System Audit 2008','2008-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21296,2305,'SBS 2003','2008-05-12');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21297,1545,'DR Server','2008-05-07');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21298,2377,'SBS 2003','2008-07-22');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21299,1823,'Network DR','2008-09-01');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21300,2359,'Adapt Server','2008-07-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21301,2025,'LEEDS Server 2008','2008-08-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21302,4763,'SBS 2003','2008-10-24');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21303,1743,'SBS 2003 (2008)','2008-12-12');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21304,2025,'Blackberry Server','2008-11-14');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21306,2214,'Office Refurb 2008','2009-05-08');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21307,2095,'Office Relocation 2008','2008-11-28');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21308,1841,'Server Upgrades 2008','2009-02-13');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21309,1545,'Blackberry Project 2008','2008-12-05');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21310,2025,'Birmingham Office','2008-12-19');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21311,4977,'Cognos 2008','2009-01-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21312,2359,'SBS 2009','2009-03-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21313,2025,'London Office - PowerEdge T300 Project','2009-03-06');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21314,1711,'Wireless Bridge','2009-03-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21315,2141,'Windows 2003 Server','2009-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21316,2025,'Bath Leased Line','2009-04-17');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21317,2343,'IT Review 2009','2009-05-29');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21318,2424,'New Server 2009','2009-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21319,5148,'New Office 2009','2009-06-04');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21320,1711,'Server Migration 2009','2009-06-05');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21321,5151,'New server 2009','2009-05-04');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21322,3147,'IT Upgrades 2009','2009-06-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21323,2389,'Remote Outlook Project','2009-07-07');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21324,2025,'Cardiff Office Move','2009-06-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21325,2025,'ISO27001','2009-09-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21326,2343,'Network/Server Work','2009-07-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21327,2025,'Bath Mail Server Replacement','2009-07-14');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (21328,4763,'Remote Office Project','2009-07-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (22,2343,'Server Relocation - Air Conditioned Room','2009-10-09');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (23,1702,'Server DR Migration','2009-08-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (24,1711,'Office Relocation','2009-09-08');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (25,1510,'Server 2003 Hardware Migration','2009-09-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (26,4817,'Office Move 2009','2009-09-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (27,2025,'Leeds Office Relocation 2009','2009-12-08');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (28,1714,'New Server Project 2009','2009-12-07');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (29,4452,'WAN Infrastructure Upgrade','2009-11-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (30,1548,'New Server Project 2009','2009-12-10');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (31,4452,'Server Consolidation 2009','2010-01-12');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (32,5348,'SBS 2008 upgrade (Dec 2009)','2010-01-11');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (33,4523,'Inventures Separation 2010','2010-02-05');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (34,2025,'Capita Transition','2010-10-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (35,520,'Leased Line','2010-09-03');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (36,1000,'Ghyllprint Migration','2010-05-07');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (37,129,'SBS 2008','2010-10-19');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (38,2923,'SBS 2008 Migration 2010','2010-07-23');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (39,2707,'Server Migration 2008','2010-06-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (40,259,'Server Replacement 2010','2010-09-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (41,1644,'SBS 2008 Project','2010-10-29');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (42,520,'Server Refresh 2010','2010-10-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (43,4977,'Office Relocation 2010','2010-07-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (44,2548,'SBS 2008','2010-11-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (45,1617,'Office Relocation 2010','2010-10-20');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (46,5662,'Initial Migration 2010','2010-12-13');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (47,1731,'Office Move 2010','2010-12-10');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (48,1969,'Office Relocation 2010','2010-12-24');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (49,1647,'New Server 2010','2010-12-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (50,3271,'New Server Project 2010','2011-01-21');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (51,5320,'SBS 2008','2011-01-14');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (52,3832,'Citrix Server','2011-02-04');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (53,520,'Virtualisation 2011','2011-03-04');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (54,4767,'SBS 2011 Server','2011-04-08');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (55,820,'SBS 2011','2011-05-20');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (56,1908,'SBS 2011','2011-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (57,2977,'SBS 2011 - April 2011','2011-05-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (58,2707,'System Updates 2011','2011-07-01');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (59,1649,'Office Relocation 2011','2011-06-27');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (60,2923,'Worthing Office','2011-07-29');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (61,2315,'Exchange 2010','2011-09-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (62,4977,'SQL Server 2011','2011-08-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (63,3832,'SBS 2011','2011-11-25');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (64,6011,'IT Refesh 2011','2011-12-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (65,6008,'Server Migration 2011','2011-12-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (66,6009,'IT Refresh 2011','2011-12-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (67,2554,'Printer Review','2011-11-18');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (68,3950,'SBS 2011','2012-01-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (69,2869,'SBS 2011','2012-02-01');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (70,4838,'Exchange 2010 & Lakeview','2012-03-26');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (71,2389,'Server Refresh 2012','2012-05-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (72,2693,'SBS 2011','2012-02-17');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (73,2554,'New PC\'s - Q1 2012','2012-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (75,2186,'IT Refresh 2012','2012-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (76,2848,'PC Replacement 2012','2012-03-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (77,1939,'New viritual file & case management server','2012-05-02');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (78,1479,'New VM server 2012','2012-04-30');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (79,3292,'SBS 2011','2012-05-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (80,1841,'SBS 2011 - May 2012','2012-05-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (81,1731,'Albourne IP Renumber 2012','2012-05-25');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (82,4838,'Keighley Office Integration May 2012','2012-05-31');
insert  into `project`(`projectID`,`customerID`,`description`,`expiryDate`) values (83,1731,'SBS 2011 Migration - May 2012','2012-06-29');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
