<?php

namespace CNCLTD\Controller;

use CNCLTD\Business\BUActivity;
use CNCLTD\ChargeableWorkCustomerRequest\Core\CancelReason;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequestTokenId;
use CNCLTD\ChargeableWorkCustomerRequest\DTO\SDManagerPendingChargeableRequestDTO;
use CNCLTD\ChargeableWorkCustomerRequest\infra\ChargeableWorkCustomerRequestMySQLRepository;
use CNCLTD\ChargeableWorkCustomerRequest\usecases\CancelPendingChargeableWorkCustomerRequest;
use CNCLTD\ChargeableWorkCustomerRequest\usecases\ResendPendingChargeableWorkCustomerRequestEmail;
use CNCLTD\Exceptions\JsonHttpException;
use CTCNC;
use DBEContact;
use Exception;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTPendingChargeableRequests extends CTCNC
{
    const GET_PENDING_CHARGEABLE_REQUESTS         = "getPendingChargeableRequests";
    const CANCEL_PENDING_CHARGEABLE_REQUEST       = "cancelPendingChargeableRequest";
    const RESEND_PENDING_CHARGEABLE_REQUEST_EMAIL = "resendPendingChargeableRequestEmail";

    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_PENDING_CHARGEABLE_REQUESTS:
                echo json_encode($this->getPendingChargeableRequestsController());
                exit;
            case self::CANCEL_PENDING_CHARGEABLE_REQUEST:
                echo json_encode($this->cancelPendingChargeableRequestController());
                exit;
            case self::RESEND_PENDING_CHARGEABLE_REQUEST_EMAIL:
                echo json_encode($this->resendPendingChargeableRequestEmailController());
                exit;
        }
    }

    private function getPendingChargeableRequestsController(): array
    {
        $query = "SELECT
       a.id,
  a.serviceRequestId,
  c.`cus_name` AS customerName,
  CONCAT(
    requestee.`con_first_name`,
    ' ',
    requestee.`con_last_name`
  ) AS requesteeName,
  sr.`emailSubjectSummary`,
  a.reason,
  a.createdAt,
  a.additionalHoursRequested,
  CONCAT(
    requester.`firstName`,
    ' ',
    requester.`lastName`
  ) AS requesterName
FROM
  `chargeableworkcustomerrequest` a
  JOIN problem sr
    ON sr.pro_problemno = a.serviceRequestId
  JOIN customer c
    ON c.`cus_custno` = sr.pro_custno
  JOIN contact requestee
    ON requestee.`con_contno` = a.requesteeId
  JOIN consultant requester
    ON requester.`cns_consno` = a.requesterId  ";
        if ($_REQUEST['hd'] == 'false') {
            $query .= ' and sr.pro_queue_no <> 1 ';
        }
        if ($_REQUEST['es'] == 'false') {
            $query .= ' and sr.pro_queue_no <> 2 ';
        }
        if ($_REQUEST['sp'] == 'false') {
            $query .= ' and sr.pro_queue_no <> 3 ';
        }
        if ($_REQUEST['p'] == 'false') {
            $query .= ' and sr.pro_queue_no <> 5 ';
        }
        $limit = $_REQUEST['limit'];
        $query .= " order by createdAt desc limit " . $limit;
        global $db;
        $statement = $db->preparedQuery($query, []);
        $toReturn  = [];
        while ($item = $statement->fetch_object(SDManagerPendingChargeableRequestDTO::class)) {
            $toReturn[] = $item;
        }
        return ["status" => "ok", "data" => $toReturn];
    }

    /**
     * @return string[]
     * @throws JsonHttpException
     */
    private function cancelPendingChargeableRequestController(): array
    {
        $repo      = new ChargeableWorkCustomerRequestMySQLRepository();
        $usecase   = new CancelPendingChargeableWorkCustomerRequest(
            $repo, new BUActivity(
                     $this
                 )
        );
        $jsonData  = $this->getJSONData();
        $requestId = @$jsonData['id'];
        if (!$requestId) {
            throw new JsonHttpException(400, 'id is required');
        }
        $cancelReason = @$jsonData['cancelReason'];
        if (!$cancelReason) {
            throw new JsonHttpException(400, 'Cancel reason is required');
        }
        try {
            $usecase->__invoke(
                new ChargeableWorkCustomerRequestTokenId($requestId),
                new CancelReason($cancelReason),
                $this->getDbeUser()
            );
        } catch (Exception $exception) {
            throw new JsonHttpException(400, $exception->getMessage());
        }
        return ["status" => "ok"];
    }

    /**
     * @return string[]
     * @throws JsonHttpException
     */
    private function resendPendingChargeableRequestEmailController(): array
    {
        $repo      = new ChargeableWorkCustomerRequestMySQLRepository();
        $usecase   = new ResendPendingChargeableWorkCustomerRequestEmail(
            $repo, new BUActivity($this), new DBEContact($this)
        );
        $jsonData  = $this->getJSONData();
        $requestId = @$jsonData['id'];
        if (!$requestId) {
            throw new JsonHttpException(400, 'id is required');
        }
        try {
            $usecase(new ChargeableWorkCustomerRequestTokenId($requestId));
        } catch (Exception $exception) {
            throw new JsonHttpException(400, $exception->getMessage());
        }
        return ["status" => "ok"];
    }

}