ALTER TABLE `callactivity` ADD COLUMN `caa_date_yearmonth` CHAR(6) NULL COMMENT 'e.g. 201305. Used for MIS report indexing' AFTER `caa_date`; 
ALTER TABLE `invhead` ADD COLUMN `inh_date_printed_yearmonth` CHAR(6) NULL AFTER `inh_date_printed`; 

UPDATE invhead SET inh_date_printed_yearmonth = DATE_FORMAT( inh_date_printed , '%Y%m');
UPDATE callactivity SET caa_date_yearmonth = DATE_FORMAT( caa_date , '%Y%m');

ALTER TABLE `invhead` ADD INDEX (`inh_date_printed_yearmonth`); 
ALTER TABLE `invhead` ADD INDEX (`inh_type`); 
ALTER TABLE `item` ADD INDEX (`itm_itemtypeno`); 
ALTER TABLE `callactivity` ADD INDEX `caa_date_yearmonth` (`caa_date_yearmonth`, `caa_callacttypeno`); 

/*[17:36:07][16 ms]*/ CREATE TRIGGER `callactivity_date_yearmonth_update` BEFORE UPDATE ON `callactivity` FOR EACH ROW BEGIN SET NEW.caa_date_yearmonth = DATE_FORMAT( NEW.caa_date , '%Y%m'); END; 
/*[17:36:18][11 ms]*/ CREATE TRIGGER `callactivity_date_yearmonth_insert` BEFORE INSERT ON `callactivity` FOR EACH ROW BEGIN SET NEW.caa_date_yearmonth = DATE_FORMAT( NEW.caa_date , '%Y%m'); END; 
/*[17:37:06][9 ms]*/ CREATE TRIGGER `invhead_date_yearmonth_update` BEFORE UPDATE ON `invhead` FOR EACH ROW BEGIN SET NEW.inh_date_printed_yearmonth = DATE_FORMAT( NEW.inh_date_printed , '%Y%m'); END; 
/*[17:37:26][12 ms]*/ CREATE TRIGGER `invhead_date_yearmonth_insert` BEFORE INSERT ON `invhead` FOR EACH ROW BEGIN SET NEW.inh_date_printed_yearmonth = DATE_FORMAT( NEW.inh_date_printed , '%Y%m'); END; 

