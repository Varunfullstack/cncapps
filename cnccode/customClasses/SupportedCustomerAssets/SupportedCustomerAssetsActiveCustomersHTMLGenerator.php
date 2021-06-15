<?php

namespace CNCLTD\SupportedCustomerAssets;
global $cfg;

use CNCLTD\Exceptions\ColumnOutOfRangeException;
use DBECustomer;

require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");

class SupportedCustomerAssetsActiveCustomersHTMLGenerator
{
    /**
     * @var array
     */
    private $cncAssetsNotMatched = [];
    /**
     * @var array
     */
    private $automateAssetsNotMatched = [];

    /**
     * SupportedCustomerAssetsActiveCustomersHTMLGenerator constructor.
     * @param null $customerId
     * @throws ColumnOutOfRangeException
     */
    public function __construct($customerId = null)
    {
        $thing = null;
        if ($customerId) {
            $customerAssets                 = new SupportedCustomerAssets($customerId);
            $this->cncAssetsNotMatched      = array_merge(
                $this->cncAssetsNotMatched,
                $customerAssets->getCNCNotMatchedAssets()
            );
            $this->automateAssetsNotMatched = array_merge(
                $this->automateAssetsNotMatched,
                $customerAssets->getAutomateNotMatchedAssets()
            );
            return;
        }
        $dbeCustomer = new DBECustomer($thing);
        $dbeCustomer->getActiveCustomers(true);
        while ($dbeCustomer->fetchNext()) {
            $customerAssets                 = new SupportedCustomerAssets(
                $dbeCustomer->getValue(DBECustomer::customerID)
            );
            $this->cncAssetsNotMatched      = array_merge(
                $this->cncAssetsNotMatched,
                $customerAssets->getCNCNotMatchedAssets()
            );
            $this->automateAssetsNotMatched = array_merge(
                $this->automateAssetsNotMatched,
                $customerAssets->getAutomateNotMatchedAssets()
            );
        }
    }

    public function printHTML()
    {
        ?>
        <html>
        <head>
            <style>
                thead th {
                    text-align: left;
                }
            </style>
            <link href="screen.css"
                  rel="stylesheet"
            >
        </head>
        <body>
        <h3>
            These items are in Automate but not covered by ServerCare Contracts
        </h3>
        <table>
            <thead>
            <tr>
                <th>
                    Customer Name
                </th>
                <th>
                    Computer Name
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            /** @var NotMatchedItemDTO $item */
            foreach ($this->automateAssetsNotMatched as $item) {
                ?>
                <tr>
                    <td>
                        <?= $item->getCustomerName(); ?>
                    </td>
                    <td>
                        <?= $item->getComputerName(); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <h3>
            These items are in CNC with ServerCare contract but not listed in Automate
        </h3>
        <table>
            <thead>
            <tr>
                <th>
                    Customer Name
                </th>
                <th>
                    Computer Name
                </th>
                <th>
                    Customer Item Number
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            /** @var NotMatchedItemDTO $item */
            foreach ($this->cncAssetsNotMatched as $item) {
                ?>
                <tr>
                    <td>
                        <?= $item->getCustomerName(); ?>
                    </td>
                    <td>
                        <?= $item->getComputerName(); ?>
                    </td>
                    <td>
                        <?php
                        if ($item->getCustomerItemId()) {
                            ?>
                            <a href="/CustomerItem.php?action=displayCI&customerItemID=<?= $item->getCustomerItemId(
                            ); ?>"
                               target="_blank"
                            ><?= $item->getCustomerItemId(); ?></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        </body>
        </html>
        <?php
    }
}