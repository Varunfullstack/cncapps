<?php

namespace CNCLTD\AssetListExport;

use BUHeader;
use DataSet;
use DateTime;
use DBECustomer;
use DBEHeader;
use DBEPortalCustomerDocument;
use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use UnexpectedValueException;

class AssetListExporter
{
    /**
     * @var PDO
     */
    private $labTechDB;
    /**
     * @var OperatingSystemsSupportDatesCollection
     */
    private $operatingSystemsCollection;
    /**
     * @var string
     */
    private $query;
    /**
     * @var array|bool|float|int|string|null
     */
    private $thresholdDays;
    private $dataHeaders = [
        "Location",
        "Computer Name",
        "Last User",
        "Last Contact",
        "Model",
        "Warranty Start Date",
        "Warranty Expiry Date",
        "Age in Years",
        "Serial No.",
        "CPU",
        "Memory",
        "Total Disk",
        "Drive Encryption",
        "Operating System",
        "Version",
        "OS End of Support Date",
        "Domain",
        "Office Version",
        "AV",
        "AV Definition"
    ];
    private $summaryData = [];
    private $buCustomer;

    /**
     * AssetListExporter constructor.
     */
    public function __construct()
    {
        $dsn                              = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
        $options                          = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
        $this->labTechDB                  = new PDO($dsn, LABTECH_DB_USERNAME, LABTECH_DB_PASSWORD, $options);
        $this->operatingSystemsCollection = new OperatingSystemsSupportDatesCollection();
        $this->summaryData                = array_merge(["Customer"], $this->dataHeaders);
        $BUHeader                         = new BUHeader($this);
        $dbeHeader                        = new DataSet($this);
        $BUHeader->getHeader($dbeHeader);
        $this->thresholdDays = $dbeHeader->getValue(DBEHeader::OSSupportDatesThresholdDays);
        if (!$this->thresholdDays) {
            throw new UnexpectedValueException('OS Support Dates Threshold days is empty');
        }
        $this->buCustomer = new \BUCustomer($this);

    }

