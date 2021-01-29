<?php

namespace CNCLTD\AssetListExport;

use BUCustomer;
use BUHeader;
use DataSet;
use DateInterval;
use DateTime;
use DBECustomer;
use DBEHeader;
use DBEPortalCustomerDocument;
use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use UnexpectedValueException;

class AssetListExporter
{
    const OPERATING_SYSTEM_SOON_END_OF_LIFE_COLOR = "B59DB6";
    const OPERATING_SYSTEM_IS_END_OF_LIFE_COLOR   = "FFFFC7CE";
    const ANTIVIRUS_OUT_OF_DATE_COLOR             = "F9B67F";
    const NO_VENDOR_WARRANTY_COLOR                = "FFC7CE";
    const OFFLINE_AGENT_COLOR                     = "C4BD97";
    const OS_END_OF_SUPPORT_DATE_COLUMN_INDEX     = 16;
    const ANTIVIRUS_DEFINITION_COLUMN_INDEX       = 20;
    const LAST_CONTACT_COLUMN_INDEX               = 4;
    const WARRANTY_EXPIRY_DATE_COLUMN_INDEX       = 7;
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
    private $osSupportDatesThresholdDays;
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
     * @var array|bool|float|int|string|null
     */
    private $antivirusOutOfDateThresholdDays;
    /**
     * @var array|bool|float|int|string|null
     */
    private $offlineAgentThresholdDays;
    private $patchManagementEligibleComputers = 0;

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
        $this->osSupportDatesThresholdDays = $dbeHeader->getValue(DBEHeader::OSSupportDatesThresholdDays);
        if (!$this->osSupportDatesThresholdDays) {
            throw new UnexpectedValueException('OS Support Dates Threshold days is empty');
        }
        $this->offlineAgentThresholdDays       = $dbeHeader->getValue(DBEHeader::offlineAgentThresholdDays);
        $this->antivirusOutOfDateThresholdDays = $dbeHeader->getValue(DBEHeader::antivirusOutOfDateThresholdDays);
        $this->buCustomer                      = new BUCustomer($this);

    }

    private function runExportForCustomer($customerId)
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerId);
        $tabularData = new ExportedItemCollection(
            $dbeCustomer, $this->operatingSystemsCollection, $this->labTechDB
        );
        if (!$tabularData->hasData()) {
            return;
        }
        $this->summaryData = array_merge(
            $this->summaryData,
            $tabularData->getSummaryData()
        );
        $spreadsheet       = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($this->dataHeaders);
        $toExportData = $tabularData->getExportData();
        $dbeHeader    = new DBEHeader($this);
        $dbeHeader->getRow(1);
        $sheet->fromArray(
            $toExportData,
            null,
            'A2'
        );
        $highestColumn = $sheet->getHighestColumn();
        $this->setHeaderRowToBold($sheet);
        $this->addLegend($sheet, $dbeHeader);
        for ($i = 0; $i < count($toExportData); $i++) {
            $currentRow              = 2 + $i;
            $OSEndOfSupportDateColor = $this->getOSEndOfSupportDateColor($tabularData, $i);
            if ($OSEndOfSupportDateColor) {
                $columnCoordinate = Coordinate::stringFromColumnIndex(self::OS_END_OF_SUPPORT_DATE_COLUMN_INDEX);
                $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $OSEndOfSupportDateColor);
            }
            $antivirusOutOfDateColor = $this->getAntivirusOutOfDateColor($toExportData[$i]);
            if ($antivirusOutOfDateColor) {
                $columnCoordinate = Coordinate::stringFromColumnIndex(self::ANTIVIRUS_DEFINITION_COLUMN_INDEX);
                $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $antivirusOutOfDateColor);
            }
            $lastContactColor = $this->getLastContactColor($toExportData[$i]);
            if ($lastContactColor) {
                $columnCoordinate = Coordinate::stringFromColumnIndex(self::LAST_CONTACT_COLUMN_INDEX);
                $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $lastContactColor);
                $operatingSystem = $tabularData->getOperatingSystem($i);
                if (strpos(strtolower($operatingSystem), "microsoft") > -1) {
                    $this->patchManagementEligibleComputers++;
                }
            }
            $warrantyColor = $this->getWarrantyColor($toExportData[$i]);
            if ($warrantyColor) {
                $columnCoordinate = Coordinate::stringFromColumnIndex(self::WARRANTY_EXPIRY_DATE_COLUMN_INDEX);
                $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $warrantyColor);
            }
        }
        $sheet->getStyle("A1:T1")->getFont()->setBold(true);
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setHorizontal('center');
        $writer         = new Xlsx($spreadsheet);
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
            $updateCustomer->setValue(DBECustomer::eligiblePatchManagement, $this->patchManagementEligibleComputers);
            $updateCustomer->updateRow();
            echo '<div>Data was found at labtech, creating file ' . $fileName . '</div>';
        } catch (Exception $exception) {
            echo '<div>Failed to save file, possibly file open</div>';
        }

    }

    public function generateSummary()
    {

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
        //                if ($generateSummary) {
//                    $currentSummaryStyleRow = $currentSummaryRow + $i;
//                    $summarySheet->getStyle(
//                        "A$currentSummaryStyleRow:{$summarySheet->getHighestColumn()}$currentSummaryStyleRow"
//                    )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor(
//                    )->setARGB($color);
//                }
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

    /**
     * @param Worksheet $sheet
     * @param DBEHeader $dbeHeader
     */
    private function addLegend(Worksheet $sheet,
                               DBEHeader $dbeHeader
    ): void
    {
        $highestRow     = $sheet->getHighestRow();
        $dateTime       = new DateTime();
        $legendRowStart = $highestRow + 2;
        $sheet->fromArray(
            [
                ["Operating System soon to be end of life"],
                ["Operating System is end of life"],
                ["Antivirus Out of Date By More Than {$dbeHeader->getValue(DBEHeader::antivirusOutOfDateThresholdDays)} Days"],
                ["No Vendor Warranty"],
                ["Offline for More Than {$dbeHeader->getValue(DBEHeader::offlineAgentThresholdDays)} Days"],
                ["Report generated at " . $dateTime->format("d-m-Y H:i:s")],
            ],
            null,
            'A' . $legendRowStart
        );
        $range = "A{$legendRowStart}";
        $this->setCellSolidColor($sheet, $range, self::OPERATING_SYSTEM_SOON_END_OF_LIFE_COLOR);
        $legendRowStart++;
        $this->setCellSolidColor($sheet, "A{$legendRowStart}", self::OPERATING_SYSTEM_IS_END_OF_LIFE_COLOR);
        $legendRowStart++;
        $this->setCellSolidColor($sheet, "A{$legendRowStart}", self::ANTIVIRUS_OUT_OF_DATE_COLOR);
        $legendRowStart++;
        $this->setCellSolidColor($sheet, "A{$legendRowStart}", self::NO_VENDOR_WARRANTY_COLOR);
        $legendRowStart++;
        $this->setCellSolidColor($sheet, "A{$legendRowStart}", self::OFFLINE_AGENT_COLOR);
    }

    /**
     * @param Worksheet $sheet
     * @param string $cellCoordinate
     * @param string $color
     */
    private function setCellSolidColor(Worksheet $sheet, string $cellCoordinate, string $color): void
    {
        $sheet->getStyle($cellCoordinate)->getFill()->setFillType(
            Fill::FILL_SOLID
        )->getStartColor()->setARGB($color);
    }

    /**
     * @param Worksheet $sheet
     */
    private function setHeaderRowToBold(Worksheet $sheet): void
    {
        $sheet->getStyle("A1:{$sheet->getHighestColumn()}1")->getFont()->setBold(true);
    }

    /**
     * @param ExportedItemCollection $tabularData
     * @param int $i
     * @return string|null
     */
    private function getOSEndOfSupportDateColor(ExportedItemCollection $tabularData, int $i): ?string
    {
        $date = $tabularData->getOSEndOfSupportDate($i);
        if (!$date) {
            return null;
        }
        $thresholdDate = new DateTime();
        $thresholdDate->add(new DateInterval('P' . $this->osSupportDatesThresholdDays . 'D'));
        $today = new DateTime();
        if ($date <= $thresholdDate) {
            return self::OPERATING_SYSTEM_SOON_END_OF_LIFE_COLOR;
        }
        if ($date <= $today) {
            return self::OPERATING_SYSTEM_IS_END_OF_LIFE_COLOR;
        }
        return null;
    }

    private function getAntivirusOutOfDateColor($exportArray): ?string
    {
        $index          = self::ANTIVIRUS_DEFINITION_COLUMN_INDEX - 1;
        $definitionDate = $exportArray[$index];
        if (!$definitionDate) {
            return null;
        }
        $date = DateTime::createFromFormat(DATE_CNC_DATE_FORMAT, $definitionDate);
        $date->add(new DateInterval('P' . $this->antivirusOutOfDateThresholdDays . 'D'));
        $today = new DateTime();
        if ($date > $today) {
            return null;
        }
        return self::ANTIVIRUS_OUT_OF_DATE_COLOR;
    }

    private function getLastContactColor($exportArray): ?string
    {
        $index               = self::LAST_CONTACT_COLUMN_INDEX - 1;
        $lastContactDateTime = $exportArray[$index];
        if (!$lastContactDateTime || $lastContactDateTime == "N/A") {
            return null;
        }
        $date = DateTime::createFromFormat(DATE_CNC_DATE_TIME_FORMAT, $lastContactDateTime);
        $date->add(new DateInterval('P' . $this->offlineAgentThresholdDays . 'D'));
        $today = new DateTime();
        if ($date > $today) {
            return null;
        }
        return self::OFFLINE_AGENT_COLOR;

    }

    private function getWarrantyColor($exportArray)
    {
        $index              = self::WARRANTY_EXPIRY_DATE_COLUMN_INDEX - 1;
        $warrantyExpiryDate = $exportArray[$index];
        if (!$warrantyExpiryDate || $warrantyExpiryDate == "Unknown" || $warrantyExpiryDate == "Not Applicable") {
            return null;
        }
        $date  = DateTime::createFromFormat(DATE_CNC_DATE_FORMAT, $warrantyExpiryDate);
        $today = new DateTime();
        if ($date > $today) {
            return null;
        }
        return self::NO_VENDOR_WARRANTY_COLOR;
    }


}