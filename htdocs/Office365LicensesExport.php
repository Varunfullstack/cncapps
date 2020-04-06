<?php

/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

use CNCLTD\LoggerCLI;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once("config.inc.php");
global $cfg;
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
$logger = new LoggerCLI($logName);

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
        $logger->error('Failed to pull data for customer: ' . $data['errorMessage'] . ' ' . $data['stackTrace']);
        createFailedSR($dbeCustomer, $data['errorMessage'], $data['stackTrace'], $data['position']);
        continue;
    }

    if (count($data['errors'])) {
        foreach ($data['errors'] as $error) {
            $logger->warning("Error received from powershell output, but the execution was not stopped:  " . $error);
        }
    }


    $mailboxes = $data['mailboxes'];
    $licenses = $data['licenses'];


    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
    if (count($mailboxes)) {
        try {
            processMailboxes(
                $spreadsheet,
                $mailboxes,
                $logger,
                $dbeCustomer,
                $dbeOffice365Licenses,
                $debugMode,
                $dbeHeader
            );
        } catch (\Exception $exception) {
            $logger->error('Failed to process mailboxes for customer: ' . $exception->getMessage());
        }
    }

    if (count($licenses)) {
        try {
            processLicenses(
                $spreadsheet,
                $licenses,
                $logger,
                $dbeCustomer,
                $dbeOffice365Licenses,
                $debugMode,
                $dbeHeader
            );
        } catch (\Exception $exception) {
            $logger->error('Failed to process licenses for customer: ' . $exception->getMessage());
        }
    }

    if (!count($mailboxes) && !count($licenses)) {
        $logger->warning('This customer does not have a licences nor mailboxes');
        continue;
    }
    global $db;

    $statement = $db->preparedQuery(
        "insert into customerOffice365StorageStats values (?,?,?,?) on duplicate key update totalOneDriveStorageUsed = ?, totalEmailStorageUsed = ?",
        [
            [
                "type"  => 'i',
                "value" => $customerID
            ],
            [
                "type"  => 's',
                "value" => (new DateTime())->format(DATE_MYSQL_DATE)
            ],
            [
                "type"  => 'd',
                "value" => $data['totalOneDriveStorageUsed']
            ],
            [
                "type"  => 'd',
                "value" => $data['totalEmailStorageUsed']
            ],
            [
                "type"  => 'd',
                "value" => $data['totalOneDriveStorageUsed']
            ],
            [
                "type"  => 'd',
                "value" => $data['totalEmailStorageUsed']
            ]
        ]
    );

    $spreadsheet->removeSheetByIndex(0);
    $writer = new Xlsx($spreadsheet);
    $customerFolder = $buCustomer->getCustomerFolderPath($customerID);
    $folderName = $customerFolder . "\Review Meetings\\";
    if (!file_exists($folderName)) {
        mkdir(
            $folderName,
            0777,
            true
        );
    }
    $fileName = "Current Mailbox Extract.xlsx";
    $filePath = $folderName . $fileName;
    try {
        $writer->save(
            $filePath
        );
        $dbeCustomerDocument = new DBEPortalCustomerDocument($thing);
        $dbeCustomerDocument->getCurrentOffice365Licenses($customerID);

        $dbeCustomerDocument->setValue(
            DBEPortalCustomerDocument::file,
            file_get_contents($filePath)
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
                'Current Mailbox List'
            );
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::filename,
                $fileName
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
    } catch (Exception $exception) {
        $logger->error('Failed to save file, possibly file open: ' . $exception->getMessage());
    }
} while ($dbeCustomer->fetchNext());

