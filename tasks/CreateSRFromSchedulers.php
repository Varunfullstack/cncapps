<?php


/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
require_once($cfg ['path_dbe'] . '/DBESRScheduler.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
/** @var $db dbSweetcode */
global $db;
$logName = 'CreateSRFromSchedulers';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}

// Script example.php
$shortopts = "dt:";
$longopts = [];
$options = getopt($shortopts, $longopts);
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
    $contactId = $dbeSrScheduler->getValue(DBESRScheduler::contactId);
    $priority = $dbeSrScheduler->getValue(DBESRScheduler::priority);

    $queue = $dbeSrScheduler->getValue(DBESRScheduler::teamId);
    $hiddenFromCustomer = $dbeSrScheduler->getValue(DBESRScheduler::hideFromCustomer);
    $siteNo = $dbeSrScheduler->getValue(DBESRScheduler::siteNo);
    $createdBy = $dbeSrScheduler->getValue(DBESRScheduler::createdBy);
    $details = $dbeSrScheduler->getValue(DBESRScheduler::details);
    $internalNotes = $dbeSrScheduler->getValue(DBESRScheduler::internalNotes);
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
    $slaResponseHours =
        $buActivity->getSlaResponseHours(
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
        DBEProblem::contactID,
        $dbeContact->getValue(DBEContact::contactID)
    );
    $dbeProblem->setValue(
        DBEProblem::hideFromCustomerFlag,
        $hiddenFromCustomer ? 'Y' : 'N'
    );
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
        DBEProblem::userID,
        $createdBy
    );        // not allocated
    $dbeProblem->insertRow();

    $problemID = $dbeProblem->getPKValue();

    $dbeCallActivity->setValue(
        DBEJCallActivity::callActivityID,
        null
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::siteNo,
        $hiddenFromCustomer
    ); // contact default siteno
    $dbeCallActivity->setValue(
        DBEJCallActivity::contactID,
        $dbeContact->getValue(DBEContact::contactID)
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::callActTypeID,
        CONFIG_INITIAL_ACTIVITY_TYPE_ID
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::date,
        date(DATE_MYSQL_DATE)
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::startTime,
        date('H:i')
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::endTime,
        date('H:i')
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::status,
        'C'
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::serverGuard,
        'N'
    );

    $dbeCallActivity->setValue(
        DBEJCallActivity::reason,
        $details
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::problemID,
        $problemID
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::internalNotes,
        $internalNotes
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::userID,
        USER_SYSTEM
    );
    $dbeCallActivity->insertRow();
    $logger->info('Successfully created SR ');
}
