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
             
