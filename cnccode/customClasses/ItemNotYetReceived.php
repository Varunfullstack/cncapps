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
    public static $items = [];

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
    protected $orderedBy;
    protected $purchaseOrderType;
    protected $hasNotBeenReceivedYet;
    protected $hasBeenOrdered;
    protected $orderedQuantity;
    protected $salesOrderID;
    protected $projectID;
    protected $isRequiredAtLeastAWeekAgo;
    protected $serviceRequestID;
    protected $deliveryConfirmedFlag;
    protected $expectedOn;
    protected $cost;
    protected $itemId;

    private function isCarriage(){
        return $this->itemId == 1491;
    }

    /**
     * @return mixed
     */
    public function getExpectedOn()
    {
        if(!$this->cost || $this->isCarriage()){
            return null;
        }
        return $this->returnDateIfValue($this->expectedOn);
    }


    /**
     * @return mixed
     */
    public function isDeliveryConfirmed()
    {
        return $this->deliveryConfirmedFlag == 'Y';
    }


    /**
     * @return mixed
     */
    public function getServiceRequestID()
    {
        return $this->serviceRequestID;
    }

    /**
     * @return mixed
     */
    public function isRequiredAtLeastAWeekAgo()
    {
        return (int)$this->isRequiredAtLeastAWeekAgo;
    }


    /**
     * @return mixed
     */
    public function getProjectID()
    {
        return $this->projectID;
    }


    /**
     * @return mixed
     */
    public function getOrderedQuantity()
    {
        return number_format(
            $this->orderedQuantity,
            0
        );
    }


    /**
     * @return array
     */
    public static function getItems(): array
    {
        return self::$items;
    }

    /**
     * @return mixed
     */
    public function getOrderedBy()
    {
        return $this->orderedBy;
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderType()
    {
        return $this->purchaseOrderType;
    }

    /**
     * @return mixed
     */
    public function getHasNotBeenReceivedYet()
    {
        return $this->hasNotBeenReceivedYet;
    }

    /**
     * @return mixed
     */
    public function getHasBeenOrdered()
    {
        return $this->hasBeenOrdered;
    }


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
     * @return \DateTime
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

    /**
     * @param $value
     * @param string $format
     * @return \DateTime|null
     */
    private function returnDateIfValue($value,
                                       $format = 'Y-m-d'
    )
    {
        if (!$value) {
            return null;
        }
        $dateTime = \DateTime::createFromFormat(
            $format,
            $value
        );

        if (!$dateTime) {
            return null;
        }

        return $dateTime;
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


    public function isOrange()
    {
        return !!$this->hasNotBeenReceivedYet;
    }

    public function isRed()
    {
        $redForTypes = ['I', 'P'];

        return in_array(
            $this->purchaseOrderType,
            $redForTypes
        );
    }

    public function isGreenType()
    {
        $greenForTypes = ['C', 'A'];

        return in_array(
            $this->purchaseOrderType,
            $greenForTypes
        );
    }

    public function color()
    {
        if (!isset(self::$items[$this->getPurchaseOrderId()]) || !self::$items[$this->getPurchaseOrderId()]) {
            return !$this->hasBeenOrdered ? 'red' : ($this->hasNotBeenReceivedYet ? 'orange' : "black");
        }
        return 'green';
    }

    public function getSalesOrderId()
    {
        return $this->salesOrderID;
    }

}