    private function runExportForCustomer($customerId)
    {
        $dbeCustomer = new \DBECustomer($this);
        $dbeCustomer->getRow($customerId);
        $tabularData = new ExportedItemCollection(
            $dbeCustomer, $this->operatingSystemsCollection, $this->labTechDB, $this->thresholdDays
        );
        if (!$tabularData->hasData()) {
            return;
        }
        $this->summaryData = array_merge(
            $this->summaryData,
            $tabularData->getSummaryData()
        );
        $spreadsheet       = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($this->dataHeaders);
        $toExportData = $tabularData->getExportData();
        $sheet->fromArray(
            $toExportData,
            null,
            'A2'
        );
        $highestColumn = $sheet->getHighestColumn();
        $highestRow    = $sheet->getHighestRow();
        $sheet->getStyle("A1:{$highestColumn}1")->getFont()->setBold(true);
        $dateTime       = new DateTime();
        $legendRowStart = $highestRow + 2;
        $sheet->fromArray(
            [
                ["Operating System soon to be end of life"],
                ["Operating System is end of life"],
                ["Report generated at " . $dateTime->format("d-m-Y H:i:s")],
            ],
            null,
            'A' . $legendRowStart
        );
        $sheet->getStyle("A{$legendRowStart}:A$legendRowStart")->getFill()->setFillType(
            Fill::FILL_SOLID
        )->getStartColor()->setARGB("FFFFEB9C");
        $sheet->getStyle("A" . ($legendRowStart + 1) . ":A" . ($legendRowStart + 1))->getFill()->setFillType(
            Fill::FILL_SOLID
        )->getStartColor()->setARGB("FFFFC7CE");
        for ($i = 0; $i < count($toExportData); $i++) {
            $currentRow = 2 + $i;
            $color      = $tabularData->getOSEndOfSupportDateColor($i);
            if ($color) {
                $sheet->getStyle("A$currentRow:$highestColumn$currentRow")->getFill()->setFillType(
                    \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
                )->getStartColor()->setARGB($color);
//                if ($generateSummary) {
//                    $currentSummaryStyleRow = $currentSummaryRow + $i;
//                    $summarySheet->getStyle(
//                        "A$currentSummaryStyleRow:{$summarySheet->getHighestColumn()}$currentSummaryStyleRow"
//                    )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor(
//                    )->setARGB($color);
//                }
            }
        }
//        if ($generateSummary) {
//            if (!$isHeaderSet) {
//                $summarySheet->fromArray(array_merge(["Customer Name"], $keys));
//                $currentSummaryRow = 2;
//                $summarySheet->getStyle("A1:U1")->getFont()->setBold(true);
//                $isHeaderSet = true;
//            }
//            $summaryData = array_map(
//                function ($originalData) use ($customerName) {
//                    return array_merge(["Customer Name" => $customerName], $originalData);
//                },
//                $data
//            );
//            $summarySheet->fromArray($summaryData, null, 'A' . $currentSummaryRow);
//        }
        $sheet->getStyle("A1:T1")->getFont()->setBold(true);
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setHorizontal('center');
        $writer         = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $customerFolder = $this->buCustomer->getCustomerFolderPath($customerId);
        $folderName     = $customerFolder . "\Review Meetings\\";
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
            $dbeCustomerDocument->getCurrentAssetList($customerId);
            $dbeCustomerDocument->setValue(
                DBEPortalCustomerDocument::file,
                file_get_contents($fileName)
            );
            if (!$dbeCustomerDocument->getValue(
                    DBEPortalCustomerDocument::createdDate
                ) || $dbeCustomerDocument->getValue(
                    DBEPortalCustomerDocument::createdDate
                ) == '0000-00-00 00:00:00') {

                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::createdDate,
                    (new DateTime())->format(DATE_MYSQL_DATETIME)
                );
            }
            if (!$dbeCustomerDocument->rowCount) {
                $dbeCustomerDocument->setValue(
                    DBEPortalCustomerDocument::customerID,
                    $customerId
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
                    DBEPortalCustomerDocument::mainContactOnlyFlag,
                    'Y'
                );
                $dbeCustomerDocument->insertRow();
            } else {
                $dbeCustomerDocument->updateRow();
            }
            $updateCustomer = new DBECustomer($thing);
            $updateCustomer->getRow($customerId);
            $updateCustomer->setValue(DBECustomer::noOfPCs, $tabularData->getNumberOfPcs());
            $updateCustomer->setValue(DBECustomer::noOfServers, $tabularData->getNumberOfServers());
            $updateCustomer->updateRow();
            echo '<div>Data was found at labtech, creating file ' . $fileName . '</div>';
        } catch (Exception $exception) {
            echo '<div>Failed to save file, possibly file open</div>';
        }

    }

    public function generateSummary()
    {
//        $tempFileName = null;
//        echo '<h1>Generating Summary</h1>';
//        $summarySheet->setAutoFilter($summarySheet->calculateWorksheetDimension());
//        $summarySheet->getStyle($summarySheet->calculateWorksheetDimension())->getAlignment()->setHorizontal('center');
//        foreach (range('A', $summarySheet->getHighestDataColumn()) as $col) {
//            $summarySheet->getColumnDimension($col)->setAutoSize(true);
//        }
//        $password   = \CNCLTD\Utils::generateStrongPassword(16);
//        $writer     = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($summarySpreadSheet);
//        $folderName = TECHNICAL_DIR;
//        if (!file_exists($folderName)) {
//            mkdir(
//                $folderName,
//                0777,
//                true
//            );
//        }
//        $tempFileName = $folderName . "\\temp.xlsx";
//        $buPassword   = new BUPassword($thing);
//        try {
//            $writer->save(
//                $tempFileName
//            );
//            $definitiveFileName = $folderName . "\\Asset List Export.zip";
//            $zip                = new ZipArchive();
//            $res                = $zip->open($definitiveFileName, ZipArchive::CREATE);
//            if ($res === true) {
//                $zip->addFile($tempFileName, 'Asset List Export.xlsx');
//                $zip->setEncryptionName('Asset List Export.xlsx', ZipArchive::EM_AES_256, $password);
//                $zip->close();
//                $dbePassword = new DBEPassword($thing);
//                $dbePassword->getAutomatedFullAssetListPasswordItem();
//                $dbePassword->setValue(DBEPassword::password, $buPassword->encrypt($password));
//                $dbePassword->setValue(DBEPassword::username, null);
//                $dbePassword->setValue(DBEPassword::level, 5);
//                $dbePassword->setValue(DBEPassword::notes, 'Full List of Asset information');
//                $dbePassword->setValue(DBEPassword::URL, $buPassword->encrypt('file:' . $definitiveFileName));
//                $dbePassword->updateRow();
//            }
//        } catch (Exception $exception) {
//            echo '<div>Failed to save Summary file, possibly file open</div>';
//        }
//        if ($tempFileName && file_exists($tempFileName)) {
//            unlink($tempFileName);
//        }
    }

    public function exportForCustomer($customerId)
    {
        $this->runExportForCustomer($customerId);
    }

    public function exportForActiveCustomersWithSummary()
    {
        $this->exportForActiveCustomers();
        $this->generateSummary();
    }

    public function exportForActiveCustomers()
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getActiveCustomers(true);
        while ($dbeCustomer->fetchNext()) {
            $this->runExportForCustomer($dbeCustomer->getValue(DBECustomer::customerID));
        }
    }


}