<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 10/12/2018
 * Time: 9:04
 */

namespace CNCLTD;


use DateTime;
use JsonSerializable;

class ItemNotYetReceived implements JsonSerializable
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
    protected $lineSequenceNumber;
    protected $expectedTBC;

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
    public function isDeliveryConfirmed()
    {
        return $this->deliveryConfirmedFlag == 'Y';
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
    public function getOrderedQuantity()
    {
        return number_format(
            $this->orderedQuantity,
            0
        );
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
     * @return DateTime
     */
    public function getPurchaseOrderDate()
    {
        return $this->returnDateIfValue($this->purchaseOrderDate);
    }

    /**
     * @param $value
     * @param string $format
     * @return DateTime|null
     */
    private function returnDateIfValue($value,
                                       $format = 'Y-m-d'
    )
    {
        if (!$value) {
            return null;
        }
        $dateTime = DateTime::createFromFormat(
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
    public function getFutureDate()
    {
        return $this->returnDateIfValue($this->futureDate);
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

    /**
     * @return mixed
     */
    public function getExpectedTBC()
    {
        return $this->expectedTBC;
    }

    public function getPurchaseOrderURL()
    {
        return SITE_URL . "/PurchaseOrder.php?action=display&porheadID=" . $this->getPurchaseOrderId();
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderId()
    {
        return $this->purchaseOrderId;
    }

    public function getSalesOrderURL()
    {
        return SITE_URL . "/SalesOrder.php?action=displaySalesOrder&ordheadID=" . $this->getSalesOrderId();
    }

    public function getSalesOrderId()
    {
        return $this->salesOrderID;
    }

    public function getProjectURL()
    {
        return SITE_URL . "/Project.php?projectID=" . $this->getProjectID() . "&action=edit";
    }

    /**
     * @return mixed
     */
    public function getProjectID()
    {
        return $this->projectID;
    }

    public function getServiceRequestURL()
    {
        return SITE_URL . "/Activity.php?problemID=" . $this->getServiceRequestID() . "&action=displayLastActivity";
    }

    /**
     * @return mixed
     */
    public function getServiceRequestID()
    {
        return $this->serviceRequestID;
    }

    public function getExpectedDateLinkURL()
    {
        return SITE_URL . "/PurchaseOrder.php?porheadID={$this->getPurchaseOrderId()}&action=editOrdline&sequenceNo={$this->getLineSequenceNumber()}";
    }

    /**
     * @return mixed
     */
    public function getLineSequenceNumber()
    {
        return $this->lineSequenceNumber;
    }

    public function getExpectedDateLinkText()
    {
        $text = 'N/A';

        if ($this->color() == 'green') {
            $text = 'Received';
        } else {
            if ($this->getExpectedOn()) {
                $text = $this->getExpectedOn()->format('d/m/Y');
            } else if ($this->getExpectedTBC()) {
                $text = "TBC";
            }
        }
        return $text;
    }

    public function color()
    {
        if (!isset(self::$items[$this->getPurchaseOrderId()]) || !self::$items[$this->getPurchaseOrderId()]) {
            $orangeOrBlack = $this->hasNotBeenReceivedYet ? 'orange' : "black";
            return !$this->hasBeenOrdered ? 'red' : $orangeOrBlack;
        }
        return 'green';
    }

    /**
     * @return DateTime
     */
    public function getExpectedOn()
    {
        if (!$this->cost || $this->isCarriage()) {
            return null;
        }
        return $this->returnDateIfValue($this->expectedOn);
    }

    private function isCarriage()
    {
        return $this->itemId == 1491;
    }

    /**
     * @return mixed
     */
    public function getExpectedTBC()
    {
        return $this->expectedTBC;
    }

    public function jsonSerialize()
    {
        return array_merge(
            get_object_vars($this),
            [
                "expectedColorClass"   => $this->getExpectedColorClass(),
                "requiredByColorClass" => $this->getRequiredByColorClass(),
                "color"                => $this->color(),
            ]
        );
    }

    function getExpectedColorClass()
    {
        $expectedColorClass = null;
        if ($this->getExpectedOn()) {
            if ($this->getExpectedOn()->format(DATE_MYSQL_DATE) < (new DateTime())->format(DATE_MYSQL_DATE)) {
                $expectedColorClass = "redBackground";
            }

        } elseif ($this->getExpectedTBC()) {
            $expectedColorClass = "amberBackground";
        }

        return $expectedColorClass;
    }

    function getRequiredByColorClass()
    {
        $requiredByColorClass = 'amberBackground';
        if ($this->getPurchaseOrderRequiredBy()) {
            $requiredByColorClass = null;
        }
        return $requiredByColorClass;
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrderRequiredBy()
    {
        return $this->returnDateIfValue($this->purchaseOrderRequiredBy);
    }
}