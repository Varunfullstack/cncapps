<?php


namespace CNCLTD\TwigDTOs;


class SiteVisitDTO
{
    public $contactFirstName;
    public $engineerFullName;
    public $siteAdd1;
    public $siteAdd2;
    public $siteAdd3;
    public $siteTown;
    public $sitePostcode;
    public $visitActivityDate;
    public $visitActivityTimeOfTheDay;
    public $visitActivityReason;
    public $serviceRequestId;

    /**
     * SiteVisitDTO constructor.
     * @param $contactFirstName
     * @param $engineerFullName
     * @param $siteAdd1
     * @param $siteAdd2
     * @param $siteAdd3
     * @param $siteTown
     * @param $sitePostcode
     * @param $visitActivityDate
     * @param $visitActivityTimeOfTheDay
     * @param $visitActivityReason
     * @param array|bool|float|int|string|null $serviceRequestId
     */
    public function __construct($contactFirstName,
                                $engineerFullName,
                                $siteAdd1,
                                $siteAdd2,
                                $siteAdd3,
                                $siteTown,
                                $sitePostcode,
                                $visitActivityDate,
                                $visitActivityTimeOfTheDay,
                                $visitActivityReason,
                                $serviceRequestId
    )
    {
        $this->contactFirstName = $contactFirstName;
        $this->engineerFullName = $engineerFullName;
        $this->siteAdd1 = $siteAdd1;
        $this->siteAdd2 = $siteAdd2;
        $this->siteAdd3 = $siteAdd3;
        $this->siteTown = $siteTown;
        $this->sitePostcode = $sitePostcode;
        $this->visitActivityDate = $visitActivityDate;
        $this->visitActivityTimeOfTheDay = $visitActivityTimeOfTheDay;
        $this->visitActivityReason = $visitActivityReason;
        $this->serviceRequestId = $serviceRequestId;
    }


}