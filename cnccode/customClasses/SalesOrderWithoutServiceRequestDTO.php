<?php

namespace CNCLTD;
class SalesOrderWithoutServiceRequestDTO implements \JsonSerializable
{
    private $salesOrderId;
    private $customerName;
    private $itemLineDescription;

    /**
     * @return mixed
     */
    public function getSalesOrderId()
    {
        return $this->salesOrderId;
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
    public function getItemLineDescription()
    {
        return $this->itemLineDescription;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}