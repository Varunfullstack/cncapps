<?php

namespace CNCLTD\SupportedCustomerAssets;

use CNCLTD\AssetListExport\ExportedItemCollection;
use CNCLTD\AssetListExport\OperatingSystemsSupportDatesCollection;
use DBECustomer;
use PDO;

global $cfg;
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');


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
        foreach ($tabularData->getExportData() as $key => $exportDatum) {
            if ($tabularData->isServerAsset($key)) {
                $asset = $tabularData->getAsset($key);
                var_dump($asset->getComputerName());
            }
        }
        $buCustomerItem = new \BUCustomerItem($this);
        $customerAssets = new \DataSet($this);
        $buCustomerItem->getCustomerItemsByContractID(28531, $customerAssets);
    }
}