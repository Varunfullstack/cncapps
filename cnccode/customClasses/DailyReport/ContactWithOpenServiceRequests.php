<?php


namespace CNCLTD\DailyReport;


use Iterator;

class ContactWithOpenServiceRequests implements Iterator
{
    private $contactId;
    private $contactName;
    private $contactEmail;
    private $customerName;
    /** @var OpenServiceRequestDTO[] */
    private $serviceRequests = [];
    /**
     * @var int
     */
    private $currentIndex;

    /**
     * ContactWithOpenServiceRequests constructor.
     * @param $contactId
     * @param $contactName
     * @param $contactEmail
     * @param $customerName
     */
    public function __construct($contactId, $contactName, $contactEmail, $customerName)
    {
        $this->contactId = $contactId;
        $this->contactName = $contactName;
        $this->contactEmail = $contactEmail;
        $this->customerName = $customerName;
    }

    public function addServiceRequest($serviceRequestId, $raisedBy, $raisedOn, $status, $details)
    {
        $this->serviceRequests[] = new OpenServiceRequestDTO($serviceRequestId, $raisedBy, $raisedOn, $status, $details);
    }

    /**
     * @return mixed
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @return mixed
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * @return mixed
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @return OpenServiceRequestDTO
     */
    public function current()
    {
        return $this->serviceRequests[$this->currentIndex];
    }

    public function next()
    {
        $this->currentIndex++;
    }

    public function key()
    {
        return $this->currentIndex;
    }

    public function valid()
    {
        return isset($this->serviceRequests[$this->currentIndex]);
    }

    public function rewind()
    {
        $this->currentIndex = 0;
    }
}