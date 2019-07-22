<?php

/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

require_once("config.inc.php");
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBEOffice365License.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
global $db;
$logName = 'office365LicenseExport';
$logger = new \CNCLTD\LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);


if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "c:d";
$longopts = array(
    "customer:",
);
$options = getopt($shortopts, $longopts);
$customerID = null;
if (isset($options['c'])) {
    $customerID = $options['c'];
    unset($options['c']);
}
if (isset($options['customer'])) {
    $customerID = $options['customer'];
    unset($options['customer']);
}

$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}

$dbeCustomer = new DBECustomer($thing);

if (isset($customerID)) {
    $dbeCustomer->getRow($customerID);
    if (!$dbeCustomer->rowCount) {
        $logger->error("Customer not found");
        exit;
    }
} else {
    $dbeCustomer->getActiveCustomers(true);
    $dbeCustomer->fetchNext();
}
$BUHeader = new BUHeader($thing);
$dbeHeader = new DataSet($thing);
$BUHeader->getHeader($dbeHeader);
$yellowThreshold = $dbeHeader->getValue(DBEHeader::office365MailboxYellowWarningThreshold);
$redThreshold = $dbeHeader->getValue(DBEHeader::office365MailboxRedWarningThreshold);

if (!$yellowThreshold || !$redThreshold) {
    throw new Exception('Yellow and Red Threshold values are required');
}
function num2alpha($n)
{
    for ($r = ""; $n >= 0; $n = intval($n / 26) - 1)
        $r = chr($n % 26 + 0x41) . $r;
    return $r;
}

