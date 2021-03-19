<?php

namespace CNCLTD\CommunicationService;

use BUMail;
use CNCLTD\ChargeableWorkCustomerRequest\Core\ChargeableWorkCustomerRequest;
use CNCLTD\TwigDTOs\ChargeableWorkCustomerRequestEmailDTO;
use CNCLTD\TwigDTOs\ChargeableWorkCustomerRequestProcessedEmailDTO;
use DBEContact;
use DBEProblem;

class CommunicationService
{
    /**
     * @var BUMail
     */
    private static $buMailInstance;

    public static function getBUMail()
    {
        if (!self::$buMailInstance) {
            $thing                = null;
            self::$buMailInstance = new BUMail($thing);
        }
        return self::$buMailInstance;
    }

    public static function sendExtraChargeableWorkRequestToContact(ChargeableWorkCustomerRequest $request)
    {
        global $twig;
        $thing      = null;
        $dbeContact = new DBEContact($thing);
        $dbeContact->getRow($request->getRequesteeId()->value());
        $dbeProblem = new DBEProblem($thing);
        $dbeUser    = new \DBEUser($thing);
        $dbeUser->getRow($request->getRequesterId()->value());
        $serviceRequestId = $request->getServiceRequestId()->value();
        $dbeProblem->getRow($serviceRequestId);
        $dbeItem = new \DBEItem($thing);
        $dbeItem->getRow(CONFIG_CONSULTANCY_HOURLY_LABOUR_ITEMID);
        $dto        = new ChargeableWorkCustomerRequestEmailDTO(
            PORTAL_URL . '/notSureWhereToPointThisYet',
            PORTAL_URL . '/notSureWhereToPointThisYet',
            $request->getAdditionalHoursRequested()->value(),
            $dbeContact->getValue(DBEContact::firstName),
            $serviceRequestId,
            $request->getReason()->value(),
            "{$dbeUser->getValue(\DBEUser::firstName)} {$dbeUser->getValue(\DBEUser::lastName)}",
            $dbeItem->getValue(\DBEItem::curUnitSale)
        );
        $body       = $twig->render(
            '@customerFacing/ChargeableWorkCustomerRequestEmail/ChargeableWorkCustomerRequest.html.twig',
            ["data" => $dto]
        );
        $buMail     = self::getBUMail();
        $recipients = $dbeContact->getValue(DBEContact::email);
        $buMail->sendSimpleEmail(
            $body,
            "Approval needed for Service Request {$serviceRequestId} - {$dbeProblem->getValue(DBEProblem::emailSubjectSummary)}",
            $recipients
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
        $requestee = new DBEContact($thing);
        $requestee->getRow($request->getRequesteeId()->value());
        $serviceRequest   = new DBEProblem($thing);
        $serviceRequestId = $request->getServiceRequestId()->value();
        $serviceRequest->getRow($serviceRequestId);
        $requester = new \DBEUser($thing);
        $requester->getRow($request->getRequesterId()->value());
        $urlService = SITE_URL . '/SRActivity.php?action=displayActivity&serviceRequestId=' . $serviceRequestId;
        $dto        = new ChargeableWorkCustomerRequestProcessedEmailDTO(
            "{$requestee->getValue(DBEContact::firstName)} {$requestee->getValue(DBEContact::lastName)}",
            $status,
            $request->getAdditionalHoursRequested()->value(),
            $urlService,
            $serviceRequestId
        );
        $body       = $twig->render(
            '@internal/ChargeableWorkCustomerRequestProcessedEmail.html.twig',
            ["data" => $dto]
        );
        $buMail     = self::getBUMail();
        $recipients = $requester->getEmail();
        $buMail->sendSimpleEmail($body, "Extra work $status", $recipients);
    }
}