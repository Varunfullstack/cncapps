<?php
/**
 * Client information controller
 * CNC Ltd
 *
 * NOTE: includes either the ClientInformationReport.inc.php or ClientInformationEmail.inc.php
 * depending upon whether $_REQUEST['Confirmation'] is set or not
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

$thing = null;
require_once("config.inc.php");
GLOBAL $cfg;
page_open(
    array(
        'sess' => PHPLIB_CLASSNAME_SESSION,
        'auth' => PHPLIB_CLASSNAME_AUTH,
        '',
        ''
    )
);
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBEUser.inc.php');
require_once($cfg['path_gc'] . '/Controller.inc.php');
$buCustomer = new BUCustomer($thing);
$buCustomer->getContactByID(
    $_REQUEST['contactID'],
    $dsContact
);
$dsContact->fetchNext();

$buCustomer->getCustomerByID(
    $dsContact->getValue(DBEContact::customerID),
    $dsCustomer
);
$dsCustomer->fetchNext();
$buCustomer->getSiteByCustomerIDSiteNo(
    $dsContact->getValue(DBEContact::customerID),
    $dsContact->getValue(DBEContact::siteNo),
    $dsSite
);
$dsContact->fetchNext();

$dbeUser = new DBEUser($thing);
$dbeUser->setValue(
    'userID',
    $GLOBALS['auth']->is_authenticated()
);
$dbeUser->getRow();

$dbeAttendeeUser = new DBEUser($thing);
$dbeAttendeeUser->setValue(
    'userID',
    $_REQUEST['attendeeUserID']
);
$dbeAttendeeUser->getRow();

if ($dsSite->getValue(DBESite::phone) != '') {
    $phone = $dsSite->getValue(DBESite::phone);
}
if ($dsContact->getValue(DBEContact::phone) != '') {
    $phone .= ' DDI: ' . $dsContact->getValue(DBEContact::phone);
}
if ($dsContact->getValue(DBEContact::mobilePhone) != '') {
    $phone .= ' Mobile: ' . $dsContact->getValue(DBEContact::mobilePhone);
}
$newClient = ($dsCustomer->getValue(DBECustomer::prospectFlag) == 'Y');

if (isset($_REQUEST['Confirmation'])) {
    require('ClientInformationEmail.php.bak');                // the confirm button was clicked so include the email template
} else {
    require('ClientInformationReport.php.bak');                // the generate report button was clicked so include the report template
}
page_close();
?>