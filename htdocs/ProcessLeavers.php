<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 20/09/2018
 * Time: 13:22
 */

require_once("config.inc.php");
GLOBAL $cfg;

require_once($cfg["path_dbe"] . "/DBEContact.inc.php");
require_once($cfg['path_bu'] . '/BUContact.inc.php');

$thing = null;
$buContact = new BUContact($thing);
$dsContact = new DataSet($thing);
$buContact->getTodayLeaverContacts($dsContact);

while ($dsContact->fetchNext()) {
    $dbeContact = new DBEContact($thing);
    $dbeContact->getRow($dsContact->getValue(DBEContact::contactID));

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
        DBEContact::reviewUser,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::hrUser,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::initialLoggingEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::fixedEmailFlag,
        'N'
    );
    $dbeContact->setValue(
        DBEContact::othersInitialLoggingEmailFlag,
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
        DBEContact::pendingLeaverFlag,
        'N'
    );

    $dbeContact->setValue(
        DBEContact::email,
        null
    );

    $dbeContact->updateRow();


    $buActivity = new BUActivity($thing);
    $dsProblem = new DataSet($thing);
    $buActivity->getOpenProblemByContactID(
        $dbeContact->getValue(DBEContact::contactID),
        $dsProblem
    );

    $buCustomer = new BUCustomer($thing);
    while ($dsProblem->fetchNext()) {
        $dbeProblem = new DBEProblem($thing);
        $dbeProblem->getRow($dsProblem->getValue(DBEJProblem::problemID));
        $contacts = $buCustomer->getMainSupportContacts($dsProblem->getValue(DBEJProblem::customerID));

        $dbeProblem->setValue(
            DBEJProblem::contactID,
            $contacts[0][DBEContact::contactID]
        );
        $dbeProblem->updateRow();
    }
};