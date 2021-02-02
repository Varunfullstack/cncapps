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
    private $cncContractAssets = [];
    private $labtechAssets;

    public function __construct()
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow(520);
        $dsn                              = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
        $options                          = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
        $this->labTechDB                  = new PDO($dsn, LABTECH_DB_USERNAME, LABTECH_DB_PASSWORD, $options);
        $this->operatingSystemsCollection = new OperatingSystemsSupportDatesCollection();
        $tabularData                      = new ExportedItemCollection(
            $dbeCustomer, $this->operatingSystemsCollection, $this->labTechDB
        );
        $buCustomerItem                   = new \BUCustomerItem($this);
        $customerAssets                   = new \DataSet($this);
        $buCustomerItem->getCustomerItemsByContractID(28531, $customerAssets);
        while ($customerAssets->fetchNext()) {
            $this->cncContractAssets[strtolower($customerAssets->getValue(\DBECustomerItem::serverName))] = false;
        }
        foreach ($tabularData->getExportData() as $key => $exportDatum) {
            if ($tabularData->isServerAsset($key)) {
                $asset                                   = $tabularData->getAsset($key);
                $lowerComputerName                       = strtolower($asset->getComputerName());
                $this->labtechAssets[$lowerComputerName] = true;
                if (!isset($this->cncContractAssets[$lowerComputerName])) {
                    var_dump($asset->getComputerName());
                } else {
                    $this->cncContractAssets[$lowerComputerName] = true;
                }
            }
        }
        var_dump('Elements that exists in CNC that do not exist in labtech');
        foreach ($this->cncContractAssets as $assetName => $hasAMatch) {
            if (!$hasAMatch) {
                var_dump($assetName);
            }
        }

        $cncContractItem = [
            "customerName" => "",
            "customerItemId" => "",

        ];


        $automateAssetItem = [

        ];

    }
}