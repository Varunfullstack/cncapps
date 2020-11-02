<?php


namespace CNCLTD\StreamOneProcessing;


class ContractData
{
    /**
     * @var mixed
     */
    private $contractId;
    /**
     * @var mixed
     */
    private $sku;
    /**
     * @var mixed
     */
    private $oldSku;
    /**
     * @var mixed
     */
    private $customerName;
    /**
     * @var mixed
     */
    private $streamOneEmail;
    /**
     * @var mixed
     */
    private $itemDescription;

    /**
     * ContractData constructor.
     * @param mixed $contractId
     * @param mixed $sku
     * @param mixed $oldSku
     * @param mixed $customerName
     * @param mixed $streamOneEmail
     * @param mixed $itemDescription
     */
    public function __construct($contractId, $sku, $oldSku, $customerName, $streamOneEmail, $itemDescription)
    {
        $this->contractId = $contractId;
        $this->sku = $sku;
        $this->oldSku = $oldSku;
        $this->customerName = $customerName;
        $this->streamOneEmail = $streamOneEmail;
        $this->itemDescription = $itemDescription;
    }

    /**
     * @return int
     */
    public function getContractId()
    {
        return $this->contractId;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return strtolower($this->sku);
    }

    /**
     * @return string
     */
    public function getOldSku()
    {
        return strtolower($this->oldSku);
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @return string
     */
    public function getStreamOneEmail()
    {
        return strtolower($this->streamOneEmail);
    }

    /**
     * @return string
     */
    public function getItemDescription()
    {
        return $this->itemDescription;
    }

}