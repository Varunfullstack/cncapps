<?php

namespace CNCLTD\AssetListExport;
global $cfg;
require_once($cfg["path_dbe"] . "/DBEOSSupportDates.php");
use DBEOSSupportDates;

class OperatingSystemsSupportDatesCollection
{
    private $operatingSystems = [];

    /**
     * OperatingSystemsSupportDatesCollection constructor.
     */
    public function __construct()
    {
        $DBEOSSupportDates = new DBEOSSupportDates($this);
        $DBEOSSupportDates->getEndOfLifeRows();
        while ($DBEOSSupportDates->fetchNext()) {
            $operatingSystem = trim(
                mb_convert_case($DBEOSSupportDates->getValue(DBEOSSupportDates::name), MB_CASE_LOWER)
            );
            if (!array_key_exists($operatingSystem, $this->operatingSystems)) {
                $this->operatingSystems[$operatingSystem] = [];
            }
            $version = trim(mb_convert_case($DBEOSSupportDates->getValue(DBEOSSupportDates::version), MB_CASE_LOWER));
            if (!array_key_exists($version, $this->operatingSystems[$operatingSystem])) {
                $this->operatingSystems[$operatingSystem][$version] = [
                    DBEOSSupportDates::endOfLifeDate => $DBEOSSupportDates->getValue(DBEOSSupportDates::endOfLifeDate),
                    DBEOSSupportDates::friendlyName  => $DBEOSSupportDates->getValue(DBEOSSupportDates::friendlyName),
                    DBEOSSupportDates::isServer      => $DBEOSSupportDates->getValue(DBEOSSupportDates::isServer)
                ];
            }
        }
    }

    public function getMatchingOperatingSystemSupportInformation($operatingSystem, $version)
    {
        $operatingSystem = mb_convert_case($operatingSystem, MB_CASE_LOWER);
        if (!$version) {
            return null;
        }
        if (!preg_match("/(\d+\.\d+(\.\d+)?)/", $version, $matches)) {
            throw new \Exception("The version does not match the regex!! $version");
        }
        $version = $matches[0];
        if (!array_key_exists($operatingSystem, $this->operatingSystems)) {
            return null;
        }
        if (!array_key_exists($version, $this->operatingSystems[$operatingSystem])) {
            return null;
        }
        return $this->operatingSystems[$operatingSystem][$version];
    }
}