$buCustomer = new BUCustomer($thing);
$buPassword = new BUPassword($thing);
$dbeOffice365Licenses = new DBEOffice365License($thing);
do {

    $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
    $customerName = $dbeCustomer->getValue(DBECustomer::name);

    $logger->info('Getting Office 365 Data for Customer: ' . $customerID . ' - ' . $customerName);

    // we have to pull from passwords.. the service 10

    $dbePassword = $buCustomer->getOffice365PasswordItem($customerID);

    if (!$dbePassword->rowCount) {
        $logger->warning('This customer does not have a Office 365 Admin Portal service password');
        continue;
    }

    $userName = $buPassword->decrypt($dbePassword->getValue(DBEPassword::username));
    $password = $buPassword->decrypt($dbePassword->getValue(DBEPassword::password));
    $path = POWERSHELL_DIR . "/365OfficeLicensesExport.ps1";
    $cmdParts = [
        "powershell.exe",
        "-executionpolicy",
        "bypass",
        "-NoProfile",
        "-command",
        $path,
        "-User",
        base64_encode($userName),
        "-Password",
        base64_encode($password)
    ];
    $escaped = implode(' ', array_map('escape_win32_argv', $cmdParts));
    /* In almost all cases, escape for cmd.exe as well - the only exception is
       when using proc_open() with the bypass_shell option. cmd doesn't handle
       arguments individually, so the entire command line string can be escaped,
       no need to process arguments individually */
    $cmd = escape_win32_cmd($escaped);

    if ($debugMode) {
        $logger->notice('The powershell line to execute is :' . $cmd);
    }
    $output = noshell_exec($cmd);
    $data = json_decode($output, true, 512);

    if (!isset($data)) {
        $logger->error('Failed to parse for customer: ' . $output);
        createFailedSR($dbeCustomer, "Could not parse Powershell response: $output");
        continue;
    }
    if (isset($data['error'])) {
        $logger->error('Failed to pull data for customer: ' . $data['errorMessage']);
        createFailedSR($dbeCustomer, $data['errorMessage'], $data['stackTrace'], $data['position']);
        continue;
    }
// we are going to build an array from the data
    $mailboxLimits = [];
    $totalizationRow = [
        "Total"         => "Total",
        "TotalMailBox"  => 0,
        "Empty"         => null,
        "LicensedUsers" => 0
    ];

    if (!count($data)) {
        $logger->warning('The customer does not have any licenses');
        continue;
    }

    foreach ($data as $key => $datum) {
        $values = [];
        $mailboxLimit = null;
        $licenseValue = null;
        if ($datum['Licenses']) {
            if (!is_array($datum['Licenses'])) {
                $datum['Licenses'] = [
                    $datum['Licenses']
                ];
            }
            $licenseValue = implode(", ", $datum['Licenses']);

            if ($licenseValue && strpos(
                    strtolower($datum['DisplayName']),
                    'leaver'
                ) !== false && $datum['RecipientTypeDetails'] == 'SharedMailbox') {
                $logger->warning('Raising a Customer Leaver with License SR');
                raiseCustomerLeaverWithLicenseSR($dbeCustomer, $datum['DisplayName']);
            }

            foreach ($datum['Licenses'] as $license) {
                $dbeOffice365Licenses->getRowForLicense($license);
                if ($dbeOffice365Licenses->rowCount()) {
                    $licenseValue = str_replace(
                        $license,
                        $dbeOffice365Licenses->getValue(DBEOffice365License::replacement),
                        $licenseValue
                    );
                    if (!$mailboxLimit && $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit)) {
                        $mailboxLimit = $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit);
                    }
                } else {
                    $logger->warning('Raising a License not found SR');
                    raiseCNCRequest($license, $dbeCustomer, $datum['DisplayName']);
                }
            }
        }
        $licensesArray = explode(", ", $licenseValue);
        sort($licensesArray);
        $licenseValue = implode(", ", $licensesArray);

        switch ($data[$key]['RecipientTypeDetails']) {
            case "SharedMailbox":
                $data[$key]['RecipientTypeDetails'] = "Shared";
                break;
            case "UserMailbox":
                $data[$key]['RecipientTypeDetails'] = "User";
                break;
            case 'RoomMailbox':
                $data[$key]['RecipientTypeDetails'] = "Room";
                break;
        }

        $data[$key]['Licenses'] = $licenseValue;
        $data[$key]['IsLicensed'] = $data[$key]['IsLicensed'] ? 'Yes' : 'No';
        $totalizationRow['TotalMailBox'] += $datum['TotalItemSize'];
        $data[$key]['TotalItemSize'] = number_format($datum['TotalItemSize']);
        $totalizationRow['LicensedUsers'] += $datum['IsLicensed'];

        $mailboxLimits[] = $mailboxLimit;
    }


    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
    $sheet = $spreadsheet->getActiveSheet();
    $dateTime = new DateTime();
    $sheet->fromArray(
        [
            "Display Name",
            "Mailbox Size (MB)",
            "Mailbox Type",
            "Is Licensed",
            "Licenses"
        ],
        null,
        'A1'
    );
    $sheet->fromArray(
        $data,
        null,
        'A2'
    );
    $highestRow = count($data) + 2;
    $totalizationRow['LicensedUsers'] = "$totalizationRow[LicensedUsers] Licensed Users";
    $totalizationRow['TotalMailBox'] = number_format($totalizationRow['TotalMailBox']);
    $sheet->fromArray(
        $totalizationRow,
        null,
        'A' . $highestRow
    );
    $sheet->fromArray(["Report generated at " . $dateTime->format("d-m-Y H:i:s")], null, 'A' . ($highestRow + 2));

    $sheet->getStyle("A$highestRow:E$highestRow")->getFont()->setBold(true);

    $sheet->getStyle("A1:E1")->getFont()->setBold(true);

    $sheet->getStyle("A1:E$highestRow")->getAlignment()->setHorizontal('center');

    for ($i = 0; $i < count($data); $i++) {
        $currentRow = 3 + $i;

        if ($mailboxLimits[$i]) {
            $usage = floatval(preg_replace('/,/', "", $data[$i]['TotalItemSize'])) / $mailboxLimits[$i] * 100;
            $color = null;
            if ($usage >= $dbeHeader->getValue(DBEHeader::office365MailboxYellowWarningThreshold)) {
                $color = "FFFFEB9C";
            }

            if ($usage >= $dbeHeader->getValue(DBEHeader::office365MailboxRedWarningThreshold)) {
                $color = "FFFFC7CE";
            }

            if ($color) {
                $sheet->getStyle("A$currentRow:E$currentRow")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($color);
            }
        }
    }

    foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
        $sheet->getColumnDimension($col)
            ->setAutoSize(true);
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $customerFolder = $buCustomer->getCustomerFolderPath($customerID);
    $folderName = $customerFolder . "\Review Meetings\\";
    if (!file_exists($folderName)) {
        mkdir(
            $folderName,
            0777,
            true
        );
    }

    $fileName = $folderName . "Current Mailbox Extract.xlsx";
    try {
        $writer->save(
            $fileName
        );
        $dbeCustomerDocument = new DBEPortalCustomerDocument($thing);
        $dbeCustomerDocument->getCurrentOffice365Licenses($customerID);

        $dbeCustomerDocument->setValue(
            DBEPortalCustomerDocument::file,
            file_get_contents($fileName)
        );

        if (!$dbeCustomerDocument->getValue(
                DBEPortalCustomerDocument::createdDate
            ) || $dbeCustomerDocument->getValue(DBEPortalCustomerDocument::createdDate) == '0000-00-00 00:00:00') {

            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::createdDate,
                (new DateTime())->format(DATE_MYSQL_DATETIME)
            );
        }

        if (!$dbeCustomerDocument->rowCount) {
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::customerID,
                $customerID
            );
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::description,
                'O365 Licenses'
            );
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::filename,
                "O365 Licenses.xlsx"
            );
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::fileMimeType,
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            );
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::startersFormFlag,
                'N'
            );
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::leaversFormFlag,
                'N'
            );
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::mainContactOnlyFlag,
                'Y'
            );

            $dbeCustomerDocument->insertRow();
        } else {
            $dbeCustomerDocument->updateRow();
        }

        $logger->info('All good!!. Creating file ' . $fileName);
    } catch (\Exception $exception) {
        $logger->warning('Failed to save file, possibly file open');
    }
} while ($dbeCustomer->fetchNext());

