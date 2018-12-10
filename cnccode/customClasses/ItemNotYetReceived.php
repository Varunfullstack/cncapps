<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 10/12/2018
 * Time: 9:04
 */

namespace CNCLTD;


class ItemNotYetReceived
{
    protected $purchaseOrderId;
    protected $customerName;
    protected $itemDescription;
    protected $supplierName;
    protected $direct;
    protected $purchaseOrderDate;
    protected $futureDate;
    protected $purchaseOrderRequiredBy;
    protected $supplierRef;
    protected $projectName;
    protected $dispatchedDate;
    protected $orderedBy;
    protected $purchaseOrderType;
    protected $hasNotBeenReceivedYet;

    /**
     * @return mixed
     */
    public function getPurchaseOrderId()
    {
        return $this->purchaseOrderId;
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
    public function getItemDescription()
    {
        return $this->itemDescription;
    }

    /**
     * @return mixed
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * @return mixed
     */
    public function getDirect()
    {
        return $this->direct;
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderDate()
    {
        return $this->returnDateIfValue($this->purchaseOrderDate);
    }

    /**
     * @return mixed
     */
    public function getFutureDate()
    {
        return $this->returnDateIfValue($this->futureDate);
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderRequiredBy()
    {
        return $this->returnDateIfValue($this->purchaseOrderRequiredBy);
    }

    private function returnDateIfValue($value)
    {
        if (!$value) {
            return null;
        }
        return \DateTime::createFromFormat(
            'Y-m-d',
            $value
        );
    }

    /**
     * @return mixed
     */
    public function getSupplierRef()
    {
        return $this->supplierRef;
    }

    /**
     * @return mixed
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @return mixed
     */
    public function getDispatchedDate()
    {
        return $this->returnDateIfValue($this->dispatchedDate);
    }


    public function lineColor()
    {
        $greenForTypes = ['C', 'A'];
        if (in_array(
            $this->purchaseOrderType,
            $greenForTypes
        )) {
            return 'green';
        }

        if ($this->hasNotBeenReceivedYet) {
            return 'orange';
        }

        if (!$this->orderedBy) {
            return 'red';
        }

        $redForTypes = ['I', 'P'];

        if (in_array(
            $this->purchaseOrderType,
            $redForTypes
        )) {
            return 'red';
        }
        return 'red';
    }

}