/**
 * @param Spreadsheet $spreadSheet
 * @param $mailboxes
 * @param LoggerCLI $logger
 * @param DBECustomer $dbeCustomer
 * @param DBEOffice365License $dbeOffice365Licenses
 * @param $debugMode
 * @param DBEHeader|DataSet $dbeHeader
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */
function processMailboxes(Spreadsheet $spreadSheet,
                          $mailboxes,
                          LoggerCLI $logger,
                          DBECustomer $dbeCustomer,
                          DBEOffice365License $dbeOffice365Licenses,
                          $debugMode,
                          $dbeHeader
)
{
    $dateTime = new DateTime();
    $mailboxLimits = [];
    $totalizationRow = [
        "Total"         => "Total",
        "TotalMailBox"  => 0,
        "Empty"         => null,
        "LicensedUsers" => 0,
        1               => null,
        2               => null,
        3               => "Total",
        "TotalOneDrive" => 0
    ];

    foreach ($mailboxes as $key => $datum) {
        $mailboxLimit = null;
        $licenseValue = null;
        if ($datum['Licenses']) {
            if (!is_array($datum['Licenses'])) {
                $datum['Licenses'] = explode(" ", $datum['Licenses']);
            }
            $licenseValue = implode(", ", $datum['Licenses']);

            if ($licenseValue && strpos(
                    strtolower($datum['DisplayName']),
                    'leaver'
                ) !== false && $datum['RecipientTypeDetails'] == 'SharedMailbox') {
                $logger->warning('Raising a Customer Leaver with License SR while processing Mailboxes');
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
                    $logger->warning('Raising a License not found SR while processing Mailboxes:' . $license);
                    raiseCNCRequest($license, $dbeCustomer, $datum['DisplayName']);
                }
            }
        }
        $licensesArray = explode(", ", $licenseValue);
        sort($licensesArray);
        $licenseValue = implode(", ", $licensesArray);

        switch ($mailboxes[$key]['RecipientTypeDetails']) {
            case "SharedMailbox":
                $mailboxes[$key]['RecipientTypeDetails'] = "Shared";
                break;
            case "UserMailbox":
                $mailboxes[$key]['RecipientTypeDetails'] = "User";
                break;
            case 'RoomMailbox':
                $mailboxes[$key]['RecipientTypeDetails'] = "Room";
                break;
            case 'EquipmentMailbox':
                $mailboxes[$key]['RecipientTypeDetails'] = "Equipment";
                break;
        }

        $mailboxes[$key]['Licenses'] = $licenseValue;
        $mailboxes[$key]['IsLicensed'] = $mailboxes[$key]['IsLicensed'] ? 'Yes' : 'No';
        $totalizationRow['TotalMailBox'] += $datum['TotalItemSize'];
        $mailboxes[$key]['TotalItemSize'] = $datum['TotalItemSize'];
        $totalizationRow['LicensedUsers'] += $datum['IsLicensed'];
        $totalizationRow['TotalOneDrive'] += $datum['OneDriveStorageUsed'];
        if ($debugMode) {
            $mailboxes[$key][] = $mailboxLimit;
        }
        $mailboxLimits[] = $mailboxLimit;
    }

    $mailboxesSheet = $spreadSheet->createSheet();
    $mailboxesSheet->setTitle('Mailboxes');
    $mailboxesSheet->fromArray(
        [
            "Display Name",
            "Mailbox Size (MB)",
            "Mailbox Type",
            "Is Licensed",
            "Licenses",
            "Webmail Enabled",
            "MFA Enabled",
            "OneDrive Size(MB)"
        ],
        null,
        'A1'
    );
    $mailboxesSheet->fromArray(
        $mailboxes,
        null,
        'A2',
        true
    );
    $highestRow = count($mailboxes) + 2;
    $totalizationRow['LicensedUsers'] = "$totalizationRow[LicensedUsers] Licensed Users";
    $mailboxesSheet->fromArray(
        $totalizationRow,
        null,
        'A' . $highestRow,
        true
    );

    $mailboxesSheet->setCellValue(
        "B$highestRow",
        '=sum(B2:B' . ($highestRow - 1) . ')'
    );
    $mailboxesSheet->setCellValue(
        "D$highestRow",
        '=countif(D2:D' . ($highestRow - 1) . ', "yes") & " Licensed Users"'
    );

    $mailboxesSheet->fromArray(
        ["Report generated at " . $dateTime->format("d-m-Y H:i:s")],
        null,
        'A' . ($highestRow + 2)
    );

    $mailboxesSheet->getStyle("A$highestRow:H$highestRow")->getFont()->setBold(true);

    $mailboxesSheet->getStyle("A1:H1")->getFont()->setBold(true);

    $mailboxesSheet->getStyle("A1:H$highestRow")->getAlignment()->setHorizontal('center');

    for ($i = 0; $i < count($mailboxes); $i++) {
        $currentRow = 2 + $i;

        if ($mailboxLimits[$i]) {
            $usage = $mailboxes[$i]['TotalItemSize'] / $mailboxLimits[$i] * 100;
            $color = null;
            if ($usage >= $dbeHeader->getValue(DBEHeader::office365MailboxYellowWarningThreshold)) {
                $color = "FFFFEB9C";
            }

            if ($usage >= $dbeHeader->getValue(DBEHeader::office365MailboxRedWarningThreshold)) {
                $color = "FFFFC7CE";
            }

            if ($color) {
                $mailboxesSheet->getStyle("A$currentRow:E$currentRow")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($color);
            }
        }
    }

    $mailboxColumn = $mailboxesSheet->getStyle("B2:B$highestRow");
    $mailboxColumn->getNumberFormat()->setFormatCode("#,##0");
    $mailboxColumn->getAlignment()->setHorizontal('right');

    $oneDriveSizeColumn = $mailboxesSheet->getStyle("H2:H$highestRow");
    $oneDriveSizeColumn->getNumberFormat()->setFormatCode("#,##0");
    $oneDriveSizeColumn->getAlignment()->setHorizontal('right');


    foreach (range('A', $mailboxesSheet->getHighestDataColumn()) as $col) {
        $mailboxesSheet->getColumnDimension($col)
            ->setAutoSize(true);
    }
}

