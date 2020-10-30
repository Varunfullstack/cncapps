<?php


namespace CNCLTD\StreamOneProcessing;


class ContractDataConfirmed implements IContractDataToConfirm
{
    private $contractData;

    /**
     * ContractDataConfirmed constructor.
     * @param $contractData
     */
    public function __construct(ContractData $contractData)
    {

        $this->contractData = $contractData;
    }

    public function getContractData(): ContractData
    {
        return $this->contractData;
    }
}