<?php

namespace CNCLTD\SupportedCustomerAssets;

use CNCLTD\AssetListExport\ExportedItemCollection;
use CNCLTD\AssetListExport\OperatingSystemsSupportDatesCollection;
use DBECustomer;
use PDO;

global $cfg;
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');


class SupportedCustomerAssets
{
    /**
     * @var PDO
     */
    private $labTechDB;
    /**
     * @var OperatingSystemsSupportDatesCollection
     */
    private $operatingSystemsCollection;
    /** @var array */
    private $cncContractAssets = [];
    private $automateAssets    = [];

    public function __construct($customerId)
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerId);
        $dsn                              = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
        $options                          = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
        $this->labTechDB                  = new PDO($dsn, LABTECH_DB_USERNAME, LABTECH_DB_PASSWORD, $options);
        $this->operatingSystemsCollection = new OperatingSystemsSupportDatesCollection();
        $tabularData                      = new ExportedItemCollection(
            $dbeCustomer, $this->operatingSystemsCollection, $this->labTechDB
        );
        $buCustomerItem                   = new \BUCustomerItem($this);
        $validContract                    = new \DataSet($this);
        $buCustomerItem->getServerCareValidContractsByCustomerID($customerId, $validContract);
        if (!$validContract->fetchNext()) {
            return;
        }
        $customerAssets = new \DataSet($this);
        $buCustomerItem->getCustomerItemsByContractID(
            $validContract->getValue(\DBECustomerItem::customerItemID),
            $customerAssets
        );
        while ($customerAssets->fetchNext()) {
            if ($customerAssets->getValue(\DBECustomerItem::bypassCWAAgentCheck)) {
                continue;
            }
            $this->cncContractAssets[strtolower($customerAssets->getValue(\DBECustomerItem::serverName))] = [
                "matched" => false,
                "item"    => new NotMatchedItemDTO(
                    $dbeCustomer->getValue(DBECustomer::name),
                    $customerAssets->getValue(\DBECustomerItem::serverName),
                    $dbeCustomer->getValue(DBECustomer::customerID),
                    $customerAssets->getValue(\DBECustomerItem::customerItemID)
                ),
            ];
        }
        foreach ($tabularData->getExportData() as $key => $exportDatum) {
            if ($tabularData->isServerAsset($key) || $tabularData->is3CX($key)) {
                $asset             = $tabularData->getAsset($key);
                $lowerComputerName = strtolower($asset->getComputerName());
                if (!isset($this->cncContractAssets[$lowerComputerName])) {
                    $this->automateAssets[$lowerComputerName] = [
                        "matched" => false,
                        "item"    => new NotMatchedItemDTO(
                            $dbeCustomer->getValue(DBECustomer::name),
                            $asset->getComputerName(),
                            $dbeCustomer->getValue(DBECustomer::customerID)
                        ),
                    ];
                } else {
                    $this->cncContractAssets[$lowerComputerName]['matched'] = true;
                }
            }
        }
    }

    /**
     * @return NotMatchedItemDTO[]
     */
    public function getCNCNotMatchedAssets(): array
    {
        return $this->getNotMatchedItems($this->cncContractAssets);
    }


    public function getAutomateNotMatchedAssets(): array
    {
        return $this->getNotMatchedItems($this->automateAssets);
    }

    private function getNotMatchedItems($matchingList): array
    {
        return array_values(
            array_map(
                function ($item) { return $item['item']; },
                array_filter($matchingList, function ($item) { return !$item['matched']; })
            )
        );
    }

}