/**
 * @param DBECustomer $dbeCustomer
 * @param $errorMsg
 * @param null $stackTrace
 * @param null $position
 */
function raiseCustomerLeaverWithLicenseSR(DBECustomer $dbeCustomer, $userName)
{
    $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
    $buActivity = new BUActivity($thing);
    $buCustomer = new BUCustomer($thing);
    $primaryContact = $buCustomer->getPrimaryContact($customerID);
    $buHeader = new BUHeader($thing);
    $dsHeader = new DataSet($thing);
    $buHeader->getHeader($dsHeader);


    $slaResponseHours = $buActivity->getSlaResponseHours(
        4,
        $customerID,
        $primaryContact->getValue(DBEContact::contactID)
    );

    $dbeProblem = new DBEProblem($thing);
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
        DBEProblem::imLimitMinutes,
        $dsHeader->getValue(DBEHeader::imTeamLimitMinutes)
    );
    $dbeProblem->setValue(
        DBEProblem::slaResponseHours,
        $slaResponseHours
    );
    $dbeProblem->setValue(
        DBEProblem::customerID,
        $customerID
    );
    $dbeProblem->setValue(
        DBEProblem::status,
        'I'
    );
    $dbeProblem->setValue(
        DBEProblem::priority,
        4
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
        DBEJProblem::hideFromCustomerFlag,
        'Y'
    );
    $dbeProblem->setValue(
        DBEJProblem::queueNo,
        3
    );

    $dbeProblem->setValue(
        DBEJProblem::rootCauseID,
        86
    );
    $dbeProblem->setValue(
        DBEJProblem::userID,
        null
    );        // not allocated
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

    $details = "<p>User $userName is marked as leaver but still has an Office 365 license assigned to it, please review and correct.</p>";

    $dbeCallActivity->setValue(
        DBEJCallActivity::reason,
        $details
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

/**
 * @param DBECustomer $dbeCustomer
 * @param $errorMsg
 * @param null $stackTrace
 * @param null $position
 */
function createFailedSR(DBECustomer $dbeCustomer, $errorMsg, $stackTrace = null, $position = null)
{
    $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
    $buActivity = new BUActivity($thing);
    $buCustomer = new BUCustomer($thing);
    $primaryContact = $buCustomer->getPrimaryContact($customerID);
    $buHeader = new BUHeader($thing);
    $dsHeader = new DataSet($thing);
    $buHeader->getHeader($dsHeader);


    $slaResponseHours = $buActivity->getSlaResponseHours(
        4,
        $customerID,
        $primaryContact->getValue(DBEContact::contactID)
    );

    $dbeProblem = new DBEProblem($thing);
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
        DBEProblem::imLimitMinutes,
        $dsHeader->getValue(DBEHeader::imTeamLimitMinutes)
    );
    $dbeProblem->setValue(
        DBEProblem::slaResponseHours,
        $slaResponseHours
    );
    $dbeProblem->setValue(
        DBEProblem::customerID,
        $customerID
    );
    $dbeProblem->setValue(
        DBEProblem::status,
        'I'
    );
    $dbeProblem->setValue(
        DBEProblem::priority,
        4
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
        DBEJProblem::hideFromCustomerFlag,
        'Y'
    );
    $dbeProblem->setValue(
        DBEJProblem::queueNo,
        2
    );

    $dbeProblem->setValue(
        DBEJProblem::rootCauseID,
        83
    );
    $dbeProblem->setValue(
        DBEJProblem::userID,
        null
    );        // not allocated
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

    $details = "Office 365 License Export Failed: " . $errorMsg;
    if ($position) {
        $details .= " " . $position;
    }

    if ($stackTrace) {
        $details .= " " . $stackTrace;
    }

    $dbeCallActivity->setValue(
        DBEJCallActivity::reason,
        $details
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

function raiseCNCRequest($license, DBECustomer $dbeCustomer, $licenseUser)
{
    $customerID = 282;
    $buActivity = new BUActivity($thing);
    $buCustomer = new BUCustomer($thing);
    $primaryContact = $buCustomer->getPrimaryContact($customerID);
    $buHeader = new BUHeader($thing);
    $dsHeader = new DataSet($thing);
    $buHeader->getHeader($dsHeader);


    $slaResponseHours = $buActivity->getSlaResponseHours(
        4,
        $customerID,
        $primaryContact->getValue(DBEContact::contactID)
    );

    $dbeProblem = new DBEProblem($thing);
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
        DBEProblem::imLimitMinutes,
        $dsHeader->getValue(DBEHeader::imTeamLimitMinutes)
    );
    $dbeProblem->setValue(
        DBEProblem::slaResponseHours,
        $slaResponseHours
    );
    $dbeProblem->setValue(
        DBEProblem::customerID,
        $customerID
    );
    $dbeProblem->setValue(
        DBEProblem::status,
        'I'
    );
    $dbeProblem->setValue(
        DBEProblem::priority,
        4
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
        83
    );
    $dbeProblem->setValue(
        DBEJProblem::userID,
        null
    );        // not allocated
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

    $details = "<p>License $license was not found for customer " . $dbeCustomer->getValue(DBECustomer::name) . " which is assigned to user $licenseUser.</p>
<p>Please add this license within CNCAPPS and rerun the license export process for this customer</p>";

    $dbeCallActivity->setValue(
        DBEJCallActivity::reason,
        $details
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