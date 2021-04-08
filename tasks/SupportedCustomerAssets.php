<?php

use CNCLTD\LoggerCLI;
use CNCLTD\SupportedCustomerAssets\NotMatchedItemDTO;
use CNCLTD\SupportedCustomerAssets\SupportedCustomerAssets;
use CNCLTD\SupportedCustomerAssets\UnsupportedCustomerAssetService;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg ["path_bu"] . "/BUActivity.inc.php");
$logName = 'SupportedCustomerAssets';
$logger  = new LoggerCLI($logName);
// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "dc:";
$longopts  = [];
$options   = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing       = null;
$dbeCustomer = new DBECustomer($thing);
$dbeCustomer->getActiveCustomers(true);
$notMatchedAutomateItems = [];
while ($dbeCustomer->fetchNext()) {

    $supportedCustomerAssets = new SupportedCustomerAssets(
        $dbeCustomer->getValue(DBECustomer::customerID)
    );
    foreach ($supportedCustomerAssets->getCNCNotMatchedAssets() as $CNCNotMatchedAsset) {
        try {
            raiseNotMatchedRequest($dbeCustomer->getValue(DBECustomer::customerID), $CNCNotMatchedAsset);
        } catch (Exception $exception) {
            $logger->error($exception->getMessage());
        }
    }
    $notMatchedAutomateItems = array_merge(
        $notMatchedAutomateItems,
        $supportedCustomerAssets->getAutomateNotMatchedAssets()
    );
}
function raiseNotMatchedRequest($customerId, NotMatchedItemDTO $notMatchedItemDTO)
{

    $buActivity     = new BUActivity($thing);
    $buCustomer     = new BUCustomer($thing);
    $primaryContact = $buCustomer->getPrimaryContact($customerId);
    if (!$primaryContact) {
        throw new Exception("Customer doesn't have a primary contact set");
    }
    $buHeader = new BUHeader($thing);
    $dsHeader = new DataSet($thing);
    $buHeader->getHeader($dsHeader);
    $priority         = 4;
    $slaResponseHours = $buActivity->getSlaResponseHours(
        $priority,
        $customerId,
        $primaryContact->getValue(DBEContact::contactID)
    );
    $dbeProblem       = new DBEProblem($thing);
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
        DBEProblem::dateRaised,
        date(DATE_MYSQL_DATETIME)
    ); // default
    $dbeProblem->setValue(
        DBEProblem::contactID,
        $primaryContact->getValue(DBEContact::contactID)
    );
    $dbeProblem->setValue(
        DBEJProblem::queueNo,
        1
    );
    $dbeProblem->setValue(
        DBEJProblem::rootCauseID,
        69
    );
    $dbeProblem->setValue(
        DBEJProblem::userID,
        null
    );
    $dbeProblem->setValue(DBEProblem::contractCustomerItemID, $notMatchedItemDTO->getCustomerItemId());
    $dbeProblem->setValue(
        DBEProblem::raiseTypeId,
        BUProblemRaiseType::ALERTID
    );
    $dbeProblem->setValue(DBEProblem::assetName, $notMatchedItemDTO->getComputerName());
    $dbeProblem->setValue(DBEProblem::assetTitle, $notMatchedItemDTO->getComputerName());
    $dbeProblem->setValue(
        DBEProblem::emailSubjectSummary,
        "Server {$notMatchedItemDTO->getComputerName()} missing CWA agent."
    );
    $dbeProblem->setValue(
        DBEProblem::hideFromCustomerFlag,
        'Y'
    );
    $dbeProblem->insertRow();
    $dbeCallActivity = new DBECallActivity($thing);
    $dbeCallActivity->setValue(
        DBEJCallActivity::callActivityID,
        null
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::siteNo,
        $siteNo
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::contactID,
        $primaryContact->getValue(DBEContact::contactID)
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::callActTypeID,
        CONFIG_INITIAL_ACTIVITY_TYPE_ID
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::date,
        date(DATE_MYSQL_DATE)
    );
    $startTime = date('H:i');
    $dbeCallActivity->setValue(
        DBEJCallActivity::startTime,
        $startTime
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::endTime,
        $startTime
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
        "Server {$notMatchedItemDTO->getComputerName()} has support contract with CNC but does not have the CWA agent installed. Please install the agent and update the asset linked to this Service Request."
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::problemID,
        $dbeProblem->getPKValue()
    );
    $dbeCallActivity->setValue(
        DBEJCallActivity::userID,
        USER_SYSTEM
    );
    $dbeCallActivity->insertRow();
}

$updater = new UnsupportedCustomerAssetService();
$updater->update($notMatchedAutomateItems);