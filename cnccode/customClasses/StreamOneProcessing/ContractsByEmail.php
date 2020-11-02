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

        $this->streamOneEmail = $streamOneEmail;
        $this->customerName = $customerName;
        $this->contractsToConfirm = [];
        $this->contractsBySKU = [];
    }

    public function addContract(ContractData $contractData)
    {
        $this->contractsToConfirm[$contractData->getContractId()] = new ContractDataNotConfirmed($contractData);
        $this->contractsBySKU[$contractData->getSku()] = $contractData->getContractId();
        if ($contractData->getOldSku()) {
            $this->contractsBySKU[$contractData->getOldSku()] = $contractData->getContractId();
        }
    }

    public function flagSKU($sku)
    {
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