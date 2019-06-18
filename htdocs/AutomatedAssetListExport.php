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
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require './../vendor/autoload.php';
global $db;

$dbeCustomer = new DBECustomer($thing);

$dbeCustomer->getActiveCustomers();


$customerIDs = [];

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
while ($dbeCustomer->fetchNext()) {

    $query = /** @lang MySQL */
        "SELECT 
  locations.name AS \"Location\",
  computers.name AS \"Computer Name\",
  SUBSTRING_INDEX(lastusername, '\\\', - 1) AS \"Last User\",
  computers.localaddress AS \"IP Address\",
   DATE_FORMAT(
    computers.lastContact,
    '%d/%m/%Y %H:%i:%s'
  ) AS \"Last Contact\",
  inv_chassis.productname AS \"Model\",
  if(inv_chassis.serialnumber like '%VMware%', null,inv_chassis.serialnumber )        AS \"Serial No.\",
  DATE_FORMAT(
    STR_TO_DATE(inv_bios.biosdate, '%m/%d/%Y'),
    '%d/%m/%Y'
  ) AS \"BIOS Date\",
  inv_processor.name AS \"CPU\",
  cim_processorfamily.value AS \"CPU Type\",
  computers.totalmemory AS \"Memory\",
  SUM(drives.Size) AS \"Total Disk\",
  if(exd.`Bitlocker Password/Key` is not null and exd.`Bitlocker Password/Key` <> '','Encrypted',null) as 'Drive Encryption',
  SUBSTRING_INDEX(
    computers.os,
    'Microsoft Windows ',
    - 1
  ) AS \"Operating System\",
  computers.version AS \"Version\",
       (select endOfSupportDate from ($fakeTable) f where computers.os = f.osName and computers.version like concat('%', f.version, '%') limit 1) as `OS End of Support Date`,
  computers.domain AS 'Domain',
  SUBSTRING_INDEX(
    software.name,
    'Microsoft Office ',
    - 1
  ) AS \"Office Version\",
  virusscanners.name AS AV,
  DATE_FORMAT(
    STR_TO_DATE(computers.VirusDefs, '%Y%m%d'),
    '%d/%m/%Y'
  ) AS \"AV Definition\" 
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
  LEFT JOIN (inv_processor) 
    ON (
      computers.computerid = inv_processor.computerid 
      AND inv_processor.enabled = 1
    ) 
  LEFT JOIN (cim_processorfamily) 
    ON (
      inv_processor.family = cim_processorfamily.id
    ) 
  LEFT JOIN (software) 
    ON (
      computers.computerid = software.computerid 
      AND software.name LIKE \"%microsoft office%\" 
      AND software.name NOT LIKE \"%visio%\" 
      AND software.name NOT LIKE \"%Activation%\" 
      AND software.name NOT LIKE \"%Access%\" 
      AND software.name NOT LIKE \"%Communicator%\" 
      AND software.name NOT LIKE \"%Converter%\" 
      AND software.name NOT LIKE \"%Excel%\" 
      AND software.name NOT LIKE \"%Frontpage%\" 
      AND software.name NOT LIKE \"%Infopage%\" 
      AND software.name NOT LIKE \"%demand%\" 
      AND software.name NOT LIKE \"%outlook%\" 
      AND software.name NOT LIKE \"%onenote%\" 
      AND software.name NOT LIKE \"%powerpoint%\" 
      AND software.name NOT LIKE \"%project%\" 
      AND software.name NOT LIKE \"%sharepoint%\" 
      AND software.name NOT LIKE \"%web%\" 
      AND software.name NOT LIKE \"%word%\" 
      AND software.name NOT LIKE \"%Live%\" 
      AND software.name NOT LIKE \"%Assemblies%\" 
      AND software.name NOT LIKE \"%Validation%\" 
      AND software.name NOT LIKE \"%Click-to-run%\" 
      AND software.name NOT LIKE \"%Sounds%\" 
      AND software.name NOT LIKE \"%Language%\" 
      AND software.name NOT LIKE \"%Resource%\" 
      AND software.name NOT LIKE \"%communications%\" 
      AND software.name NOT LIKE \"%media%\" 
      AND software.name NOT LIKE \"%ODF%\" 
      AND software.name NOT LIKE \"%SDK%\"
    ) 
  LEFT JOIN (inv_bios) 
    ON (
      computers.computerid = inv_bios.computerid
    ) 
  LEFT JOIN (inv_chassis) 
    ON (
      computers.computerid = inv_chassis.computerid
    ) 
  LEFT JOIN (drives) 
    ON (
      computers.computerid = drives.computerid 
      AND drives.filesystem = \"NTFS\" 
      AND drives.missing = \"0\" 
      AND drives.internal = \"1\"
    )  
  LEFT JOIN (virusscanners) 
    ON (
      computers.VirusScanner = virusscanners.vscanid
    )
  left join v_extradatacomputers exd
  on (exd.computerid = computers.computerid)
    where clients.externalID = ? 
GROUP BY computers.computerid 
ORDER BY clients.name,
  computers.os,
  computers.name,
  software.name";

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
        continue;
    }
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);
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

        $sheet->getStyle("A1:T1")->getFont()->setBold(true);

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
                $sheet->getStyle("A$currentRow:T$currentRow")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($color);
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

                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::createdDate,
                    (new DateTime())->format(DATE_MYSQL_DATETIME)
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








