<?php

namespace CNCLTD\SupportedCustomerAssets;
class UnsupportedCustomerAssetService
{
    /**
     * @param $listOfUnsupportedAssets NotMatchedItemDTO[]
     */
    public function update(array $listOfUnsupportedAssets)
    {
        $db = \DBConnect::instance()->getDB();
        $db->query("delete from UnsupportedCustomerAsset");
        $insertQuery    = "insert into UnsupportedCustomerAsset(customerId, assetName) values ";
        $values         = [];
        $subQueryValues = [];
        foreach ($listOfUnsupportedAssets as $notMatchedItemDTO) {
            $subQueryValues[] = "(?, ?)";
            $values[]         = $notMatchedItemDTO->customerId();
            $values[]         = $notMatchedItemDTO->getComputerName();
        }
        $insertQuery .= implode(",", $subQueryValues);
        $statement   = $db->prepare($insertQuery);
        $statement->execute($values);
    }

    public function getAllForCustomer($customerId): array
    {
        $db        = \DBConnect::instance()->getDB();
        $statement = $db->prepare("select assetName from UnsupportedCustomerAsset where customerId = ?");
        $statement->execute([$customerId]);
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function checkAssetUnsupported($customerId, $assetName): bool
    {
        $db        = \DBConnect::instance()->getDB();
        $statement = $db->prepare(
            "select assetName from UnsupportedCustomerAsset where customerId = ? and assetName = ?"
        );
        $statement->execute([$customerId, $assetName]);
        return (bool)$statement->columnCount();
    }
}