<?php

namespace CNCLTD\AssetListExport;

use BUCustomer;
use BUHeader;
use BUPassword;
use CNCLTD\Business\BURenContract;
use DataSet;
use DateInterval;
use DateTime;
use DBECustomer;
use DBEHeader;
use DBEPassword;
use DBEPortalCustomerDocument;
use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use UnexpectedValueException;
use ZipArchive;

class AssetListExporter
{
    const OPERATING_SYSTEM_SOON_END_OF_LIFE_COLOR = "B59DB6";
    const OPERATING_SYSTEM_IS_END_OF_LIFE_COLOR   = "FFEB9C";
    const NO_VENDOR_WARRANTY_COLOR                = "FFC7CE";
    const OFFLINE_AGENT_COLOR                     = "C4BD97";
    const OPERATING_SYSTEM_COLUMN_INDEX           = 14;
    const OPERATING_SYSTEM_VERSION_COLUMN_INDEX   = 15;
    const OS_END_OF_SUPPORT_DATE_COLUMN_INDEX     = 16;
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
     * @var array|bool|float|int|string|null
     */
    private $osSupportDatesThresholdDays;
    private $dataHeaders               = [
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
    private $summaryHeaders;
    private $customersTabularDataItems = [];
    private $buCustomer;
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
        $this->summaryHeaders             = array_merge(["Customer"], $this->dataHeaders);
        $BUHeader                         = new BUHeader($this);
        $dbeHeader                        = new DataSet($this);
        $BUHeader->getHeader($dbeHeader);
        $this->osSupportDatesThresholdDays = $dbeHeader->getValue(DBEHeader::OSSupportDatesThresholdDays);
        if (!$this->osSupportDatesThresholdDays) {
            throw new UnexpectedValueException('OS Support Dates Threshold days is empty');
        }
        $this->offlineAgentThresholdDays = $dbeHeader->getValue(DBEHeader::offlineAgentThresholdDays);
        $this->buCustomer                = new BUCustomer($this);

    }

