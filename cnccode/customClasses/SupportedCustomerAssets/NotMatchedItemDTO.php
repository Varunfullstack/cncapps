<?php

namespace CNCLTD\SupportedCustomerAssets;
class NotMatchedItemDTO
{
    private $customerName;
    private $computerName;
    private $customerItemId;
    private $customerId;
    /**
     * @var null
     */
    private $customerContractId;

    /**
     * NotMatchedItemDTO constructor.
     * @param $customerName
     * @param $computerName
     * @param $customerId
     * @param null $customerItemId
     * @param null $customerContractId
     */
    public function __construct($customerName,
                                $computerName,
                                $customerId,
                                $customerItemId = null,
                                $customerContractId = null
    )
    {
        $this->customerName       = $customerName;
        $this->computerName       = $computerName;
        $this->customerItemId     = $customerItemId;
        $this->customerId         = $customerId;
        $this->customerContractId = $customerContractId;
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

    public function customerContractId()
    {
        return $this->customerContractId;
    }
}