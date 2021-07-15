<?php


namespace CNCLTD;


use DateTimeInterface;

class ToCheckDevice
{
    /** @var string */
    public $customerName;
    /** @var string */
    public $computerName;
    /** @var DateTimeInterface */
    public $lastSeenDateTime;

    /**
     * ToCheckDevice constructor.
     * @param $customerName
     * @param $computerName
     * @param $lastSeenDateTime
     */
    public function __construct($customerName, $computerName, $lastSeenDateTime)
    {
        $this->customerName = $customerName;
        $this->computerName = $computerName;
        $this->lastSeenDateTime = $lastSeenDateTime;
    }

}