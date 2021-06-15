<?php

namespace CNCLTD\StreamOneProcessing;
class ContractsByEmail
{
    private $streamOneEmail;
    private $customerName;
    /** @var IContractDataToConfirm[] */
    private $contractsToConfirm;
    private $contractsBySKU;

    /**
     * ContractsByEmail constructor.
     * @param $streamOneEmail
     * @param $customerName
     */
    public function __construct($streamOneEmail, $customerName)
    {

        $this->streamOneEmail     = $streamOneEmail;
        $this->customerName       = $customerName;
        $this->contractsToConfirm = [];
        $this->contractsBySKU     = [];
    }

    /**
     * @param ContractData $contractData
     * @throws ContractWithDuplicatedSKU
     */
    public function addContract(ContractData $contractData)
    {
        $this->contractsToConfirm[$contractData->getContractId()] = new ContractDataNotConfirmed($contractData);
        if (isset($this->contractsBySKU[$contractData->getSku()])) {
            throw new ContractWithDuplicatedSKU(
                $contractData->getSku(),
                $this->contractsToConfirm[$this->contractsBySKU[$contractData->getSku()]]->getContractData(),
                $contractData
            );
        }
        $this->contractsBySKU[$contractData->getSku()] = $contractData->getContractId();
        if ($contractData->getOldSku()) {
            if (isset($this->contractsBySKU[$contractData->getOldSku()])) {
                throw new ContractWithDuplicatedSKU(
                    $contractData->getOldSku(),
                    $contractData,
                    $this->contractsToConfirm[$this->contractsBySKU[$contractData->getOldSku()]]->getContractData()
                );
            }
            $this->contractsBySKU[$contractData->getOldSku()] = $contractData->getContractId();
        }
    }

    public function flagSKU($sku)
    {
        $sku = strtolower($sku);
        if (!isset($this->contractsBySKU[$sku])) {
            return;
        }
        $this->contractsToConfirm[$this->contractsBySKU[$sku]] = new ContractDataConfirmed(
            $this->contractsToConfirm[$this->contractsBySKU[$sku]]->getContractData()
        );
    }

    public function getNotFlaggedContracts()
    {
        $notFlaggedContracts = [];
        foreach ($this->contractsToConfirm as $contract) {
            if ($contract instanceof ContractDataConfirmed) {
                continue;
            }
            $notFlaggedContracts[] = $contract->getContractData();
        }
        return $notFlaggedContracts;
    }
}