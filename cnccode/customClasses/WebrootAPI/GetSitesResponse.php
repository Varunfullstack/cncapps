<?php


namespace CNCLTD\WebrootAPI;


class GetSitesResponse
{
    public $totalSeatsAllowed;
    public $parentDescription;
    public $sumTotalDevices;
    public $sumTotalDevicesAllowed;
    public $sumTotalDevicesNotTrial;
    public $sumTotalMobileDevicesAllowed;
    public $totalCount;
    /** @var Site[] */
    public $sites;
}