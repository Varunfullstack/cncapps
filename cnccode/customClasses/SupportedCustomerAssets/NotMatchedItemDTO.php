<?php

namespace CNCLTD\SupportedCustomerAssets;
class NotMatchedItemDTO
{
    private $customerName;
    private $computerName;
    private $customerItemId;

    /**
     * NotMatchedItemDTO constructor.
     * @param $customerName
     * @param $computerName
     * @param $customerItemId
     */
    public function __construct($customerName, $computerName, $customerItemId = null)
    {
        $this->customerName   = $customerName;
        $this->computerName   = $computerName;
        $this->customerItemId = $customerItemId;
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

}