/**
 * @param Spreadsheet $spreadSheet
 * @param $licenses
 * @param LoggerCLI $logger
 * @param DBECustomer $dbeCustomer
 * @param DBEOffice365License $dbeOffice365Licenses
 * @param $debugMode
 * @param DBEHeader|DataSet $dbeHeader
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */
function processLicenses(Spreadsheet $spreadSheet,
                         $licenses,
                         LoggerCLI $logger,
                         DBECustomer $dbeCustomer,
                         DBEOffice365License $dbeOffice365Licenses,
                         $debugMode,
                         $dbeHeader
)
{
    $thing = null;
    if (!$licenses || !count($licenses)) {
        return;
    }
    $dateTime = new DateTime();
    $sparedLicenseErrors = [];
    foreach ($licenses as $key => $datum) {
        $licenses[$key][array_key_last($licenses[$key])] = null;
        $dbeOffice365Licenses->getRowForLicense($datum['AccountSkuId']);
        if ($dbeOffice365Licenses->rowCount()) {
            $licenses[$key]['AccountSkuId'] = str_replace(
                $datum['AccountSkuId'],
                $dbeOffice365Licenses->getValue(DBEOffice365License::replacement),
                $datum['AccountSkuId']
            );
            if ($dbeOffice365Licenses->getValue(DBEOffice365License::reportOnSpareLicenses) && $datum['Unallocated']) {
                $sparedLicenseErrors[] = [
                    "licenseName" => $licenses[$key]['AccountSkuId'],
                    "quantity"    => $datum['Unallocated']
                ];
            }
        } else {
            $logger->warning('Raising a License not found SR while processing Licenses');
            raiseCNCRequest($datum['AccountSkuId'], $dbeCustomer);
        }
    }

    if (count($sparedLicenseErrors)) {
        // we have found some spared licenses errors...lets send an email to inform about this
        $buMail = new BUMail($thing);


        $template = new Template (
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->set_file(
            array(
                'page' => 'SpareLicensesEmail.html',
            )
        );

        $template->set_block('page', 'licensesBlock', 'licenses');
        $template->setVar(
            [
                "customerName" => $dbeCustomer->getValue(DBECustomer::name)
            ]
        );
        foreach ($sparedLicenseErrors as $licenseError) {
            $template->setVar(
                [
                    "licenseName" => $licenseError['licenseName'],
                    "quantity"    => $licenseError['quantity'],
                ]
            );
            $template->parse("licenses", 'licensesBlock', true);
        }

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $subject = "Unallocated O365 licenses for customer " . $dbeCustomer->getValue(DBECustomer::name);
        $emailTo = CONFIG_SALES_EMAIL;

        $hdrs = array(
            'From'         => CONFIG_SUPPORT_EMAIL,
            'To'           => $emailTo,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $mime = new Mail_mime();

        $mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $mime->get($mime_params);

        $hdrs = $mime->headers($hdrs);


        $buMail->putInQueue(
            CONFIG_SUPPORT_EMAIL,
            "O365sparelicenses@cnc-ltd.co.uk",
            $hdrs,
            $body
        );

    }

    $licensesSheet = $spreadSheet->createSheet();
    $licensesSheet->setTitle('Licenses');

    $licensesSheet->fromArray(
        [
            "License Name",
            "Number of Licenses",
            "Number of Unallocated Licenses"
        ],
        null,
        'A1'
    );
    $licensesSheet->fromArray(
        $licenses,
        null,
        'A2',
        true
    );
    $highestRow = count($licenses) + 2;
    $licensesSheet->fromArray(
        ["Report generated at " . $dateTime->format("d-m-Y H:i:s")],
        null,
        'A' . ($highestRow + 1)
    );
    $highestCol = $licensesSheet->getHighestDataColumn();
    $licensesSheet->getStyle("A$highestRow:$highestCol$highestRow")->getFont()->setBold(true);
    $licensesSheet->getStyle("A1:" . $highestCol . "1")->getFont()->setBold(true);
    $licensesSheet->getStyle("A1:$highestCol$highestRow")->getAlignment()->setHorizontal('center');
    foreach (range('A', $licensesSheet->getHighestDataColumn()) as $col) {
        $licensesSheet->getColumnDimension($col)
            ->setAutoSize(true);
    }
}

/**
 * @param DBECustomer $dbeCustomer
 * @param $userName
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

function raiseCNCRequest($license, DBECustomer $dbeCustomer, $licenseUser = null)
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

    $details = "<p>License $license was not found for customer " . $dbeCustomer->getValue(
            DBECustomer::name
        ) . ($licenseUser ? " which is assigned to user $licenseUser." : '') . "</p>
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