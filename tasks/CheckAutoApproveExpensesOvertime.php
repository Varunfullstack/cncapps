<?php


/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
global $db;
$logName = 'CheckAutoApproveExpensesOvertime';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;
$expense = new DBEExpense($thing);
$expense->getUnapprovedExpense();
$checkedUsers = [];

while ($expense->nextRecord()) {
    $logger->info('Verifying expense with ID: ' . $expense->getValue(DBEExpense::expenseID));
    $activity = new DBECallActivity($thing);
    $activity->getRow($expense->getValue(DBEExpense::callActivityID));
    $userID = $activity->getValue(DBECallActivity::userID);
    $logger->info(
        'The user with ID : ' . $userID . ' owner of this expense has auto approve enable, proceeding to approve expense '
    );
    $dbeExpense = new DBEExpense($thing);
    $dbeExpense->getRow($userID);
    $dbeExpense->setValue(DBEExpense::approvedBy, USER_SYSTEM);
    $dbeExpense->setValue(DBEExpense::approvedDate, date(DATE_MYSQL_DATETIME));
    $dbeExpense->updateRow();

}

$callactivity = new DBECallActivity($thing);
$callactivity->getUnapprovedOvertime();

while ($callactivity->nextRecord()) {
    $logger->info('Verifying overtime activity with ID: ' . $callactivity->getValue(DBECallActivity::callActivityID));
    $userID = $callactivity->getValue(DBECallActivity::userID);

    $logger->info(
        'The user with ID : ' . $userID . ' owner of this overtime has auto approve enable, proceeding to approve overtime '
    );
    $activity = new DBECallActivity($thing);
    $activity->getRow($callactivity->getValue(DBECallActivity::callActivityID));
    $activity->setValue(DBECallActivity::overtimeApprovedBy, USER_SYSTEM);
    $activity->setValue(DBECallActivity::overtimeApprovedDate, date(DATE_MYSQL_DATETIME));
    $dbeExpense->updateRow();
}