    private function runExportForCustomer($customerId, $generateWithMonthYear = false)
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerId);
        $tabularData = new ExportedItemCollection(
            $dbeCustomer, $this->operatingSystemsCollection, $this->labTechDB
        );
        if (!$tabularData->hasData()) {
            return;
        }
        $this->customersTabularDataItems[] = $tabularData;
        $spreadsheet                       = new Spreadsheet();
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
        $this->setHeaderRowToBold($sheet);
        $this->addLegend($sheet);
        for ($i = 0; $i < count($toExportData); $i++) {
            $currentRow = 2 + $i;
            $this->setEndOfSupportDateColorForCurrentRow(
                $tabularData,
                $i,
                $sheet,
                $currentRow
            );
            $this->setLastContactColorForCurrentRow(
                $toExportData[$i],
                $i,
                $sheet,
                $currentRow,
                $tabularData
            );
            $this->setWarrantyColorForCurrentRow($toExportData[$i], $sheet, $currentRow);
        }
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setHorizontal('center');
        $writer   = new Xlsx($spreadsheet);
        $fileName = $this->getFileDestinationPath($customerId, $generateWithMonthYear);
        try {
            $writer->save(
                $fileName
            );
            if (!$generateWithMonthYear) {
                $this->saveOrUpdateAssetListDocumentInPortalDocuments($customerId, $fileName);
            }
            $this->updateCustomerInfo($customerId, $tabularData);
            echo '<div>Data was found at labtech, creating file ' . $fileName . '</div>';
        } catch (Exception $exception) {
            echo '<div>Failed to save file, possibly file open</div>';
        }

    }

    public function generateSummary()
    {


        $summarySpreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $summarySpreadSheet->getDefaultStyle()->getFont()->setName('Arial');
        $summarySpreadSheet->getDefaultStyle()->getFont()->setSize(10);
        $summarySheet = $summarySpreadSheet->getActiveSheet();
        $summarySheet->fromArray(array_merge(["Customer Name"], $this->dataHeaders));
        $currentSummaryRow = 2;
        $this->setHeaderRowToBold($summarySheet);
        foreach ($this->customersTabularDataItems as $customersTabularDataItem) {
            $toExportData = $customersTabularDataItem->getSummaryData();
            $summarySheet->fromArray($toExportData, null, "A$currentSummaryRow");
            for ($i = 0; $i < count($toExportData); $i++) {

                $this->setEndOfSupportDateColorForCurrentRow(
                    $customersTabularDataItem,
                    $i,
                    $summarySheet,
                    $currentSummaryRow,
                    true
                );
                $this->setLastContactColorForCurrentRow(
                    $toExportData[$i],
                    $i,
                    $summarySheet,
                    $currentSummaryRow,
                    $customersTabularDataItem,
                    true
                );
                $this->setWarrantyColorForCurrentRow($toExportData[$i], $summarySheet, $currentSummaryRow, true);
                $currentSummaryRow++;
            }
        }
        $this->addLegend($summarySheet);
        $tempFileName = null;
        echo '<h1>Generating Summary</h1>';
        $summarySheet->setAutoFilter($summarySheet->calculateWorksheetDimension());
        $summarySheet->getStyle($summarySheet->calculateWorksheetDimension())->getAlignment()->setHorizontal('center');
        foreach (range('A', $summarySheet->getHighestDataColumn()) as $col) {
            $summarySheet->getColumnDimension($col)->setAutoSize(true);
        }
        $password   = \CNCLTD\Utils::generateStrongPassword(16);
        $writer     = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($summarySpreadSheet);
        $folderName = TECHNICAL_DIR;
        if (!file_exists($folderName)) {
            mkdir(
                $folderName,
                0777,
                true
            );
        }
        $tempFileName = $folderName . "\\temp.xlsx";
        $buPassword   = new BUPassword($this);
        try {
            $writer->save(
                $tempFileName
            );
            $definitiveFileName = $folderName . "\\Asset List Export.zip";
            $zip                = new ZipArchive();
            $res                = $zip->open($definitiveFileName, ZipArchive::CREATE);
            if ($res === true) {
                $zip->addFile($tempFileName, 'Asset List Export.xlsx');
                $zip->setEncryptionName('Asset List Export.xlsx', ZipArchive::EM_AES_256, $password);
                $zip->close();
                $dbePassword = new DBEPassword($this);
                $dbePassword->getAutomatedFullAssetListPasswordItem();
                $dbePassword->setValue(DBEPassword::password, $buPassword->encrypt($password));
                $dbePassword->setValue(DBEPassword::username, null);
                $dbePassword->setValue(DBEPassword::level, 5);
                $dbePassword->setValue(DBEPassword::notes, $buPassword->encrypt('Full List of Asset information'));
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

    public function exportForCustomer($customerId, $generateWithMonthYear = false)
    {
        $this->runExportForCustomer($customerId, $generateWithMonthYear);
    }

    public function exportForActiveCustomersWithSummary()
    {
        $this->exportForActiveCustomers();
        $this->generateSummary();
    }

    public function exportForActiveCustomers($generateWithMonthYear = false)
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getActiveCustomers(true);
        while ($dbeCustomer->fetchNext()) {
            $this->runExportForCustomer($dbeCustomer->getValue(DBECustomer::customerID), $generateWithMonthYear);
        }
    }

    /**
     * @param Worksheet $sheet
     */
    private function addLegend(Worksheet $sheet): void
    {
        $dbeHeader = new DBEHeader($this);
        $dbeHeader->getRow(1);
        $highestRow     = $sheet->getHighestRow();
        $dateTime       = new DateTime();
        $legendRowStart = $highestRow + 2;
        $sheet->fromArray(
            [
                ["OS no longer supported after {$dbeHeader->getValue(DBEHeader::OSSupportDatesThresholdDays)} days"],
                ["Operating System is end of life"],
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
        $sheet->getStyle("1:1")->getFont()->setBold(true);
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
        if ($date <= $today) {
            return self::OPERATING_SYSTEM_IS_END_OF_LIFE_COLOR;
        }
        if ($date <= $thresholdDate) {
            return self::OPERATING_SYSTEM_SOON_END_OF_LIFE_COLOR;
        }
        return null;
    }

    private function getLastContactColor($exportArray, $isSummary = false): ?string
    {
        $index               = self::LAST_CONTACT_COLUMN_INDEX - 1 + $isSummary;
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

    /**
     * @param ExportedItemCollection $tabularData
     * @param int $i
     * @param Worksheet $sheet
     * @param int $currentRow
     * @param bool $isSummary
     * @return void
     */
    private function setEndOfSupportDateColorForCurrentRow(ExportedItemCollection $tabularData,
                                                           int $i,
                                                           Worksheet $sheet,
                                                           int $currentRow,
                                                           $isSummary = false
    ): void
    {
        $OSEndOfSupportDateColor = $this->getOSEndOfSupportDateColor($tabularData, $i);
        if ($OSEndOfSupportDateColor) {
            $columnCoordinate = Coordinate::stringFromColumnIndex(
                self::OS_END_OF_SUPPORT_DATE_COLUMN_INDEX + $isSummary
            );
            $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $OSEndOfSupportDateColor);
            $columnCoordinate = Coordinate::stringFromColumnIndex(self::OPERATING_SYSTEM_COLUMN_INDEX + $isSummary);
            $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $OSEndOfSupportDateColor);
            $columnCoordinate = Coordinate::stringFromColumnIndex(
                self::OPERATING_SYSTEM_VERSION_COLUMN_INDEX + $isSummary
            );
            $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $OSEndOfSupportDateColor);
        }
    }

    /**
     * @param $exportArray
     * @param int $i
     * @param Worksheet $sheet
     * @param int $currentRow
     * @param ExportedItemCollection $tabularData
     * @param bool $isSummary
     */
    private function setLastContactColorForCurrentRow($exportArray,
                                                      int $i,
                                                      Worksheet $sheet,
                                                      int $currentRow,
                                                      ExportedItemCollection $tabularData,
                                                      $isSummary = false
    )
    {
        $lastContactColor = $this->getLastContactColor($exportArray, $isSummary);
        if ($lastContactColor) {
            $columnCoordinate = Coordinate::stringFromColumnIndex(self::LAST_CONTACT_COLUMN_INDEX + $isSummary);
            $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $lastContactColor);
        }
    }

    /**
     * @param $exportArray
     * @param Worksheet $sheet
     * @param int $currentRow
     * @param bool $isSummary
     */
    private function setWarrantyColorForCurrentRow($exportArray,
                                                   Worksheet $sheet,
                                                   int $currentRow,
                                                   $isSummary = false
    ): void
    {
        $warrantyColor = $this->getWarrantyColor($exportArray);
        if ($warrantyColor) {
            $columnCoordinate = Coordinate::stringFromColumnIndex(self::WARRANTY_EXPIRY_DATE_COLUMN_INDEX + $isSummary);
            $this->setCellSolidColor($sheet, "{$columnCoordinate}{$currentRow}", $warrantyColor);
        }
    }

    /**
     * @param $customerId
     * @param string $fileName
     */
    private function saveOrUpdateAssetListDocumentInPortalDocuments($customerId, string $fileName)
    {
        $dbeCustomerDocument = new DBEPortalCustomerDocument($this);
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
    }

    /**
     * @param $customerId
     * @param ExportedItemCollection $tabularData
     */
    private function updateCustomerInfo($customerId, ExportedItemCollection $tabularData)
    {
        $updateCustomer = new DBECustomer($this);
        $updateCustomer->getRow($customerId);
        $updateCustomer->setValue(DBECustomer::noOfPCs, $tabularData->getNumberOfPcs());
        $updateCustomer->setValue(DBECustomer::noOfServers, $tabularData->getNumberOfServers());
        $updateCustomer->setValue(DBECustomer::eligiblePatchManagement, $tabularData->patchManagementEligibleComputers());
        $updateCustomer->updateRow();
        $buContract = new BURenContract($this);
        $buContract->updatePatchManagementContractForCustomer($customerId, $tabularData->patchManagementEligibleComputers());
    }

    /**
     * @param $customerId
     * @param bool $generateWithMonthYear
     * @return string
     */
    private function getFileDestinationPath($customerId, $generateWithMonthYear = true): string
    {
        $customerFolder = $this->buCustomer->getCustomerFolderPath($customerId);
        $folderName     = $customerFolder . "\Review Meetings\\";
        if (!file_exists($folderName)) {
            mkdir(
                $folderName,
                0777,
                true
            );
        }
        $fileDescription = "Current Asset List Extract.xlsx";
        if ($generateWithMonthYear) {
            $date            = new DateTime();
            $fileDescription = "Asset List - {$date->format('F Y')}.xlsx";
        }
        return $folderName . $fileDescription;
    }


}