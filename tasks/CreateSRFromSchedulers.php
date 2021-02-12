<?php

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
require_once($cfg ['path_dbe'] . '/DBESRScheduler.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ["path_bu"] . "/BUProblemRaiseType.inc.php");
/** @var $db dbSweetcode */
global $db;
$logName = 'CreateSRFromSchedulers';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "dt:";
$longopts  = [];
$options   = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$startDate = new DateTime();
if (isset($options['t'])) {
    $date = DateTime::createFromFormat('d-m-Y', $options['t']);
    if (!$date) {
        echo 'The date must have the following format `DD-MM-YYYY`';
        exit;
    }
    $startDate = $date;
}
$startDate->setTime(0, 0, 0, 0);
$thing = null;
try {


    $dbeSrScheduler = new DBESRScheduler($thing);
    $dbeSrScheduler->getRows();
    while ($dbeSrScheduler->fetchNext()) {
        $schedulerString = $dbeSrScheduler->getValue(DBESRScheduler::rruleString);
        $logger->info('Checking scheduler: ' . $schedulerString);
        $rrule = new \RRule\RRule($schedulerString);
        $dates = $rrule->getOccurrencesAfter($startDate, true, 1);
        if (!$dates || !count($dates)) {
            $logger->notice('No more instances to run for this scheduler, deleting it');
            $dbeSrSchedulerDelete = new DBESRScheduler($thing);
            $dbeSrSchedulerDelete->deleteRow($dbeSrScheduler->getValue(DBESRScheduler::id));
            continue;
        }
        if ($dates[0]->format('Y-m-d') > $startDate->format('Y-m-d')) {
            $logger->notice('Next instance should run in the future, ignoring...');
            continue;
        }
        $customerId = $dbeSrScheduler->getValue(DBESRScheduler::customerId);
        $logger->info('Creating SR for customer ' . $customerId);
        $dbeProblem = new DBEProblem($thing);
        $dbeContact = new DBEContact($thing);
        $buActivity = new BUActivity($thing);
        $contactId  = $dbeSrScheduler->getValue(DBESRScheduler::contactId);
        $priority   = $dbeSrScheduler->getValue(DBESRScheduler::priority);
        $queue               = $dbeSrScheduler->getValue(DBESRScheduler::teamId);
        $hiddenFromCustomer  = $dbeSrScheduler->getValue(DBESRScheduler::hideFromCustomer);
        $siteNo              = $dbeSrScheduler->getValue(DBESRScheduler::siteNo);
        $createdBy           = $dbeSrScheduler->getValue(DBESRScheduler::createdBy);
        $details             = $dbeSrScheduler->getValue(DBESRScheduler::details);
        $internalNotes       = $dbeSrScheduler->getValue(DBESRScheduler::internalNotes);
        $emailSubjectSummary = $dbeSrScheduler->getValue(DBESRScheduler::emailSubjectSummary);
        if (!$dbeContact->getRow($contactId)) {
            $logger->warning(
                'Could not find assigned contact: ' . $contactId . ' trying to find main contact for customer'
            );
            $dbeContact->getMainSupportRowsByCustomerID($customerId);
            if (!$dbeContact->fetchNext()) {
                $logger->error('Could not find main contact, ignoring this scheduler');
                continue;
            }
        }
        $buHeader = new BUHeader($thing);
        $dsHeader = new DataSet($thing);
        $buHeader->getHeader($dsHeader);
        $dbeCallActivity = new DBECallActivity($thing);
        /*
    Is there an existing activity for this exact problem?

    If so, we will append to that SR
    */
        $slaResponseHours = $buActivity->getSlaResponseHours(
            $priority,
            $customerId,
            $dbeContact->getValue(DBEContact::contactID)
        );
        /* create new issue */
        $dbeProblem->setValue(
            DBEProblem::slaResponseHours,
            $slaResponseHours
        );
        $dbeProblem->setValue(
            DBEProblem::customerID,
            $customerId
        );
        $dbeProblem->setValue(
            DBEProblem::status,
            'I'
        );
        $dbeProblem->setValue(
            DBEProblem::priority,
            $priority
        );
        $dbeProblem->setValue(
            DBEProblem::queueNo,
            $queue
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            date(DATE_MYSQL_DATETIME)
        );
        $dbeProblem->setValue(
            DBEProblem::assetName,
            $dbeSrScheduler->getValue(DBESRScheduler::assetName)
        );
        $dbeProblem->setValue(
            DBEProblem::assetTitle,
            $dbeSrScheduler->getValue(DBESRScheduler::assetTitle)
        );
        $dbeProblem->setValue(
            DBEProblem::emptyAssetReason,
            $dbeSrScheduler->getValue(DBESRScheduler::emptyAssetReason)
        );

        $dbeProblem->setValue(
            DBEProblem::contactID,
            $dbeContact->getValue(DBEContact::contactID)
        );
        $dbeProblem->setValue(
            DBEProblem::hideFromCustomerFlag,
            $hiddenFromCustomer ? 'Y' : 'N'
        );
        $dbeProblem->setValue(DBEProblem::emailSubjectSummary, $emailSubjectSummary);
        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::smallProjectsTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::smallProjectsTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::projectTeamLimitMinutes,
            $dsHeader->getValue(DBEHeader::projectTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::linkedSalesOrderID,
            $dbeSrScheduler->getValue(DBESRScheduler::linkedSalesOrderId)
        );
        $dbeProblem->setValue(DBEProblem::internalNotes, $internalNotes);
        $dbeProblem->setValue(
            DBEProblem::raiseTypeId,
            BUProblemRaiseType::MANUALID
        );
        $dbeProblem->insertRow();
        $problemID = $dbeProblem->getPKValue();
        $dbeCallActivity->setValue(
            DBECallActivity::callActivityID,
            null
        );
        $dbeCallActivity->setValue(
            DBECallActivity::siteNo,
            $hiddenFromCustomer
        ); // contact default siteno
        $dbeCallActivity->setValue(
            DBECallActivity::contactID,
            $dbeContact->getValue(DBEContact::contactID)
        );
        $dbeCallActivity->setValue(
            DBECallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBECallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBECallActivity::startTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBECallActivity::endTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBECallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBECallActivity::serverGuard,
            'N'
        );
        $dbeCallActivity->setValue(
            DBECallActivity::reason,
            $details
        );
        $dbeCallActivity->setValue(
            DBECallActivity::problemID,
            $problemID
        );
        $dbeCallActivity->setValue(
            DBECallActivity::userID,
            USER_SYSTEM
        );
        $dbeCallActivity->insertRow();
        if (!$hiddenFromCustomer) {
            $buActivity->sendManuallyLoggedServiceRequestEmail($dbeProblem->getPKValue());
        }
        $logger->info('Successfully created SR ');
    }
} catch (\Exception $exception) {
    // log the error
    $logger->error('Failed to process scheduler:' . $exception->getMessage());
    $buActivity     = new BUActivity($thing);
    $buCustomer     = new BUCustomer($thing);
    $customerId     = 282;
    $primaryContact = $buCustomer->getPrimaryContact($customerID);
    $buHeader       = new BUHeader($thing);
    $dsHeader       = new DataSet($thing);
    $buHeader->getHeader($dsHeader);
    $siteNo   = 0;
    $priority = 2;
    $slaResponseHours = $buActivity->getSlaResponseHours(
        $priority,
        $customerID,
        $primaryContact->getValue(DBEContact::contactID)
    );
    $dbeProblem = new DBEProblem($thing);
    $dbeProblem->setValue(DBEProblem::problemID, null);
    $siteNo = $primaryContact->getValue(DBEContact::siteNo);
    $dbeProblem->setValue(
        DBEProblem::hdLimitMinutes,
        $dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
    );
    $dbeProblem->setValue(
        DBEProblem::esLimitMinutes,
        $dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
    );
    $dbeProblem->setValue(
        DBEProblem::smallProjectsTeamLimitMinutes,
        $dsHeader->getValue(DBEHeader::smallProjectsTeamLimitMinutes)
    );
    $dbeProblem->setValue(
        DBEProblem::projectTeamLimitMinutes,
        $dsHeader->getValue(DBEHeader::projectTeamLimitMinutes)
    );
    $dbeProblem->setValue(
        DBEProblem::slaResponseHours,
        $slaResponseHours
    );
    $dbeProblem->setValue(
        DBEProblem::customerID,
        $customerID
    );
    $dbeProblem->setValue(
        DBEProblem::status,
        'I'
    );
    $dbeProblem->setValue(
        DBEProblem::priority,
        $priority
    );
    $dbeProblem->setValue(
        DBEProblem::dateRaised,
        date(DATE_MYSQL_DATETIME)
    ); // default
    $dbeProblem->setValue(
        DBEProblem::contactID,
        $primaryContact->getValue(DBEContact::contactID)
    );
    $dbeProblem->setValue(
        DBEProblem::hideFromCustomerFlag,
        'N'
    );
    $dbeProblem->setValue(
        DBEProblem::queueNo,
        1
    );
    $dbeProblem->setValue(
        DBEProblem::rootCauseID,
        86
    );
    $dbeProblem->setValue(
        DBEProblem::userID,
        null
    );        // not allocated
    $dbeProblem->setValue(
        DBEProblem::raiseTypeId,
        BUProblemRaiseType::MANUALID
    );
    $dbeProblem->insertRow();
    $dbeCallActivity = new DBECallActivity($thing);
    $dbeCallActivity->setValue(
        DBECallActivity::callActivityID,
        null
    );
    $dbeCallActivity->setValue(
        DBECallActivity::siteNo,
        $siteNo
    );
    $dbeCallActivity->setValue(
        DBECallActivity::contactID,
        $primaryContact->getValue(DBEContact::contactID)
    );
    $dbeCallActivity->setValue(
        DBECallActivity::callActTypeID,
        CONFIG_INITIAL_ACTIVITY_TYPE_ID
    );
    $dbeCallActivity->setValue(
        DBECallActivity::date,
        date(DATE_MYSQL_DATE)
    );
    $startTime = date('H:i');
    $dbeCallActivity->setValue(
        DBECallActivity::startTime,
        $startTime
    );
    $dbeCallActivity->setValue(
        DBECallActivity::endTime,
        $startTime
    );
    $dbeCallActivity->setValue(
        DBECallActivity::status,
        'C'
    );
    $dbeCallActivity->setValue(
        DBECallActivity::serverGuard,
        'N'
    );
    $details = "CreateSRFromScheduler Failed: " . $exception->getMessage();
    $dbeCallActivity->setValue(
        DBECallActivity::reason,
        $details
    );
    $dbeCallActivity->setValue(
        DBECallActivity::problemID,
        $dbeProblem->getPKValue()
    );
    $dbeCallActivity->setValue(
        DBECallActivity::userID,
        USER_SYSTEM
    );
    $dbeCallActivity->insertRow();
}