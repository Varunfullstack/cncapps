<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/09/2018
 * Time: 11:57
 */

require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerReviewMeeting.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_bu'] . '/BUContact.inc.php');
require_once($cfg ['path_bu'] . '/BUServiceDeskReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerSrAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerReviewMeetingDocuments extends CTCNC
{
    private $buCustomerReviewMeetingDocuments;

    /**
     * CTCustomerReviewMeetingDocuments constructor.
     * @param $requestMethod
     * @param array $postVars
     * @param array $getVars
     * @param array $cookieVars
     * @param array|bool|int|string $cfg
     */
    public function __construct($requestMethod,
                                array $postVars,
                                array $getVars,
                                array $cookieVars,
                                $cfg
    )
    {

        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            "sales",
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomerReviewMeetingDocuments = new BUCustomerReviewMeetingDocuments ($this);


    }
}