<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

require_once("config.inc.php");
global $cfg;
require_once($cfg["path_dbe"] . "/DBEPortalCustomerDocument.php");
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBEPassword.inc.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require __DIR__ . '/../vendor/autoload.php';
global $db;

$dbeCustomer = new DBECustomer($thing);

$generateSummary = isset($_REQUEST['generateSummary']);
$customerID = isset($_REQUEST['customerID']) ? $_REQUEST['customerID'] : null;
if ($customerID) {
    $dbeCustomer->getRow($customerID);
} else {
    $dbeCustomer->getActiveCustomers();
}
//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$labtechDB = new PDO(
    $dsn,
    LABTECH_DB_USERNAME,
    LABTECH_DB_PASSWORD,
    $options
);
$DBEOSSupportDates = new DBEOSSupportDates($thing);

$DBEOSSupportDates->getRows();
$fakeTable = null;
while ($DBEOSSupportDates->fetchNext()) {
    if (!$DBEOSSupportDates->getValue(DBEOSSupportDates::endOfLifeDate)) {
        continue;
    }
    if ($fakeTable) {
        $fakeTable .= " union all ";
    }
    $date = DateTime::createFromFormat('Y-m-d', $DBEOSSupportDates->getValue(DBEOSSupportDates::endOfLifeDate));

    $fakeTable .= " select '" . $DBEOSSupportDates->getValue(
            DBEOSSupportDates::name
        ) . "' as osName,  '" . $DBEOSSupportDates->getValue(
            DBEOSSupportDates::version
        ) . "' as version, '" . $date->format('d/m/Y') . "' as endOfSupportDate";
}

if (!$fakeTable) {
    $fakeTable = "select null as endOfSupportDate, null as osName, null as version";
}

$BUHeader = new BUHeader($thing);
$dbeHeader = new DataSet($thing);
$BUHeader->getHeader($dbeHeader);
$thresholdDays = $dbeHeader->getValue(DBEHeader::OSSupportDatesThresholdDays);

if (!$thresholdDays) {
    throw new Exception('OS Support Dates Threshold days is empty');
}

$buCustomer = new BUCustomer($thing);
$thresholdDate = new DateTime();
$thresholdDate->add(new DateInterval('P' . $thresholdDays . 'D'));

$today = new DateTime();

$currentSummaryRow = 1;
if ($generateSummary) {
    $summarySpreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $summarySpreadSheet->getDefaultStyle()->getFont()->setName('Arial');
    $summarySpreadSheet->getDefaultStyle()->getFont()->setSize(10);
    $summarySheet = $summarySpreadSheet->getActiveSheet();
    $isHeaderSet = false;
}

function getUnrepeatedUsername($str)
{
    $n = strlen($str);
    if ($n < 6) {
        return $str;
    }
    $length = 3;
    $match = false;
    do {
        $prospect = substr($str, 0, $length);
        $restOfTheString = substr($str, $length, $length);
        if (strlen($restOfTheString) < $length) {
            return $str;
        }
        if ($restOfTheString == $prospect) {
            // we have a match...but we need to analyze next part of the string...just in case
            if ($length * 2 == $n) {
                return $prospect;
            }
            $nextRestOfString = substr($str, $length * 2, $length);
            if ($prospect == $nextRestOfString) {
                return $prospect;
            }
        }

        $length++;
    } while (!$match && $length < $n);

    return $prospect;
}

