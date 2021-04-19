<?php

namespace CNCLTD\AssetListExport;

use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LabtechAssetDTO
{
    private $isHostServer;
    private $location;
    private $computerName;
    private $lastUser;
    private $lastContact;
    private $model;
    private $warrantyStartDate;
    private $warrantyExpiryDate;
    private $ageInYears;
    private $serialNumber;
    private $cpu;
    private $memory;
    private $totalDisk;
    private $driveEncryption;
    private $operatingSystem;
    private $version;
    private $domain;
    private $officeVersion;
    private $antivirus;
    private $antivirusDefinition;
    private $isAzureADJoined;
    private $isHybrid;

    /**
     * LabtechAssetDTO constructor.
     */
    public function __construct()
    {
        $text                = $this->lastUser;
        $text                = str_replace('null', "", $text);
        $this->lastUser      = $this->getUnrepeatedUsername($text);
        $this->cpu           = preg_replace('/\s+/', ' ', $this->cpu);
        $this->model         = preg_replace('/\s+/', ' ', $this->model);
        $this->officeVersion = ucwords($this->officeVersion);
        if ($this->isHostServer) {
            $re  = '/.*Service tag=(.*?)(;|$)/m';
            $str = $this->serialNumber;
            if (preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0)) {
                $this->serialNumber = $matches[0][1];
            } else {
                $this->serialNumber = null;
            }
        }
        if ($this->domain) {
            if ($this->isHybrid) {
                $this->domain = "{$this->domain} - Hybrid";
            } elseif ($this->isAzureADJoined) {
                $this->domain = "Azure AD Joined";
            }
        }
        $firstStopPosition = strpos($this->computerName, '.');
        if ($firstStopPosition > 0) {
            $this->computerName = substr($this->computerName, 0, $firstStopPosition);
        }
        if ($this->antivirusDefinition) {
            $testDate = DateTime::createFromFormat(DATE_CNC_DATE_FORMAT, $this->antivirusDefinition);
            if (!$testDate || $testDate->format(DATE_CNC_DATE_FORMAT) !== $this->antivirusDefinition) {
                $this->antivirusDefinition = null;
            }
        }

    }

    public function is3CX(): bool
    {
        if (!$this->computerName || !preg_match('/.*3CX.*/', $this->computerName)) {
            return false;
        }
        return true;
    }

    private function getUnrepeatedUsername($str)
    {
        $n = strlen($str);
        if ($n < 6) {
            return $str;
        }
        $length = 3;
        $match  = false;
        do {
            $prospect        = substr($str, 0, $length);
            $restOfTheString = substr($str, $length, $length);
            if (strlen($restOfTheString) < $length) {
                return $str;
            }
            if ($restOfTheString == $prospect) {
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

    /**
     * @return mixed
     */
    public function getIsHostServer()
    {
        return $this->isHostServer;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed
     */
    public function getComputerName()
    {
        return $this->computerName;
    }

    /**
     * @return false|string
     */
    public function getLastUser()
    {
        return $this->lastUser;
    }

    /**
     * @return string|string[]|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getWarrantyStartDate()
    {
        return $this->warrantyStartDate;
    }

    public function getWarrantyStartDateAsOfficeDate()
    {
        return $this->getDateAsExcelDate($this->warrantyStartDate);
    }

    public function getWarrantyEndDateAsOfficeDate()
    {
        return $this->getDateAsExcelDate($this->warrantyExpiryDate);
    }

    /**
     * @return mixed
     */
    public function getWarrantyExpiryDate()
    {
        return $this->warrantyExpiryDate;
    }

    /**
     * @return mixed
     */
    public function getAgeInYears()
    {
        return $this->ageInYears;
    }

    /**
     * @return mixed
     */
    public function getSerialNumber()
    {
        return trim($this->serialNumber);
    }

    /**
     * @return string|string[]|null
     */
    public function getCpu()
    {
        return $this->cpu;
    }

    /**
     * @return mixed
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * @return mixed
     */
    public function getTotalDisk()
    {
        return $this->totalDisk;
    }

    /**
     * @return mixed
     */
    public function getDriveEncryption()
    {
        return $this->driveEncryption;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getOfficeVersion(): ?string
    {
        return $this->officeVersion;
    }

    /**
     * @return mixed
     */
    public function getAntivirus()
    {
        return $this->antivirus;
    }

    /**
     * @return mixed
     */
    public function getAntivirusDefinition()
    {
        return $this->antivirusDefinition;
    }

    /**
     * @return mixed
     */
    public function getIsAzureADJoined()
    {
        return $this->isAzureADJoined;
    }

    /**
     * @return mixed
     */
    public function getIsHybrid()
    {
        return $this->isHybrid;
    }

    /**
     * @return mixed
     */
    public function getOperatingSystem()
    {
        return $this->operatingSystem;
    }

    /**
     * @return mixed
     */
    public function getLastContact()
    {
        return $this->lastContact;
    }

    public function lastContactAsExcelDate()
    {
        return $this->getDateAsExcelDate($this->lastContact);
    }

    /**
     * @param $data
     * @return bool|float|int
     */
    private function getDateAsExcelDate($data)
    {
        $dateTime = DateTime::createFromFormat('d/m/Y', $data);
        if (!$dateTime) {
            return $data;
        }
        return Date::PHPToExcel($dateTime->getTimestamp());
    }

}