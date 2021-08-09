<?php
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
require_once $cfg['path_dbe'] . '/DBECustomer.inc.php';
require_once $cfg['path_dbe'] . '/DBESite.inc.php';
require_once $cfg['path_dbe'] . '/DBECustomerNote.inc.php';
require_once $cfg['path_dbe'] . '/DBEContact.inc.php';
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "p:";
$longopts  = [];
$options   = getopt($shortopts, $longopts);
$filePath         = @$options["p"];
if (!isset($filePath)) {
    echo 'The path to the CSV file to import is mandatory' . PHP_EOL;
    exit;
}
if (!file_exists($filePath)) {
    echo 'The file does not exist, or the path is incorrect' . PHP_EOL;
    exit;
}
$csvFile = fopen($filePath, 'r');
if (!$csvFile) {
    echo 'Failed to open file';
    exit;
}
$headers = fgetcsv($csvFile);
$thing   = null;
while ($line = fgetcsv($csvFile)) {
    if (!$line[6]) {
        continue;
    }
    echo "Processing customer {$line[6]}" . PHP_EOL;
    $dbeCustomer = new DBECustomer($thing);
    $nowDate     = (new DateTime())->format(DATE_MYSQL_DATE);
    $nowDateTime = (new DateTime())->format(DATE_MYSQL_DATETIME);
    $reviewDate  = $nowDate;
    $dbeCustomer->setValue(DBECustomer::name, $line[6]);
    $dbeCustomer->getRowsByColumn(DBECustomer::name);
    if (!$dbeCustomer->fetchFirst()) {
        $customerInsert = new DBECustomer($thing);
        $customerInsert->setValue(DBECustomer::name, $line[6]);
        $customerInsert->setValue(DBECustomer::createDate, $nowDate);
        $customerInsert->setValue(DBECustomer::referredFlag, 1);
        $customerInsert->setValue(DBECustomer::modifyDate, $nowDateTime);
        $customerInsert->setValue(DBECustomer::noOfServers, 0);
        $customerInsert->setValue(DBECustomer::reviewDate, $reviewDate);
        $customerInsert->setValue(DBECustomer::reviewUserID, 137);
        $customerInsert->setValue(DBECustomer::modifyUserID, 110);
        $customerInsert->setValue(DBECustomer::accountManagerUserID, 137);
        $customerInsert->setValue(DBECustomer::leadStatusId, 1);
        $customerInsert->setValue(DBECustomer::websiteURL, $line[14]);
        $customerInsert->setValue(DBECustomer::sectorID, $line[16]);
        $customerInsert->setValue(DBECustomer::noOfPCs, $line[18]);
        $customerInsert->setValue(DBECustomer::mailshotFlag, 'Y');
        $customerInsert->setValue(DBECustomer::customerTypeID, 47);
        $customerInsert->setValue(DBECustomer::deliverSiteNo, 0);
        $customerInsert->insertRow();
        $customerId      = $customerInsert->getPKValue();
        $dbeCustomerNote = new DBECustomerNote($thing);
        $dbeCustomerNote->setValue(DBECustomerNote::customerID, $customerId);
        $dbeCustomerNote->setValue(DBECustomerNote::created, $nowDateTime);
        $dbeCustomerNote->setValue(DBECustomerNote::createdUserID, 110);
        $dbeCustomerNote->setValue(DBECustomerNote::modifiedAt, $nowDateTime);
        $dbeCustomerNote->setValue(DBECustomerNote::modifiedUserID, 110);
        $dbeCustomerNote->setValue(DBECustomerNote::details, $line[17]);
        $dbeCustomerNote->insertRow();

    } else {
        $customerId = $dbeCustomer->getValue(DBECustomer::customerID);
    }
    if (!$customerId) {
        continue;
    }
    $dbeSite = new DBESite($thing);
    $dbeSite->setValue(DBESite::siteNo, 0);
    $dbeSite->setValue(DBESite::customerID, $customerId);
    if (!$dbeSite->getRowByCustomerIDSiteNo()) {
        $siteInsert = new DBESite($thing);
        $siteInsert->setValue(DBESite::siteNo, 0);
        $siteInsert->setValue(DBESite::customerID, $customerId);
        $siteInsert->setValue(DBESite::add1, $line[7]);
        $siteInsert->setValue(DBESite::add2, $line[8]);
        $siteInsert->setValue(DBESite::add3, $line[9]);
        $siteInsert->setValue(DBESite::town, $line[10]);
        $siteInsert->setValue(DBESite::county, $line[11]);
        $siteInsert->setValue(DBESite::postcode, $line[12]);
        $siteInsert->setValue(DBESite::phone, $line[13]);
        $siteInsert->setValue(DBESite::activeFlag, 'Y');
        $siteInsert->setValue(DBESite::maxTravelHours, 0);
        $siteInsert->insertRow();
    }
    $dbeContact = new DBEContact($thing);
    $dbeContact->getRowsByCustomerID($customerId, true);
    if (!$dbeContact->rowCount()) {
        $contactInsert = new DBEContact($thing);
        $contactInsert->setValue(DBEContact::customerID, $customerId);
        $contactInsert->setValue(DBEContact::mailshot, 1);
        $contactInsert->setValue(DBEContact::initialLoggingEmail, 1);
        $contactInsert->setValue(DBEContact::title, $line[1]);
        $contactInsert->setValue(DBEContact::firstName, $line[2]);
        $contactInsert->setValue(DBEContact::lastName, $line[3]);
        $contactInsert->setValue(DBEContact::position, $line[4]);
        $contactInsert->setValue(DBEContact::email, $line[5]);
        $contactInsert->setValue(DBEContact::siteNo, 0);
        $contactInsert->setValue(DBEContact::supportLevel, DBEContact::supportLevelMain);
        $contactInsert->insertRow();
        $customerUpdate = new DBECustomer($thing);
        $customerUpdate->getRow($customerId);
        $customerUpdate->setValue(DBECustomer::primaryMainContactID, $contactInsert->getPKValue());
        $customerUpdate->updateRow();
    }


}