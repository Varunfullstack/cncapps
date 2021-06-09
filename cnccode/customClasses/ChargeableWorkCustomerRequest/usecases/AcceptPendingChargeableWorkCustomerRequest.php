<?php

namespace CNCLTD\ChargeableWorkCustomerRequest\usecases;

use BUCustomer;
use BUCustomerItem;
use BUHeader;
use BUSalesOrder;
use CNCLTD\Business\BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestRepository;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\CommunicationService\CommunicationService;
use CNCLTD\Data\DBEJProblem;
use CNCLTD\Exceptions\ChargeableWorkCustomerRequestNotFoundException;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CNCLTD\Exceptions\ServiceRequestNotFoundException;
use DataSet;
use DateTimeImmutable;
use DateTimeInterface;
use DBEContact;
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

class AcceptPendingChargeableWorkCustomerRequest
{
    /**
     * @var ChargeableWorkCustomerRequestRepository
     */
    private $repository;
    private $requestee;
    private $requester;

    /**
     * ApprovePendingChargeableWorkCustomerRequest constructor.
     * @param ChargeableWorkCustomerRequestRepository $repository
     */
    public function __construct(ChargeableWorkCustomerRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @param string|null $comments
     * @throws ChargeableWorkCustomerRequestNotFoundException
     * @throws ServiceRequestNotFoundException|ColumnOutOfRangeException
     */
    public function __invoke(ChargeableWorkCustomerRequestTokenId $id, ?string $comments)
    {
        $request             = $this->getRequest($id);
        $serviceRequest      = $this->getServiceRequest($request);
        $requestApprovedAt   = new DateTimeImmutable();
        $buCustomer          = new BUCustomer($this);
        $hasPrepay           = $buCustomer->hasPrepayContract($serviceRequest->getValue(DBEProblem::customerID));
        $hasLinkedSalesOrder = (bool)$serviceRequest->getValue(DBEJProblem::linkedSalesOrderID);
        $this->logCustomerContactActivity($request, $requestApprovedAt, $serviceRequest, $comments, $hasPrepay);
        if (!$hasPrepay || $hasLinkedSalesOrder) {
            $this->createOrUpdateSalesOrder($serviceRequest, $request);
        }
        $this->updateServiceRequest($serviceRequest, $request, $hasPrepay, $hasLinkedSalesOrder);
        $this->sendEmailToEngineer($request);
        $this->deleteChargeableRequest($request);
    }

    /**
     * @param ChargeableWorkCustomerRequestTokenId $id
     * @return ChargeableWorkCustomerRequest
     * @throws ChargeableWorkCustomerRequestNotFoundException
     */
    private function getRequest(ChargeableWorkCustomerRequestTokenId $id): ChargeableWorkCustomerRequest
    {
        $request = $this->repository->getById($id);
        if (!$request) {
            throw new ChargeableWorkCustomerRequestNotFoundException();
        }
        return $request;
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @return DBEJProblem
     * @throws ServiceRequestNotFoundException
     */
    private function getServiceRequest(ChargeableWorkCustomerRequest $request): DBEJProblem
    {
        $dbeProblem       = new DBEJProblem($this);
        $serviceRequestId = $request->getServiceRequestId()->value();
        if (!$dbeProblem->getRow($serviceRequestId)) {
            throw new ServiceRequestNotFoundException();
        }
        return $dbeProblem;
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @return DBEContact
     */
    private function getRequestee(ChargeableWorkCustomerRequest $request): DBEContact
    {
        if (!$this->requestee) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($request->getRequesteeId()->value());
            $this->requestee = $dbeContact;
        }
        return $this->requestee;
    }

    /**
     * @param ChargeableWorkCustomerRequest $request
     * @param DateTimeInterface|null $requestApprovedAt
     * @param DBEJProblem $serviceRequest
     * @param string|null $comments
     * @param bool $hasPrepay
     * @throws ColumnOutOfRangeException
     * @throws \Exception
     */
    private function logCustomerContactActivity(ChargeableWorkCustomerRequest $request,
                                                ?DateTimeInterface $requestApprovedAt,
                                                DBEJProblem $serviceRequest,
                                                ?string $comments,
                                                bool $hasPrepay
    ): void
    {
        $requestee   = $this->getRequestee($request);
        $buActivity  = new BUActivity($this);
        $description = "<p>{$requestee->getValue(DBEContact::firstName)} {$requestee->getValue(DBEContact::lastName)} approved the request for {$request->getAdditionalHoursRequested()->value()} hours at {$requestApprovedAt->format('d/m/Y H:i:s')}</p>";
        if ($comments) {
            $description .= "<p>$comments</p>";
        }
        if (!$hasPrepay) {
            $description .= "<p>Priority Changed from {$serviceRequest->getValue(DBEProblem::priority)} to 5</p>";
        }
        $requester = $this->getRequester($request);
        $buActivity->addCustomerContactActivityToServiceRequest($serviceRequest, $description, $requester);
    }

    /**
     * @param DBEJProblem $DBEJProblem
     * @param ChargeableWorkCustomerRequest $request
     * @return void
     * @throws ColumnOutOfRangeException
     */
    private function createOrUpdateSalesOrder(DBEJProblem $DBEJProblem, ChargeableWorkCustomerRequest $request): void
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
            $this->insertItemLine($ordheadID, $customerID, $request);
            return;
        }
        $buSalesOrders->getOrderByOrdheadID($salesOrderId, $dsOrdline, $dsOrdline);
        $labourLine = $this->getLabourLine($dsOrdline);
        if (!$labourLine) {
            $this->insertItemLine($salesOrderId, $customerID, $request);
        } else {
            $this->updateItemLine($labourLine, $request);
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
     * @param ChargeableWorkCustomerRequest $request
     * @throws ColumnOutOfRangeException
     */
    private function insertItemLine($ordheadID,
                                    $customerID,
                                    ChargeableWorkCustomerRequest $request
    ): void
    {
        $dbeOrdline = $this->getNewLine($ordheadID, $customerID);
        $dbeItem    = new DBEItem($this);
        $dbeItem->getRow(CONFIG_CONSULTANCY_HOURLY_LABOUR_ITEMID);
        $amount = $request->getAdditionalHoursRequested()->value();
        $dbeOrdline->setValue(DBEOrdline::lineType, 'I');
        $dbeOrdline->setValue(DBEOrdline::itemID, $dbeItem->getValue(DBEItem::itemID));
        $dbeOrdline->setValue(DBEOrdline::stockcat, $dbeItem->getValue(DBEItem::stockcat));
        $dbeOrdline->setValue(DBEOrdline::sequenceNo, $dbeOrdline->getNextSortOrder());
        $dbeOrdline->setValue(DBEOrdline::qtyOrdered, $amount);
        $dbeOrdline->setValue(DBEOrdline::curUnitCost, $dbeItem->getValue(DBEItem::curUnitCost));
        $dbeOrdline->setValue(DBEOrdline::curTotalCost, $dbeItem->getValue(DBEItem::curUnitCost) * $amount);
        $dbeOrdline->setValue(DBEOrdline::curUnitSale, $dbeItem->getValue(DBEItem::curUnitSale));
        $dbeOrdline->setValue(DBEOrdline::curTotalSale, $dbeItem->getValue(DBEItem::curUnitSale) * $amount);
        $dbeOrdline->setValue(DBEOrdline::description, $dbeItem->getValue(DBEItem::description));
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
    private function updateItemLine(DBEOrdline $labourLine, ChargeableWorkCustomerRequest $request)
    {
        $quantity = $labourLine->getValue(DBEOrdline::qtyOrdered) + $request->getAdditionalHoursRequested()->value();
        $labourLine->setValue(DBEOrdline::qtyOrdered, $quantity);
        $cost = $labourLine->getValue(DBEOrdline::curUnitCost);
        $labourLine->setValue(DBEOrdline::curTotalCost, $cost * $quantity);
        $sale = $labourLine->getValue(DBEOrdline::curUnitSale);
        $labourLine->setValue(DBEOrdline::curTotalSale, $sale * $quantity);
        $labourLine->updateRow();
    }

    /**
     * @param DataSet $dsOrdline
     * @return DBEOrdline|null
     * @throws ColumnOutOfRangeException
     */
    private function getLabourLine(DataSet $dsOrdline): ?DBEOrdline
    {
        while ($dsOrdline->fetchNext()) {
            if ($dsOrdline->getValue(DBEOrdline::itemID) === CONFIG_CONSULTANCY_HOURLY_LABOUR_ITEMID) {
                $dbeOrdline = new DBEOrdline($this);
                $dbeOrdline->getRow($dsOrdline->getValue(DBEOrdline::id));
                return $dbeOrdline;
            }
        }
        return null;
    }

    /**
     * @param DBEJProblem $dbeProblem
     * @param ChargeableWorkCustomerRequest $request
     * @param bool $hasPrepay
     * @param $hasLinkedSalesOrder
     * @throws ColumnOutOfRangeException
     */
    private function updateServiceRequest(DBEJProblem $dbeProblem,
                                          ChargeableWorkCustomerRequest $request,
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
        $requesterId = $request->getRequesterId()->value();
        $dbeUser     = new DBEJUser($this);
        $dbeUser->setValue(DBEUser::userID, $requesterId);
        $dbeUser->getRow();
        $dbeUser->getValue(DBEJUser::teamLevel);
        switch ($dbeUser->getValue(DBEJUser::teamLevel)) {
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
                $toUpdateProblem->getValue($teamField) + ($request->getAdditionalHoursRequested()->value() * 60)
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

    private function getRequester(ChargeableWorkCustomerRequest $request): DBEUser
    {
        if (!$this->requester) {
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($request->getRequesterId()->value());
            $this->requester = $dbeUser;
        }
        return $this->requester;
    }

    private function sendEmailToEngineer(ChargeableWorkCustomerRequest $request)
    {
        CommunicationService::sendExtraChargeableWorkRequestAcceptedEmail($request);
    }

    private function deleteChargeableRequest(ChargeableWorkCustomerRequest $request)
    {
        $this->repository->delete($request);
    }
}
