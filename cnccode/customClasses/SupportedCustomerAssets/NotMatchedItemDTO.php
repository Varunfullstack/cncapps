<?php

namespace CNCLTD\SupportedCustomerAssets;
class NotMatchedItemDTO
{
    private $customerName;
    private $computerName;
    private $customerItemId;
    private $customerId;

    /**
     * NotMatchedItemDTO constructor.
     * @param $customerName
     * @param $computerName
     * @param $customerItemId
     */
    public function __construct($customerName, $computerName,$customerId, $customerItemId = null)
    {
        $this->customerName   = $customerName;
        $this->computerName   = $computerName;
        $this->customerItemId = $customerItemId;
        $this->customerId = $customerId;
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
    public function getComputerName()
    {
        return $this->computerName;
    }

    /**
     * @return mixed|null
     */
    public function getCustomerItemId()
    {
        return $this->customerItemId;
    }

    public function customerId()
    {
        return $this->customerId;
    }

}