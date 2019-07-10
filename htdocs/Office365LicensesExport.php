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
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require './../vendor/autoload.php';
global $db;

$dbeCustomer = new DBECustomer($thing);

if (isset($_REQUEST['customerID'])) {

    $dbeCustomer->getRow($_REQUEST['customerID']);
    echo $dbeCustomer->getValue(DBECustomer::name);
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


/**
 * @param DataSet|DBECustomer $dbeCustomer
 * @param $errorMsg
 * @param null $stackTrace
 * @param null $position
 */
function createFailedSR(DataSet $dbeCustomer, $errorMsg, $stackTrace = null, $position = null)
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

$buCustomer = new BUCustomer($thing);
$buPassword = new BUPassword($thing);
$dbeOffice365Licenses = new DBEOffice365License($thing);
do {

    $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
    $customerName = $dbeCustomer->getValue(DBECustomer::name);

    echo '<div>Getting Office 365 Data for Customer: ' . $customerID . ' - ' . $customerName . '</div>';

    // we have to pull from passwords.. the service 10

    $dbePassword = $buCustomer->getOffice365PasswordItem($customerID);

    if (!$dbePassword->rowCount) {
        echo '<div> This customer does not have a Office 365 Admin Portal service password</div>';
        continue;
    }

    $userName = $buPassword->decrypt($dbePassword->getValue(DBEPassword::username));
    $password = $buPassword->decrypt($dbePassword->getValue(DBEPassword::password));


    $path = POWERSHELL_DIR . "/365OfficeLicensesExport.ps1";

    $cmd = "powershell.exe -executionpolicy bypass -NoProfile -command $path -User '$userName' -Password '$password'";
    $output = shell_exec($cmd);

    $data = json_decode($output, true);

    if (!$data) {
        echo '<div>Failed to parse for customer: ' . $output . '</div>';
        createFailedSR($dbeCustomer, "Could not parse Powershell response: $output");
        continue;
    }
    if (isset($data['error'])) {
        echo '<div>Failed to pull data for customer: ' . $data['errorMessage'] . '</div>';
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
    foreach ($data as $key => $datum) {
        $values = [];
        $mailboxLimit = null;
        $licenseValue = null;
        if ($datum['Licenses']) {
            if (!is_array($datum['Licenses'])) {
                $datum['Licenses'] = [$datum['Licenses']];
            }
            $licenseValue = implode(" ", $datum['Licenses']);
            foreach ($datum['Licenses'] as $license) {
                $dbeOffice365Licenses->getRowForLicense($license);
                if ($dbeOffice365Licenses->rowCount) {
                    $licenseValue = str_replace(
                        $license,
                        $dbeOffice365Licenses->getValue(DBEOffice365License::replacement),
                        $licenseValue
                    );
                    if (!$mailboxLimit && $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit)) {
                        $mailboxLimit = $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit);
                    }
                }
            }
        }
        $data[$key]['RecipientTypeDetails'] = $data[$key]['RecipientTypeDetails'] == "SharedMailbox" ? "Shared Mailbox" : ($data[$key]['RecipientTypeDetails'] == "UserMailbox" ? "User Mailbox" : $data[$key]['RecipientTypeDetails']);
        $data[$key]['Licenses'] = $licenseValue;
        $data[$key]['IsLicensed'] = $data[$key]['IsLicensed'] ? 'Yes' : 'No';
        $totalizationRow['TotalMailBox'] += $datum['TotalItemSize'];
        $totalizationRow['LicensedUsers'] += $datum['IsLicensed'];

        $mailboxLimits[] = $mailboxLimit;
    }

//    uasort($data, function ($a, $b) { return $a['TotalItemSize'] - $b['TotalItemSize']; });

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
    $sheet = $spreadsheet->getActiveSheet();
    $dateTime = new DateTime();
    $sheet->fromArray(["Report generated at " . $dateTime->format("d-m-Y H:i:s")]);
    $sheet->fromArray(
        [
            "Display Name",
            "Mailbox Size (MB)",
            "Mailbox Type",
            "Is Licensed",
            "Licenses"
        ],
        null,
        'A2'
    );
    $sheet->fromArray(
        $data,
        null,
        'A3'
    );
    $highestRow = count($data) + 3;
    $totalizationRow['LicensedUsers'] = "$totalizationRow[LicensedUsers] Licensed Users";
    $sheet->fromArray(
        $totalizationRow,
        null,
        'A' . $highestRow
    );

    $sheet->getStyle("A$highestRow:E$highestRow")->getFont()->setBold(true);

    $sheet->getStyle("A2:E2")->getFont()->setBold(true);

    for ($i = 0; $i < count($data); $i++) {
        $currentRow = 3 + $i;

        if ($mailboxLimits[$i]) {
            $usage = $data[$i]['TotalItemSize'] / $mailboxLimits[$i] * 100;
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

    $fileName = $folderName . "O365 Licenses.xlsx";
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

        echo '<div>All good!!. Creating file ' . $fileName . '</div>';
    } catch (\Exception $exception) {
        echo '<div>Failed to save file, possibly file open</div>';
    }
} while ($dbeCustomer->fetchNext());








