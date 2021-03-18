<?php

namespace CNCLTD\CommunicationService;

use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\TwigDTOs\ChargeableWorkCustomerRequestEmailDTO;
use CNCLTD\TwigDTOs\ChargeableWorkCustomerRequestProcessedEmailDTO;

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
        $twig->render(
            '@customerFacing/ChargeableWorkCustomerRequestEmail/ChargeableWorkCustomerRequest.html.twig',
            ["data" => $dto]
        );

    }

    public static function sendExtraChargeableWorkRequestApprovedEmail(ChargeableWorkCustomerRequest $request)
    {
        self::sendExtraChargeableWorkRequestProcessedEmail($request);
    }

    public static function sendExtraChargeableWorkRequestDeniedEmail(ChargeableWorkCustomerRequest $request)
    {
        self::sendExtraChargeableWorkRequestProcessedEmail($request, 'denied');
    }

    private static function sendExtraChargeableWorkRequestProcessedEmail(ChargeableWorkCustomerRequest $request,
                                                                         $status = "approved"
    )
    {
        global $twig;
        $thing     = null;
        $requestee = new \DBEContact($thing);
        $requestee->getRow($request->getRequesteeId()->value());
        $serviceRequest   = new \DBEProblem($thing);
        $serviceRequestId = $request->getServiceRequestId()->value();
        $serviceRequest->getRow($serviceRequestId);
        $urlService = SITE_URL . '/SRActivity.php?action=displayActivity&serviceRequestId=' . $serviceRequestId;
        $dto        = new ChargeableWorkCustomerRequestProcessedEmailDTO(
            "{$requestee->getValue(\DBEContact::firstName)} {$requestee->getValue(\DBEContact::lastName)}",
            $status,
            $request->getAdditionalHoursRequested()->value(),
            $urlService,
            $serviceRequestId
        );
        $twig->render(
            '@internal/ChargeableWorkCustomerRequestProcessedEmail.html.twig',
            ["data" => $dto]
        );
    }
}