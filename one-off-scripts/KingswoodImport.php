<?php
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
$that       = null;
$dbeContact = new DBEContact($that);
$dbeContact->getRowsByCustomerID('6391');
$existingContacts = [];
while ($dbeContact->fetchNext()) {
    $contactId                    = $dbeContact->getValue(DBEContact::contactID);
    $existingContacts[$contactId] = false;
}
$handle  = fopen('E:\\Temp\\kingswood-contact-list.csv', "r");
$isFirst = true;
/** @var $db dbSweetcode */
global $db;
/**
 * @param $data
 * @param $db
 */
function handleRow($data, dbSweetcode $db, &$existingContacts): void
{
    $email  = $data[8];
    $toFind = "%$email%";
    $db->query("select * from contact where con_email like {$toFind} and con_custno = 6391");
    if (!$db->num_rows()) {
        $toInsert = new DBEContact($that);
        $toInsert->setValue(DBEContact::email, $email);
        $toInsert->setValue(DBEContact::title, $data[0]);
        $toInsert->setValue(DBEContact::firstName, $data[1]);
        $toInsert->setValue(DBEContact::lastName, $data[2]);
        $toInsert->setValue(DBEContact::position, $data[3]);
        $toInsert->setValue(DBEContact::siteNo, $data[4]);
        $toInsert->setValue(DBEContact::supportLevel, $data[5]);
        $toInsert->setValue(DBEContact::phone, $data[6]);
        $toInsert->setValue(DBEContact::mobilePhone, $data[7]);
        $toInsert->setValue(DBEContact::initialLoggingEmailFlag, 'Y');
        $toInsert->setValue(DBEContact::workStartedEmailFlag, 'Y');
        $toInsert->setValue(DBEContact::workUpdatesEmailFlag, 'Y');
        $toInsert->setValue(DBEContact::fixedEmailFlag, 'Y');
        $toInsert->setValue(DBEContact::pendingClosureEmailFlag, 'Y');
        $toInsert->setValue(DBEContact::closureEmailFlag, 'Y');
        $toInsert->insertRow();
        return;
    }
    // update the guy
    $db->next_record(MYSQLI_ASSOC);
    $existingContacts[$db->Record[DBEContact::contactID]] = true;
    $toUpdate                                             = new DBEContact($that);
    $toUpdate->getRow($db->Record[DBEContact::contactID]);
    $toUpdate->setValue(DBEContact::title, $data[0]);
    $toUpdate->setValue(DBEContact::firstName, $data[1]);
    $toUpdate->setValue(DBEContact::lastName, $data[2]);
    $toUpdate->setValue(DBEContact::position, $data[3]);
    $toUpdate->setValue(DBEContact::siteNo, $data[4]);
    $toUpdate->setValue(DBEContact::supportLevel, $data[5]);
    $toUpdate->setValue(DBEContact::phone, $data[6]);
    $toUpdate->setValue(DBEContact::mobilePhone, $data[7]);
    $toUpdate->updateRow();
}

while (($data = fgetcsv($handle)) !== FALSE) {
    if ($isFirst) {
        $isFirst = false;
        continue;
    }
    handleRow($data, $db, $existingContacts);
}
foreach ($existingContacts as $notMatchedContactId => $matched) {
    if ($matched) {
        continue;
    }
    $dbeContact = new DBEContact($that);
    $dbeContact->getRow($notMatchedContactId);
    $dbeContact->setValue(DBEContact::email, null);
    $dbeContact->setValue(DBEContact::active, false);
    $dbeContact->updateRow();
}