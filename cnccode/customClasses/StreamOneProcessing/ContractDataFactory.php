<?php


namespace CNCLTD\StreamOneProcessing;


class ContractDataFactory
{
    public function fromDB($row)
    {
        return new ContractData(
            $row['contractId'],
            $row['sku'],
            $row['oldSku'],
            $row['customerName'],
            $row['streamOneEmail'],
            $row['itemDescription']
        );
    }
}