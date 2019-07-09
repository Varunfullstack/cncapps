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
        continue;
    }
    if (isset($data['error'])) {
        echo '<div>Failed to pull data for customer: ' . $data['errorMessage'] . '</div>';
        continue;
    }
    echo '<pre>';
    var_dump($data);

// we are going to build an array from the data
    $mailboxLimits = [];
    foreach ($data as $key => $datum) {
        $values = [];
        $mailboxLimit = null;
        $licenseValue = null;
        if ($datum['Licenses']) {
            if (!is_array($datum['Licenses'])) {
                $datum['Licenses'] = [$datum['Licenses']];
            }
            $licenseValue = implode(" ", $datum['Licenses']);
            $dbeOffice365Licenses->getRowForLicenses($datum['Licenses']);
            if ($dbeOffice365Licenses->rowCount) {
                $licenseValue = $dbeOffice365Licenses->getValue(DBEOffice365License::replacement);
                $mailboxLimit = $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit);
            }
        }
        $data[$key]['Licenses'] = $licenseValue;
        $data[$key]['IsLicensed'] = $data[$key]['IsLicensed'] ? 'True' : 'False';

        $mailboxLimits[] = $mailboxLimit;
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
    $sheet = $spreadsheet->getActiveSheet();
    $keys = array_keys($data[0]);
    $sheet->fromArray($keys);
    $sheet->fromArray(
        $data,
        null,
        'A2'
    );

    $sheet->getStyle("A1:E1")->getFont()->setBold(true);

    $sheet->setAutoFilter(
        $sheet->calculateWorksheetDimension()
    );

    for ($i = 0; $i < count($data); $i++) {
        $currentRow = 2 + $i;
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








