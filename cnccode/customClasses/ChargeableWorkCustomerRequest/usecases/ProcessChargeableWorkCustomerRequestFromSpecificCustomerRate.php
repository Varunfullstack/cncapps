<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use BUCustomer;
use BUCustomerItem;
use BUHeader;
use BUSalesOrder;
use CNCLTD\AdditionalChargesRates\Application\GetOneSpecificRateForCustomer\GetOneSpecificRateForCustomerQuery;
use CNCLTD\AdditionalChargesRates\Application\GetOneSpecificRateForCustomer\GetOneSpecificRateForCustomerResponse;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateId;
use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\Data\DBEJProblem;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CNCLTD\Shared\Domain\Bus\QueryBus;
use DataSet;
use DBECustomer;
use DBEHeader;
use DBEItem;
use DBEJContract;
use DBEJOrdhead;
use DBEJOrdline;
use DBEJUser;
use DBEOrdhead;
use DBEOrdline;
use DBEProblem;
use DBEUser;

class ProcessChargeableWorkCustomerRequestFromSpecificCustomerRate
{
    /**
     * @var QueryBus
     */
    private $queryBus;


    /**
     * ApprovePendingChargeableWorkCustomerRequest constructor.
     */
    public function __construct(QueryBus $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    /**
     * @param DBEProblem $serviceRequest
     * @param string $additionalChargeRateId
     * @throws ColumnOutOfRangeException
     */
    public function __invoke(DBEJProblem $serviceRequest, string $additionalChargeRateId, DBEUser $requester)
    {
        $buCustomer = new BUCustomer($this);
        $customerId = $serviceRequest->getValue(DBEProblem::customerID);
        $hasPrepay  = $buCustomer->hasPrepayContract($customerId);
        /** @var GetOneSpecificRateForCustomerResponse $response */
        $response            = $this->queryBus->ask(
            new GetOneSpecificRateForCustomerQuery(
                new CustomerId($customerId), AdditionalChargeRateId::fromNative($additionalChargeRateId)
            )
        );
        $hasLinkedSalesOrder = (bool)$serviceRequest->getValue(DBEJProblem::linkedSalesOrderID);
        if (!$hasPrepay || $hasLinkedSalesOrder) {
            $this->createOrUpdateSalesOrder($serviceRequest, $response);
        }
        $this->updateServiceRequest($serviceRequest, $response, $requester, $hasPrepay, $hasLinkedSalesOrder);
    }

    /**
     * @param DBEJProblem $DBEJProblem
     * @param GetOneSpecificRateForCustomerResponse $response
     * @return void
     * @throws ColumnOutOfRangeException
     */
    private function createOrUpdateSalesOrder(DBEJProblem $DBEJProblem,
                                              GetOneSpecificRateForCustomerResponse $response
    ): void
    {
        $salesOrderId     = $DBEJProblem->getValue(DBEProblem::linkedSalesOrderID);
        $serviceRequestId = $DBEJProblem->getValue(DBEJProblem::problemID);
        $buSalesOrders    = new BUSalesOrder($this);
        $dsOrdhead        = new DataSet($this);
        $dsOrdline        = new DataSet($this);
        $dbeCustomer      = new DBECustomer($this);
        $customerID       = $DBEJProblem->getValue(DBEJProblem::customerID);
        if (!$salesOrderId) {
            $dbeCustomer->getRow($customerID);
            $buSalesOrders->initialiseOrder($dsOrdhead, $dsOrdline, $dbeCustomer);
            $dsOrdhead->setUpdateModeUpdate();
            $dsOrdhead->setValue(DBEJOrdhead::custPORef, 'T & M Service');
            $dsOrdhead->setValue(DBEJOrdhead::addItem, 'N');
            $dsOrdhead->setValue(DBEJOrdhead::partInvoice, 'N');
            $dsOrdhead->setValue(DBEJOrdhead::paymentTermsID, CONFIG_PAYMENT_TERMS_30_DAYS);
            $dsOrdhead->post();
            $buSalesOrders->updateHeader(
                $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                $dsOrdhead->getValue(DBEOrdhead::custPORef),
                $dsOrdhead->getValue(DBEOrdhead::paymentTermsID),
                $dsOrdhead->getValue(DBEOrdhead::partInvoice),
                $dsOrdhead->getValue(DBEOrdhead::addItem)
            );
            $ordheadID  = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->getRow($serviceRequestId);
            $dbeProblem->setValue(
                DBEJProblem::linkedSalesOrderID,
                $ordheadID
            );
            $dbeProblem->updateRow();
            $this->insertCommentLine($ordheadID, $customerID, $DBEJProblem);
            $this->insertItemLine($ordheadID, $customerID, $response);
            return;
        }
        $buSalesOrders->getOrderByOrdheadID($salesOrderId, $dsOrdline, $dsOrdline);
        $additionalChargeLine = $this->getAdditionalChargeLine($dsOrdline, $response);
        if (!$additionalChargeLine) {
            $this->insertItemLine($salesOrderId, $customerID, $response);
        } else {
            $this->updateItemLine($additionalChargeLine);
        }
    }

    /**
     * @param float|null $ordheadID
     * @param float|null $customerID
     * @param DBEJProblem $DBEJProblem
     * @return void
     * @throws ColumnOutOfRangeException
     */
    private function insertCommentLine(?float $ordheadID, ?float $customerID, DBEJProblem $DBEJProblem): void
    {
        $dbeOrdline = $this->getNewLine($ordheadID, $customerID);
        $dbeOrdline->setValue(DBEJOrdline::lineType, 'C');
        $dbeOrdline->setValue(DBEJOrdline::itemID, null);
        $dbeOrdline->setValue(DBEJOrdline::stockcat, null);
        $dbeOrdline->setValue(DBEJOrdline::sequenceNo, $dbeOrdline->getNextSortOrder());
        $dbeOrdline->setValue(DBEJOrdline::qtyOrdered, 0);
        $dbeOrdline->setValue(DBEJOrdline::curUnitCost, 0);
        $dbeOrdline->setValue(DBEJOrdline::curTotalCost, 0);
        $dbeOrdline->setValue(DBEJOrdline::curUnitSale, 0);
        $dbeOrdline->setValue(DBEJOrdline::curTotalSale, 0);
        $dbeOrdline->setValue(DBEJOrdline::description, $DBEJProblem->getValue(DBEProblem::emailSubjectSummary));
        $dbeOrdline->insertRow();
    }

    /**
     * @param  $ordheadID
     * @param $customerID
     * @param GetOneSpecificRateForCustomerResponse $response
     * @throws ColumnOutOfRangeException
     */
    private function insertItemLine($ordheadID,
                                    $customerID,
                                    GetOneSpecificRateForCustomerResponse $response
    ): void
    {
        $dbeOrdline = $this->getNewLine($ordheadID, $customerID);
        $dbeItem    = new DBEItem($this);
        $dbeItem->getRow(CONFIG_ADDITIONAL_CHARGE_ITEMID);
        $amount = 1;
        $dbeOrdline->setValue(DBEOrdline::lineType, 'I');
        $dbeOrdline->setValue(DBEOrdline::itemID, $dbeItem->getValue(DBEItem::itemID));
        $dbeOrdline->setValue(DBEOrdline::stockcat, $dbeItem->getValue(DBEItem::stockcat));
        $dbeOrdline->setValue(DBEOrdline::sequenceNo, $dbeOrdline->getNextSortOrder());
        $dbeOrdline->setValue(DBEOrdline::qtyOrdered, 1);
        $dbeOrdline->setValue(DBEOrdline::curUnitCost, 0);
        $dbeOrdline->setValue(DBEOrdline::curTotalCost, 0);
        $dbeOrdline->setValue(DBEOrdline::curUnitSale, $response->salePrice());
        $dbeOrdline->setValue(DBEOrdline::curTotalSale, $response->salePrice() * $amount);
        $dbeOrdline->setValue(DBEOrdline::description, $response->description());
        $dbeOrdline->insertRow();
    }

    /**
     * @param $ordheadID
     * @param $customerID
     * @return DBEOrdline
     */
    private function getNewLine($ordheadID, $customerID): DBEOrdline
    {
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue(DBEJOrdline::ordheadID, $ordheadID);
        $dbeOrdline->setValue(DBEJOrdline::customerID, $customerID);
        $dbeOrdline->setValue(DBEJOrdline::qtyDespatched, 0);
        $dbeOrdline->setValue(DBEJOrdline::qtyLastDespatched, 0);
        $dbeOrdline->setValue(
            DBEJOrdline::supplierID,
            CONFIG_SALES_STOCK_SUPPLIERID
        );
        return $dbeOrdline;
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function updateItemLine(DBEOrdline $labourLine)
    {
        $quantity = $labourLine->getValue(DBEOrdline::qtyOrdered) + 1;
        $labourLine->setValue(DBEOrdline::qtyOrdered, $quantity);
        $cost = $labourLine->getValue(DBEOrdline::curUnitCost);
        $labourLine->setValue(DBEOrdline::curTotalCost, $cost * $quantity);
        $sale = $labourLine->getValue(DBEOrdline::curUnitSale);
        $labourLine->setValue(DBEOrdline::curTotalSale, $sale * $quantity);
        $labourLine->updateRow();
    }

    /**
     * @param DataSet $dsOrdline
     * @param GetOneSpecificRateForCustomerResponse $response
     * @return DBEOrdline|null
     * @throws ColumnOutOfRangeException
     */
    private function getAdditionalChargeLine(DataSet $dsOrdline,
                                             GetOneSpecificRateForCustomerResponse $response
    ): ?DBEOrdline
    {
        while ($dsOrdline->fetchNext()) {
            if ($dsOrdline->getValue(DBEOrdline::itemID) === CONFIG_ADDITIONAL_CHARGE_ITEMID && $dsOrdline->getValue(
                    DBEOrdline::description
                ) === $response->description()) {
                $dbeOrdline = new DBEOrdline($this);
                $dbeOrdline->getRow($dsOrdline->getValue(DBEOrdline::id));
                return $dbeOrdline;
            }
        }
        return null;
    }

    /**
     * @param DBEJProblem $dbeProblem
     * @param GetOneSpecificRateForCustomerResponse $request
     * @param DBEUser $requester
     * @param bool $hasPrepay
     * @param bool $hasLinkedSalesOrder
     * @throws ColumnOutOfRangeException
     */
    private function updateServiceRequest(DBEJProblem $dbeProblem,
                                          GetOneSpecificRateForCustomerResponse $request,
                                          DBEUser $requester,
                                          bool $hasPrepay,
                                          bool $hasLinkedSalesOrder
    ): void
    {
        $toUpdateProblem  = new DBEProblem($this);
        $serviceRequestId = $dbeProblem->getValue(DBEProblem::problemID);
        $toUpdateProblem->getRow($serviceRequestId);
        if (!$hasLinkedSalesOrder) {
            if (!$hasPrepay) {
                $toUpdateProblem->setValue(DBEProblem::priority, 5);
            } else {
                $toUpdateProblem->setValue(DBEProblem::prePayChargeApproved, 1);
                $buCustomer       = new BUCustomerItem($this);
                $datasetContracts = new DataSet($this);
                $buCustomer->getPrepayContractByCustomerID(
                    $dbeProblem->getValue(DBEProblem::customerID),
                    $datasetContracts
                );
                $toUpdateProblem->setValue(
                    DBEProblem::contractCustomerItemID,
                    $datasetContracts->getValue(DBEJContract::customerItemID)
                );
            }
        }
        switch ($requester->getValue(DBEJUser::teamLevel)) {
            case 1:
                $teamField = DBEProblem::hdLimitMinutes;
                break;
            case 2:
                $teamField = DBEProblem::esLimitMinutes;
                break;
            case 3:
                $teamField = DBEProblem::smallProjectsTeamLimitMinutes;
                break;
            case 5:
                $teamField = DBEProblem::projectTeamLimitMinutes;
                break;
            default:
                $teamField = null;
        }
        if ($teamField) {
            $toUpdateProblem->setValue(
                $teamField,
                $toUpdateProblem->getValue($teamField) + $request->timeBudgetMinutes()->value()
            );
        }
        if ($toUpdateProblem->getValue(DBEProblem::queueNo) === 3) {
            $buHeader = new BUHeader($this);
            $dsHeader = new DataSet($this);
            $buHeader->getHeader($dsHeader);
            if ($dsHeader->getValue(DBEHeader::holdAllSOSmallProjectsP5sforQAReview)) {
                $toUpdateProblem->setValue(DBEProblem::holdForQA, 1);
            }
        }
        $toUpdateProblem->setValue(DBEProblem::awaitingCustomerResponseFlag, 'N');
        $toUpdateProblem->updateRow();
    }
}
