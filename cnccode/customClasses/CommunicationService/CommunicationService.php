<?php

namespace CNCLTD\CommunicationService;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\TwigDTOs\ChargeableWorkCustomerRequestEmailDTO;

class CommunicationService
{

    public static function sendExtraChargeableWorkRequestToContact(ChargeableWorkCustomerRequest $request)
    {
        global $twig;
        $thing      = null;
        $dbeContact = new \DBEContact($thing);
        $dbeContact->getRow($request->getRequesteeId()->value());
        $dbeProblem = new \DBEProblem($thing);
        $dbeProblem->getRow($request->getServiceRequestId()->value());
        $dto = new ChargeableWorkCustomerRequestEmailDTO(
            PORTAL_URL . '/notSureWhereToPointThisYet',
            PORTAL_URL . '/notSureWhereToPointThisYet',
            $request->getAdditionalHoursRequested()->value(),
            $dbeContact->getValue(\DBEContact::firstName),
            $request->getServiceRequestId()->value(),
            $dbeProblem->getValue(\DBEProblem::emailSubjectSummary)
        );
        $twig->render('@customerFacing/ChargeableWorkCustomerRequestEmail/ChargeableWorkCustomerRequest.html.twig', ["data" => $dto]);

    }
}