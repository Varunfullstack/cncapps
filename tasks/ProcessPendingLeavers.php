<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 14/02/2019
 * Time: 10:59
 */

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'ProcessPendingLeavers';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [
    "toScreen"
];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . '/BUContact.inc.php');


$outputToScreen = isset($options['toScreen']);
$something = null;
$buContact = new BUContact($something);


$sender_name = "System";
$sender_email = CONFIG_SALES_EMAIL;
$headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";

$dsResults = new DataSet($something);
$buContact->getTodayLeaverContacts($dsResults);


while ($dsResults->fetchNext()) {
    ?>
    <div>
        <?= $dsResults->getValue(DBEContact::firstName) ?> <?= $dsResults->getValue(DBEContact::lastName) ?> is
        leaving <?= $dsResults->getValue(DBEContact::pendingLeaverDate) ?>

    </div>
    <?php
    $dbeContact = new DBEContact($something);
    $dbeContact->getRow($dsResults->getValue(DBEContact::contactID));
    $dbeContact->setValue(DBEContact::active, 0);
    $dbeContact->setValue(DBEContact::pendingLeaverFlag, 'N');
    $dbeContact->setValue(
        DBEContact::email,
        null
    );
    $dbeContact->updateRow();
}


$buContact->getContactsWithPendingFurloughActionForToday($dsResults);

while ($dsResults->fetchNext()) {
    ?>
    <div>
        <?= $dsResults->getValue(DBEContact::firstName) ?> <?= $dsResults->getValue(DBEContact::lastName) ?> has a
        pending furlough action
        of type <?= $dsResults->getValue(
            DBEContact::pendingFurloughAction
        ) === DBEContact::FURLOUGH_ACTION_TO_FURLOUGH ? "To Furlough" : 'To Unfurlough' ?>
    </div>
    <?php
    $dbeContact = new DBEContact($something);
    $dbeContact->getRow($dsResults->getValue(DBEContact::contactID));
    if ($dsResults->getValue(
            DBEContact::pendingFurloughAction
        ) === DBEContact::FURLOUGH_ACTION_TO_FURLOUGH) {
        $dbeContact->setValue(DBEContact::pendingFurloughActionLevel, $dsResults->getValue(DBEContact::supportLevel));
        $dbeContact->setValue(DBEContact::supportLevel, DBEContact::supportLevelFurlough);
    } else {
        $dbeContact->setValue(DBEContact::supportLevel, $dsResults->getValue(DBEContact::pendingFurloughActionLevel));
    }

    $dbeContact->setValue(DBEContact::pendingFurloughAction, null);
    $dbeContact->setValue(DBEContact::pendingFurloughActionDate, null);
    $dbeContact->updateRow();
}