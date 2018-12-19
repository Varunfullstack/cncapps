<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 17/12/2018
 * Time: 11:26
 */

require_once("config.inc.php");
require './../vendor/autoload.php';
global $db;


$db->query('select * from customer where cus_referred <> "Y"');

$customerIDs = [];

//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=192.168.33.64;dbname=labtech';
$DB_USER = "root";
$DB_PASSWORD = "kj389fj29fjh";
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$labtechDB = new PDO(
    $dsn,
    $DB_USER,
    $DB_PASSWORD,
    $options
);

while ($db->next_record(MYSQLI_ASSOC)) {

    $query = /** @lang MySQL */
        "SELECT 
  locations.name AS \"Location\",
  computers.name AS \"Computer Name\",
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
  SUBSTRING_INDEX(
    computers.os,
    'Microsoft Windows ',
    - 1
  ) AS \"Operating System\",
  computers.version AS \"Version\",
  computers.domain AS 'Domain',
  SUBSTRING_INDEX(lastusername, '\\\', - 1) AS \"Last User\",
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
    where clients.externalID = ? 
GROUP BY computers.computerid 
ORDER BY clients.name,
  computers.os,
  computers.name,
  software.name";
    echo '<div>Getting Labtech Data for Customer: ' . $db->Record['cus_custno'] . ' - ' . $db->Record['cus_name'] . '</div>';
    $statement = $labtechDB->prepare($query);
    $test = $statement->execute([$db->Record['cus_custno']]);
    if (!$test) {
        echo '<div>Something went wrong...' . $statement->errorInfo() . ' </div>';

        return;
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

        $sheet->setAutoFilter(
            $sheet->calculateWorksheetDimension()
        );

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $folderName = '\\\cncltd.local\cnc\Customer\\' . $db->Record['cus_name'] . "\Review Meetings\\";
        if (!file_exists($folderName)) {
            mkdir(
                $folderName,
                0777,
                true
            );
        }

        $fileName = $folderName . "Current Asset List Extract.xlsx";
        $writer->save(
            $fileName
        );
        echo '<div>Data was found at labtech, creating file ' . $fileName . '</div>';

    } else {
        echo '<div>No Data was found</div>';
    }

};








