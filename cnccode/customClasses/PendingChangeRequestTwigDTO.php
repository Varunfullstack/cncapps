<?php


namespace CNCLTD;


class PendingChangeRequestTwigDTO
{
    private $customerName;
    private $srURL;
    private $requestedBy;
    private $requestedDateTime;
    private $processURL;
    private $changeRequested;

    public function __construct($customerName,
                                $srURL,
                                $requestedBy,
                                $requestedDateTime,
                                $processURL,
                                $changeRequested
    )
    {
        $this->customerName = $customerName;
        $this->srURL = $srURL;
        $this->requestedBy = $requestedBy;
        $this->requestedDateTime = $requestedDateTime;
        $this->processURL = $processURL;
        $this->changeRequested = $changeRequested;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @return mixed
     */
    public function getSrURL()
    {
        return $this->srURL;
    }


    /**
     * @return mixed
     */
    public function getRequestedBy()
    {
        return $this->requestedBy;
    }

    /**
     * @return mixed
     */
    public function getRequestedDateTime()
    {
        return $this->requestedDateTime;
    }

    /**
     * @return mixed
     */
    public function getProcessURL()
    {
        return $this->processURL;
    }

    /**
     * @return mixed
     */
    public function getChangeRequested()
    {
        return $this->changeRequested;
    }
}