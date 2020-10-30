<?php


namespace CNCLTD\StreamOneProcessing;


class ContractsByStreamOneEmailAndSKUCollection
{
    /**
     * @var ContractsStreamOneEmail
     */
    private $mappedContracts;

    /**
     * ContractsByStreamOneEmailAndSKUCollection constructor.
     */
    public function __construct()
    {
        $this->mappedContracts = new ContractsStreamOneEmail();
    }

    public function add(ContractData $contractData)
    {
        $this->mappedContracts->add($contractData);
    }

    public function checkSubscriptions($allSubscriptions)
    {
        foreach ($allSubscriptions as $subscription) {
            $this->mappedContracts->flagContractAsFoundByEmailAndSKU($subscription['email'], $subscription['sku']);
        }
    }

    public function checkAddons($orderDetails)
    {
        foreach ($orderDetails as $detail) {
            $this->mappedContracts->flagContractAsFoundByEmailAndSKU($detail['email'], $detail['sku']);
        }
    }

    public function getNotFlaggedContracts()
    {
        return $this->mappedContracts->getNotFlaggedContracts();
    }


}