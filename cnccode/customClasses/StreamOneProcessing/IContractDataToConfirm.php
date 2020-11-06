<?php


namespace CNCLTD\StreamOneProcessing;


interface IContractDataToConfirm
{
    public function getContractData(): ContractData;
}