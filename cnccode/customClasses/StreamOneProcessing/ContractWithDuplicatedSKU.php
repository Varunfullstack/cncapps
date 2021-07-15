<?php


namespace CNCLTD\StreamOneProcessing;


use Exception;

class ContractWithDuplicatedSKU extends Exception
{
    protected $duplicatedSKU;
    protected $contractA;
    protected $contractB;

    public function __construct($duplicatedSKU, ContractData $contractData, ContractData $otherContract)
    {
        $this->duplicatedSKU = $duplicatedSKU;
        $this->contractA = $contractData;
        $this->contractB = $otherContract;
        $message = "We have found 2 contracts that belong to {$contractData->getCustomerName()} with the same SKU {$duplicatedSKU} for these two items {$contractData->getItemDescription()} | {$otherContract->getItemDescription()}";
        parent::__construct(
            $message,
            52451
        );
    }

    /**
     * @return mixed
     */
    public function getDuplicatedSKU()
    {
        return $this->duplicatedSKU;
    }

    /**
     * @return ContractData
     */
    public function getContractA(): ContractData
    {
        return $this->contractA;
    }

    /**
     * @return ContractData
     */
    public function getContractB(): ContractData
    {
        return $this->contractB;
    }
}