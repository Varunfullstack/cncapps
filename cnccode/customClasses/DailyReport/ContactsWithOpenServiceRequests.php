<?php


namespace CNCLTD\DailyReport;


use Iterator;

class ContactsWithOpenServiceRequests implements Iterator
{
    /** @var ContactWithOpenServiceRequests[] */
    private $contactsWithOpenServiceRequests = [];
    private $currentIndex = 0;

    public function add($contactId, $contactName, $contactEmail, $customerName,$serviceRequestId, $raisedBy, $raisedOn, $status, $details)
    {
        if (!isset($this->contactsWithOpenServiceRequests[$contactId])) {
            $this->contactsWithOpenServiceRequests[$contactId] = new ContactWithOpenServiceRequests(
                $contactId,
                $contactName,
                $contactEmail,
                $customerName
            );
        }
        $this->contactsWithOpenServiceRequests[$contactId]->addServiceRequest($serviceRequestId, $raisedBy, $raisedOn, $status, $details);
    }

    /**
     * @return ContactWithOpenServiceRequests
     */
    public function current()
    {
        return array_values($this->contactsWithOpenServiceRequests)[$this->currentIndex];
    }

    public function next()
    {
        ++$this->currentIndex;
    }

    public function key()
    {
        return $this->currentIndex;
    }

    public function valid()
    {
        return isset(array_values($this->contactsWithOpenServiceRequests)[$this->currentIndex]);
    }

    public function rewind()
    {
        $this->currentIndex = 0;
    }
}