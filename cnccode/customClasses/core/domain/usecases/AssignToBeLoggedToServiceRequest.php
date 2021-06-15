<?php

namespace CNCLTD\core\domain\usecases;

use CNCLTD\Business\BUActivity;
use DBEProblem;
use DBEUser;
use Exception;

class AssignToBeLoggedToServiceRequest
{
    /**
     * @param $toBeLoggedRequestId
     * @param $serviceRequestId
     * @param DBEUser $currentUser
     * @throws Exception
     */
    public function __invoke($toBeLoggedRequestId, $serviceRequestId, DBEUser $currentUser)
    {
        $buActivity             = new BUActivity($this);
        $customerServiceRequest = $this->getCustomerRaisedRequest($buActivity, $toBeLoggedRequestId);
        $dbeProblem             = $this->getServiceRequest($serviceRequestId);
        $this->checkServiceRequestIsValid($dbeProblem);
        $buActivity->addCustomerContactActivityToServiceRequest(
            $dbeProblem,
            $customerServiceRequest['cpr_reason'],
            $currentUser
        );
        $buActivity->deleteCustomerRaisedRequest($toBeLoggedRequestId);
    }

    /**
     * @param $serviceRequestId
     * @return DBEProblem
     * @throws Exception
     */
    private function getServiceRequest($serviceRequestId): DBEProblem
    {
        $dbeProblem = new DBEProblem($this);
        if (!$dbeProblem->getRow($serviceRequestId)) {
            throw new Exception("Service request not found");
        }
        return $dbeProblem;
    }

    /**
     * @param DBEProblem $dbeProblem
     * @throws Exception
     */
    private function checkServiceRequestIsValid(DBEProblem $dbeProblem): void
    {
        if (!(in_array($dbeProblem->getValue(DBEProblem::status), ["I", "P"]))) {
            throw new Exception("Service request must be in status Initial or In Progress");
        }
    }

    /**
     * @param BUActivity $buActivity
     * @param $toBeLoggedRequestId
     * @return array
     * @throws Exception
     */
    private function getCustomerRaisedRequest(BUActivity $buActivity, $toBeLoggedRequestId): array
    {
        $customerServiceRequest = $buActivity->getCustomerRaisedRequest($toBeLoggedRequestId);
        if (!$customerServiceRequest) {
            throw new Exception("Customer raised request not found");
        }
        return $customerServiceRequest;
    }
}