<?php

namespace CNCLTD\StreamOneProcessing;
class ContractsStreamOneEmail
{
    /**
     * @var ContractsByEmail[]
     */
    private $map;

    /**
     * ContractsStreamOneEmailMap constructor.
     */
    public function __construct()
    {
        $this->map = [];
    }

    /**
     * @param ContractData $contractData
     * @throws ContractWithDuplicatedSKU
     */
    public function add(ContractData $contractData)
    {

        if (!isset($this->map[$contractData->getStreamOneEmail()])) {
            $this->map[$contractData->getStreamOneEmail()] = new ContractsByEmail(
                $contractData->getStreamOneEmail(), $contractData->getCustomerName()
            );
        }
        $this->map[$contractData->getStreamOneEmail()]->addContract($contractData);
    }

    public function flagContractAsFoundByEmailAndSKU($email, $sku)
    {
        if (!isset($this->map[$email])) {
            return;
        }
        $this->map[$email]->flagSKU($sku);
    }

    /**
     * @return ContractData[]
     */
    public function getNotFlaggedContracts()
    {
        return array_reduce(
            $this->map,
            function ($acc, ContractsByEmail $item) {
                return array_merge($acc, $item->getNotFlaggedContracts());
            },
            []
        );
    }
}