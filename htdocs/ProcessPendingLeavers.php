<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 14/02/2019
 * Time: 10:59
 */

require_once("config.inc.php");
global $cfg;
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . '/BUContact.inc.php');


$outputToScreen = isset($_GET['toScreen']);
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
