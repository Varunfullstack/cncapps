<?php


namespace CNCLTD\StreamOneProcessing;


class ContractDataNotConfirmed implements IContractDataToConfirm
{
    /**
     * @var ContractData
     */
    private $contractData;

    public function __construct(ContractData $contractData)
    {
        $this->contractData = $contractData;
    }

    public function getContractData(): ContractData
    {
        return $this->contractData;
    }
}