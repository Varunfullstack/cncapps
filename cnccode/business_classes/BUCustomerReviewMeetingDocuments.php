<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/09/2018
 * Time: 11:59
 */

require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerAnalysisReport.inc.php");
require_once($cfg["path_dbe"] . "/DBEContact.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerReviewMeetingDocuments extends Business
{

    /**
     * BUCustomerReviewMeetingDocuments constructor.
     * @param CTCustomerReviewMeetingDocuments $param
     */
    public function __construct(CTCustomerReviewMeetingDocuments $param)
    {
        parent::__construct($owner);
    }
}