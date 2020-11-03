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

    /**
     * @param ContractData $contractData
     * @throws ContractWithDuplicatedSKU
     */
    public function add(ContractData $contractData)
    {
        $this->mappedContracts->add($contractData);
    }

    /**
     * @param StreamOneLicenseData[] $licenses
     */
    public function checkLicenses(array $licenses)
    {
        foreach ($licenses as $license) {
            $this->mappedContracts->flagContractAsFoundByEmailAndSKU(
                $license->getEndCustomerEmail(),
                $license->getSku()
            );
        }
    }

    public function getNotFlaggedContracts()
    {
        return $this->mappedContracts->getNotFlaggedContracts();
    }


}