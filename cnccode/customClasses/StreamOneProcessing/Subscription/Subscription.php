<?php

namespace CNCLTD\StreamOneProcessing\Subscription;

use phpDocumentor\Reflection\Utils;

class Subscription
{
    private $id;
    private $orderNumber;
    private $sku;
    private $productType;
    private $name;
    private $quantity;
    private $unitPrice;
    private $licenceStatus;
    private $customerEmail;
    private $companyName;
    private $customerName;
    private $endCustomerPO;
    private $additionalData;

    /**
     * Subscription constructor.
     * @param $additionalData
     * @param $id
     * @param $orderNumber
     * @param $sku
     * @param $productType
     * @param $name
     * @param $quantity
     * @param $unitPrice
     * @param $licenceStatus
     * @param $customerEmail
     * @param $endCustomerPO
     * @param $customerName
     */
    public function __construct($id,
                                $orderNumber,
                                $sku,
                                $productType,
                                $name,
                                $quantity,
                                $unitPrice,
                                $licenceStatus,
                                $customerEmail,
                                $companyName,
                                $customerName,
                                $endCustomerPO,
                                $additionalData
    )
    {
        $this->id             = $id;
        $this->orderNumber    = $orderNumber;
        $this->sku            = $sku;
        $this->productType    = $productType;
        $this->name           = $name;
        $this->quantity       = $quantity;
        $this->unitPrice      = $unitPrice;
        $this->licenceStatus  = $licenceStatus;
        $this->customerEmail  = $customerEmail;
        $this->companyName    = $companyName;
        $this->customerName   = $customerName;
        $this->endCustomerPO  = $endCustomerPO === '' ? null : $endCustomerPO;
        $this->additionalData = $additionalData;
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function orderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @return mixed
     */
    public function sku()
    {
        return $this->sku;
    }

    /**
     * @return mixed
     */
    public function productType()
    {
        return $this->productType;
    }

    /**
     * @return mixed
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function quantity()
    {
        return $this->quantity;
    }

    /**
     * @return mixed
     */
    public function unitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @return mixed
     */
    public function licenceStatus()
    {
        return $this->licenceStatus;
    }

    /**
     * @return mixed
     */
    public function customerEmail()
    {
        return $this->customerEmail;
    }

    public function companyName()
    {
        return $this->companyName;
    }

    public function customerName()
    {
        return $this->customerName;
    }

    public function endCustomerPO()
    {
        return $this->endCustomerPO;
    }

    public function additionalData()
    {
        return $this->additionalData;
    }

    public function isActive()
    {
        return $this->licenceStatus === 'active';
    }
}