while ($dbeCustomer->fetchNext()) {

    $query = /** @lang MySQL */
        'SELECT 
  locations.name AS "Location",
  computers.name AS "Computer Name",
  SUBSTRING_INDEX(lastusername, \'\\\\\', - 1) AS "Last User",
   DATE_FORMAT(
    computers.lastContact,
    \'%d/%m/%Y %H:%i:%s\'
  ) AS "Last Contact",
  inv_chassis.productname AS "Model",
 if(inv_chassis.productname like "%VMware%", "Not Applicable",coalesce((select DATE_FORMAT(PurchaseDate,"%d-%m-%Y") from plugin_warrantymaster_aux where ComputerID = computers.computerid ), "Unknown")) as "Warranty Start Date",
  if(inv_chassis.productname like "%VMware%", "Not Applicable",coalesce((select DATE_FORMAT(ExpiryDate,"%d-%m-%Y") from plugin_warrantymaster_aux where ComputerID = computers.computerid ), "Unknown")) as "Warranty Expiry Date",
IF(
    (SELECT
      ExpiryDate
    FROM
      plugin_warrantymaster_aux
    WHERE ComputerID = computers.computerid) IS NOT NULL,
    (SELECT
      ROUND(
        TIMESTAMPDIFF(YEAR, PurchaseDate, CURDATE()) + (
          (
            TIMESTAMPDIFF(MONTH, PurchaseDate, CURDATE()) - (
              TIMESTAMPDIFF(YEAR, PurchaseDate, CURDATE()) * 12
            )
          ) / 12
        ),
        1
      )
    FROM
      plugin_warrantymaster_aux
    WHERE ComputerID = computers.computerid),
    NULL
  ) AS "Age in Years",
  if(inv_chassis.serialnumber like \'%VMware%\', null,inv_chassis.serialnumber )        AS "Serial No.",
  processor.name AS "CPU",
  computers.totalmemory AS "Memory",
  SUM(drives.Size) AS "Total Disk",
  if(exd.`Bitlocker Enabled` and exd.`Bitlocker Password/Key` regexp \'[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}\',\'Encrypted\',null) as \'Drive Encryption\',
  SUBSTRING_INDEX(
    computers.os,
    \'Microsoft Windows \',
    - 1
  ) AS "Operating System",
  computers.version AS "Version",
       (select endOfSupportDate from (' . $fakeTable . ') f where computers.os = f.osName and computers.version like concat(\'%\', f.version, \'%\') limit 1) as `OS End of Support Date`,
  computers.domain AS \'Domain\',
  SUBSTRING_INDEX(
    software.name,
    \'Microsoft Office \',
    - 1
  ) AS "Office Version",
  virusscanners.name AS AV,
  DATE_FORMAT(
    STR_TO_DATE(computers.VirusDefs, \'%Y%m%d\'),
    \'%d/%m/%Y\'
  ) AS "AV Definition"
FROM
  computers 
  LEFT JOIN (clients) 
    ON (
      computers.clientid = clients.clientid
    ) 
  LEFT JOIN (locations) 
    ON (
      computers.locationid = locations.locationid
    ) 
  LEFT JOIN
(SELECT
*
FROM
inv_processor
WHERE inv_processor.Enabled = 1
GROUP BY inv_processor.computerid) processor
ON computers.computerid = processor.computerid
  LEFT JOIN (software) 
    ON (
      computers.computerid = software.computerid 
      AND software.name LIKE "%microsoft office%" 
      AND software.name NOT LIKE "%visio%" 
      AND software.name NOT LIKE "%Activation%" 
      AND software.name NOT LIKE "%Access%" 
      AND software.name NOT LIKE "%Communicator%" 
      AND software.name NOT LIKE "%Converter%" 
      AND software.name NOT LIKE "%Excel%" 
      AND software.name NOT LIKE "%Frontpage%" 
      AND software.name NOT LIKE "%Infopage%" 
      AND software.name NOT LIKE "%demand%" 
      AND software.name NOT LIKE "%outlook%" 
      AND software.name NOT LIKE "%onenote%" 
      AND software.name NOT LIKE "%powerpoint%" 
      AND software.name NOT LIKE "%project%" 
      AND software.name NOT LIKE "%sharepoint%" 
      AND software.name NOT LIKE "%web%" 
      AND software.name NOT LIKE "%word%" 
      AND software.name NOT LIKE "%Live%" 
      AND software.name NOT LIKE "%Assemblies%" 
      AND software.name NOT LIKE "%Validation%" 
      AND software.name NOT LIKE "%Click-to-run%" 
      AND software.name NOT LIKE "%Sounds%" 
      AND software.name NOT LIKE "%Language%" 
      AND software.name NOT LIKE "%Resource%" 
      AND software.name NOT LIKE "%communications%" 
      AND software.name NOT LIKE "%media%" 
      AND software.name NOT LIKE "%ODF%" 
      AND software.name NOT LIKE "%SDK%"
    ) 
  LEFT JOIN (inv_chassis) 
    ON (
      computers.computerid = inv_chassis.computerid
    ) 
  LEFT JOIN (drives) 
    ON (
      computers.computerid = drives.computerid 
      AND drives.filesystem = "NTFS" 
      AND drives.missing = "0" 
      AND drives.internal = "1"
    )  
  LEFT JOIN (virusscanners) 
    ON (
      computers.VirusScanner = virusscanners.vscanid
    )
  left join v_extradatacomputers exd
  on (exd.computerid = computers.computerid)
    where clients.externalID = ? 
GROUP BY computers.computerid 
ORDER BY Location, `Computer Name`';

    $customerID = $dbeCustomer->getValue(DBECustomer::customerID);
    $customerName = $dbeCustomer->getValue(DBECustomer::name);

    echo '<div>Getting Labtech Data for Customer: ' . $customerID . ' - ' . $customerName . '</div>';
    $statement = $labtechDB->prepare($query);
    $test = $statement->execute(
        [
            $customerID
        ]
    );
    if (!$test) {
        echo '<div>Something went wrong...' . implode(
                ',',
                $statement->errorInfo()
            );
        var_dump($query);
        echo ' </div>';
        exit;
        continue;
    }
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data as $key => $datum) {
        $text = $datum['Last User'];
        $text = str_replace('null', "", $text);
        $data[$key]['Last User'] = getUnrepeatedUsername($text);
    }


    if (count($data)) {
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
        if ($generateSummary) {
            if (!$isHeaderSet) {
                $summarySheet->fromArray(array_merge(["Customer Name"], $keys));
                $currentSummaryRow = 2;
                $summarySheet->getStyle("A1:{$summarySheet->getHighestColumn()}1")->getFont()->setBold(true);
                $isHeaderSet = true;
            }

            $summaryData = array_map(
                function ($originalData) use ($customerName) {
                    return array_merge(["Customer Name" => $customerName], $originalData);
                },
                $data
            );

            $summarySheet->fromArray($summaryData, null, 'A' . $currentSummaryRow);
        }
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:{$highestColumn}1")->getFont()->setBold(true);

        $sheet->setAutoFilter(
            $sheet->calculateWorksheetDimension()
        );

        for ($i = 0; $i < count($data); $i++) {
            if (!$data[$i]['OS End of Support Date']) {
                continue;
            }
            $date = DateTime::createFromFormat('d/m/Y', $data[$i]['OS End of Support Date']);

            if (!$date) {
                continue;
            }
            $currentRow = 2 + $i;

            $color = null;
            if ($date <= $thresholdDate) {
                $color = "FFFFEB9C";
            }

            if ($date <= $today) {
                $color = "FFFFC7CE";
            }

            if ($color) {
                $sheet->getStyle("A$currentRow:$highestColumn$currentRow")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($color);

                if ($generateSummary) {
                    $currentSummaryStyleRow = $currentSummaryRow + $i;
                    $summarySheet->getStyle(
                        "A$currentSummaryStyleRow:{$summarySheet->getHighestColumn()}$currentSummaryStyleRow"
                    )
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB($color);
                }
            }

        }
        $currentSummaryRow += count($data);
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)
                ->setAutoSize(true);
        }
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setHorizontal('center');
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

        $fileName = $folderName . "Current Asset List Extract.xlsx";
        try {
            $writer->save(
                $fileName
            );
            $dbeCustomerDocument = new DBEPortalCustomerDocument($thing);
            $dbeCustomerDocument->getCurrentAssetList($customerID);

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
                    'Current Asset List'
                );
                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::filename,
                    "Current Asset List Extract.xlsx"
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

            echo '<div>Data was found at labtech, creating file ' . $fileName . '</div>';
        } catch (\Exception $exception) {
            echo '<div>Failed to save file, possibly file open</div>';
        }

    } else {
        echo '<div>No Data was found</div>';
    }
};
$tempFileName = null;
if ($generateSummary) {
    echo '<h1>Generating Summary</h1>';
    $summarySheet->setAutoFilter($summarySheet->calculateWorksheetDimension());
    $summarySheet->getStyle($summarySheet->calculateWorksheetDimension())->getAlignment()->setHorizontal('center');
    foreach (range('A', $summarySheet->getHighestDataColumn()) as $col) {
        $summarySheet->getColumnDimension($col)
            ->setAutoSize(true);
    }
    $password = \CNCLTD\Utils::generateStrongPassword(16);

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($summarySpreadSheet);
    $folderName = TECHNICAL_DIR;
    if (!file_exists($folderName)) {
        mkdir(
            $folderName,
            0777,
            true
        );
    }
    $tempFileName = $folderName . "\\temp.xlsx";

    $buPassword = new BUPassword($thing);
    try {
        $writer->save(
            $tempFileName
        );
        $definitiveFileName = $folderName . "\\Asset List Export.zip";
        $zip = new ZipArchive();
        $res = $zip->open($definitiveFileName, ZipArchive::CREATE);
        if ($res === true) {
            $zip->addFile($tempFileName, 'Asset List Export.xlsx');
            $zip->setEncryptionName('Asset List Export.xlsx', ZipArchive::EM_AES_256, $password);
            $zip->close();

            $dbePassword = new DBEPassword($thing);
            $dbePassword->getAutomatedFullAssetListPasswordItem();
            $dbePassword->setValue(DBEPassword::password, $buPassword->encrypt($password));
            $dbePassword->setValue(DBEPassword::username, null);
            $dbePassword->setValue(DBEPassword::level, 5);
            $dbePassword->setValue(DBEPassword::notes, 'Full List of Asset information');
            $dbePassword->setValue(DBEPassword::URL, $buPassword->encrypt('file:' . $definitiveFileName));
            $dbePassword->updateRow();
        }
    } catch (Exception $exception) {
        echo '<div>Failed to save Summary file, possibly file open</div>';
    }
    if ($tempFileName && file_exists($tempFileName)) {
        unlink($tempFileName);
    }
}







