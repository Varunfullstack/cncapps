<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 14/02/2019
 * Time: 10:59
 */


require_once("config.inc.php");
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
    $dbeContact->setValue(
        DBEContact::sendMailshotFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::discontinuedFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::accountsFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::mailshot2Flag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::mailshot3Flag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::mailshot4Flag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::mailshot8Flag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::mailshot9Flag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::mailshot11Flag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::initialLoggingEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::workStartedEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::workUpdatesEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::fixedEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::pendingClosureEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::closureEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::othersInitialLoggingEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::othersWorkStartedEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::othersWorkUpdatesEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::othersFixedEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::othersPendingClosureEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::othersClosureEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::pendingLeaverFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::specialAttentionContactFlag,
        'N'
    );

    $dbeContact->setValue(
        DBEContact::hrUser,
        'N'
    );

    $dbeContact->setValue(
        DBEContact::reviewUser,
        'N'
    );

    $dbeContact->setValue(
        DBEContact::email,
        null
    );

    $dbeContact->setValue(
        DBEContact::supportLevel,
        null
    );

    $dbeContact->updateRow();

}
