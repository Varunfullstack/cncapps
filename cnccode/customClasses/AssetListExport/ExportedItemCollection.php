<?php

namespace CNCLTD\AssetListExport;

use DateTime;
use PDO;

class ExportedItemCollection
{
    private $pcs          = 0;
    private $servers      = 0;
    private $labTechData  = [];
    private $summaryData  = [];
    private $customerData = [];


    /**
     * ExportedItemCollection constructor.
     * @param \DBECustomer $customer
     * @param OperatingSystemsSupportDatesCollection $collection
     * @param \PDO $labTechDB
     */
    public function __construct(\DBECustomer $customer,
                                OperatingSystemsSupportDatesCollection $collection,
                                \PDO $labTechDB
    )
    {
        /** @noinspection SqlIdentifierLength */
        $query                = /** @lang MySQL */
            'SELECT
       0 AS isHostServer,
  locations.name AS location,
  computers.name AS computerName,
  SUBSTRING_INDEX(lastusername, "\\\\", - 1) AS lastUser,
   DATE_FORMAT(
    computers.lastContact,
    "%d/%m/%Y %H:%i:%s"
  ) AS lastContact,
  inv_chassis.productname AS model,
 IF(inv_chassis.productname LIKE "%VMware%", "Not Applicable",COALESCE(
     (SELECT
        DATE_FORMAT(STR_TO_DATE(`plugin_sd_warranty_looker_upper_lookups`.`start_date`,"%c/%e/%Y"),"%d/%m/%Y") 
      FROM
        plugin_sd_warranty_looker_upper_lookups
      WHERE plugin_sd_warranty_looker_upper_lookups.`computerid` = computers.computerid ), "Unknown")) AS warrantyStartDate,
  IF(inv_chassis.productname LIKE "%VMware%", "Not Applicable",COALESCE((SELECT
        DATE_FORMAT(STR_TO_DATE(`plugin_sd_warranty_looker_upper_lookups`.`end_date`,"%c/%e/%Y"),"%d/%m/%Y") 
      FROM
        plugin_sd_warranty_looker_upper_lookups
      WHERE plugin_sd_warranty_looker_upper_lookups.`computerid` = computers.computerid), "Unknown")) AS warrantyExpiryDate,
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
  ) AS ageInYears,
  IF(inv_chassis.serialnumber LIKE "%VMware%", NULL,inv_chassis.serialnumber )        AS serialNumber,
  processor.name AS `cpu`,
  computers.totalmemory AS `memory`,
  SUM(drives.Size) AS totalDisk,
  CASE
    WHEN (exd.`Bitlocker Enabled`
    AND exd.`Bitlocker Password/Key` REGEXP "[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}-[0-9]{6}")
        OR exd.`Bitlocker Failure Reson` LIKE "Bitlocker Enabled%"
        OR exd.`Bitlocker Failure Reson` LIKE "Bitlocker has Completed Sucessfully%"
    THEN "Encrypted"
    WHEN exd.`Bitlocker Failure Reson` LIKE "TPM is not enabled%"
    THEN "Hardware not enabled / capable"
    WHEN exd.`Bitlocker Failure Reson` LIKE "TPM is not ready%"
    THEN "Hardware capable but not enabled"
    WHEN exd.`Bitlocker Failure Reson` LIKE "TPM is present and activated but Windows cannot encrypt the drive%"
    THEN "Capable but failed"
    WHEN exd.`Bitlocker Failure Reson` LIKE "Bitlocker Key is not Present Check the Key field for any additional Errors%" THEN NULL
    ELSE exd.`Bitlocker Failure Reson`
  END AS driveEncryption,
    computers.os AS operatingSystem,
  computers.version AS `version`,
  computers.domain AS domain,
       COALESCE(
    SUM(
      `computerroledefinitions`.`RoleDefinitionId` = 9364
    )> 0,
    0
  ) AS "isHybrid",
  COALESCE(
    SUM(
      `computerroledefinitions`.`RoleDefinitionId` = 9362
    )> 0 ,
    0
  ) AS "isAzureADJoined",
       SUBSTRING_INDEX(REPLACE(REPLACE(sf.`Name`, "Microsoft Office ",""),"Microsoft 365","365")," - ",1) AS officeVersion,
  virusscanners.name AS antivirus,
  DATE_FORMAT(
    STR_TO_DATE(computers.VirusDefs, "%Y%m%d"),
    "%d/%m/%Y"
  ) AS antivirusDefinition
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
  LEFT JOIN (SELECT
      *
    FROM
      software
    WHERE (
        software.name LIKE "%microsoft office%"
        OR software.name LIKE "%microsoft 365%"
      )
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
    GROUP BY computerid) sf
    ON computers.computerid = sf.computerid
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
  LEFT JOIN v_extradatacomputers exd
  ON (exd.computerid = computers.computerid)
LEFT JOIN computerroledefinitions
    ON computerroledefinitions.`ComputerId` = computers.`ComputerID`
    WHERE clients.externalID = ? AND  ServiceVersion
GROUP BY computers.computerid 
UNION ALL
SELECT
  1 AS isHostServer,
  locations.name AS location,
  plugin_vm_esxhosts.`DeviceName` AS computerName,
  NULL AS lastUser,
  "N/A" AS lastContact,
  plugin_vm_esxhosts.`Model` AS model,
  DATE_FORMAT(
    STR_TO_DATE(
      `plugin_sd_warranty_looker_upper_esx_lookups`.`start_date`,
      "%c/%e/%Y"
    ),
    "%d/%m/%Y"
  ) AS warrantyStartDate,
  DATE_FORMAT(
    STR_TO_DATE(
      `plugin_sd_warranty_looker_upper_esx_lookups`.`end_date`,
      "%c/%e/%Y"
    ),
    "%d/%m/%Y"
  ) AS warrantyStartDate,
  ROUND(
    TIMESTAMPDIFF(
      YEAR,
      STR_TO_DATE(
        `plugin_sd_warranty_looker_upper_esx_lookups`.`start_date`,
        "%c/%e/%Y"
      ),
      CURDATE()
    ) + (
      TIMESTAMPDIFF(
        MONTH,
        STR_TO_DATE(
          `plugin_sd_warranty_looker_upper_esx_lookups`.`start_date`,
          "%c/%e/%Y"
        ),
        CURDATE()
      ) - (
        TIMESTAMPDIFF(
          YEAR,
          STR_TO_DATE(
            `plugin_sd_warranty_looker_upper_esx_lookups`.`start_date`,
            "%c/%e/%Y"
          ),
          CURDATE()
        ) * 12
      )
    ) / 12,
    1
  ) AS ageInYears,
  plugin_vm_esxhosts.`OtherInformation` AS serialNumber,
  plugin_vm_esxhosts.`ProcessorType` AS `cpu`,
  plugin_vm_esxhosts.`RamMb` AS `memory`,
  SUM(
    `plugin_vm_esxdatastorage`.`CapacityGB`
  ) * 1024 AS totalDisk,
  NULL AS driveEncryption,
  `plugin_vm_esxhosts`.`ProductName` AS operatingSystem,
  TRIM(
    REPLACE(
      `plugin_vm_esxhosts`.`ProductName`,
      "VMware ESXi ",
      ""
    )
  ) AS `version`,
  NULL AS domain,
  FALSE AS "isHybrid",
  FALSE "isAzureADJoined",
  NULL AS officeVersion,
  NULL AS antivirus,
  NULL AS antivirusDefinition
FROM
  `plugin_vm_esxhosts`
  LEFT JOIN networkdevices
    ON networkdevices.`DeviceID` = plugin_vm_esxhosts.`DeviceId`
  LEFT JOIN locations
    ON locations.`LocationID` = networkdevices.`LocationID`
  LEFT JOIN plugin_sd_warranty_looker_upper_esx_lookups
    ON plugin_sd_warranty_looker_upper_esx_lookups.`deviceid` = `plugin_vm_esxhosts`.`DeviceId`
  LEFT JOIN `plugin_vm_esxdatastorage`
    ON `plugin_vm_esxdatastorage`.`DeviceId` = `plugin_vm_esxhosts`.`DeviceId`
  LEFT JOIN clients
    ON clients.`ClientID` = locations.`ClientID`
    WHERE `clients`.`ExternalID` = ?
GROUP BY plugin_vm_esxhosts.`DeviceId`
ORDER BY location, operatingSystem desc, computerName';
        $statement            = $labTechDB->prepare($query);
        $queryExecutionResult = $statement->execute(
            [$customer->getValue(\DBECustomer::customerID), $customer->getValue(\DBECustomer::customerID)]
        );
        if (!$queryExecutionResult) {
            echo '<div>Something went wrong...' . implode(
                    ',',
                    $statement->errorInfo()
                );
            var_dump($query);
            echo ' </div>';
            return;
        }
        /** @var LabtechAssetDTO[] $labtechData */
        $labtechData = $statement->fetchAll(PDO::FETCH_CLASS, LabtechAssetDTO::class);
        // we have to build the information from labtech and os support dates collection
        foreach ($labtechData as $labtechDatum) {
            $supportDates          = $collection->getMatchingOperatingSystemSupportInformation(
                $labtechDatum->getOperatingSystem(),
                $labtechDatum->getVersion()
            );
            $isServer              = false;
            $endOfLifeDate         = null;
            $this->labTechData[]   = ["dataItem" => $labtechDatum, "supportDates" => $supportDates];
            $operatingSystemString = str_replace('Microsoft Windows', "", $labtechDatum->getOperatingSystem());
            if ($supportDates) {
                $isServer      = $supportDates[\DBEOSSupportDates::isServer];
                $dateString    = $supportDates[\DBEOSSupportDates::endOfLifeDate];
                $dateTime      = \DateTime::createFromFormat(DATE_MYSQL_DATE, $dateString);
                $endOfLifeDate = $dateTime->format(DATE_CNC_DATE_FORMAT);
                if (isset($supportDates[\DBEOSSupportDates::friendlyName])) {
                    $operatingSystemString = "{$operatingSystemString} ({$supportDates[\DBEOSSupportDates::friendlyName]})";
                }
            }
            $genericRow           = [
                $labtechDatum->getLocation(),
                $labtechDatum->getComputerName(),
                $labtechDatum->getLastUser(),
                $labtechDatum->getLastContact(),
                $labtechDatum->getModel(),
                $labtechDatum->getWarrantyStartDateAsOfficeDate(),
                $labtechDatum->getWarrantyEndDateAsOfficeDate(),
                $labtechDatum->getAgeInYears(),
                $labtechDatum->getSerialNumber(),
                $labtechDatum->getCpu(),
                $labtechDatum->getMemory(),
                $labtechDatum->getTotalDisk(),
                $labtechDatum->getDriveEncryption(),
                $operatingSystemString,
                $labtechDatum->getVersion(),
                $endOfLifeDate,
                $labtechDatum->getDomain(),
                $labtechDatum->getOfficeVersion(),
                $labtechDatum->getAntivirus(),
                $labtechDatum->getAntivirusDefinition()
            ];
            $this->customerData[] = $genericRow;
            $summaryRow           = array_merge([$customer->getValue(\DBECustomer::name)], $genericRow);
            $this->summaryData[]  = $summaryRow;
            if ($isServer) {
                $this->servers++;
            } else {
                $this->pcs++;
            }
        }
    }

    public function getOSEndOfSupportDate($index): ?DateTime
    {
        $found = @$this->labTechData[$index];
        if (!$found || !isset($found["supportDates"])) {
            return null;
        }
        return DateTime::createFromFormat(
            DATE_MYSQL_DATE,
            $found['supportDates'][\DBEOSSupportDates::endOfLifeDate]
        );
    }

    public function getNumberOfPcs()
    {
        return $this->pcs;
    }

    public function getNumberOfServers()
    {
        return $this->servers;
    }

    public function hasData(): bool
    {
        return (bool)count($this->customerData);
    }

    public function getSummaryData(): array
    {
        return $this->summaryData;
    }

    public function getExportData(): array
    {
        return $this->customerData;
    }

    /**
     * @param $index
     * @return mixed
     */
    public function getOperatingSystem($index)
    {
        return $this->labTechData[$index]["dataItem"]->getOperatingSystem();
    }

    public function isServerAsset($index): bool
    {
        if (!isset($this->labTechData[$index]) || !isset($this->labTechData[$index]["supportDates"]) || !$this->labTechData[$index]["supportDates"]["isServer"]) {
            return false;
        }
        return true;
    }

    /**
     * @param int $key
     * @return mixed|null
     */
    public function getAsset(int $key): ?LabtechAssetDTO
    {
        if (!isset($this->labTechData[$key]) || !isset($this->labTechData[$key]["dataItem"])) {
            return null;
        }
        return $this->labTechData[$key]["dataItem"];
    }

    public function is3CX(int $index): bool
    {
        if (!isset($this->labTechData[$index])) {
            return false;
        }
        return $this->labTechData[$index]["dataItem"]->is3CX();
    }
}