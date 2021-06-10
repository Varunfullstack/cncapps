<?php

namespace CNCLTD\StreamOneProcessing;
global $cfg;
require_once($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomer.inc.php");

use CNCLTD\Data\DBEItem;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CNCLTD\LoggerCLI;
use CNCLTD\StreamOneProcessing\Subscription\Subscription;
use DBECustomer;
use DBECustomerItem;
use Exception;

class StreamOneContractsUpdates
{


    /**
     * @var Subscription[]
     */
    private $subscriptions;

    private $customerContractsWithLicenses = [];

    /**
     * @var CustomerForLicenseEmailGetter
     */
    private $customerForLicenseEmailGetter;

    /**
     * @var LoggerCLI
     */
    private $loggerCLI;

    /**
     * StreamOneContractsUpdates constructor.
     */
    public function __construct($subscriptions, LoggerCLI $loggerCLI)
    {
        $this->subscriptions                 = $subscriptions;
        $this->customerForLicenseEmailGetter = new CustomerForLicenseEmailGetter();
        $this->loggerCLI                     = $loggerCLI;
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    public function __invoke()
    {
        $this->processSubscriptions();
        foreach ($this->customerContractsWithLicenses as $customerId => $customerContractsWithLicences) {
            foreach ($customerContractsWithLicences as $contractId => $subscriptions) {
                try {
                    $this->checkSubscriptionCount($subscriptions, $customerId, $contractId);
                    if ($this->shouldDisableContract($subscriptions)) {
                        $this->disableContract($contractId);
                        continue;
                    }
                    $this->updateContractPriceAndUnits($subscriptions, $contractId);

                } catch (Exception $exception) {
                    $this->loggerCLI->error($exception->getMessage());
                }
            }
        }
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    function getItemIdForSKU($sku)
    {
        $items = new DBEItem($this);
        if ($items->getItemsByPartNoOrOldPartNo($sku)) {
            return $items->getValue(DBEItem::itemID);
        }
        return null;
    }

    /**
     * @param $contractId
     */
    private function disableContract($contractId): void
    {
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->getRow($contractId);
        $dbeCustomerItem->setValue(DBECustomerItem::renewalStatus, 'D');
        $dbeCustomerItem->setValue(DBECustomerItem::declinedFlag, 'Y');
        $dbeCustomerItem->updateRow();
    }

    /**
     * @param $subscriptions
     * @param $customerId
     * @param $contractId
     * @throws Exception
     */
    private function checkSubscriptionCount($subscriptions, $customerId, $contractId): void
    {
        $subscriptionsCount = count($subscriptions);
        if (!$subscriptionsCount) {
            throw new Exception(
                "Customer ($customerId) with contract $contractId does not have any matching subscriptions in StreamOne"
            );
        }
        if ($subscriptionsCount > 2) {
            throw new Exception(
                "Customer ($customerId) with contract $contractId has more than 2 matching subscriptions in StreamOne (This shouldn't be possible..)"
            );
        }
    }

    /**
     * @param Subscription[] $subscriptions
     * @return bool
     */
    private function shouldDisableContract(array $subscriptions): bool
    {
        foreach ($subscriptions as $subscription) {
            if ($subscription->isActive()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Subscription[] $subscriptions
     * @return float
     */
    private function getPriceFromSubscriptions(array $subscriptions): float
    {
        return $subscriptions[0]->unitPrice();
    }

    /**
     * @param Subscription[] $subscriptions
     * @return int|mixed
     */
    private function getUnitsFromSubscriptions(array $subscriptions)
    {
        $units = 0;
        foreach ($subscriptions as $subscription) {
            if ($subscription->isActive()) {
                $units += $subscription->quantity();
            }
        }
        return $units;
    }

    /**
     * @param $subscriptions
     * @param $contractId
     * @throws ColumnOutOfRangeException
     */
    private function updateContractPriceAndUnits($subscriptions, $contractId): void
    {
        $price           = $this->getPriceFromSubscriptions($subscriptions);
        $units           = $this->getUnitsFromSubscriptions($subscriptions);
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->getRow($contractId);
        $salePrice      = $dbeCustomerItem->getValue(DBECustomerItem::salePrice);
        $salePriceAnnum = ($salePrice * $units) * 12;
        $costPriceAnnum = ($price * $units) * 12;
        $dbeCustomerItem->setValue(DBECustomerItem::costPricePerMonth, $price);
        $dbeCustomerItem->setValue(DBECustomerItem::costPrice, $costPriceAnnum);
        $dbeCustomerItem->setValue(DBECustomerItem::users, $units);
        $dbeCustomerItem->setValue(DBECustomerItem::salePrice, $salePriceAnnum);
        $dbeCustomerItem->updateRow();
    }

    /**
     * @return void
     * @throws ColumnOutOfRangeException
     */
    private function processSubscriptions()
    {
        foreach ($this->subscriptions as $subscription) {
            $subscriptionId = $subscription->id();
            $sku            = $subscription->sku();
            $units          = $subscription->quantity();
            $licenseStatus  = $subscription->licenceStatus();
            $licenseEmail   = $subscription->customerEmail();
            $customer       = $this->customerForLicenseEmailGetter->__invoke($licenseEmail);
            if (!$customer) {
                $this->loggerCLI->error(
                    "Failed to retrieve customer for subscription $subscriptionId for license $sku and email $licenseEmail with $units and status $licenseStatus"
                );
                if ($subscription->isActive()) {

                    $this->emailSales(
                        "StreamOne Customer Not Linked To CNC customer",
                        "$licenseEmail has active subscriptions for SKU $sku and it is not linked to a CNC customer"
                    );
                }
                continue;
            }
            $customerId   = $customer->getValue(DBECustomer::customerID);
            $customerName = $customer->getValue(DBECustomer::name);
            $itemId       = $this->getItemIdForSKU($sku);
            if (!$itemId) {
                $message = "There is no Item with partNo or oldPartNO matching the SKU $sku in CNCAPPS";
                $this->loggerCLI->error($message);
                if ($subscription->isActive()) {
                    $this->emailSales("StreamOne Licence in use with no CNC Item", $message);
                }
                continue;
            }
            if (!key_exists($customerId, $this->customerContractsWithLicenses)) {
                $this->customerContractsWithLicenses[$customerId] = [];
            }
            $contracts = new DBECustomerItem($this);
            $contracts->getRowsByCustomerAndItemID($customerId, $itemId, true);
            if (!$contracts->fetchNext()) {
                if ($subscription->isActive()) {
                    $this->loggerCLI->error(
                        "Customer $customerName($customerId) $licenseEmail  does not have a corresponding valid contract for SKU $sku in CNCAPPS"
                    );
                }
                continue;
            }
            $contractId = $contracts->getValue(DBECustomerItem::customerItemID);
            if (!key_exists($contractId, $this->customerContractsWithLicenses[$customerId])) {
                $this->customerContractsWithLicenses[$customerId][$contractId] = [];
            }
            $this->customerContractsWithLicenses[$customerId][$contractId][] = $subscription;
        }
    }

    private function emailSales($subject, $body)
    {
        $buMail = new \BUMail($this);
        $buMail->sendSimpleEmail($body, $subject, CONFIG_SALES_EMAIL);
    }

}