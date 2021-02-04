<?php

namespace CNCLTD\SupportedCustomerAssets;
global $cfg;

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
     */
    public function __construct()
    {
        $thing       = null;
        $dbeCustomer = new DBECustomer($thing);
        $dbeCustomer->getActiveCustomers(true);
        while ($dbeCustomer->fetchNext()) {
            $customerAssets                 = new \CNCLTD\SupportedCustomerAssets\SupportedCustomerAssets(
                $dbeCustomer->getValue(DBECustomer::customerID)
            );
            $this->cncAssetsNotMatched      = array_merge(
                $this->cncAssetsNotMatched,
                $customerAssets->getCNCNotMatchedAssets()
            );
            var_dump(count($this->cncAssetsNotMatched));
            $this->automateAssetsNotMatched = array_merge(
                $this->automateAssetsNotMatched,
                $customerAssets->getAutomateNotMatchedAssets()
            );
            var_dump(count($this->automateAssetsNotMatched));
        }
    }

    public function printHTML()
    {
        ?>
        <html>
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