<?php
/** @noinspection HtmlDeprecatedAttribute */

/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\AutomatedRequest;

require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJContract.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg ["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg ["path_dbe"] . "/DBEItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBEItemType.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJProblem.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActivitySearch.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallDocument.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActType.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActType.php");
require_once($cfg ["path_dbe"] . "/DBEProject.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg ["path_bu"] . "/BUSite.inc.php");
require_once($cfg ["path_bu"] . "/BUHeader.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_bu"] . "/BURenContract.inc.php");
require_once($cfg ["path_bu"] . "/BUContact.inc.php");
require_once($cfg ["path_bu"] . "/BUProblemSLA.inc.php");
require_once($cfg ["path_func"] . "/activity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBESite.inc.php");
require_once($cfg ["path_dbe"] . "/DBEUtilityEmail.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_bu"] . "/BUStandardText.inc.php");
require_once($cfg["path_dbe"] . "/DBEJPorhead.inc.php");
require_once($cfg["path_ct"] . "/CTProject.inc.php");

define(
    'BUACTIVITY_RESOLVED',
    9
);

class BUActivity extends Business
{

    const hour = 3600;          // hour in seconds
    const halfHour = 1800;      // half-hour in seconds
    const day = 43200;     // one day in seconds
    const quarterHour = 900;
    const InitialCustomerEmailCategory = 'initialCustomerEmailCategory';
    const WorkStartedCustomerEmailCategory = 'workStartedCustomerEmailCategory';
    const WorkUpdatesCustomerEmailCategory = 'workUpdatesCustomerEmailCategory';
    const FixedCustomerEmailCategory = 'completedCustomerEmailCategory';
    const PendingClosureCustomerEmailCategory = 'autoCloseCustomerEmailCategory';
    const ClosureCustomerEmailCategory = 'closureCustomerEmailCategory';
    const WorkUpdatesPriorityChanged = 'workUpdatesPriorityChanged';
    const WorkUpdatesActivityLogged = 'workUpdatesActivityLogged';
    const Initial24HSupport = 'initial24HSupport';
    const InitialNot24HSupport = 'InitialNot24HSupport';
    const Fixed = 'completedFixed';
    const FixedReopen = 'completedReopen';

    const searchFormStatus = 'status';
    const searchFormCustomerID = 'customerID';
    const searchFormUserID = 'userID';
    const searchFormRootCauseID = 'rootCauseID';
    const searchFormContractCustomerItemID = 'contractCustomerItemID';
    const searchFormPriority = 'priority';
    const searchFormCustomerName = 'customerName';
    const searchFormActivityText = 'activityText';
    const searchFormServiceRequestSpentTime = 'serviceRequestSpentTime';
    const searchFormIndividualActivitySpentTime = 'individualActivitySpentTime';
    const searchFormFromDate = 'fromDate';
    const searchFormToDate = 'toDate';
    const searchFormCallActivityID = 'callActivityID';
    const searchFormProblemID = 'problemID';
    const searchFormCallActTypeID = 'callActTypeID';
    const searchFormContractType = 'contractType';
    const searchFormLinkedSalesOrderID = 'linkedSalesOrderID';
    const searchFormManagementReviewOnly = 'managementReviewOnly';
    const searchFormBreachedSlaOption = 'breachedSlaOption';

    const customerActivityMonthFormCustomerID = 'customerID';
    const customerActivityMonthFormFromDate = 'fromDate';
    const customerActivityMonthFormToDate = 'toDate';


    const exportDataSetEndDate = 'endDate';
    const exportDataSetPreviewRun = 'previewRun';

    const exportPrePayActivitiesFormCustomerName = 'customerName';
    const exportPrePayActivitiesFormPreviousBalance = 'previousBalance';
    const exportPrePayActivitiesFormCurrentBalance = 'currentBalance';
    const exportPrePayActivitiesFormExpiryDate = 'expiryDate';
    const exportPrePayActivitiesFormTopUp = 'topUp';
    const exportPrePayActivitiesFormContacts = 'contacts';
    const exportPrePayActivitiesFormContractType = 'contractType';
    const exportPrePayActivitiesFormWebFileLink = 'webFileLink';

    const customerActivityFormCustomerID = 'customerID';
    const customerActivityFormUserID = 'userID';
    const customerActivityFormContractType = 'contractType';
    const customerActivityFormCustomerName = 'customerName';
    const customerActivityFormFromDate = 'fromDate';
    const customerActivityFormToDate = 'toDate';


    /** @var Template */
    public $template;
    var $csvSummaryFileHandle;
    var $totalCost = 0;
    var $loggedInEmail = null;
    var $loggedInUserID = null;
    var $standardVatRate = 0;
    /**
     *
     * @var DBEJCallActivity
     */
    public $dbeJCallActivity = null;
    public $priorityArray;
    public $problemStatusArray =
        array(
            "I" => "Initial",
            "P" => "In Progress",
            "F" => "Fixed",
            "C" => "Confirmed Completed"
        );
    public $breachedSlaOptionArray =
        array(
            "B" => "SLA Breached",
            "N" => "SLA Met"
        );
    public $awaitingCustomerArray =
        array(
            "Y" => "Customer",
            "N" => "CNC"
        );
    public $workQueueDescriptionArray =
        array(
            "1" => "Helpdesk",
            "2" => "Escalations",
            "3" => "Implementations",
            "4" => "Sales",
            "5" => "Managers",
            "6" => "Fixed",
            "7" => "Future"
        );
    public $allocatedMinutesArray =
        array(
            15 => 15,
            30 => 30,
            45 => 45,
            60 => 60
        );
    /**
     *
     * @var DBEProblem
     */
    private $dbeProblem = null;
    /**
     *
     * @var DBEUser
     */
    private $dbeUser = null;
    /**
     *
     * @var DBECallActivitySearch
     */
    private $dbeCallActivitySearch = null;
    /** @var DataSet */
    public $dsHeader;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);

        $this->dbeJCallActivity = new DBEJCallActivity($this);
        $this->dbeCallActivitySearch = new DBECallActivitySearch($this);
        $this->dbeUser = new DBEUser($this);
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($this->dsHeader);
        $this->priorityArray =
            array(
                1 => $this->dsHeader->getValue(DBEHeader::priority1Desc),
                2 => $this->dsHeader->getValue(DBEHeader::priority2Desc),
                3 => $this->dsHeader->getValue(DBEHeader::priority3Desc),
                4 => $this->dsHeader->getValue(DBEHeader::priority4Desc),
                5 => $this->dsHeader->getValue(DBEHeader::priority5Desc)
            );

        if (isset($GLOBALS ['auth'])) {
            $this->loggedInUserID = $GLOBALS ['auth']->is_authenticated();
        } else {
            $this->loggedInUserID = USER_SYSTEM;
        }
        $this->dbeUser->getRow($this->loggedInUserID);
        $this->loggedInEmail = $this->dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;
    }

    /**
     * Initialise search form
     *
     * @param DSForm $dsData
     */
    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::searchFormStatus,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormUserID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormRootCauseID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormContractCustomerItemID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormPriority,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCustomerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormActivityText,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormServiceRequestSpentTime,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormIndividualActivitySpentTime,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCallActivityID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormProblemID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCallActTypeID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormContractType,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormLinkedSalesOrderID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormManagementReviewOnly,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormBreachedSlaOption,
            DA_STRING,
            DA_ALLOW_NULL
        );


        $dsData->setValue(
            self::searchFormCustomerID,
            null
        );
        $dsData->setValue(
            self::searchFormUserID,
            null
        );
        $dsData->setValue(
            self::searchFormContractType,
            null
        );
        $dsData->setValue(
            self::searchFormRootCauseID,
            null
        );
        $dsData->setValue(
            self::searchFormContractCustomerItemID,
            '99'
        ); // all(blank is used for T&M)
        $dsData->setValue(
            self::searchFormPriority,
            null
        );
        $dsData->setValue(
            self::searchFormCustomerName,
            null
        );
        $dsData->setValue(
            self::searchFormStatus,
            'U'
        );
        $dsData->setValue(
            self::searchFormCallActTypeID,
            null
        );
        $dsData->setValue(
            self::searchFormLinkedSalesOrderID,
            null
        );
        $dsData->setValue(
            self::searchFormManagementReviewOnly,
            'N'
        );
        $dsData->setValue(
            self::searchFormBreachedSlaOption,
            null
        );
    } // end sendServiceReallocatedEmail

    function initialiseCustomerActivityMonthForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::customerActivityMonthFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::customerActivityMonthFormFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::customerActivityMonthFormToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::customerActivityMonthFormCustomerID,
            null
        );
    } // end sendSalesRequestAlertEmail

    /**
     * @param DataSet $dsSearchForm
     * @param DataSet $dsResults
     * @param bool $sortColumn
     * @param bool $sortDirection
     * @param bool $limit
     */
    function search(
        &$dsSearchForm,
        &$dsResults,
        $sortColumn = false,
        $sortDirection = false,
        $limit = true
    )
    {
        $this->dbeCallActivitySearch->getRowsBySearchCriteria(
            trim($dsSearchForm->getValue(self::searchFormCallActivityID)),
            trim($dsSearchForm->getValue(self::searchFormProblemID)),
            trim($dsSearchForm->getValue(self::searchFormCustomerID)),
            trim($dsSearchForm->getValue(self::searchFormUserID)),
            trim($dsSearchForm->getValue(self::searchFormStatus)),
            trim($dsSearchForm->getValue(self::searchFormRootCauseID)),
            trim($dsSearchForm->getValue(self::searchFormPriority)),
            trim($dsSearchForm->getValue(self::searchFormActivityText)),
            trim($dsSearchForm->getValue(self::searchFormServiceRequestSpentTime)),
            trim($dsSearchForm->getValue(self::searchFormIndividualActivitySpentTime)),
            trim($dsSearchForm->getValue(self::searchFormFromDate)),
            trim($dsSearchForm->getValue(self::searchFormToDate)),
            trim($dsSearchForm->getValue(self::searchFormContractCustomerItemID)),
            trim($dsSearchForm->getValue(self::searchFormCallActTypeID)),
            trim($dsSearchForm->getValue(self::searchFormLinkedSalesOrderID)),
            trim($dsSearchForm->getValue(self::searchFormManagementReviewOnly)),
            trim($dsSearchForm->getValue(self::searchFormBreachedSlaOption)),
            $sortColumn,
            $sortDirection,
            $limit
        );
        $this->dbeCallActivitySearch->fetchNext();

        $dsResults->replicate($this->dbeCallActivitySearch); // into a dataset for return
    } // end sendServiceRemovedEmail

    /*
  Email to GL when a new activity is created that has same description/customerID
  as initial activity of a recently removed Request
  */

    function sendEmailToSales($callActivityID,
                              $message
    )
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($GLOBALS['auth']->is_authenticated());

        $this->sendPartsUsedEmail(
            $callActivityID,
            $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(DBEUser::lastName),
            date(DATE_MYSQL_DATE),
            $message
        );

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeCallActivity->getValue(DBECallActivity::problemID));
        $dbeProblem->setValue(
            DBEJProblem::internalNotes,
            $dbeProblem->getValue(DBEProblem::internalNotes) . '<BR/><BR/><STRONG>' .
            'Parts Used on ' . date('d/m/Y H:i') . ' from  ' .
            $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(DBEUser::lastName) .
            '</STRONG><BR/><BR/>' .
            $message
        );

        $dbeProblem->updateRow();
    }

    /**
     * Send an email alert to the internal email address against given further action type
     * @param $callActivityID
     * @param $engineerName
     * @param $dateCreated
     * @param bool $emailBody
     */
    function sendPartsUsedEmail(
        $callActivityID,
        $engineerName,
        $dateCreated,
        $emailBody = false
    )
    {
        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivityID);
        $emailBody = str_replace(
            "\r\n",
            "<BR/>",
            $emailBody
        );

        $template = new Template(EMAIL_TEMPLATE_DIR);

        $template->setFile(
            'internalFurtherActionEmail',
            'InternalFurtherActionEmail.html'
        );
        $content = "";
        if (!$emailBody) { // if there is a body then don't display activity details
            $content .= 'REASON:' . "<BR/><BR/>";
            $content .= $dbeJCallActivity->getValue(DBECallActivity::reason) . "<BR/><BR/>";
            if ($dbeJCallActivity->getValue(DBECallActivity::internalNotes)) {
                $content .= 'NOTES:' . "<BR/><BR/>";
                $content .= $dbeJCallActivity->getValue(DBECallActivity::internalNotes) . "<BR/><BR/>";
            }
        } else {
            $content = $emailBody . "<BR/><BR/>";
        }


        $activityURL = "http://" . $_SERVER['HTTP_HOST'] . Controller::formatForHTML(
                '/Activity.php?action=displayActivity&callActivityID=' . $callActivityID,
                1
            );

        $subject = 'Parts used for ' . $dbeJCallActivity->getValue(
                DBEJCallActivity::customerName
            ) . ' against activity ' . $callActivityID . ' today(' . date('d/m/Y') . ')';

        $template->setVar(
            [
                "content"      => $content,
                "engineerName" => $engineerName,
                "dateCreated"  => $dateCreated,
                "activityURL"  => $activityURL,
            ]
        );

        $template->parse(
            'OUTPUT',
            "internalFurtherActionEmail"
        );

        $body = $template->getVar('OUTPUT');

        $emailTo = CONFIG_SALES_EMAIL;

        $hdrs = array(
            'From'         => CONFIG_SUPPORT_EMAIL,
            'To'           => $emailTo,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $mime = new Mail_mime();

        $mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $mime->get($mime_params);

        $hdrs = $mime->headers($hdrs);

        $buMail = new BUMail($this);

        $buMail->putInQueue(
            CONFIG_SUPPORT_EMAIL,
            $emailTo,
            $hdrs,
            $body
        );
    } // end sendSpecialAttentionEmail

    /**
     * @param $customerID
     * @param $userID
     * @param DataSet $dsCallActivity
     */
    function initialiseCallActivity($customerID,
                                    $userID,
                                    &$dsCallActivity
    )
    {
        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $customerID,
            $dsCustomer
        );
        $buSite = new BUSite($this);
        $dsSite = new DataSet($this);
        $buSite->getSiteByID(
            $customerID,
            $dsCustomer->getValue(DBECustomer::deliverSiteNo),
            $dsSite
        );
        $dsCallActivity->setUpdateModeInsert();
        $dsCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            0
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::customerID,
            $customerID
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $dsCustomer->getValue(DBECustomer::deliverSiteNo)
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::contactID,
            $dsSite->getValue(DBESite::invoiceContactID)
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::userID,
            $userID
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            null
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::endTime,
            null
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::status,
            'O'
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::reason,
            null
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::siteDesc,
            $dsSite->getValue(DBESite::add1) .
            ' ' . $dsSite->getValue(DBESite::add2) .
            ' ' .
            $dsSite->getValue(DBESite::town)
        );
        $dsCallActivity->post();
    }

    /**
     *
     * Set the activity type to customer contact
     *
     * @param mixed $callActivityID
     */
    function setWorkNotCarriedOut($callActivityID)
    {

        $dbeCallActivity = new DBECallActivity($this);

        $dbeCallActivity->getRow($callActivityID);

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID
        );

        $dbeCallActivity->updateRow();

    } // end sendFutureVisitEmail

    /**
     * Sends email to GL when request closed early by technician
     *
     * @param mixed $problemID
     */
    function sendRequestCompletedEarlyEmail(
        $problemID
    )
    {
        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $toEmail = false; // Sd managers only

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'RequestCompletedEarlyEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $template->setVar(
            array(
                'activityRef'  => $activityRef,
                'reason'       => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'engineerName' => $dbeJProblem->getValue(DBEJProblem::engineerName),
                'customerName' => $dbeJProblem->getValue(DBEJProblem::customerName),
                'urlActivity'  => $urlActivity,
                'CONFIG_SERVICE_REQUEST_DESC'
                               => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => CONFIG_SERVICE_REQUEST_DESC . ' ' . $activityRef . ' - Completed Early',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    function getFirstActivityInProblem($problemID)
    {

        $dbeCallActivity = new DBEJCallActivity($this);

        $dbeCallActivity->getRowsByProblemID(
            $problemID,
            false
        );

        if ($dbeCallActivity->fetchNext()) {

            return $dbeCallActivity;

        } else {

            return false;

        }
    }

    function escalateProblemByCallActivityID($callActivityID)
    {
        $dsCallActivity = new DataSet($this);
        $this->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $this->escalateProblemByProblemID($dsCallActivity->getValue(DBEJCallActivity::problemID));
    }

    /**
     * @param $callActivityID
     * @param DataSet $dsResults
     * @return bool
     */
    function getActivityByID($callActivityID,
                             &$dsResults
    )
    {
        $this->dbeJCallActivity->setPKValue($callActivityID);
        $this->dbeJCallActivity->getRow();

        return ($this->getData(
            $this->dbeJCallActivity,
            $dsResults
        ));
    }

    function escalateProblemByProblemID($problemID,
                                        $newQueueNo = null
    )
    {

        $dbeProblem = new DBEProblem(
            $this,
            $problemID
        );

        $oldQueueNo = $dbeProblem->getValue(DBEJProblem::queueNo);

        if (!$newQueueNo) {
            $newQueueNo = $dbeProblem->getValue(DBEJProblem::queueNo) + 1;
        }

        if ($newQueueNo < $oldQueueNo) {
            return $this->deEscalateProblemByProblemID(
                $problemID,
                $newQueueNo
            );
        }

        if ($oldQueueNo < 5) {


            $dbeProblem->setValue(
                DBEJProblem::queueNo,
                $newQueueNo
            );

            $dbeProblem->setValue(
                DBEJProblem::userID,
                null
            );

            $dbeProblem->setValue(
                DBEJProblem::escalatedUserID,
                $this->loggedInUserID
            );

            $dbeProblem->setValue(
                DBEJProblem::awaitingCustomerResponseFlag,
                'N'
            );

            $dbeProblem->updateRow();

            $this->logOperationalActivity(
                $problemID,
                'Escalated from ' . $this->workQueueDescriptionArray[$oldQueueNo] . ' to ' . $this->workQueueDescriptionArray[$newQueueNo]
            );
        }
        return true;
    }

    function deEscalateProblemByProblemID($problemID,
                                          $newQueueNo = null
    )
    {

        $dbeProblem = new DBEProblem(
            $this,
            $problemID
        );

        $oldQueueNo = $dbeProblem->getValue(DBEJProblem::queueNo);

        if (!$newQueueNo) {
            $newQueueNo = $oldQueueNo - 1;
        }

        if ($newQueueNo > $oldQueueNo) {
            return $this->escalateProblemByProblemID(
                $problemID,
                $newQueueNo
            );
        }

        if ($oldQueueNo > 1) {


            $dbeProblem->setValue(
                DBEJProblem::queueNo,
                $newQueueNo
            );

            $dbeProblem->setValue(
                DBEJProblem::userID,
                null
            );
            $dbeProblem->setValue(
                DBEJProblem::awaitingCustomerResponseFlag,
                'N'
            );

            $dbeProblem->updateRow();

            $this->logOperationalActivity(
                $problemID,
                'Deescalated from ' . $this->workQueueDescriptionArray[$oldQueueNo] . ' to ' . $this->workQueueDescriptionArray[$newQueueNo]
            );

        }
        return true;
    } // end sendNotifyEscalatorUserEmail

    /**
     * Create an operational activity using passed description
     *
     * @param $problemID
     * @param mixed $description
     */
    function logOperationalActivity($problemID,
                                    $description
    )
    {
        $lastActivity = $this->getLastActivityInProblem($problemID);

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($lastActivity->getValue(DBEJCallActivity::callActivityID));
        $dbeCallActivity->setPKValue(null);
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $this->loggedInUserID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $description
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            'N'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );              // Checked

        $dbeCallActivity->insertRow();
    }

    function getLastActivityInProblem($problemID)
    {

        $dbeCallActivity = new DBEJCallActivity($this);

        $dbeCallActivity->getRowsByProblemID(
            $problemID,
            false,
            true,
            true
        ); // 3rd param= descending date

        if ($dbeCallActivity->fetchNext()) {

            return $dbeCallActivity;

        } else {

            return false;

        }
    } // end sendUpdatedByAnotherUserEmail

    /**
     * reopen problem that has previously been fixed
     *
     * @param mixed problemID
     * @throws Exception
     */
    function reopenProblem($problemID)
    {

        $dbeProblem = new DBEProblem(
            $this,
            $problemID
        );

        $dbeProblem->setValue(
            DBEJProblem::status,
            'P'
        );                                     // in progress
        if ($dbeProblem->getValue(DBEJProblem::fixedUserID) != USER_SYSTEM) {
            $dbeProblem->setValue(
                DBEJProblem::userID,
                $dbeProblem->getValue(DBEJProblem::fixedUserID)
            ); // reallocate


            $dbeUser = new DBEJUser($this);
            $dbeUser->setValue(
                DBEJUser::userID,
                $dbeProblem->getValue(DBEJProblem::fixedUserID)
            );
            $dbeUser->getRow();

            $teamID = $dbeUser->getValue(DBEJUser::teamID);

            switch ($teamID) {
                case 1:

                    if ($dbeProblem->getValue(DBEProblem::hdLimitMinutes) <= 0) {
                        $dbeProblem->setValue(
                            DBEProblem::hdLimitMinutes,
                            5
                        );
                    }

                    break;
                case 2:
                    if ($dbeProblem->getValue(DBEProblem::esLimitMinutes) <= 0) {
                        $dbeProblem->setValue(
                            DBEProblem::esLimitMinutes,
                            5
                        );
                    }
                    break;
                case 4:
                    if ($dbeProblem->getValue(DBEProblem::imLimitMinutes) <= 0) {
                        $dbeProblem->setValue(
                            DBEProblem::imLimitMinutes,
                            5
                        );
                    }
                    break;
            }

        }


        $dbeProblem->updateRow();

        $this->sendEmailToCustomer(
            $problemID,
            self::FixedCustomerEmailCategory
        );

        $this->logOperationalActivity(
            $problemID,
            'Reopened'
        );

    }


    /**
     * Sends email to client when a service request it's priority changed
     *
     * @param $problemID
     * @param $category
     * @param $subCategory
     * @param null $callActivityID
     * @throws Exception
     */
    function sendEmailToCustomer(
        $problemID,
        $category,
        $subCategory = null,
        $callActivityID = null
    )
    {
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $dbeFirstActivity = $this->getFirstActivityInProblem($problemID);
        $dbeLastActivity = $this->getLastActivityInProblem($problemID);

        $dbeCallActType = new DBECallActType($this);

        if ($dbeLastActivity) {
            $dbeCallActType->getRow($dbeLastActivity->getValue(DBEJCallActivity::callActTypeID));
        } else {
            $dbeCallActType->getRow($dbeFirstActivity->getValue(DBEJCallActivity::callActTypeID));
        }

        if (
            $dbeJProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'Y' ||
            $dbeFirstActivity->getValue(DBEJCallActivity::serverGuard) == 'Y' ||
            $dbeLastActivity->getValue(DBECallActivity::hideFromCustomerFlag) == 'Y'
        ) {
            return; // no email to customer for this request
        }


        $templateName = null;
        $subjectSuffix = null;

        $contactID = $dbeLastActivity->getValue(DBECallActivity::contactID);

        $contact = new DBEContact($this);
        $contact->getRow($contactID);

        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($dbeJProblem->getValue(DBEJProblem::customerID));

        $buCustomer = new BUCustomer($this);
        $mainSupportContacts = $buCustomer->getMainSupportContacts(
            $dbeJProblem->getValue(DBEJProblem::customerID),
            $contact->getValue(DBEContact::supportLevel) === DBEContact::supportLevelDelegate
        );

        $emails = "";
        $fields = [];
        if ($dbeJProblem->getValue(DBEJProblem::rootCauseID)) {
            $dbeRootCause = new DBERootCause($this);
            $dbeRootCause->getRow($dbeJProblem->getValue(DBEJProblem::rootCauseID));
            $rootCause = $dbeRootCause->getValue(DBERootCause::description);
        } else {
            $rootCause = 'Unknown';
        }
        $selfFlagName = null;
        $othersFlagName = null;
        switch ($category) {
            case  self::InitialCustomerEmailCategory:

                // the last activity should be an initial
                if (
                    $dbeFirstActivity->getValue(DBECallActivity::callActTypeID) !=
                    $dbeLastActivity->getValue(
                        DBECallActivity::callActTypeID
                    )
                ) {
                    throw new Exception('Flagged as initial, but the last and first activities do not match');
                }
                $selfFlagName = DBEContact::initialLoggingEmailFlag;
                $othersFlagName = DBEContact::othersInitialLoggingEmailFlag;

                if ($dbeJProblem->getValue(DBEJProblem::priority) == 5) {
                    $fields['submittedTo'] = 'Project Team';
                } else {
                    $fields['submittedTo'] = 'Service Desk';
                }

                $templateName = 'ServiceLoggedEmail';
                $subjectSuffix = 'New Request Logged';

                break;
            case self::WorkStartedCustomerEmailCategory:
                $selfFlagName = DBEContact::workStartedEmailFlag;
                $othersFlagName = DBEContact::othersWorkStartedEmailFlag;

                $templateName = 'WorkCommencedEmail';
                $subjectSuffix = 'Work Started';
                break;
            case self::WorkUpdatesCustomerEmailCategory:
                $selfFlagName = DBEContact::workUpdatesEmailFlag;
                $othersFlagName = DBEContact::othersWorkUpdatesEmailFlag;
                break;
            case self::FixedCustomerEmailCategory:
                $selfFlagName = DBEContact::fixedEmailFlag;
                $othersFlagName = DBEContact::othersFixedEmailFlag;
                break;
            case self::PendingClosureCustomerEmailCategory:
                $selfFlagName = DBEContact::pendingClosureEmailFlag;
                $othersFlagName = DBEContact::othersPendingClosureEmailFlag;

                $fixedUserID = $dbeJProblem->getValue(DBEJProblem::fixedUserID);
                $dbeFixedUser = new DBEUser($this);
                $dbeFixedUser->getRow($fixedUserID);

                $templateName = 'ServiceCompletionAlertEmail';

                $completeDate = $dbeJProblem->getValue(DBEProblem::completeDate);
                $fields['reason'] = $dbeFirstActivity->getValue(DBEJCallActivity::reason);
                $fields['completeDate'] = Controller::dateYMDtoDMY($completeDate);
                $fields['resolvedEngineerName'] = $dbeFixedUser->getValue(
                        DBEUser::firstName
                    ) . ' ' . $dbeFixedUser->getValue(
                        DBEUser::lastName
                    );

                $subjectSuffix = 'Pending Closure on ' . Controller::dateYMDtoDMY($completeDate);

                break;
            case self::ClosureCustomerEmailCategory:
                $selfFlagName = DBEContact::closureEmailFlag;
                $othersFlagName = DBEContact::othersClosureEmailFlag;
                $templateName = 'ServiceCompletedEmail';
                $subjectSuffix = 'Now Closed';

                $fields['reason'] = $dbeFirstActivity->getValue(DBEJCallActivity::reason);
                $fields['rootCause'] = $rootCause;
                $fields['fixedActivityReason'] = $dbeLastActivity->getValue(DBEJCallActivity::reason);
                $fields['urlQuestionnaire'] = 'http://www.cnc-ltd.co.uk/questionnaire/index.php?problemno=' . $problemID . '&questionnaireno=1';
                break;
        }

        if ($contact->getValue($selfFlagName) == 'Y') {
            $emails = $contact->getValue(DBEContact::email);
        }

        foreach ($mainSupportContacts as $supportContact) {
            if ($supportContact[DBEContact::contactID] == $contactID ||
                $supportContact[$othersFlagName] != 'Y'
            ) {
                continue;
            }
            if (!empty($emails)) {
                $emails .= ',';
            }
            $emails .= $supportContact[DBEContact::email];
        }

        switch ($subCategory) {
            case self::Initial24HSupport:
                $templateName = 'ServiceLoggedEmail24h';
                break;
            case self::InitialNot24HSupport:
                $templateName = 'ServiceLoggedEmailNot24h';
                break;
            case self::WorkUpdatesPriorityChanged:
                $templateName = 'ServicePriorityChangedEmail';
                $subjectSuffix = 'Priority Changed';
                break;
            case self::WorkUpdatesActivityLogged:
                $templateName = 'ActivityLoggedCustomerEmail';
                if ($dbeCallActType->getValue(DBEJCallActType::customerEmailFlag) != 'Y') {
                    return;
                }
                $fields['extra'] = 'The service request requires further action by CNC as detailed above. 
                We will contact you when this commences or if we need further information.';
                $subjectSuffix = 'Requires Further Action By CNC';
                if (
                    $dbeLastActivity->getValue(DBEJCallActivity::awaitingCustomerResponseFlag) == 'Y'
                ) {

                    $subjectSuffix = 'Requires YOUR Attention';
                    $fields['extra'] = "The service request requires YOUR attention as detailed above. 
                    Please note that we will take NO further action until we hear from you.";
                }

                break;
            case self::FixedReopen:
                $templateName = 'ServiceReopenedEmail';
                $subjectSuffix = 'Reopened';
                break;

            case self::Fixed:

                if ($dbeJProblem->getValue(DBEJProblem::fixedUserID) == USER_SYSTEM) {
                    return;
                }
                $templateName = 'ServiceFixedEmail';
                $subjectSuffix = 'Fixed';

                $fields['completeDate'] = Controller::dateYMDtoDMY(
                    $dbeJProblem->getValue(DBEJProblem::completeDate)
                );
                $fields['rootCause'] = $rootCause;
                break;
        }

        $buMail = new BUMail($this);


        $senderEmail = CONFIG_SUPPORT_EMAIL;
        if (empty($emails)) {
            return;                     // no email recipients so abort
        }

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            $templateName . '.inc.html'
        );

        $technicianResponsibleName = $dbeJProblem->getValue(DBEJProblem::engineerName);
        $activityReason = $dbeLastActivity->getValue(DBEJCallActivity::reason);

        if ($callActivityID) {
            $dbeCallActivity = new DBEJCallActivity($this);
            $dbeCallActivity->getRow($callActivityID);
            $technicianResponsibleName = $dbeCallActivity->getValue(DBEJCallActivity::userName);
            $activityReason = $dbeCallActivity->getValue(DBECallActivity::reason);
        }

        $template->setVar(
            array(
                'contactFirstName'      => $contact->getValue(DBEContact::firstName),
                'activityRef'           => $problemID,
                'CONFIG_SERVICE_REQUEST_DESC'
                                        => CONFIG_SERVICE_REQUEST_DESC,
                'priority'              => $this->priorityArray[$dbeJProblem->getValue(DBEJProblem::priority)],
                'reason'                => $dbeFirstActivity->getValue(DBEJCallActivity::reason),
                'lastActivityReason'    => $activityReason,
                'responseDetails'       => strtolower(
                    $this->getResponseDetails($dbeFirstActivity)
                ),
                'technicianResponsible' => $technicianResponsibleName
            )
        );

        foreach ($fields as $key => $value) {

            $template->setVar(
                $key,
                $value
            );

        }

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $subject = CONFIG_SERVICE_REQUEST_DESC . ' ' . $problemID;

        if ($subjectSuffix) {
            $subject .= ' - ' . $subjectSuffix;
        }

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $emails,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $emails,
            $hdrs,
            $body
        );
    }

    /**
     * @param DBEJCallActivity $dbeJCallActivity
     * @return string
     */
    function getResponseDetails($dbeJCallActivity)
    {
        $slaResponseHours =
            $this->getSlaResponseHours(
                $dbeJCallActivity->getValue(DBEJCallActivity::priority),
                $dbeJCallActivity->getValue(DBEJCallActivity::customerID)
            );

        if ($slaResponseHours > 0) {
            $responseDetails = 'we will respond to your ' . strtolower(
                    CONFIG_SERVICE_REQUEST_DESC
                ) . ' within ' . $slaResponseHours . ' working hours as per your service level agreement for priority ' . $dbeJCallActivity->getValue(
                    DBEJCallActivity::priority
                ) . ' ' . strtolower(CONFIG_SERVICE_REQUEST_DESC) . 's';

        } else {
            $responseDetails = 'As this ' . strtolower(
                    CONFIG_SERVICE_REQUEST_DESC
                ) . ' is outside the scope of your service level agreement it will be responded to on a best endeavours basis';
        }

        return $responseDetails;
    } // end sendRequestCompletedEarlyEmail

    function getSlaResponseHours($priority,
                                 $customerID,
                                 $contactID = null
    )
    {
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);

        $priorityValue = null;

        switch ($priority) {
            case 1:
                $priorityValue = DBECustomer::slaP1;
                break;
            case 2:
                $priorityValue = DBECustomer::slaP2;
                break;
            case 3:
                $priorityValue = DBECustomer::slaP3;
                break;
            case 4:
                $priorityValue = DBECustomer::slaP4;
                break;
            case 5:
                $priorityValue = DBECustomer::slaP5;
                break;
        }

        $slaHours = $dbeCustomer->getValue($priorityValue);

        $dbeContact = null;
        if ($contactID) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($contactID);
        }

        /*
    Special attention customers get half of normal SLA
    */
        if (
            ($dbeCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
                $dbeCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')) ||
            ($dbeContact && $dbeContact->getValue(DBEContact::specialAttentionContactFlag) == 'Y')
        ) {
            $slaHours = $slaHours / 2;
        }

        return $slaHours;
    }

    function sendPriorityFiveFixedEmail($problemID)
    {
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $buMail = new BUMail($this);


        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $toEmail = false;

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'PriorityFiveFixedEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $template->setVar(
            array(
                'activityRef'  => $activityRef,
                'reason'       => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'customerName' => $dbeJProblem->getValue(DBEJProblem::customerName),
                'urlActivity'  => $urlActivity,
                'CONFIG_SERVICE_REQUEST_DESC'
                               => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Priority 5 Fixed: SR ' . $problemID . ' ' . $dbeJProblem->getValue(
                    DBEJProblem::customerName
                ),
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /**
     * Create or update activity
     *
     * @param mixed $dsCallActivity
     * @return bool
     * @throws Exception
     */
    function updateCallActivity(&$dsCallActivity
    )
    {
        $this->setMethodName('updateCallActivity');
        $dbeCallActivity = new DBECallActivity($this);
        $oldEndTime = null; // new activity
        $newReason = null;
        if ($dsCallActivity->getValue(DBEJCallActivity::callActivityID) != 0) {
            $dbeCallActivity->getRow($dsCallActivity->getValue(DBEJCallActivity::callActivityID));
            $oldEndTime = $dbeCallActivity->getValue(DBEJCallActivity::endTime);
            $oldReason = $dbeCallActivity->getValue(DBEJCallActivity::reason);
            $newReason = $dsCallActivity->getValue(DBEJCallActivity::reason);
        }

        $dbeCallActType = new DBECallActType($this);
        $dbeCallActType->getRow($dsCallActivity->getValue(DBEJCallActivity::callActTypeID));


        // if this activity will now have an end time and the type specifies that we do not need to check it, set status to checked
        if (!$oldEndTime && $dsCallActivity->getValue(DBEJCallActivity::endTime)) {
            if ($dbeCallActType->getValue(DBECallActType::requireCheckFlag) == 'N') {
                $dsCallActivity->setUpdateModeUpdate();
                $dsCallActivity->setValue(
                    DBEJCallActivity::status,
                    'C'
                );
                $dsCallActivity->post();
            }
            $enteredEndTime = true;
        } else {
            $enteredEndTime = false;
        }

        $this->updateDataAccessObject(
            $dsCallActivity,
            $dbeCallActivity
        );
        /**Get total hours spent*/
        $sql =
            "SELECT
        SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 3600 AS totalHours
      FROM
          callactivity
      WHERE
          caa_problemno = " . $dsCallActivity->getValue(DBEJProblem::problemID);

        $result = $this->db->query($sql);
        $totalHours = $result->fetch_object()->totalHours;

        /*
    Get total travel hours spent
    */
        $sql =
            "SELECT
        SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 3600 AS totalHours
      FROM
          callactivity ca
          JOIN callacttype cat ON ca.caa_callacttypeno = cat.cat_callacttypeno
      WHERE
          cat.travelFlag = 'Y'
          AND ca.caa_problemno = " . $dsCallActivity->getValue(DBEJProblem::problemID);


        $result = $this->db->query($sql);
        $totalTravelHours = $result->fetch_object()->totalHours;

        $sql =
            "SELECT
        SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 3600 AS chargeableHours
      FROM
        callactivity
        JOIN problem ON pro_problemno = caa_problemno
        LEFT JOIN custitem ON cui_cuino = pro_contract_cuino
        JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
        JOIN item AS at_item ON cat_itemno = at_item.itm_itemno
      WHERE
        caa_problemno = " . $dsCallActivity->getValue(DBEJCallActivity::problemID) .
            " AND  at_item.itm_sstk_price  > 0";

        $result = $this->db->query($sql);
        $chargeableHours = $result->fetch_object()->chargeableHours;

        $dbeProblem = new DBEProblem($this);

        $dbeProblem->getRow($dsCallActivity->getValue(DBEJCallActivity::problemID));

        $oldPriority = $dbeProblem->getValue(DBEJProblem::priority);

        $dbeProblem->setValue(
            DBEJProblem::awaitingCustomerResponseFlag,
            $dsCallActivity->getValue(DBEJCallActivity::awaitingCustomerResponseFlag)
        );
        $dbeProblem->setValue(
            DBEJProblem::contractCustomerItemID,
            $dsCallActivity->getValue(DBEJCallActivity::contractCustomerItemID)
        );
        $dbeProblem->setValue(
            DBEJProblem::internalNotes,
            $dsCallActivity->getValue(DBEJCallActivity::internalNotes)
        );
        $dbeProblem->setValue(
            DBEJProblem::completeDate,
            $dsCallActivity->getValue(DBEJCallActivity::completeDate)
        );
        $dbeProblem->setValue(
            DBEJProblem::alarmDate,
            $dsCallActivity->getValue(DBEJCallActivity::alarmDate)
        );
        $dbeProblem->setValue(
            DBEJProblem::alarmTime,
            $dsCallActivity->getValue(DBEJCallActivity::alarmTime)
        );

        $dbeProblem->setValue(
            DBEJProblem::priority,
            $dsCallActivity->getValue(DBEJCallActivity::priority)
        );

        $dbeProblem->setValue(
            DBEJProblem::projectID,
            $dsCallActivity->getValue(DBEJCallActivity::projectID)
        );

        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            $dsCallActivity->getValue(DBEJCallActivity::rootCauseID)
        );

        $dbeProblem->setValue(
            DBEJProblem::totalActivityDurationHours,
            $totalHours
        );

        $dbeProblem->setValue(
            DBEJProblem::totalTravelActivityDurationHours,
            $totalTravelHours
        );

        $dbeProblem->setValue(
            DBEJProblem::chargeableActivityDurationHours,
            $chargeableHours
        );

        $dbeProblem->setValue(
            DBEJProblem::workingHoursCalculatedToTime,
            null
        );

        // if amended initial call activity date/time then set the problem date raised field to match
        if ($dsCallActivity->getValue(DBEJCallActivity::callActTypeID) == CONFIG_INITIAL_ACTIVITY_TYPE_ID) {
            var_dump(
                $dsCallActivity->getValue(DBEJCallActivity::date),
                $dsCallActivity->getValue(
                    DBEJCallActivity::startTime
                )
            );
            $dbeProblem->setValue(
                DBEJProblem::dateRaised,
                $dsCallActivity->getValue(DBEJCallActivity::date) . ' ' . $dsCallActivity->getValue(
                    DBEJCallActivity::startTime
                ) . ':00'
            );
        }

        $dbeProblem->updateRow();
        /*


    Have the contact notes been changed?
    If so then update contact table with new notes
    */
        if ($dsCallActivity->getValue(DBEJCallActivity::contactNotes) && $dsCallActivity->getValue(
                DBEJCallActivity::contactID
            )) {
            $sql =
                "SELECT con_notes
          FROM contact
          WHERE con_contno = " . $dsCallActivity->getValue(DBEJCallActivity::contactID);

            $oldNotes = $this->db->query($sql)->fetch_object()->con_notes;
            if (
                $oldNotes != $dsCallActivity->getValue(DBEJCallActivity::contactNotes)
            ) {

                $dbeContact = new DBEContact($this);

                $dbeContact->getRow($dsCallActivity->getValue(DBEJCallActivity::contactID));

                $dbeContact->setValue(
                    DBEContact::notes,
                    $dsCallActivity->getValue(DBEJCallActivity::contactNotes)
                );
                $dbeContact->updateRow();
            }
        }
        if ($dsCallActivity->getValue(DBEJCallActivity::techNotes) && $dsCallActivity->getValue(
                DBEJCallActivity::customerID
            )) {
            $sql =
                "SELECT cus_tech_notes
          FROM customer
          WHERE cus_custno = " . $dsCallActivity->getValue(DBEJCallActivity::customerID);

            $oldTechNotes = $this->db->query($sql)->fetch_object()->cus_tech_notes;

            if (
                $oldTechNotes != $dsCallActivity->getValue(DBEJCallActivity::techNotes)
            ) {
                $sql =
                    "UPDATE customer
              SET cus_tech_notes = '" . $dsCallActivity->getValue(DBEJCallActivity::techNotes) .
                    "' WHERE cus_custno = " . $dsCallActivity->getValue(DBEJCallActivity::customerID);

                $this->db->query($sql);
            }
        }
        if (
            $oldPriority != $dbeProblem->getValue(DBEJProblem::priority)
        ) {
            $slaResponseHours =
                $this->getSlaResponseHours(
                    $dbeProblem->getValue(DBEJProblem::priority),
                    $dbeProblem->getValue(DBEJProblem::customerID),
                    $dbeCallActivity->getValue(DBECallActivity::contactID)
                );

            $dbeProblem->setValue(
                DBEJProblem::slaResponseHours,
                $slaResponseHours
            );
            $dbeProblem->updateRow();

            $this->sendEmailToCustomer(
                $dsCallActivity->getValue(DBEJProblem::problemID),
                self::WorkUpdatesCustomerEmailCategory,
                self::WorkUpdatesPriorityChanged
            );

            $this->logOperationalActivity(
                $dsCallActivity->getValue(DBEJCallActivity::problemID),
                'Priority Changed from ' . $oldPriority . ' to ' . $dbeProblem->getValue(DBEJProblem::priority)
            );
        } else {
            if ((!isset($oldReason) || (isset($oldReason) && $oldReason != $newReason)) && $dsCallActivity->getValue(
                    DBEJCallActivity::endTime
                )) {
                $this->sendEmailToCustomer(
                    $dsCallActivity->getValue(DBEJProblem::problemID),
                    self::WorkUpdatesCustomerEmailCategory,
                    self::WorkUpdatesActivityLogged,
                    $dsCallActivity->getValue(DBEJCallActivity::callActivityID)
                );
            }
        }

        $this->sendMonitoringEmails($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));


        /*
    Send emails UNLESS this is an escalation or change request activity type
    */
        if (
        !in_array(
            $dbeCallActivity->getValue(DBEJCallActivity::callActTypeID),
            array(
                CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID,
                CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
            )
        )
        ) {

            $this->highActivityAlertCheck($dbeProblem->getValue(DBEJProblem::problemID));

            $this->updatedByAnotherUser(
                $dbeProblem,
                $dbeCallActivity
            );

            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dbeProblem->getValue(DBEJProblem::customerID),
                $dsCustomer
            );
            $dbeContact = null;
            if ($dsCallActivity->getValue(DBEJCallActivity::contactID)) {
                $dbeContact = new DBEContact($this);
                $dbeContact->getRow($dsCallActivity->getValue(DBEJCallActivity::contactID));
            }


            if (
                ($dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
                    $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date(
                        'Y-m-d'
                    )) || ($dbeContact && $dbeContact->getValue(DBEContact::specialAttentionContactFlag) == 'Y')

            ) {
                $this->sendSpecialAttentionEmail($dbeCallActivity->getPKValue());
            }

            if ($dbeProblem->getValue(DBEJProblem::criticalFlag) == 'Y') {
                $this->sendCriticalEmail($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));
            }


            /*
      If this is a future on-site visit then send notification email( issue #8750 )
      */
            if (
                !$dbeCallActivity->getValue(DBEJCallActivity::endTime) &
                $dbeCallActivity->getValue(DBEJCallActivity::date) >= date('Y-m-d')
            ) {
                $this->sendFutureVisitEmail($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));
            }
        }

        /*
    If this is a change request activity then send request email
    */
        if (
            $dbeCallActivity->getValue(DBEJCallActivity::callActTypeID) == CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
        ) {
            $this->sendChangeRequestEmail($dbeCallActivity);
        }


        if ($dbeCallActivity->getValue(DBEJCallActivity::userID) != USER_SYSTEM) {
            $this->updateTotalUserLoggedHours(
                $dbeCallActivity->getValue(DBEJCallActivity::userID),
                $dbeCallActivity->getValue(DBEJCallActivity::date)
            );
        }

        return $enteredEndTime;
    }

    function computeDiff($from,
                         $to
    )
    {
        $diffValues = array();
        $diffMask = array();

        $dm = array();
        $n1 = count($from);
        $n2 = count($to);

        for ($j = -1; $j < $n2; $j++) $dm[-1][$j] = 0;
        for ($i = -1; $i < $n1; $i++) $dm[$i][-1] = 0;
        for ($i = 0; $i < $n1; $i++) {
            for ($j = 0; $j < $n2; $j++) {
                if ($from[$i] == $to[$j]) {
                    $ad = $dm[$i - 1][$j - 1];
                    $dm[$i][$j] = $ad + 1;
                } else {
                    $a1 = $dm[$i - 1][$j];
                    $a2 = $dm[$i][$j - 1];
                    $dm[$i][$j] = max(
                        $a1,
                        $a2
                    );
                }
            }
        }

        $i = $n1 - 1;
        $j = $n2 - 1;
        while (($i > -1) || ($j > -1)) {
            if ($j > -1) {
                if ($dm[$i][$j - 1] == $dm[$i][$j]) {
                    $diffValues[] = $to[$j];
                    $diffMask[] = 1;
                    $j--;
                    continue;
                }
            }
            if ($i > -1) {
                if ($dm[$i - 1][$j] == $dm[$i][$j]) {
                    $diffValues[] = $from[$i];
                    $diffMask[] = -1;
                    $i--;
                    continue;
                }
            }
            {
                $diffValues[] = $from[$i];
                $diffMask[] = 0;
                $i--;
                $j--;
            }
        }

        $diffValues = array_reverse($diffValues);
        $diffMask = array_reverse($diffMask);

        return array('values' => $diffValues, 'mask' => $diffMask);
    }

    function highActivityAlertCheck($problemID)
    {


        $sql =
            "SELECT
        COUNT(*) as activityCount
      FROM
        callactivity
      WHERE
        caa_problemno = $problemID
        AND caa_date = CURDATE()";


        $totalActivities = 0;
        $result = $this->db->query($sql);
        if ($result) {
            $totalActivities = $result->fetch_object()->activityCount;
        } else {
            var_dump($this->db->error_list);
        }

        if ($totalActivities == $this->dsHeader->getValue(DBEJHeader::highActivityAlertCount)) {
            $this->sendHighActivityAlertEmail($problemID);
        }

    }

    function sendHighActivityAlertEmail($problemID)
    {
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $buMail = new BUMail($this);


        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $toEmail = 'srhighactivity@' . CONFIG_PUBLIC_DOMAIN;

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'HighActivityAlertEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $template->setVar(
            array(
                'activityRef'  => $activityRef,
                'reason'       => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'customerName' => $dbeJProblem->getValue(DBEJProblem::customerName),
                'urlActivity'  => $urlActivity,
                'CONFIG_SERVICE_REQUEST_DESC'
                               => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'High Activity Alert: SR ' . $problemID . ' ' . $dbeJProblem->getValue(
                    DBEJProblem::customerName
                ),
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /*
  Send an alert email if number of activities per SR per day exceeds system max
  */

    /**
     * @param DataAccess $dbeProblem
     * @param DataAccess $dbeCallActivity
     */
    function updatedByAnotherUser($dbeProblem,
                                  $dbeCallActivity
    )
    {

        $dbeJLastActivity = $this->getLastActivityInProblem($dbeProblem->getValue(DBEJProblem::problemID));
        if (
            $dbeCallActivity->getValue(DBEJCallActivity::callActTypeID) != CONFIG_INITIAL_ACTIVITY_TYPE_ID AND
            // Always include activity triggered by an email from the customer
            $this->loggedInUserID == USER_SYSTEM AND $dbeCallActivity->getValue(DBEJCallActivity::serverGuard) == 'N' OR
            (
                /*
        Don't send unwanted alerts
        */
                $dbeProblem->getValue(DBEJProblem::userID) != $dbeJLastActivity->getValue(
                    DBEJCallActivity::userID
                ) && // exclude previous user same as assigned user
                $dbeProblem->getValue(
                    DBEJProblem::userID
                ) != $this->loggedInUserID &&                   // exclude logged in user = assigned user
                $this->loggedInUserID != USER_SYSTEM &&                                        // exclude automated server alerts
                $dbeCallActivity->getValue(
                    DBEJCallActivity::endTime
                )                // exclude future scheduled activity
            )
        ) {
            $this->sendUpdatedByAnotherUserEmail(
                $dbeProblem->getValue(DBEJProblem::problemID),
                $dbeCallActivity
            );
        }

    }

    /**
     * @param $problemID
     * @param DBEJCallActivity|DataSet|DataAccess $callActivity
     */
    function sendUpdatedByAnotherUserEmail($problemID,
                                           $callActivity
    )
    {
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);
        if (!$dbeJProblem->getValue(DBEJProblem::userID) || $dbeJProblem->getValue(
                DBEJProblem::userID
            ) == USER_SYSTEM) {
            return;       // not assigned to anyone or assigned to System user
        }
        $buMail = new BUMail($this);


        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($dbeJProblem->getValue(DBEJProblem::userID));
        $toEmail = $dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'NotifyUpdatedByAnotherUserEmail.inc.html'
        );


        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivity->getValue(DBECallActivity::callActivityID));


        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $template->setVar(
            array(
                'activityRef'                 => $activityRef,
                'reason'                      => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'customerName'                => $dbeJProblem->getValue(DBEJProblem::customerName),
                'urlActivity'                 => $urlActivity,
                'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC,
                'userName'                    => $dbeJCallActivity->getValue(DBEJCallActivity::userName)

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Service Request ' . $problemID . ' has been updated by another user',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    } // end sendUpdatedByAnotherUserEmail


    /**
     * Sends email to service desk managers when activity logged against  customer
     *
     * @param $callActivityID
     */
    function sendSpecialAttentionEmail($callActivityID)
    {
        $buMail = new BUMail($this);

        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivityID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $activityRef = $dbeJCallActivity->getValue(DBEJCallActivity::problemID) . ' ' . $dbeJCallActivity->getValue(
                DBEJCallActivity::customerName
            );

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'SpecialAttentionEmail.inc.html'
        );

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $durationHours = common_convertHHMMToDecimal(
                $dbeJCallActivity->getValue(DBEJCallActivity::endTime)
            ) - common_convertHHMMToDecimal($dbeJCallActivity->getValue(DBEJCallActivity::startTime));

        $awaitingCustomerResponse = null;

        if ($dbeJCallActivity->getValue(DBEJCallActivity::requestAwaitingCustomerResponseFlag) == 'Y') {
            $awaitingCustomerResponse = 'Awaiting Customer';
        } else {
            $awaitingCustomerResponse = 'Awaiting CNC';
        }

        $template->setVar(
            array(
                'activityRef'   => $activityRef,
                'urlActivity'   => $urlActivity,
                'userName'      => $dbeJCallActivity->getValue(DBEJCallActivity::userName),
                'durationHours' => round(
                    $durationHours,
                    2
                ),
                'requestStatus' => $this->problemStatusArray[$dbeJCallActivity->getValue(
                    DBEJCallActivity::problemStatus
                )],
                'awaitingCustomerResponse'
                                => $awaitingCustomerResponse,
                'customerName'  => $dbeJCallActivity->getValue(DBEJCallActivity::customerName),
                'reason'        => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'CONFIG_SERVICE_REQUEST_DESC'
                                => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $toEmail = 'srspecialattention@' . CONFIG_PUBLIC_DOMAIN;


        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Special Attention Activity ' . $dbeJCallActivity->getValue(
                    DBEJCallActivity::customerName
                ) . ': ' . $dbeJCallActivity->getValue(DBEJCallActivity::activityType),
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    function sendCriticalEmail($callActivityID)
    {
        $buMail = new BUMail($this);

        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivityID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $activityRef = $dbeJCallActivity->getValue(DBEJCallActivity::problemID) . ' ' . $dbeJCallActivity->getValue(
                DBEJCallActivity::customerName
            );

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'CriticalEmail.inc.html'
        );

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $durationHours = common_convertHHMMToDecimal(
                $dbeJCallActivity->getValue(DBEJCallActivity::endTime)
            ) - common_convertHHMMToDecimal($dbeJCallActivity->getValue(DBEJCallActivity::startTime));

        $awaitingCustomerResponse = null;

        if ($dbeJCallActivity->getValue(DBEJCallActivity::requestAwaitingCustomerResponseFlag) == 'Y') {
            $awaitingCustomerResponse = 'Awaiting Customer';
        } else {
            $awaitingCustomerResponse = 'Awaiting CNC';
        }

        $template->setVar(
            array(
                'activityRef'   => $activityRef,
                'urlActivity'   => $urlActivity,
                'userName'      => $dbeJCallActivity->getValue(DBEJCallActivity::userName),
                'durationHours' => round(
                    $durationHours,
                    2
                ),
                'requestStatus' => $this->problemStatusArray[$dbeJCallActivity->getValue(
                    DBEJCallActivity::problemStatus
                )],
                'awaitingCustomerResponse'
                                => $awaitingCustomerResponse,
                'customerName'  => $dbeJCallActivity->getValue(DBEJCallActivity::customerName),
                'reason'        => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'CONFIG_SERVICE_REQUEST_DESC'
                                => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $toEmail = 'criticalactivity@' . CONFIG_PUBLIC_DOMAIN;

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Critical SR Activity For ' . $dbeJCallActivity->getValue(
                    DBEJCallActivity::customerName
                ) . ': ' . $dbeJCallActivity->getValue(DBEJCallActivity::activityType),
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    } // end sendChangeRequestEmail

    /**
     * Sends email to sales when future on-site activity logged
     *
     * @param $callActivityID
     */
    function sendFutureVisitEmail($callActivityID)
    {
        $buMail = new BUMail($this);

        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivityID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $toEmail = CONFIG_SALES_EMAIL;

        $activityRef = $dbeJCallActivity->getValue(DBEJCallActivity::problemID) . ' ' . $dbeJCallActivity->getValue(
                DBEJCallActivity::customerName
            );

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'FutureVisitEmail.inc.html'
        );

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $template->setVar(
            array(
                'activityRef'   => $activityRef,
                'urlActivity'   => $urlActivity,
                'userName'      => $dbeJCallActivity->getValue(DBEJCallActivity::userName),
                'requestStatus' => $this->problemStatusArray[$dbeJCallActivity->getValue(
                    DBEJCallActivity::problemStatus
                )],
                'customerName'  => $dbeJCallActivity->getValue(DBEJCallActivity::customerName),
                'reason'        => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'CONFIG_SERVICE_REQUEST_DESC'
                                => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Future Visit To ' . $dbeJCallActivity->getValue(
                    DBEJCallActivity::customerName
                ) . ' Logged : ' . $dbeJCallActivity->getValue(DBEJCallActivity::activityType),
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /**
     * @param DBECallActivity $dbeCallActivity
     */
    private function sendChangeRequestEmail($dbeCallActivity)
    {
        $buMail = new BUMail($this);

        $problemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);

        $dsInitialCallActivity = $this->getFirstActivityInProblem($problemID);


        $this->dbeUser->getRow($dbeCallActivity->getValue(DBEJCallActivity::userID));

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->set_file(
            'page',
            'ChangeRequestEmail.inc.html'
        );

        $userName = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(DBEUser::lastName);

        $urlChangeControlRequest = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=changeRequestReview&callActivityID=' . $dbeCallActivity->getValue(
                DBEJCallActivity::callActivityID
            ) . '&fromEmail=true';

        $urlLastActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeCallActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $template->setVar(
            array(
                'problemID' => $problemID,

                'userName' => $userName,

                'urlChangeControlRequest' => $urlChangeControlRequest,

                'urlLastActivity' => $urlLastActivity,

                'initialReason' => $dsInitialCallActivity->getValue(DBEJCallActivity::reason),

                'requestReason' => $dbeCallActivity->getValue(DBEJCallActivity::reason)

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $toEmail = 'changerequest@' . CONFIG_PUBLIC_DOMAIN;

        $subject = 'Change Request for ' . $dsInitialCallActivity->getValue(
                DBEJCallActivity::customerName
            ) . ' by ' . $userName . ' for SR' . $problemID;


        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }


    /*
  Update total hours worked by activity user today
  */

    function updateTotalUserLoggedHours($userID,
                                        $date
    )
    {

        $startTime = '07:00';
        $endTime = '18:30';
        $sql =
            "UPDATE 
              user_time_log 
            SET
              loggedHours = 
              (SELECT 
                ROUND(
                  COALESCE(
                    SUM(
                      COALESCE(
                        TIME_TO_SEC(
                          IF(
                            caa_endtime > '$endTime',
                            '$endTime',
                            caa_endtime
                          )
                        ) - TIME_TO_SEC(
                          IF(
                            caa_starttime < '$startTime',
                            '$startTime',
                            caa_starttime
                          )
                        ),
                        0
                      )
                    ) / 3600,
                    0
                  ),
                  2
                ) 
              FROM
                callactivity 
                JOIN callacttype 
                  ON cat_callacttypeno = caa_callacttypeno 
              WHERE caa_consno = userID 
                AND caa_date = loggedDate 
                AND callacttype.travelFlag <> 'Y' 
                AND caa_starttime < '$endTime' 
                AND caa_endtime > '$startTime') 
            WHERE userID = $userID 
              AND loggedDate = '$date' ";

        $this->db->query($sql);

    }

    /**
     * @param $callActivityID
     * @param $userID
     * @param $response
     * @param $comments
     * @throws Exception
     */
    public function salesRequestProcess($callActivityID,
                                        $userID,
                                        $response,
                                        $comments
    )
    {
        $dsCallActivity = new DataSet($this);
        $this->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );


        $requestingUserID = $dsCallActivity->getValue(DBEJCallActivity::userID);

        $this->dbeUser->getRow($userID);


        $userName = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(DBEUser::lastName);
        $approval = false;
        $problem = new DBEProblem($this);
        $problem->getRow($dsCallActivity->getValue(DBECallActivity::problemID));
        $subject = null;
        $reason = null;
        switch ($response) {

            case 'A':
                $reason = '<p>The following sales request has been approved by ' . $userName . '</p>';

                $subject = 'Sales Request approved';
                $approval = true;

                break;

            case 'D':
                $reason = '<p>The following sales request has been denied by ' . $userName . '</p>';

                $subject = 'Sales Request denied';

                $problem->setValue(
                    DBEProblem::userID,
                    $requestingUserID
                );

                break;
            default:
                throw new Exception('Invalid response value');
        }

        $problem->setValue(
            DBEProblem::alarmDate,
            null
        );
        $problem->setValue(
            DBEProblem::alarmTime,
            null
        );

        $problem->updateRow();
        /*
    Append any comments
    */
        $subject .= ' for ' . $dsCallActivity->getValue(DBEJCallActivity::customerName) . ' by ' . $userName .
            ' for SR ' . $dsCallActivity->getValue(DBEJCallActivity::problemID);

        if ($comments) {
            $reason .= '<div style="color: red"><p>Comments:</p>' . $comments . '</div>';
        }

        /*
    and the original request
    */
        $reason .= '<p></p>' . $dsCallActivity->getValue(DBEJCallActivity::reason);


        $newCallActivity = $this->createSalesRequestActivity(
            $dsCallActivity->getValue(DBEJCallActivity::problemID),
            $reason
        );


        $this->sendSalesRequestReplyEmail(
            $newCallActivity,
            $subject,
            $requestingUserID,
            $approval
        );

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);
        $dbeCallActivity->setValue(
            DBECallActivity::salesRequestStatus,
            'C'
        );

        $dbeCallActivity->updateRow();
    }

    public function timeRequestProcess($callActivityID,
                                       $userID,
                                       $response,
                                       $comments,
                                       $minutes
    )
    {
        $dsCallActivity = new DataSet($this);
        $this->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $requestingUserID = $dsCallActivity->getValue(DBEJCallActivity::userID);
        $requestingUser = new DBEUser($this);
        $requestingUser->getRow($requestingUserID);


        $reviewingUser = new DBEUser($this);
        $reviewingUser->getRow($userID);

        $teamID = $requestingUser->getValue(DBEUser::teamID);
        switch ($response) {
            case 'A':
                $this->allocateAdditionalTime(
                    $dsCallActivity->getValue(DBECallActivity::problemID),
                    $teamID,
                    $minutes,
                    $comments
                );
                $this->logOperationalActivity(
                    $dsCallActivity->getValue(DBECallActivity::problemID),
                    '<p>Additional time allocated: ' . $minutes . ' minutes</p><p>' . $comments . '</p>'
                );
                break;
            case 'D':
                $this->logOperationalActivity(
                    $dsCallActivity->getValue(DBECallActivity::problemID),
                    '<p style="color: red;">Time request denied: ' . $comments . '</p>'
                );
                $this->sendTimeRequestDeniedEmail(
                    $dsCallActivity,
                    $requestingUser,
                    $comments
                );
                break;
            case 'DEL':
                break;
        }

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($dsCallActivity->getValue(DBEJCallActivity::callActivityID));
        $dbeCallActivity->setUpdateModeUpdate();
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );

        $dbeCallActivity->post();
    }

    /**
     * @param $callActivityID
     * @param $userID
     * @param $response
     * @param $comments
     * @throws Exception
     */
    public function changeRequestProcess($callActivityID,
                                         $userID,
                                         $response,
                                         $comments
    )
    {
        $dsCallActivity = new DataSet($this);
        $this->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        $requestingUserID = $dsCallActivity->getValue(DBEJCallActivity::userID);

        $this->dbeUser->getRow($userID);

        $userName = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(DBEUser::lastName);
        $subject = null;
        switch ($response) {

            case 'A':
                $reason = '<p>The following change request has been approved by ' . $userName . '</p>';
                $subject = 'Change Request approved';
                $status = 'C';
                break;
            case 'D':
                $reason = '<p>The following change request has been denied by ' . $userName . '</p>';
                $subject = 'Change Request denied';
                $status = 'C';
                break;
            case 'I':
                $reason = '<p>Further details/discussion requested by ' . $userName . '</p>';
                $subject = 'More information/discussion required for change request';
                $status = 'O';
                break;
            default:
                throw new Exception('Invalid response');
        }

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($dsCallActivity->getValue(DBEJCallActivity::callActivityID));
        $dbeCallActivity->setUpdateModeUpdate();
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            $status
        );

        $dbeCallActivity->post();
        /*
    Append any comments
    */
        $subject .= ' for ' . $dsCallActivity->getValue(DBEJCallActivity::customerName) . ' by ' . $userName .
            ' for SR ' . $dsCallActivity->getValue(DBEJCallActivity::problemID);

        if ($comments) {
            $reason .= '<div style="color: red"><p>Comments:</p>' . $comments . '</div>';
        }
        /*
    and the original request
    */
        $reason .= '<p></p>' . $dsCallActivity->getValue(DBEJCallActivity::reason);

        $this->resetProblemAlarm($dsCallActivity->getValue(DBEJCallActivity::problemID));

        $newCallActivityID = $this->createChangeRequestActivity(
            $callActivityID,
            $reason,
            $userID
        );

        $this->getActivityByID(
            $newCallActivityID,
            $dsCallActivity
        );    // get activity just created

        $this->sendChangeRequestReplyEmail(
            $dsCallActivity,
            $subject,
            $requestingUserID
        );
    }


    /*
  does a travel activity for this customer and engineer already exists for today
  */

    function resetProblemAlarm($problemID)
    {
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        $dbeProblem->setValue(
            DBEJProblem::alarmDate,
            null
        );
        $dbeProblem->setValue(
            DBEJProblem::alarmTime,
            null
        );
        return ($dbeProblem->updateRow());
    }

    private function createTimeRequestsActivity($problemID,
                                                $reason,
                                                $userID
    )
    {
        $dbeJLastCallActivity = $this->getLastActivityInProblem($problemID);

        $dbeNewActivity = new DBECallActivity($this);
        $dbeNewActivity->getRow($dbeJLastCallActivity->getValue(DBEJCallActivity::callActivityID));

        $dbeNewActivity->setPKValue(null);

        $dbeNewActivity->setValue(
            DBEJCallActivity::date,
            date('Y-m-d')
        );         // today
        $dbeNewActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::endTime,
            date('H:i')
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::userID,
            $userID
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_TIME_REQUEST_ACTIVITY_TYPE_ID
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::status,
            'O'
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::reason,
            $reason
        );

        $dbeNewActivity->insertRow();

        return $dbeNewActivity->getPKValue();
    }

    function createChangeRequestActivity($callActivityID,
                                         $reason,
                                         $userID
    )
    {

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $dbeNewActivity = $dbeCallActivity;

        $dbeNewActivity->setPKValue(null);

        $dbeNewActivity->setValue(
            DBEJCallActivity::date,
            date('Y-m-d')
        );         // today
        $dbeNewActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::endTime,
            date('H:i')
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::userID,
            $userID
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeNewActivity->setValue(
            DBEJCallActivity::reason,
            $reason
        );

        $dbeNewActivity->insertRow();

        return $dbeNewActivity->getPKValue();
    }// end reenter end time

    /**
     * @param DataAccess $dbeCallActivity
     * @param string $subject
     * @param string|int $requestingUserID
     * @param bool $approval
     */
    private function sendSalesRequestReplyEmail($dbeCallActivity,
                                                $subject,
                                                $requestingUserID,
                                                $approval = false
    )
    {
        $buMail = new BUMail($this);

        $problemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);

        $this->dbeUser->getRow($dbeCallActivity->getValue(DBEJCallActivity::userID));

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->set_file(
            'page',
            'SalesRequestReplyEmail.inc.html'
        );

        $userName = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(DBEUser::lastName);

        $urlLastActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeCallActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $template->setVar(
            array(
                'problemID' => $problemID,

                'userName' => $userName,

                'subject' => $subject,

                'urlLastActivity' => $urlLastActivity,

                'requestReason' => $dbeCallActivity->getValue(DBEJCallActivity::reason)

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');
        /*
    Send reply to allocated user
    */
        $this->dbeUser->getRow($requestingUserID);

        $toEmail = 'salesRequestReply@' . CONFIG_PUBLIC_DOMAIN . ',' . $this->dbeUser->getValue(
                DBEUser::username
            ) . '@' . CONFIG_PUBLIC_DOMAIN;

        if ($approval) {
            $toEmail .= ',sales@' . CONFIG_PUBLIC_DOMAIN;
        }

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);


        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /**
     * @param DataAccess $dbeCallActivity
     * @param DBEUser $requestingUser
     * @param $reason
     */
    private function sendTimeRequestDeniedEmail($dbeCallActivity,
                                                $requestingUser,
                                                $reason
    )
    {
        $buMail = new BUMail($this);

        $problemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->set_file(
            'page',
            'TimeRequestDeniedEmail.inc.html'
        );

        $userName = $requestingUser->getValue(DBEUser::firstName) . ' ' . $requestingUser->getValue(DBEUser::lastName);

        $urlLastActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeCallActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $template->setVar(
            array(
                'problemID'       => $problemID,
                'userName'        => $userName,
                'urlLastActivity' => $urlLastActivity,
                'requestReason'   => $reason
            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $toEmail = $requestingUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => "Time Request Denied - SR " . $problemID,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);


        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /**
     * @param DataAccess $dbeCallActivity
     * @param string $subject
     * @param string|int $requestingUserID
     */
    private function sendChangeRequestReplyEmail($dbeCallActivity,
                                                 $subject,
                                                 $requestingUserID
    )
    {
        $buMail = new BUMail($this);

        $problemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);

        $this->dbeUser->getRow($dbeCallActivity->getValue(DBEJCallActivity::userID));

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->set_file(
            'page',
            'ChangeRequestReplyEmail.inc.html'
        );

        $userName = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(DBEUser::lastName);

        $urlChangeControlRequest = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=changeControlRequest&callActivityID=' . $dbeCallActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $urlLastActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeCallActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $template->setVar(
            array(
                'problemID' => $problemID,

                'userName' => $userName,

                'subject' => $subject,

                'urlChangeControlRequest' => $urlChangeControlRequest,

                'urlLastActivity' => $urlLastActivity,

                'requestReason' => $dbeCallActivity->getValue(DBEJCallActivity::reason)

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');
        /*
    Send reply to allocated user
    */
        $this->dbeUser->getRow($requestingUserID);

        $toEmail = 'changerequestreply@' . CONFIG_PUBLIC_DOMAIN . ',' . $this->dbeUser->getValue(
                DBEUser::username
            ) . '@' . CONFIG_PUBLIC_DOMAIN;

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);


        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /*
  Check to se whether this site record requires travel hours added to the site record.
  i.e. is this a chargeable activity and does this site have zero travel hours.
  */

    function updateAllHistoricUserLoggedHours(DateTime $startDate = null)
    {
        $sql =
            "SELECT
        userID,
        loggedDate
      FROM
        user_time_log";

        if ($startDate) {
            $sql .= " where loggedDate > '" . $startDate->format('Y-m-d') . "'";
        }
        $sql .= " and holiday = 0 ";
        $sql .= " order by loggedDate asc, userID";
        $result = $this->db->query($sql);
        while ($record = $result->fetch_assoc()) {
            echo "User: " . $record['userID'] . " Date: " . $record['loggedDate'] . "<BR/>";
            $this->updateTotalUserLoggedHours(
                $record['userID'],
                $record['loggedDate']
            );
        }
    }

    function travelActivityForCustomerEngineerTodayExists(
        $customerID,
        $siteNo,
        $userID,
        $date
    )
    {
        $dbeCallActivity = new DBECallActivity($this);

        return (
        $dbeCallActivity->countTravelRowsForTodayByCustomerSiteNoEngineer(
            $customerID,
            $siteNo,
            $userID,
            $date
        )
        );

    }

    /**
     * Create travel activities using site maxTravelHours field from address
     *
     * 1: startTime - maxTravelTime
     * 2: endTime + maxTravelTime
     *
     * GL:
     * "The travel activity start time will be the on-site activity start time less the agreed travel time and the end time as per the on-site start time
     *
     * Updated 15/4/2009:
     * zero is now a valid travel time and means that a travel activity is not created
     * -1 now means the travel time has not been set for this site and blocks the creation of an on-site activity
     * @param $callActivityID
     */
    function createTravelActivity($callActivityID)
    {

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeCallActivity->getValue(DBEJCallActivity::problemID));

        $buSite = new BUSite($this);
        $dsSite = new DataSet($this);
        $buSite->getSiteByID(
            $dbeProblem->getValue(DBEJProblem::customerID),
            $dbeCallActivity->getValue(DBEJCallActivity::siteNo),
            $dsSite
        );

        $activityStartTime = $dbeCallActivity->getValue(DBEJCallActivity::startTime);

        $travelStart = common_convertDecimalToHHMM(
            common_convertHHMMToDecimal($activityStartTime) - $dsSite->getValue(DBESite::maxTravelHours)
        );

        $dbeTravelActivity = $dbeCallActivity;

        $dbeTravelActivity->setPKValue(null);

        $dbeTravelActivity->setValue(
            DBEJCallActivity::startTime,
            $travelStart
        );
        $dbeTravelActivity->setValue(
            DBEJCallActivity::endTime,
            $activityStartTime
        );
        $dbeTravelActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_TRAVEL_ACTIVITY_TYPE_ID
        );
        $dbeTravelActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeTravelActivity->setValue(
            DBEJCallActivity::reason,
            null
        );

        $dbeTravelActivity->insertRow();
    }

    function needsTravelHoursAdding($callActTypeID,
                                    $customerID,
                                    $siteNo
    )
    {

        $ret = false;

        $dbeCallActType = new DBECallActType($this);

        $dbeCallActType->getRow($callActTypeID);

        $typeDescription = $dbeCallActType->getValue(DBEJCallActType::description);

        if (strpos(
                $typeDescription,
                'FOC'
            ) === FALSE) {

            $dbeSite = new DBESite($this);

            $dbeSite->setValue(
                DBESite::customerID,
                $customerID
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $siteNo
            );

            $dbeSite->getRowByCustomerIDSiteNo();

            if ($dbeSite->getValue(DBESite::maxTravelHours) == -1) {  // new value for travel not set

                $ret = true;

            }

        }

        return $ret;

    }

    function deleteCallActivity($callActivityID)
    {

        $this->setMethodName('deleteCallActivity');

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $problemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);

        if ($this->countActivitiesInProblem($problemID) == 2) {
            /* This is the start-work activity (initial + 1 )so reset the responded hours */
            $dbeProblem = new DBEProblem(
                $this,
                $problemID
            );
            $dbeProblem->setValue(
                DBEJProblem::respondedHours,
                0
            );
            $dbeProblem->updateRow();
        }
        /*
    If there are no activities left then delete the problem row too and send an email to help desk managers
    */
        if ($this->countActivitiesInProblem($problemID) == 1) {

            $this->sendServiceRemovedEmail($problemID);

            $dbeProblem = new DBEProblem($this);

            $dbeProblem->deleteRow($problemID);

            $problemID = false;

        }

        $dbeCallActivity->deleteRow($callActivityID);

        return $problemID;

    }

    function countActivitiesInProblem($problemID)
    {

        $dbeCallActivity = new DBECallActivity($this);

        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $problemID
        );

        $count = $dbeCallActivity->countRowsByColumn(DBEJCallActivity::problemID);

        return $count;

    }

    function sendServiceRemovedEmail($problemID,
                                     $allocatedToSystemUser = false
    )
    {

        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $toEmail = 'sremoved@' . CONFIG_PUBLIC_DOMAIN;

        if ($allocatedToSystemUser) {
            $sendToSDManagers = false;
        } else {
            $sendToSDManagers = true;
        }

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'ServiceRemovedEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);

        $template->setVar(
            array(
                'activityRef'    => $activityRef,
                'customerName'   => $dbeJProblem->getValue(DBEJProblem::customerName),
                'reason'         => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'status'         => $this->problemStatusArray[$dbeJProblem->getValue(DBEJProblem::status)],
                'awaitingStatus' => ($dbeJProblem->getValue(
                        DBEJProblem::awaitingCustomerResponseFlag
                    ) == 'Y') ? 'Customer' : 'CNC',
                'dateRaisedDMY'  => $dbeJProblem->getValue(DBEJProblem::dateRaisedDMY),
                'timeRaised'     => $dbeJProblem->getValue(DBEJProblem::timeRaised),
                'respondedHours' => common_convertDecimalToHHMM($dbeJProblem->getValue(DBEJProblem::respondedHours)),
                'workingHours'   => common_convertDecimalToHHMM($dbeJProblem->getValue(DBEJProblem::workingHours)),
                'engineerName'   => $dbeJProblem->getValue(DBEJProblem::engineerName),
                'removedByUser'  => $this->dbeUser->getValue(DBEUser::name),
                'CONFIG_SERVICE_REQUEST_DESC'
                                 => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => CONFIG_SERVICE_REQUEST_DESC . ' ' . $activityRef . ' Has Been Removed From The System',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    function setActivityStatusChecked($callactivityID)
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callactivityID);
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        return ($dbeCallActivity->updateRow());
    }

    function setActivityStatusAuthorised($callactivityID)
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callactivityID);
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'A'
        );
        return ($dbeCallActivity->updateRow());
    }

    /**
     * sets problem into pause mode by setting flag on activity
     *
     * @param mixed $callactivityID
     * @param mixed $date
     * @param mixed $time
     */
    function setActivityAwaitingCustomer($callactivityID,
                                         $date,
                                         $time
    )
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callactivityID);
        $dbeCallActivity->setValue(
            DBEJCallActivity::awaitingCustomerResponseFlag,
            'Y'
        );
        $dbeCallActivity->updateRow();

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeCallActivity->getValue(DBEJCallActivity::problemID));
        $dbeProblem->setValue(
            DBEJCallActivity::awaitingCustomerResponseFlag,
            'Y'
        );
        $dbeProblem->updateRow();
        /*
    do we have an alarm time?
    */
        if ($date) {
            $this->setProblemAlarm(
                $dbeCallActivity->getValue(DBEJCallActivity::problemID),
                $date,
                $time
            );
        }
    }

    /**
     * sets alarm
     *
     * @param mixed $problemID
     * @param mixed $date
     * @param mixed $time
     * @return bool
     */
    function setProblemAlarm($problemID,
                             $date,
                             $time
    )
    {
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        if ($date) {
            $dbeProblem->setValue(
                DBEJProblem::alarmDate,
                $date
            );
        }
        if ($time) {
            $dbeProblem->setValue(
                DBEJProblem::alarmTime,
                $time
            );
        }
        return ($dbeProblem->updateRow());
    }

//end completeSRs

    /**
     * This is called from CTActivity when sales order production is skipped
     * and we still want to set the checked activities to authorised.
     * @param $activityIDArray
     * @return bool
     * @throws Exception
     */
    function skipSalesOrdersForActivities(&$activityIDArray)
    {
        $this->setMethodName('skipSalesOrdersForActivities');

        $dbeJCallActivity = new DBEJCallActivity($this);

        /* these come back in customerID order */
        $dbeJCallActivity->getRowsInIdArray($activityIDArray);

        if ($dbeJCallActivity->rowCount() == 0) {
            return FALSE; // no activities so return false
        }

        $dbeCallActivity = new DBECallActivity($this); // for status update


        while ($dbeJCallActivity->fetchNext()) {
            /*
      Set all activities on the parent SR to Authorised status
      */
            $dbeCallActivity->setAllActivitiesToAuthorisedByProblemID(
                $dbeJCallActivity->getValue(DBEJCallActivity::problemID)
            );
            $this->setProblemToCompleted($dbeJCallActivity->getValue(DBEJCallActivity::problemID));
        } // end while($dbeJCallActivity->fetchNext())
        return true;
    }

    /**
     * Set the problem to completed
     *
     *    2. Send an email to the client
     *    3. Change the problem status to "C"
     *
     * @param $problemID
     * @return string
     * @throws Exception
     */
    function setProblemToCompleted($problemID)
    {

        $dbeFirstCallActivity = $this->getFirstActivityInProblem($problemID);

        if ($dbeFirstCallActivity->getValue(DBEJCallActivity::problemStatus) == 'C') {
            return null;
        }

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($dbeFirstCallActivity->getValue(DBEJCallActivity::callActivityID));


        $reason = '<P>Completed</P>';
        $userID = $this->loggedInUserID;
        // create a completion activity
        $dbeCallActivity->setPKValue(null);

        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $problemID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $userID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_RESOLVED_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            date(date('H:i'))
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $reason
        );
        $dbeCallActivity->insertRow();

        $newActivityID = $dbeCallActivity->getPKValue();

        $dbeProblem = new DBEProblem(
            $this,
            $problemID
        );

        $dbeProblem->setValue(
            DBEJProblem::status,
            'C'
        );
        $dbeProblem->setValue(
            DBEJProblem::awaitingCustomerResponseFlag,
            'N'
        );

        $dbeProblem->updateRow();

        $this->sendEmailToCustomer(
            $problemID,
            self::ClosureCustomerEmailCategory
        );

        return $newActivityID;

    }

    /**
     * @param $activityIDArray
     * @return bool
     * @throws Exception
     */
    function createSalesOrdersFromActivities(&$activityIDArray)
    {
        $db = new dbSweetcode(); // database connection for query

        $this->setMethodName('createSalesOrderFromActivities');

        $dbeJCallActivity = new DBEJCallActivity($this);

        $dbeCallActivity = new DBECallActivity($this); // for status update

        $this->dbeProblem = new DBEProblem($this);

        $dbeCallActType = new DBECallActType($this);

        $buCustomer = new BUCustomer($this);

        $activityIDsAsString = implode(
            ',',
            $activityIDArray
        );
        /*
    Get a list of the associated problem id's
    */
        $select =
            "SELECT
        DISTINCT caa_problemno
      FROM
        callactivity
      WHERE
        caa_callactivityno IN( $activityIDsAsString )";

        $db->query($select);
        $problemIDArray = [];
        while ($db->next_record()) {

            $problemIDArray[] = $db->Record['caa_problemno'];
        }
        /*
    Get a list of completed T&M activities for these problems
    */
        $problemIDsAsString = implode(
            ',',
            $problemIDArray
        );

        $select =
            "SELECT
        caa_callactivityno
      FROM
        callactivity
        JOIN problem ON pro_problemno = caa_problemno
      WHERE
        caa_problemno IN( $problemIDsAsString ) and caa_callacttypeno <> 43
        AND pro_contract_cuino = 0";

        $db->query($select);
        $finalActivityIDArray = [];
        while ($db->next_record()) {

            $finalActivityIDArray[] = $db->Record['caa_callactivityno'];
        }

        /*
    Get full activity rows(these come back in customerID, date order)
    */
        $dbeJCallActivity->getRowsInIdArray($finalActivityIDArray);

        if ($dbeJCallActivity->rowCount() == 0) {
            return FALSE; // no activities so return false
        }
        /* need to loop activities checking for change of Request Number(problemID) */

        $buSalesOrder = new BUSalesOrder($this);

        $dbeOrdline = new DBEOrdline($this);

        $ordheadID = false;
        $lastProblemID = false;
        $lastUserID = false;
        $lastDate = false;
        $consultantName = null;
        $sequenceNo = null;

        while ($dbeJCallActivity->fetchNext()) {

            if ($dbeJCallActivity->getValue(DBEJCallActivity::activityTypeCost) == 0) {
                // update status on call activity to Authorised
                $dbeCallActivity->getRow($dbeJCallActivity->getValue(DBEJCallActivity::callActivityID));
                $dbeCallActivity->setValue(
                    DBEJCallActivity::status,
                    'A'
                );

                $dbeCallActivity->updateRow();
                continue;
            }
            $problemID = $dbeJCallActivity->getValue(DBEJCallActivity::problemID);
            $customerID = $dbeJCallActivity->getValue(DBEJCallActivity::customerID);

            if ($problemID != $lastProblemID) {
                /*
        Consolidate sales order lines on previous order by description/rate
        */
                if ($ordheadID) {
                    $buSalesOrder->consolidateSalesOrderLines($ordheadID);
                }

                $buCustomer->getCustomerByID(
                    $customerID,
                    $dsCustomer
                );

                $ordheadID = false;
                /*
        If the SR is linked to an open sales order then we append details to that Order
        */
                if ($dbeJCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID)) {
                    $dsOrdhead = new DataSet($this);
                    $dsOrdline = new DataSet($this);
                    $buSalesOrder->getOrderByOrdheadID(
                        $dbeJCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
                        $dsOrdhead,
                        $dsOrdline
                    );

                    if (!in_array(
                        $dsOrdhead->getValue(DBEOrdhead::type),
                        array('C', 'Q')
                    )) {
                        $ordheadID = $dbeJCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID);
                    }
                }

                if ($ordheadID) {

                    $buSalesOrder->getOrderByOrdheadID(
                        $ordheadID,
                        $dsOrdhead,
                        $dsOrdline
                    );
                    $dsOrdhead->fetchNext();
                    $dbeOrdline->setValue(
                        DBEJOrdline::ordheadID,
                        $ordheadID
                    );
                    $dbeOrdline->getRowsByColumn(
                        DBEJOrdline::ordheadID,
                        DBEJOrdline::sequenceNo
                    );
                    $sequenceNo = $dbeOrdline->rowCount(); // so we paste after the last row
                    $dbeOrdline->resetQueryString();
                } else {

                    $dsOrdhead = new DataSet($this);
                    $dsOrdline = new DataSet($this);
                    $buSalesOrder->initialiseOrder(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsCustomer
                    );
                    $dsOrdhead->setUpdateModeUpdate();
                    $dsOrdhead->setValue(
                        DBEJOrdhead::custPORef,
                        'T & M Service'
                    );
                    $dsOrdhead->setValue(
                        DBEJOrdhead::addItem,
                        'N'
                    );
                    $dsOrdhead->setValue(
                        DBEJOrdhead::partInvoice,
                        'N'
                    );
                    $dsOrdhead->setValue(
                        DBEJOrdhead::paymentTermsID,
                        CONFIG_PAYMENT_TERMS_30_DAYS
                    );
                    $dsOrdhead->post();
                    $buSalesOrder->updateHeader(
                        $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                        $dsOrdhead->getValue(DBEOrdhead::custPORef),
                        $dsOrdhead->getValue(DBEOrdhead::paymentTermsID),
                        $dsOrdhead->getValue(DBEOrdhead::partInvoice),
                        $dsOrdhead->getValue(DBEOrdhead::addItem)
                    );

                    $ordheadID = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
                    /*
          Link SR to new Order
          */
                    $this->dbeProblem->getRow($problemID);
                    $this->dbeProblem->setValue(
                        DBEJProblem::linkedSalesOrderID,
                        $ordheadID
                    );
                    $this->dbeProblem->updateRow();

                    $sequenceNo = 0;
                }

                // Common to all order lines
                $dbeOrdline->setValue(
                    DBEJOrdline::ordheadID,
                    $ordheadID
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::sequenceNo,
                    $sequenceNo
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::customerID,
                    $customerID
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::qtyDespatched,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::qtyLastDespatched,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::supplierID,
                    CONFIG_SALES_STOCK_SUPPLIERID
                );

                // first line is Service Request Number
                $sequenceNo++;
                $dbeOrdline->setValue(
                    DBEJOrdline::lineType,
                    'C'
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::itemID,
                    null
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::stockcat,
                    null
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::sequenceNo,
                    $sequenceNo
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::qtyOrdered,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curUnitCost,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curTotalCost,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curUnitSale,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curTotalSale,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::description,
                    'Service Request ' . $problemID
                );
                $dbeOrdline->insertRow();


                $lastUserID = false;
            } // end if($problemID != $lastProblemID)


            $lastProblemID = $problemID;

            if ($lastUserID != $dbeJCallActivity->getValue(
                    DBEJCallActivity::userID
                ) or $lastDate != $dbeJCallActivity->getValue(
                    DBEJCallActivity::date
                )) {
                $consultantName = $dbeJCallActivity->getValue(DBEJCallActivity::userName);
            }

            $lastUserID = $dbeJCallActivity->getValue(DBEJCallActivity::userID);
            $lastDate = $dbeJCallActivity->getValue(DBEJCallActivity::date);

            $dbeCallActType->getRow($dbeJCallActivity->getValue(DBEJCallActivity::callActTypeID));

            /* mantis 359: Apply maximum travel hours to travel type activities */
            if ($dbeCallActType->getValue(DBEJCallActivity::travelFlag) == 'Y') {
                $dsSite = new DataSet($this);
                $buCustomer->getSiteByCustomerIDSiteNo(
                    $customerID,
                    $dbeJCallActivity->getValue(DBEJCallActivity::siteNo),
                    $dsSite
                );
                $max_hours = $dsSite->getValue(DBESite::maxTravelHours);
            } else {
                // use the max hours field from call activity
                $max_hours = $dbeCallActType->getValue(DBECallActType::maxHours);
            }

            // this function is found in Functions/Activity
            getRatesAndHours(
                $dbeJCallActivity->getValue(DBEJCallActivity::date),
                $dbeJCallActivity->getValue(DBEJCallActivity::startTime),
                $dbeJCallActivity->getValue(DBEJCallActivity::endTime),
                $dbeCallActType->getValue(DBECallActType::minHours),
                $max_hours,
                $dbeCallActType->getValue(DBECallActType::oohMultiplier),
                $dbeCallActType->getValue(DBECallActType::itemID),
                $this->dsHeader,
                $normalHours,
                $beforeHours,
                $afterHours,
                $outOfHoursRate,
                $normalRate
            );

            if ($normalHours > 0) {
                $description = $consultantName . ' - Consultancy';
                $sequenceNo++;
                $dbeOrdline->setValue(
                    DBEJOrdline::lineType,
                    'I'
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::sequenceNo,
                    $sequenceNo
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::stockcat,
                    'G'
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::itemID,
                    CONFIG_CONSULTANCY_DAY_LABOUR_ITEMID
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::qtyOrdered,
                    $normalHours
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curUnitCost,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curTotalCost,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curUnitSale,
                    $normalRate
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curTotalSale,
                    $normalHours * $normalRate
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::description,
                    $description
                );
                $dbeOrdline->insertRow();
            }
            /*
      Out of hours
      */
            if ($beforeHours > 0 OR $afterHours > 0) {
                $description = $consultantName . ' - Consultancy';
                $sequenceNo++;
                $dbeOrdline->setValue(
                    DBEJOrdline::lineType,
                    'I'
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::sequenceNo,
                    $sequenceNo
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::stockcat,
                    'G'
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::itemID,
                    CONFIG_CONSULTANCY_OUT_OF_HOURS_LABOUR_ITEMID
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::qtyOrdered,
                    $beforeHours + $afterHours
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curUnitCost,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curTotalCost,
                    0
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curUnitSale,
                    $outOfHoursRate
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::curTotalSale,
                    ($beforeHours + $afterHours) * $outOfHoursRate
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::description,
                    $description
                );
                $dbeOrdline->insertRow();
            }
            // update status on call activity to Authorised
            $dbeCallActivity->getRow($dbeJCallActivity->getValue(DBEJCallActivity::callActivityID));
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'A'
            );
            $dbeCallActivity->updateRow();


        } // end while($dbeJCallActivity->fetchNext())

        foreach ($problemIDArray as $currentProblemID) {
            $this->setProblemToCompleted($currentProblemID);
        }
        /*
    Consolidate sales order lines on final order by description/rate
    */
        if ($ordheadID) {
            $buSalesOrder->consolidateSalesOrderLines($ordheadID);
        }
        return true;
    } // end check default site contacts exists

    /**
     * @param $activityIDArray
     * @throws Exception
     */
    function completeSRs($activityIDArray)
    {
        $this->setMethodName('completeSRs');

        $dbeCallActivity = new DBECallActivity($this);

        foreach ($activityIDArray as $activityID) {
            $dbeCallActivity->getRow($activityID);
            $this->setProblemToCompleted($dbeCallActivity->getValue(DBEJCallActivity::problemID));

        }
    }

    /*
    work out whether a top-up is required and if so then generate one
    We generate a top-up T&M call so that this can later be amended and/or checked and used to generate a sales
    order for the top-up amount.
    This call will now appear on
  */

    function initialiseExportDataset(&$dsData)
    {
        $this->setMethodName('initialiseExportDataset');
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::exportDataSetEndDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::exportDataSetPreviewRun,
            DA_YN_FLAG,
            DA_ALLOW_NULL
        );
        $dsData->setUpdateModeUpdate();
        $dsData->setValue(
            self::exportDataSetPreviewRun,
            'Y'
        );
        $dsData->post();
    }

    function doTopUp(&$Record,
                     $update = false
    )
    {
        $newBalance = $Record ['curGSCBalance'] + $this->totalCost;
        // generate top-up call and activity if required
        if ($update) {
            $dbeCustomerItem = new DBECustomerItem($this);
            $dbeCustomerItem->getRow($Record ['cui_cuino']);
            $dbeCustomerItem->setValue(
                DBECustomerItem::curGSCBalance,
                $newBalance
            );
            $dbeCustomerItem->updateRow();
        }

        if ($newBalance >= 100) {
            return 0;
        }

        if ($newBalance < 0) {
            // value of the top-up activity is the GSC item price plus amount required to clear balance
            $topUpValue = (0 - $newBalance) + $Record ['gscTopUpAmount'];
        } else {
            $topUpValue = $Record ['gscTopUpAmount']; // just the top-up amount
        }
        //   Create sales order
        if ($update) {
            $this->createTopUpSalesOrder(
                $Record,
                $topUpValue
            );
        }

        return $topUpValue;
    }

    /**
     * @param $Record
     * @param $topUpValue
     * @return bool|float|int|string
     */
    function createTopUpSalesOrder(&$Record,
                                   $topUpValue
    )
    {
        $this->setMethodName('createTopUpSalesOrder');

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID(
            $Record ['custno'],
            $dsCustomer
        );

        $dsOrdhead = new DataSet($this);
        $dbeOrdline = new DataSet($this);
        // create sales order header with correct field values
        $buSalesOrder = new BUSalesOrder($this);

        $buSalesOrder->initialiseOrder(
            $dsOrdhead,
            $dbeOrdline,
            $dsCustomer
        );
        $dsOrdhead->setUpdateModeUpdate();
        $dsOrdhead->setValue(
            DBEOrdhead::custPORef,
            'Top Up'
        );
        $dsOrdhead->setValue(
            DBEOrdhead::addItem,
            'N'
        );
        $dsOrdhead->setValue(
            DBEOrdhead::partInvoice,
            'N'
        );
        $dsOrdhead->setValue(
            DBEOrdhead::paymentTermsID,
            CONFIG_PAYMENT_TERMS_30_DAYS
        );
        $dsOrdhead->post();
        $buSalesOrder->updateHeader(
            $dsOrdhead->getValue(DBEOrdhead::ordheadID),
            $dsOrdhead->getValue(DBEOrdhead::custPORef),
            $dsOrdhead->getValue(DBEOrdhead::paymentTermsID),
            $dsOrdhead->getValue(DBEOrdhead::partInvoice),
            $dsOrdhead->getValue(DBEOrdhead::addItem)
        );

        $ordheadID = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
        $sequenceNo = 1;

        // get topUp item details
        $dbeItem = new DBEItem($this);
        $dbeItem->getRow(CONFIG_DEF_PREPAY_TOPUP_ITEMID);

        // create order line
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue(
            DBEJOrdline::ordheadID,
            $ordheadID
        );
        $dbeOrdline->setValue(
            DBEJOrdline::sequenceNo,
            $sequenceNo
        );
        $dbeOrdline->setValue(
            DBEJOrdline::customerID,
            $Record ['custno']
        );
        $dbeOrdline->setValue(
            DBEJOrdline::qtyDespatched,
            0
        );
        $dbeOrdline->setValue(
            DBEJOrdline::qtyLastDespatched,
            0
        );
        $dbeOrdline->setValue(
            DBEJOrdline::supplierID,
            CONFIG_SALES_STOCK_SUPPLIERID
        );
        $dbeOrdline->setValue(
            DBEJOrdline::lineType,
            'I'
        );
        $dbeOrdline->setValue(
            DBEJOrdline::sequenceNo,
            $sequenceNo
        );
        $dbeOrdline->setValue(
            DBEJOrdline::stockcat,
            'R'
        );
        $dbeOrdline->setValue(
            DBEJOrdline::itemID,
            CONFIG_DEF_PREPAY_TOPUP_ITEMID
        );
        $dbeOrdline->setValue(
            DBEJOrdline::qtyOrdered,
            1
        );
        $dbeOrdline->setValue(
            DBEJOrdline::curUnitCost,
            0
        );
        $dbeOrdline->setValue(
            DBEJOrdline::curTotalCost,
            0
        );
        $dbeOrdline->setValue(
            DBEJOrdline::curUnitSale,
            $topUpValue
        );
        $dbeOrdline->setValue(
            DBEJOrdline::curTotalSale,
            $topUpValue
        );
        $dbeOrdline->setValue(
            DBEJOrdline::description,
            $dbeItem->getValue(DBEItem::description)
        );
        $dbeOrdline->insertRow();
        return $dsOrdhead->getValue(DBEOrdhead::ordheadID);
    }

    /**
     * @param $Record
     * @param DataSet $dsResults
     * @param DataSet $dsStatementContact
     * @param $newBalance
     * @param $topUpAmount
     * @param $endDate
     */
    function postRowToSummaryFile(&$Record,
                                  &$dsResults,
                                  &$dsStatementContact,
                                  $newBalance,
                                  $topUpAmount,
                                  $endDate
    )
    {
        $contacts = null;
        while ($dsStatementContact->fetchNext()) {
            $contacts .= $dsStatementContact->getValue(DBEContact::firstName) . ' ' . $dsStatementContact->getValue(
                    DBEContact::lastName
                );
        }
        // to CSV file
        fwrite(
            $this->csvSummaryFileHandle,
            '"' . $Record ['cus_name'] . '",' . '"' . $Record ['curGSCBalance'] . '",' . // previous balance
            '"' . common_numberFormat($newBalance) . '",' . // hours
            '"' . common_numberFormat($topUpAmount) . '"' . // value
            "\r\n"
        );
        $webFileLink = 'export/PP_' . substr(
                $Record ['cus_name'],
                0,
                10
            ) . $endDate . '.html';

        $dsResults->setUpdateModeInsert();
        $dsResults->setValue(
            self::exportPrePayActivitiesFormCustomerName,
            $Record ['cus_name']
        );
        $dsResults->setValue(
            self::exportPrePayActivitiesFormPreviousBalance,
            $Record ['curGSCBalance']
        );
        $dsResults->setValue(
            self::exportPrePayActivitiesFormCurrentBalance,
            common_numberFormat($newBalance)
        );
        $dsResults->setValue(
            self::exportPrePayActivitiesFormExpiryDate,
            Controller::dateYMDtoDMY($Record ['cui_expiry_date'])
        );
        $dsResults->setValue(
            self::exportPrePayActivitiesFormTopUp,
            common_numberFormat($topUpAmount)
        );
        $dsResults->setValue(
            self::exportPrePayActivitiesFormContacts,
            $contacts
        );
        $dsResults->setValue(
            self::exportPrePayActivitiesFormContractType,
            $Record ['ity_desc']
        );
        $dsResults->setValue(
            self::exportPrePayActivitiesFormWebFileLink,
            $webFileLink
        );
        $dsResults->post();
    }

    /**
     * @param $statementFilepath
     * @param DataSet $dsContact
     * @param $balance
     * @param $date
     * @param $topUpValue
     */
    function sendGSCStatement($statementFilepath,
                              &$dsContact,
                              $balance,
                              $date,
                              $topUpValue
    )
    {

        $buMail = new BUMail($this);

        $id_user = $this->loggedInUserID;
        $this->dbeUser->getRow($id_user);

//        $statementFilename = basename($statementFilepath);

        $senderEmail = CONFIG_SALES_EMAIL;
        //    $buMail->mime_boundary = "----=_NextPart_" . md5(time());
        while ($dsContact->fetchNext()) {
            // Send email with attachment
            $message = '<body><p class=MsoNormal style="font: normal 2px Arial, sans-serif ;color: navy" ><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'Dear ' . $dsContact->getValue(DBEContact::firstName) . ',';
            $message .= '<!--suppress CheckTagEmptyBody --><o:p></o:p></span></p>';
            $message .= '<p class=MsoNormal><span style=\'font-size:10.0pt;color:black\'>';
            // Temporary:
            $message .= 'Please find attached your latest Pre-Pay Contract statement, on which there
is currently a balance of ';
            $message .= '&pound;' . common_numberFormat($balance) . ' + VAT.';
            $message .= '</p>';

            $message .= '<p class=MsoNormal style="font: normal 2px Arial, sans-serif ;color: navy"><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'If you have any queries relating to any of the items detailed on this statement, then please notify us within 7 days so that we can make any adjustments if applicable.';
            $message .= '</p>';

            if ($balance <= 100) {
                $message .= '<p class=MsoNormal"  style="font: normal 2px Arial, sans-serif ;color: navy"><span style=\'font-size:10.0pt;color:black\'>';
                $message .= 'If no response to the contrary is received within 7 days of this statement, then we will automatically raise an invoice for &pound;' . common_numberFormat(
                        $topUpValue * (1 + ($this->standardVatRate / 100))
                    ) . ' Inc VAT.';
                $message .= '</p>';
            }

            $message .= '<p class=MsoNormal style="font: normal 2px Arial, sans-serif ;color: navy"><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'Are you aware that you can receive up to &pound;500 for the referral of any company made to CNC that results in the purchase of a support contract?  Please call us for further information.';
            $message .= '</p>';

            $subject = 'Pre-Pay Contract Statement: ' . Controller::dateYMDtoDMY($date);

            $toEmail = $dsContact->getValue(DBEContact::firstName) . ' ' .
                $dsContact->getValue(DBEContact::lastName) . '<' . $dsContact->getValue(DBEContact::email) . '>';

            $senderName = null;

            // create mime
            $html = '<html lang="en">' . $message . '</html>';
//            $file = '$statementFilename';
//      $crlf = "\n";

            $hdrs = array(
                'From'         => $senderName . " <" . $senderEmail . ">",
                'To'           => $toEmail,
                'Subject'      => $subject,
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($html);
            $buMail->mime->addAttachment(
                $statementFilepath,
                'text/html'
            );
            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $body = $buMail->mime->get($mime_params);
            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
        } // end while

    }

    function postRowToPrePayExportFile(&$csvFileHandle,
                                       $timeFrameFlag,
                                       &$Record,
                                       $hours,
                                       $rate
    )
    {

        $timeFrameDesc = null;
        if ($timeFrameFlag == 'O') {
            $timeFrameDesc = ' - Out of hours';
        }
        if (!$Record['reason']) {
            $details = trim($Record ['cat_desc']);
        } else {
            $details = substr(
                    strip_tags($Record ['reason']),
                    0,
                    80
                ) . "  " . trim($timeFrameDesc);
            $details = str_replace(
                "\r\n",
                "",
                $details
            );
            $details = str_replace(
                "\"",
                "",
                $details
            );
        }

        if ($timeFrameFlag == 'M') { // Monetary value is treated as-is. e.g. Top-up should be a positive value
            $value = $hours * $rate;
        } else {
            $value = 0 - ($hours * $rate);
        }
        $contacts = trim($Record ['cns_name']) . '/' . trim($Record ['con_first_name']) . ' ' . trim(
                $Record ['con_last_name']
            );

        // to CSV file
        fwrite(
            $csvFileHandle,
            '"' . $Record ['cus_name'] . '",' . '"' . $Record ['activityDate'] . '",' . '"' . $Record ['add_postcode'] . '",' . '"' . $Record ['caa_callactivityno'] . '",' . '"' . $details . '",' . '"' . $contacts . '",' . '"' . trim(
                $Record ['cat_desc']
            ) . '",' . // type
            '"' . common_numberFormat($hours) . '",' . // hours
            '"",' . // empty string
            '"' . common_numberFormat($value) . '"' . // value
            "\r\n"
        );

        $postcode = $Record ['add_postcode'];
        $activityRef = $Record ['caa_callactivityno'];
        $hours = common_numberFormat($hours);
        if ($timeFrameFlag == 'M') { // Monetary value like topUp
            $contacts = null;
            $postcode = null;
            $activityRef = null;
            $hours = null;
        }

        // don't display zero values
        $displayValue = null;
        if ($value != 0) {
            $displayValue = common_numberFormat($value);
        }

        $this->template->set_var(
            array(
                'activityDate'     => $Record ['activityDate'],
                'activityPostcode' => $postcode,
                'activityRef'      => $activityRef,
                'activityDetails'  => trim($details),
                'activityContact'  => $contacts,
                'activityType'     => trim($Record ['cat_desc']),
                'activityHours'    => $hours,
                'activityCost'     => $displayValue
            )
        );

        $this->template->parse(
            'lines',
            'lineBlock',
            true
        );

        $this->totalCost += $value;
    }

    function createTopUpActivity($customerID,
                                 $value,
                                 $invoiceID
    )
    {

        $reason = 'Top-up - Invoice No ' . $invoiceID;

        $callActivityID = $this->createActivityFromCustomerID(
            $customerID,
            false,
            'C'
        );

        $dbeCustomerItem = new DBECustomerItem($this);
        if ($dbeCustomerItem->getGSCRow($customerID)) {
            // set fields to topUp
            $dbeCallActivity = new DBECallActivity($this);
            $dbeCallActivity->getRow($callActivityID);
            $dbeCallActivity->setValue(
                DBEJCallActivity::callActTypeID,
                CONFIG_TOPUP_ACTIVITY_TYPE_ID
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::startTime,
                '12:00'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::endTime,
                '12:00'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'C'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::reason,
                $reason
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::curValue,
                $value
            );
            $dbeCallActivity->updateRow();
            /*
      Set contract to prepay
      */
            $this->dbeProblem = new DBEProblem($this);
            $this->dbeProblem->getRow($dbeCallActivity->getValue(DBEJCallActivity::problemID));
            $this->dbeProblem->setValue(
                DBEJProblem::contractCustomerItemID,
                $dbeCustomerItem->getPKValue()
            );
            $this->dbeProblem->updateRow();

        } else {
            $this->raiseError('No Pre-pay Contract Found');
            return FALSE;
        }
        return true;
    }

    function createActivityFromCustomerID(
        $customerID,
        $userID = false,
        $problemStatus = 'I',
        $contractCustomerItemID = false
    )
    {

        if (!$userID) {
            $userID = ( string )$GLOBALS['auth']->is_authenticated();
        }

        $buCustomer = new BUCustomer($this);

        $dsCustomer = new DataSet($this);

        $buCustomer->getCustomerByID(
            $customerID,
            $dsCustomer
        );
        $buSite = new BUSite($this);

        $dsSite = new DataSet($this);
        $buSite->getSiteByID(
            $customerID,
            $dsCustomer->getValue(DBECustomer::deliverSiteNo),
            $dsSite
        );

        // create new problem here
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(
            DBEJProblem::customerID,
            $customerID
        );
        $dbeProblem->setValue(
            DBEJProblem::status,
            $problemStatus
        );
        $dbeProblem->setValue(
            DBEJProblem::priority,
            4
        );
        $dbeProblem->setValue(
            DBEJProblem::dateRaised,
            date(DATE_MYSQL_DATETIME)
        ); // default
        $dbeProblem->setValue(
            DBEJProblem::contractCustomerItemID,
            $contractCustomerItemID
        );
        $dbeProblem->insertRow();

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            0
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $dsSite->getValue(DBESite::siteNo)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $dsSite->getValue(DBESite::invoiceContactID)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            1
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'O'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $userID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::curValue,
            0.00
        );

        $dbeCallActivity->insertRow();

        $callActivityID = $dbeCallActivity->getPKValue();

        return $callActivityID;
    }

    /**
     * @param $sessionKey
     * @return DataSet
     * @throws Exception
     */
    function createActivityFromSession($sessionKey)
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dsCallActivity = new DataSet($this);
        $dsCallActivity->copyColumnsFrom($dbeCallActivity);
        $dsCallActivity->setNull(DBECallActivity::callActivityID, DA_ALLOW_NULL);

        $dateTimeRaised = $_SESSION [$sessionKey] ['dateRaised'] . ' ' . $_SESSION [$sessionKey] ['timeRaised'] . ':00';

        $slaResponseHours = $this->getSlaResponseHours(
            $_SESSION [$sessionKey] ['priority'],
            $_SESSION [$sessionKey] ['customerID'],
            $_SESSION [$sessionKey] ['contactID']
        );

        /*
    * Create a new problem
    */
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $this->dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $this->dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::imLimitMinutes,
            $this->dsHeader->getValue(DBEHeader::imTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::customerID,
            @$_SESSION [$sessionKey] ['customerID']
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            $dateTimeRaised
        );
        $userID = null;

        if (@$_SESSION[$sessionKey]['userID']) {
            $userID = @$_SESSION[$sessionKey]['userID'];
        }

        $dbeProblem->setValue(
            DBEProblem::userID,
            $userID
        );
        $dbeProblem->setValue(
            DBEProblem::rootCauseID,
            @$_SESSION [$sessionKey] ['rootCauseID']
        );

        $dbeProblem->setValue(
            DBEProblem::authorisedBy,
            @$_SESSION[$sessionKey]['authorisedBy']
        );

        $dbeProblem->setValue(
            DBEProblem::status,
            'I'
        );
        $dbeProblem->setValue(
            DBEProblem::slaResponseHours,
            $slaResponseHours
        );

        $queueNo = 1;
        if (isset($_SESSION[$sessionKey]['queueNo'])) {
            $queueNo = $_SESSION[$sessionKey]['queueNo'];
        }

        $dbeProblem->setValue(
            DBEProblem::queueNo,
            $queueNo
        ); // initial queue number
        $dbeProblem->setValue(
            DBEProblem::priority,
            @$_SESSION [$sessionKey] ['priority']
        );
        $dbeProblem->setValue(
            DBEProblem::hideFromCustomerFlag,
            @$_SESSION [$sessionKey] ['hideFromCustomerFlag']
        );
        $dbeProblem->setValue(
            DBEProblem::internalNotes,
            @$_SESSION [$sessionKey] ['internalNotes']
        );
        $dbeProblem->setValue(
            DBEProblem::contactID,
            @$_SESSION [$sessionKey] ['contactID']
        );
        $dbeProblem->setValue(
            DBEProblem::contractCustomerItemID,
            @$_SESSION [$sessionKey] ['contractCustomerItemID']
        );
        $dbeProblem->setValue(
            DBEProblem::projectID,
            @$_SESSION [$sessionKey] ['projectID']
        );
        $dbeProblem->insertRow();

        $endTime = $this->getEndtime(
            @$_SESSION [$sessionKey] ['callActTypeID'],
            @$_SESSION [$sessionKey] ['timeRaised']
        );

        $dsCallActivity->setUpdateModeInsert();
        $dsCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            0
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::siteNo,
            @$_SESSION [$sessionKey] ['siteNo']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::contactID,
            @$_SESSION [$sessionKey] ['contactID']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            @$_SESSION [$sessionKey] ['callActTypeID']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::date,
            @$_SESSION [$sessionKey] ['dateRaised']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::startTime,
            @$_SESSION [$sessionKey] ['timeRaised']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::endTime,
            $endTime
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        ); // Checked
        $dsCallActivity->setValue(
            DBEJCallActivity::expenseExportFlag,
            'N'
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::reason,
            @$_SESSION [$sessionKey] ['reason']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            @$_SESSION [$sessionKey] ['serverGuard']
        );

        $dsCallActivity->setValue(
            DBEJCallActivity::curValue,
            @$_SESSION [$sessionKey] ['curValue']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::statementYearMonth,
            null
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::customerItemID,
            null
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::authorisedFlag,
            'Y'
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::userID,
            $GLOBALS['auth']->is_authenticated()
        ); // user that created activity
        $dsCallActivity->post();
        $dbeContact = null;
        if (@$_SESSION[$sessionKey]['contactID']) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($_SESSION[$sessionKey]['contactID']);
        }

        $this->updateDataAccessObject(
            $dsCallActivity,
            $dbeCallActivity
        ); // Update the DB
        if ($dbeProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'N') {       // skip work commenced

            if ($dbeProblem->getValue(DBEJProblem::priority) == 5) {
                $fields['submittedTo'] = 'Project Team';
            } else {
                $fields['submittedTo'] = 'Service Desk';
            }
            $this->sendEmailToCustomer(
                $dbeProblem->getPKValue(),
                self::InitialCustomerEmailCategory
            );
        }


        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            @$_SESSION[$sessionKey]['customerID'],
            $dsCustomer
        );

        if (($dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
                $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date(
                    'Y-m-d'
                )) || ($dbeContact && $dbeContact->getValue(DBEContact::specialAttentionContactFlag) == 'Y')) {
            $this->sendSpecialAttentionEmail($dbeCallActivity->getPKValue());
        }

        unset($_SESSION[$sessionKey]);

        return $dsCallActivity;
    }

    /**
     * Calculate end time from start time for special types of activity
     *
     * @param mixed $callActTypeID
     * @param mixed $startTime Optional. If false then use current time
     * @return string
     * @throws Exception
     */
    function getEndtime($callActTypeID,
                        $startTime = false
    )
    {
        if (!$startTime) {
            $startTime = null; // use time now
        }

        switch ($callActTypeID) {

            case CONFIG_INITIAL_ACTIVITY_TYPE_ID:
                $minutesToAdd = 5;
                break;

            case CONFIG_FIXED_ACTIVITY_TYPE_ID:
                $minutesToAdd = 3;
                break;

            case CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID:
                $minutesToAdd = 7;
                break;

            default:
                $minutesToAdd = 0;
                break;

        }
        $date = new DateTime($startTime);
        $date->modify('+' . $minutesToAdd . ' minutes');
        return $date->format('H:i');
    }

    function sendServiceReAddedEmail($newProblemID,
                                     $oldProblemID
    )
    {

        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($newProblemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $toEmail = false; // sdManager only

//        $activityRef = $newProblemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'ServiceReAddedEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($newProblemID);

        $subject = 'Similar activity added for ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $template->setVar(
            array(

                'newProblemID' => $newProblemID,
                'oldProblemID' => $oldProblemID,
                'urlActivity'  => $urlActivity,
                'customerName' => $dbeJProblem->getValue(DBEJProblem::customerName),
                'CONFIG_SERVICE_REQUEST_DESC'
                               => CONFIG_SERVICE_REQUEST_DESC

            )
        );


        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    function sendUncheckedActivityEmail()
    {
        $buMail = new BUMail($this);

        $this->setMethodName('sendUncheckedActivityEmail');

        $this->dbeCallActivitySearch->getRowsBySearchCriteria(
            null,
            null,
            null,
            'UC',
            null,
            null,
            date('Y-m-d'),
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            'N'
        );

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $toEmail = false;

        ob_start();
        ?>
        <html lang="en">

        <body style="font-family: arial, helvetica, sans-serif; font-size: 10pt">
        <p>Unchecked Support Activities</p>

        <TABLE bordercolor="#0000FF">
            <TR>
                <TD style="background-color:#CCCCCC"><strong> Ref </strong></TD>
                <TD style="background-color:#CCCCCC"><strong> Customer </strong></TD>
                <TD style="background-color:#CCCCCC"><strong> Details </strong></TD>
                <TD style="background-color:#CCCCCC"><strong> Engineer </strong></TD>
                <TD style="background-color:#CCCCCC"><strong> Date </strong></TD>
            </TR>
            <?php
            while ($this->dbeCallActivitySearch->fetchNext()) {
                ?>
                <TR>
                    <TD nowrap
                        style="background-color:#E0DFE3"
                    ><A
                                href="http://<?php
                                echo $_SERVER ['HTTP_HOST'] ?>/Activity.php?action=displayActivity&callActivityID=<?php
                                echo $this->dbeCallActivitySearch->getPKValue();
                                ?>"
                        ><?php
                            echo $this->dbeCallActivitySearch->getPKValue();
                            ?></A>
                    </TD>
                    <TD nowrap
                        style="background-color:#E0DFE3"
                    >
                        <?php
                        echo $this->dbeCallActivitySearch->getValue(DBEJCallActivity::customerName) ?>        </TD>
                    <TD nowrap
                        style="background-color:#E0DFE3"
                    >
                        <?php
                        echo $this->dbeCallActivitySearch->getValue(DBEJCallActivity::userName) ?>        </TD>
                    <TD nowrap
                        style="background-color:#E0DFE3"
                    >
                        <?php
                        echo Controller::dateYMDtoDMY(
                            $this->dbeCallActivitySearch->getValue(DBEJCallActivity::date)
                        ) ?>        </TD>
                </TR>
                <?php
            }
            ?>
        </TABLE>

        </body>
        </html>
        <?php
        $message = ob_get_contents();
        ob_end_clean();

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Unchecked Activities',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

//        $buCustomer = new BUCustomer($this);

        $buMail->mime->setHTMLBody($message);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        return $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    /**
     * Upload document file
     * NOTE: Only expects one document
     * @param $problemID
     * @param $description
     * @param $userfile
     * @return bool
     */
    function uploadDocumentFile($problemID,
                                $description,
                                &$userfile
    )
    {
        $this->setMethodName('uploadDocumentFile');
        if (!$problemID) {
            $this->raiseError('problemID not passed');
        }
        if (!$description) {
            $this->raiseError('description not passed');
        }

        return $this->addDocument(
            $problemID,
            $userfile ['tmp_name'],
            $userfile ['size'],
            $description,
            $userfile ['name'],
            $userfile ['type']
        );
    }

    function addDocument($problemID,
                         $filePath,
                         $fileSizeBytes,
                         $description,
                         $fileName,
                         $mimeType
    )
    {
        $dbeCallDocument = new DBECallDocument($this);
        $dbeCallDocument->setPKValue(null);
        $dbeCallDocument->setValue(
            DBEJCallDocument::problemID,
            $problemID
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::file,
            fread(
                fopen(
                    $filePath,
                    'rb'
                ),
                $fileSizeBytes
            )
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::description,
            ( string )$description
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::filename,
            ( string )$fileName
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::fileLength,
            ( int )$fileSizeBytes
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::createUserID,
            ( string )$this->loggedInUserID
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::createDate,
            date(DATE_MYSQL_DATETIME)
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::fileMIMEType,
            ( string )$mimeType
        );

        return ($dbeCallDocument->insertRow());
    }

    /*
  get first last next and previous activities in this chain
  */

    function createPrepayAdjustment($customerID,
                                    $value,
                                    $date
    )
    {

        $dbeCustomerItem = new DBECustomerItem($this);

        if ($dbeCustomerItem->getGSCRow($customerID)) {
            $reason = 'Prepay Adjustment';

            $callActivityID = $this->createActivityFromCustomerID(
                $customerID,
                false,
                'C',
                $dbeCustomerItem->getValue(DBECustomerItem::customerItemID)
            );

            $dbeCallActivity = new DBECallActivity($this);
            $dbeCallActivity->getRow($callActivityID);
            $dbeCallActivity->setValue(
                DBEJCallActivity::callActTypeID,
                CONFIG_CONTRACT_ADJUSTMENT_ACTIVITY_TYPE_ID
            );

            $dbeCallActivity->setValue(
                DBEJCallActivity::date,
                $date
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::startTime,
                '12:00'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::endTime,
                '12:00'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'C'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::reason,
                $reason
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::curValue,
                $value
            );
            $dbeCallActivity->updateRow();

        } else {
            $this->raiseError('No Pre-pay Contract Found');
            return FALSE;
        }
        return true;
    }

    function initialiseCustomerActivityForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::customerActivityFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::customerActivityFormUserID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::customerActivityFormContractType,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::customerActivityFormCustomerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::customerActivityFormFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::customerActivityFormToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::customerActivityFormCustomerID,
            null
        );
        $dsData->setValue(
            self::customerActivityFormUserID,
            null
        );
        $dsData->setValue(
            self::customerActivityFormCustomerName,
            null
        );
    }

    /**
     * @param $callActivityID
     * @param DataSet $dsCallActivity
     * @param bool $includeTravel
     * @param bool $includeOperationalTasks
     * @param bool $includeServerGuardUpdates
     * @return bool
     */
    function getNavigateLinks(
        $callActivityID,
        &$dsCallActivity,
        $includeTravel = false,
        $includeOperationalTasks = false,
        $includeServerGuardUpdates = false
    )
    {
        $navigateLinksArray = false;

        $dbeCallActivity = new DBEJCallActivity($this);
        $dbeCallActivity->getRow($callActivityID);


        $problemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);

        if (!$problemID) {

            $this->raiseError('No problemID for activityID ' . $callActivityID);

        }

        $dbeCallActivity->getRowsByProblemID(
            $problemID,
            $includeTravel,
            $includeOperationalTasks,
            false,
            false,
            $includeServerGuardUpdates
        );

        $this->getData(
            $dbeCallActivity,
            $dsCallActivity
        );

        $navigateLinksArray ['first'] = false;
        $navigateLinksArray ['last'] = false;
        $navigateLinksArray ['next'] = false;
        $navigateLinksArray ['previous'] = false;

        $lastID = false;

        $followingIDIsNextID = false;

        $rowCount = 0;
        $thisID = null;
        while ($dsCallActivity->fetchNext()) {

            $rowCount++;

            $thisID = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);

            if (!$lastID) { // first activity in set


                $navigateLinksArray ['first'] = $thisID;

            }

            if ($followingIDIsNextID) { // next activity in set


                $navigateLinksArray ['next'] = $thisID;

                $followingIDIsNextID = false;

            }

            if ($thisID == $callActivityID) { // current in set is our activity


                $navigateLinksArray ['thisRowNumber'] = $rowCount;

                if ($lastID) {

                    $navigateLinksArray ['previous'] = $lastID;

                }

                $followingIDIsNextID = true;

            }

            $lastID = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);

        }

        if (!@$navigateLinksArray ['next']) {
            $navigateLinksArray ['last'] = false;
        }

        if ($thisID !== $callActivityID) {
            $navigateLinksArray ['last'] = $thisID;
        }

        if ($callActivityID == @$navigateLinksArray ['first']) {

            $navigateLinksArray ['first'] = false;

        }

        if (@$navigateLinksArray ['next'] == @$navigateLinksArray ['last']) {

            $navigateLinksArray ['next'] = false;

        }

        if (@$navigateLinksArray ['first'] == @$navigateLinksArray ['previous']) {

            $navigateLinksArray ['previous'] = false;

        }

        if (!@$navigateLinksArray ['thisRowNumber']) {
            $navigateLinksArray ['thisRowNumber'] = 1;
        }

        $dsCallActivity->initialise();

        return $navigateLinksArray;

    }

    function linkActivities($fromCallActivityID,
                            $toCallActivityID,
                            $wholeProblem = TRUE
    )
    {

        $dbeCallActivity = new DBECallActivity($this);

        $fromProblemID = null;
        $toProblemID = null;

        if (!$dbeCallActivity->getRow($fromCallActivityID)) {
            $this->raiseError('link to activity ' . $fromCallActivityID . ' does not exist');
        } else {
            $toProblemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);
        }

        if (!$dbeCallActivity->getRow($toCallActivityID)) {
            $this->raiseError('activity ' . $toCallActivityID . ' does not exist');
        } else {
            $fromProblemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);
        }
        if ($wholeProblem) { // move all the activities in this problem
            $dbeCallActivity->changeProblemID(
                $fromProblemID,
                $toProblemID
            );

        } else { // just the one activity
            $dbeCallActivity->setValue(
                DBEJCallActivity::problemID,
                $toProblemID
            );

            $dbeCallActivity->updateRow();
        }

    }

    function updateTextFields(
        $callActivityID,
        $reason,
        $internalNotes
    )
    {
        $dbeCallActivity = new DBECallActivity($this);

        if (!$dbeCallActivity->getRow($callActivityID)) {

            echo 'Activity ' . $callActivityID . ' not found';
            exit;

        }

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeCallActivity->getValue(DBEJCallActivity::problemID));

        if (
            trim($reason) &&
            $reason <> $dbeCallActivity->getValue(DBEJCallActivity::reason)
        ) {
            $dbeCallActivity->setValue(
                DBEJCallActivity::reason,
                $reason
            );
            $dbeCallActivity->updateRow();


        }

        if (
            trim($internalNotes) &&
            $internalNotes <> $dbeProblem->getValue(DBEJProblem::internalNotes)
        ) {

            $dbeProblem->setValue(
                DBEJProblem::internalNotes,
                $internalNotes
            );
            $dbeProblem->updateRow();

        }

    }

    /**
     * @param DataSet $dsCallActivity
     * @param CTCNC $ctActivity
     * @return bool
     */
    function canEdit($dsCallActivity,
                     $ctActivity
    )
    {
        if ($ctActivity->hasPermissions(PHPLIB_PERM_SUPERVISOR)) {
            return true;
        }
        // status is NOT Authorised AND NOT Checked
        if ($dsCallActivity->getValue(DBEJCallActivity::status) != 'A' && $dsCallActivity->getValue(
                DBEJCallActivity::status
            ) != 'C'
        ) {
            return true;
        }

        return false;

    }

    function finaliseActivity(
        $callActivityID,
        $onSite = false
    )
    {

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeCallActivity->getValue(DBEJCallActivity::problemID));

        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );              // checked for billing

        $dbeCallActivity->updateRow();

        // if this is onSite then the report and email generated when the send time is confirmed
        if ($dbeProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'N' && !$onSite) {
            //TODO: check this ..as the function doesn't exist
//            $this->sendActivityLoggedEmail(
//                $callActivityID,
//                false
//            );
            $test = null;
        }
        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $dbeProblem->getValue(DBEJProblem::customerID),
            $dsCustomer
        );


        $dbeContact = null;
        if ($dbeCallActivity->getValue(DBECallActivity::contactID)) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($dbeCallActivity->getValue(DBECallActivity::contactID));
        }

        if (
            ($dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
                $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d'))
            ||
            (
                $dbeContact && $dbeContact->getValue(DBEContact::specialAttentionContactFlag) == 'Y'
            )
        ) {
            $this->sendSpecialAttentionEmail($dbeCallActivity->getPKValue());
        }

        if ($dbeProblem->getValue(DBEJProblem::criticalFlag) == 'Y') {
            $this->sendCriticalEmail($callActivityID);
        }

        $this->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        return $dsCallActivity;
    }

    /**
     * Allocate an technician to a request, sending an email to the engineer if this request
     * was previously with another technician
     *
     * @param mixed $problemID
     * @param mixed $userID
     * @param $allocatedBy
     */
    function allocateUserToRequest($problemID,
                                   $userID,
                                   $allocatedBy
    )
    {
        if (!$this->dbeProblem) {
            $this->dbeProblem = new DBEProblem($this);
        }

        $this->dbeProblem->getRow($problemID);

        /*
    Send an email to the new person new user is not "unallocated" user
    */
        if ($userID) { // not de-allocating
            $this->sendServiceReallocatedEmail(
                $problemID,
                $userID,
                $allocatedBy
            );
        }

        $this->dbeProblem->setValue(
            DBEJProblem::userID,
            $userID
        );

        $this->dbeProblem->updateRow();
    }

    /**
     * Sends email to new user when service request reallocated
     *
     * @param mixed $problemID
     * @param $newUserID
     * @param $DBUser
     */
    function sendServiceReallocatedEmail($problemID,
                                         $newUserID,
                                         DBEUser $DBUser
    )
    {

        if ($newUserID == 0) {
            return;
        }

        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $this->dbeUser->getRow($newUserID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $toEmail = $this->dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'ServiceReallocatedEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);
        $dbeJLastCallActivity = $this->getLastActivityInProblem($problemID);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJLastCallActivity->getPKValue(
            );

        $assignedByUserName = (string)$DBUser->getValue(DBEUser::name);

        $template->setVar(
            array(
                'activityRef'                 => $activityRef,
                'customerName'                => $dbeJProblem->getValue(DBEJProblem::customerName),
                'reason'                      => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'urlActivity'                 => $urlActivity,
                'lastDetails'                 => $dbeJLastCallActivity->getValue(DBEJCallActivity::reason),
                'assignedByUserName'          => $assignedByUserName,
                'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $subject = CONFIG_SERVICE_REQUEST_DESC . ' ' . $activityRef . ' allocated to you by ' . $assignedByUserName;

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    function getOpenProblemByContactID($contactID,
                                       &$dsResults
    )
    {
        $dbeJProblem = new DBEJProblem($this);

        $dbeJProblem->getOpenRowsByContactID($contactID);

        $this->getData(
            $dbeJProblem,
            $dsResults
        );
    }

    /**
     * Get problems by status
     *
     * @param mixed $status
     * @param mixed $dsResults
     * @param bool $includeAutomaticallyFixed
     */
    function getProblemsByStatus($status,
                                 &$dsResults,
                                 $includeAutomaticallyFixed = true
    )
    {
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRowsByStatus(
            $status,
            $includeAutomaticallyFixed
        );
        $this->getData(
            $dbeJProblem,
            $dsResults
        );

    }

    /**
     * Get future dated SRs
     *
     * @param mixed $dsResults
     */
    function getFutureProblems(&$dsResults)
    {
        $dbeJProblem = new DBEJProblem($this);

        $dbeJProblem->getFutureRows();

        $this->getData(
            $dbeJProblem,
            $dsResults
        );

    }

    /**
     * @param $queueNo
     * @param DataSet $dsResults
     */
    function getProblemsByQueueNo($queueNo,
                                  &$dsResults
    )
    {
        $dbeJProblem = new DBEJProblem($this);


        $dbeJProblem->getRowsByQueueNo(
            $queueNo,
            true
        ); // unassigned first
        $this->getData(
            $dbeJProblem,
            $dsResults
        );

        $dsResults->sortAscending(
            'dashboardSortColumn'
        );
        $dbeJProblem->getRowsByQueueNo($queueNo);       // then assigned

        $dsAssignedResults = new DataSet($this);
        $this->getData(
            $dbeJProblem,
            $dsAssignedResults
        );

        $dsAssignedResults->sortAscending(
            'dashboardSortColumn'
        );

        $dsResults->setClearRowsBeforeReplicateOff();

        $dsResults->replicate($dsAssignedResults);
    }

    /**
     * Get active problems by customer
     *
     * @param mixed $customerID
     * @return mixed $dsResults
     */
    function getActiveProblemsByCustomer($customerID)
    {
        $dbeJProblem = new DBEJProblem($this);

        $dbeJProblem->getActiveProblemsByCustomer($customerID);

        $this->getData(
            $dbeJProblem,
            $dsResults
        );

        return $dsResults;

    }

    function toggleCriticalFlag($problemID)
    {
        if (!$this->dbeProblem) {
            $this->dbeProblem = new DBEProblem($this);
        }

        $this->dbeProblem->getRow($problemID);

        if ($this->dbeProblem->getValue(DBEJProblem::criticalFlag) == 'Y') {
            $this->dbeProblem->setValue(
                DBEJProblem::criticalFlag,
                'N'
            );
        } else {
            $this->dbeProblem->setValue(
                DBEJProblem::criticalFlag,
                'Y'
            );
        }
        $this->dbeProblem->updateRow();

    } // end email to customer

    /**
     * Create copy of this activity but with:
     *    start time now and end time not set
     *   User = current user
     *    date = today
     *    reason = finalStatus( from old activity )
     *    Status = not completed
     *
     * $moveToUsersQueue: Whether to move the SR to the logged in user's queue
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */

    /**
     * @param $ordheadID
     * @param DataSet $dsInput
     * @param bool|int $selectedOrderLine
     * @return string
     * @throws Exception
     */
    function createSalesServiceRequest($ordheadID,
                                       $dsInput,
                                       $selectedOrderLine = false
    )
    {
        $buSalesOrder = new BUSalesOrder($this);

        $dbeItem = new DBEItem($this);
        $dbeItemType = new DBEItemType($this);
        $dsOrdhead = new DataSet($this);
        $dsOrdline = new DataSet($this);
        $buSalesOrder->getOrderByOrdheadID(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline
        );


        $dateRaised = date(DATE_MYSQL_DATE . ' ' . DATE_MYSQL_TIME);
        $timeRaised = date(CONFIG_MYSQL_TIME_HOURS_MINUTES);

        $internalNotes = '<P>' . str_replace(
                "\r\n",
                "",
                $dsInput->getValue(BURenContract::serviceRequestText)
            ) . '</P>';

        if ($dsInput->getValue(BURenContract::etaDate)) {
            $internalNotes .=
                '<P>ETA: ' . Controller::dateYMDtoDMY($dsInput->getValue(BURenContract::etaDate)) . '</P><BR/>';
        } else {
            $internalNotes .=
                '<P>ETA: TBA</P><BR/>';
        }

        /*
    Determine whether delivery is direct or via CNC and set a note accordingly
    */
        $dbePorhead = new DBEPorhead($this);
        $dbePorhead->setValue(
            DBEJPorhead::ordheadID,
            $ordheadID
        );

        if ($dbePorhead->countRowsByColumn(DBEJPorhead::ordheadID)) {

            $dbePorhead->setValue(
                DBEJPorhead::ordheadID,
                $ordheadID
            );

            $dbePorhead->getRowsByColumn(DBEJPorhead::ordheadID);

            $directDelivery = false;
            while ($dbePorhead->fetchNext()) {
                if ($dbePorhead->getValue(DBEPorhead::directDeliveryFlag) == 'Y') {
                    $directDelivery = true;
                }
            }

            if ($directDelivery) {
                $internalNotes .= '<P>Delivery is direct to site</P><BR/>';
            } else {
                $internalNotes .= '<P>Delivery is to CNC</P>';
            }
        }

        $slaResponseHours =
            $this->getSlaResponseHours(
                $dsInput->getValue(BURenContract::serviceRequestPriority),
                $dsOrdhead->getValue(DBEOrdhead::customerID),
                $dsOrdhead->getValue(DBEOrdhead::delContactID)
            );

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(
            DBEJProblem::customerID,
            $dsOrdhead->getValue(DBEOrdhead::customerID)
        );
        $dbeProblem->setValue(
            DBEJProblem::dateRaised,
            $dateRaised
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            null
        );
        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            3
        );      //Managers
        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            null
        );
        $dbeProblem->setValue(
            DBEJProblem::status,
            'I'
        );
        $dbeProblem->setValue(
            DBEJProblem::slaResponseHours,
            $slaResponseHours
        );
        $dbeProblem->setValue(
            DBEJProblem::priority,
            $dsInput->getValue(BURenContract::serviceRequestPriority)
        );
        $dbeProblem->setValue(
            DBEJProblem::hideFromCustomerFlag,
            'N'
        );
        $dbeProblem->setValue(
            DBEJProblem::contactID,
            $dsOrdhead->getValue(DBEOrdhead::delContactID)
        );
        $dbeProblem->setValue(
            DBEJProblem::contractCustomerItemID,
            $dsInput->getValue(BURenContract::serviceRequestCustomerItemID)
        );
        $dbeProblem->setValue(
            DBEJProblem::internalNotes,
            $internalNotes
        );
        $dbeProblem->setValue(
            DBEJProblem::linkedSalesOrderID,
            $ordheadID
        );

        if ($dsInput->getValue(BURenContract::serviceRequestPriority) == 5) {
            $buHeader = new BUHeader($this);
            $dsHeader = new DataSet($this);
            $buHeader->getHeader($dsHeader);
            $dbeProblem->setValue(DBEProblem::imLimitMinutes, $dsHeader->getValue(DBEHeader::imTeamLimitMinutes));
            $dsOrdlineBudget = new DataSet($this);
            $buSalesOrder->getOrderByOrdheadID(
                $ordheadID,
                $dsOrdHead,
                $dsOrdlineBudget
            );

            $minutesInADay = $dsHeader->getValue(DBEHeader::ImplementationTeamMinutesInADay);

            $normalMinutes = 0;
            while ($dsOrdlineBudget->fetchNext()) {

                if ($dsOrdlineBudget->getValue(DBEOrdline::lineType) == 'I') {
                    switch ($dsOrdlineBudget->getValue(DBEOrdline::itemID)) {
                        case CTProject::DAILY_LABOUR_CHARGE:
                        case CTProject::DAILY_OOH_LABOUR_CHARGE:
                            $normalMinutes += ((float)$dsOrdlineBudget->getValue(
                                    DBEOrdline::qtyOrdered
                                )) * $minutesInADay;
                            break;
                        case CTProject::HOURLY_LABOUR_CHARGE:
                        case CTProject::HOURLY_OOH_LABOUR_CHARGE:
                            $normalMinutes += ((float)$dsOrdlineBudget->getValue(DBEOrdline::qtyOrdered)) * 60;
                            break;
                    }
                }
            }

            if ($normalMinutes > 0) {
                $dbeProblem->setValue(DBEProblem::imLimitMinutes, $normalMinutes);
            }
        }

        $dbeProblem->insertRow();
        $reason = null;

        /* Use type of first SO line as first line of reason */
        while ($dsOrdline->fetchNext()) {

            if ($dsOrdline->getValue(DBEOrdline::itemID) &&
                (!is_array($selectedOrderLine) ||
                    in_array(
                        $dsOrdline->getValue(DBEOrdline::sequenceNo),
                        $selectedOrderLine
                    )
                )
            ) {
                $dbeItem->getRow($dsOrdline->getValue(DBEOrdline::itemID));
                $dbeItemType->getRow($dbeItem->getValue(DBEItem::itemTypeID));
                $reason = '<P>' . $dbeItemType->getValue(DBEItemType::description) . '</P><BR/>';
            }
        }
        // insert selected items
        $reason .= '<table>';

        $reason .= '<tr><td><strong>Qty</strong></td><td><strong>Item</strong></td></tr>';

        $dsOrdline->initialise();

        while ($dsOrdline->fetchNext()) {

            if (

                !$selectedOrderLine OR

                ($selectedOrderLine &&
                    in_array(
                        $dsOrdline->getValue(DBEOrdline::sequenceNo),
                        $selectedOrderLine
                    )
                )
            ) {

                $reason .= '<tr><td>';

                if ($dsOrdline->getValue(DBEOrdline::lineType) == 'I') {
                    $reason .= $dsOrdline->getValue(DBEOrdline::qtyOrdered);
                } else {
                    $reason .= '&nbsp;';
                }


                $reason .= '</td><td>' . $dsOrdline->getValue(DBEOrdline::description) . '</td></tr>';
            }

        } // end while

        $reason .= '</table>';

        $dbeCallActivity = new DBECallActivity($this);

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $dsOrdhead->getValue(DBEOrdhead::delSiteNo)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $dsOrdhead->getValue(DBEOrdhead::delContactID)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            $dateRaised
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            $timeRaised
        );

        $endTime = $this->getEndtime(CONFIG_INITIAL_ACTIVITY_TYPE_ID);

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $endTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::expenseExportFlag,
            'N'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $reason
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            'N'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::curValue,
            0
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::statementYearMonth,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::customerItemID,
            null
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::underContractFlag,
            'N'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::authorisedFlag,
            'Y'
        );
        if (isset($GLOBALS['auth'])) {
            $dbeCallActivity->setValue(
                DBEJCallActivity::userID,
                $GLOBALS['auth']->is_authenticated()
            ); // user that created activity
        } else {
            $dbeCallActivity->setValue(
                DBEJCallActivity::userID,
                USER_SYSTEM
            );
        }
        //$dbeCallActivity->setValue( 'overtimeExportedFlag', 'N' );

        $dbeCallActivity->insertRow();

        $db = new dbSweetcode(); // database connection for query

        $sql =
            "UPDATE
      ordhead
    SET
      odh_service_request_text = null,
      odh_service_request_custitemno = 0
    WHERE
      odh_ordno = " . $ordheadID;

        $db->query($sql);

        $ret = $dbeProblem->getPKValue();
        /*
    Email to AC
    */
        $this->sendSalesRequestAlertEmail($ret);

        return $ret;

    }

    function sendSalesRequestAlertEmail($problemID)
    {

        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);


        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $this->dbeUser->getRow();
        $toEmail = 'newproject' . '@' . CONFIG_PUBLIC_DOMAIN;

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'SalesRequestAlertEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $projectURL = 'http://' . $_SERVER ['HTTP_HOST'] . '/Project.php?action=add&customerID=' . $dbeJCallActivity->getValue(
                DBEJCallActivity::customerID
            );

        $createProjectLink = "<a href='" . $projectURL . "'>Click here to create a project for this request</a>";

        $template->setVar(
            array(
                'activityRef'       => $activityRef,
                'urlActivity'       => $urlActivity,
                'customerName'      => $dbeJProblem->getValue(DBEJProblem::customerName),
                'reason'            => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'createProjectLink' => $createProjectLink,
                'internalNotes'     =>
                    $dbeJCallActivity->getValue(
                        DBEJCallActivity::internalNotes
                    ),
                'CONFIG_SERVICE_REQUEST_DESC'
                                    => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'New Project Incident ' . $problemID . ' Created for ' . $dbeJProblem->getValue(
                    DBEJProblem::customerName
                ),
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    function getOnSiteActivitiesWithinFiveDaysOfActivity($callActivityID)
    {
        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivityID);

        $activityDate = $dbeJCallActivity->getValue(DBEJCallActivity::date);
        $customerID = $dbeJCallActivity->getValue(DBEJCallActivity::customerID);

        $db = new dbSweetcode(); // database connection for query

        $queryString = "
      SELECT
        caa_callactivityno,
        caa_problemno,
        DATE_FORMAT(caa_date, '%d/%m/%Y') as formattedDate,
        cns_name
      FROM
        callactivity USE INDEX( date_callacttypeno )
        JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
        JOIN consultant ON caa_consno = cns_consno
        JOIN problem ON pro_problemno = caa_problemno
      WHERE
        pro_custno = $customerID
        AND cat_on_site_flag = 'Y'
        AND caa_date BETWEEN DATE_SUB( '$activityDate', INTERVAL 7 DAY ) AND DATE_ADD( '$activityDate', INTERVAL 7 DAY )
        AND caa_callactivityno <> $callActivityID ";

        $db->query($queryString);

        return $db;

    } // end email to customer

    function getCustomerRaisedRequests()
    {
        $db = new dbSweetcode(); // database connection for query

        $queryString = "
      SELECT
        cpr_customerproblemno,
        cpr_date,
        cpr_contno,
        cpr_priority,
        cpr_reason,
        cpr_source,
        cpr_problemno,
        cpr_update_existing_request,
        cus_name,
        con_custno,
        con_first_name,
        con_last_name
      FROM
        customerproblem
        LEFT JOIN contact ON con_contno = cpr_contno
        LEFT JOIN customer ON cus_custno = con_custno
      ORDER BY
        cus_name, cpr_reason";

        $db->query($queryString);

        return $db;

    }

    /**
     * Gets one customer raised request
     * @param $customerproblemno
     * @return array
     */
    function getCustomerRaisedRequest($customerproblemno)
    {
        $db = new dbSweetcode(); // database connection for query

        $queryString = "
      SELECT
        cpr_customerproblemno,
        cpr_date,
        cpr_contno,
        cpr_priority,
        cpr_reason,
        cpr_internal_notes,
        cpr_serverguard_flag,
        cpr_send_email,
        cpr_siteno,
        cpr_problemno,
        cus_name,
        con_custno,
        con_siteno,
        con_phone,
        con_first_name,
        con_last_name,
        add_add1,
        add_add2,
        add_add3,
        add_postcode,
        con_notes,
        cus_tech_notes


      FROM
        customerproblem
        LEFT JOIN contact ON con_contno = cpr_contno
        LEFT JOIN address ON add_siteno = con_siteno AND add_custno = con_custno
        LEFT JOIN customer ON cus_custno = con_custno

      WHERE
        cpr_customerproblemno = $customerproblemno
        ";

        $db->query($queryString);

        $db->next_record();

        return $db->Record;

    }

    /**
     * Delete one customer raised request
     * @param $customerproblemno
     */
    function deleteCustomerRaisedRequest($customerproblemno)
    {
        $db = new dbSweetcode(); // database connection for query

        $queryString = "
      DELETE FROM
        customerproblem
      WHERE
        cpr_customerproblemno = $customerproblemno
        ";

        $db->query($queryString);
    }

    /**
     * @param dbSweetcode $db
     * @param $table
     * @return int
     */
    function getNextId($db,
                       $table
    )
    {
        $sql =
            "SELECT
            nextid
          FROM
            db_sequence
          WHERE
            seq_name = '$table'";

        $db->query($sql);

        $db->next_record();

        $nextid = $db->Record[0] + 1;

        $sql =
            "UPDATE
            db_sequence
          SET
            nextid = nextid + 1
          WHERE
            seq_name = '$table'";

        $db->query($sql);

        return $nextid;
    }

    /**
     * @param AutomatedRequest $automatedRequest
     * @return bool|mixed
     * @throws Exception
     */
    function processAutomaticRequest(AutomatedRequest $automatedRequest
    )
    {

        $details = $automatedRequest->getTextBody();
        echo '<div>The sender email is ' . $automatedRequest->getSenderEmailAddress() . ' </div>';
        if (!$automatedRequest->getCustomerID()) {
            echo "<div>We couldn't find a customer ID, should log in to be logged</div>";
            $prependMessage = '<div style="color: red">Update from email received from ' . $automatedRequest->getSenderEmailAddress(
                ) . ' on ' . date(
                    DATE_MYSQL_DATETIME
                ) . "</div>";
            return $this->addCustomerRaisedRequest(
                $automatedRequest,
                null,
                null,
                $prependMessage
            );
        }
        echo "<div>We do have a customer ID, we can continue: " . $automatedRequest->getCustomerID() . "</div>";

        if ($automatedRequest->getServiceRequestID()) {

            echo "<div>We do have a Service Request ID: " . $automatedRequest->getServiceRequestID() . "</div>";

            $dbeProblem = new DBEProblem($this);

            $dbeProblem->getRow($automatedRequest->getServiceRequestID());

            if (!$dbeProblem->rowCount()) {
                echo "<div>The service request doesn't exist </div>";
                // create a new service request
                return $this->raiseNewRequestFromImport($automatedRequest);
            }

            $dbeContact = new DBEContact($this);

            $dbeContact->setValue(
                DBEContact::email,
                $automatedRequest->getSenderEmailAddress()
            );
            $dbeContact->getRowsByColumn(DBEContact::email);

            $status = $dbeProblem->getValue(DBEProblem::status);
            if ($status == 'C') {   // is request completed?
                echo "<div>The found service request is closed, raising a new service request</div>";

                if (!$dbeContact->fetchNext() || $dbeContact->getValue(
                        DBEContact::customerID
                    ) != $automatedRequest->getCustomerID()) {
                    echo "<div>The contact was not found or the contact doesn't belong to the same customer ID</div>";
                    $prependMessage = '<div style="color: red">Update from email received from ' . $automatedRequest->getSenderEmailAddress(
                        ) . ' on ' . date(
                            DATE_MYSQL_DATETIME
                        ) . "</div>";
                    return $this->addCustomerRaisedRequest(
                        $automatedRequest,
                        null,
                        null,
                        $prependMessage
                    );
                }
                return $this->raiseNewRequestFromImport($automatedRequest);
            }

            if (!$dbeContact->fetchNext()) {
                echo "<div>We have tried to pull a the contact from the sender email, but we couldn't find it</div>";
                $details = '<div style="color: red">Update from email received from ' . $automatedRequest->getSenderEmailAddress(
                    ) . ' on ' . date(
                        DATE_MYSQL_DATETIME
                    ) . " who was not the original service request initiator</div>" . $details;

                $dbeLastActivity = $this->getLastActivityInProblem($automatedRequest->getServiceRequestID());
                $this->createFollowOnActivity(
                    $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                    CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID,
                    $dbeProblem->getValue(DBEProblem::contactID),
                    $details,
                    false,
                    true,
                    USER_SYSTEM,
                    false,
                    true
                );

                if ($automatedRequest->getAttachment() == 'Y') {
                    $this->processAttachment(
                        $automatedRequest->getServiceRequestID(),
                        $automatedRequest
                    );
                }
                return true;
            }

            if ($dbeContact->getValue(DBEContact::customerID) != $automatedRequest->getCustomerID()) {
                $details = '<div style="color: red">Update from email received from ' . $automatedRequest->getSenderEmailAddress(
                    ) . ' on ' . date(
                        DATE_MYSQL_DATETIME
                    ) . " who was not the original service request initiator</div>" . $details;

                $dbeLastActivity = $this->getLastActivityInProblem($automatedRequest->getServiceRequestID());
                $this->createFollowOnActivity(
                    $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                    CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID,
                    $dbeProblem->getValue(DBEProblem::contactID),
                    $details,
                    false,
                    true,
                    USER_SYSTEM,
                    false,
                    true
                );

                if ($automatedRequest->getAttachment() == 'Y') {
                    $this->processAttachment(
                        $automatedRequest->getServiceRequestID(),
                        $automatedRequest
                    );
                }
                return true;
            }

            $dbeLastActivity = $this->getLastActivityInProblem($automatedRequest->getServiceRequestID());

            if ($dbeProblem->getValue(DBEProblem::contactID) != $dbeContact->getValue(DBEContact::contactID)) {
                $details = '<div style="color: red">Update from email received from ' . $automatedRequest->getSenderEmailAddress(
                    ) . ' on ' . date(
                        DATE_MYSQL_DATETIME
                    ) . " who was not the original service request initiator</div>" . $details;
            }

            $this->createFollowOnActivity(
                $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID,
                $dbeContact->getValue(DBEContact::contactID),
                $details,
                false,
                true,
                USER_SYSTEM,
                false,
                true
            );

            if ($automatedRequest->getAttachment() == 'Y') {
                $this->processAttachment(
                    $automatedRequest->getServiceRequestID(),
                    $automatedRequest
                );
            }

            return true;
        }
        echo "<div>We do not have a service request ID</div>";

        if ($automatedRequest->getServerGuardFlag() == 'Y') {
            echo '<div>This is SERVER GUARD request!</div>';
            return $this->processServerGuard($automatedRequest);
        }


        return $this->raiseNewRequestFromImport($automatedRequest);
    }

    /**
     * @param AutomatedRequest $automatedRequest
     * @return bool
     * @throws Exception
     */
    function processServerGuard(AutomatedRequest $automatedRequest)
    {
        $details = $automatedRequest->getTextBody();
        if (!$automatedRequest->getMonitorStatus()) {
            /* Create new request */
            $details = $automatedRequest->getSubjectLine() . "\n\n" . $details . "\n\n";
            $details .= 'Raised from ServerGuard on ' . date(DATE_MYSQL_DATETIME);

            $this->raiseNewRequestFromImport(
                $automatedRequest,
                $details,
                true
            );

            return true;       // nothing more to do
        }

        if ($automatedRequest->getMonitorStatus() == 'S') { // success

            $request =
                $this->getRequestByCustPostcodeMonitorNameAgentName(
                    $automatedRequest->getCustomerID(),
                    $automatedRequest->getPostcode(),
                    $automatedRequest->getMonitorName(),
                    $automatedRequest->getMonitorAgentName()
                );

            if ($request) {

                $details = $automatedRequest->getSubjectLine() . "\n\n" . $details . "\n\n";
                $details .= 'Issue resolved - from ServerGuard';

                $dbeLastActivity = $this->getLastActivityInProblem($request['pro_problemno']);

                $this->createFollowOnActivity(
                    $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                    CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID,
                    $request['pro_contno'],
                    $details,
                    false,
                    true
                );

                /*
        if the request has engineer activity(i.e. more than just the 1 initial activity)
        then set to awaiting CNC.
        */
                $engineerActivityCount = $this->countEngineerActivitiesInProblem($request['pro_problemno']);
                if ($engineerActivityCount > 0) {

                    $this->setActivityAwaitingCNC($dbeLastActivity->getValue(DBEJCallActivity::callActivityID));

                } else {
                    $this->setProblemToFixed(
                        $request['pro_problemno'],
                        USER_SYSTEM,
                        $automatedRequest->getContractCustomerItemID(),
                        CONFIG_NOTHING_FOUND_ROOT_CAUSE_ID,
                        'Automatically fixed'
                    );
                    $this->setProblemAlarm(
                        $request['pro_problemno'],
                        false,
                        false
                    );  // reset
                }
                return true;
            } else {
                return true; // ignore SR not found
            }

        } else { // failed
            $request =
                $this->getRequestByCustPostcodeMonitorNameAgentName(
                    $automatedRequest->getCustomerID(),
                    $automatedRequest->getPostcode(),
                    $automatedRequest->getMonitorName(),
                    $automatedRequest->getMonitorAgentName()
                );
            if ($request && $request['pro_status'] != 'C') { // request exists that is not completed

                $details = $automatedRequest->getSubjectLine() . "\n\n" . $details . "\n\n";
                $details .= 'Updated from ServerGuard';

                $dbeLastActivity = $this->getLastActivityInProblem($request['pro_problemno']);
                $callActivityID =
                    $this->createFollowOnActivity(
                        $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                        CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID,
                        $request['pro_contno'],
                        $details,
                        false,
                        true
                    );
                $this->setActivityAwaitingCNC($callActivityID);

                if ($automatedRequest->getAttachment() == 'Y') {
                    $this->processAttachment(
                        $request['pro_problemno'],
                        $automatedRequest
                    );
                }

                return true;
            } else {
                /* Create new request */
                $details = $automatedRequest->getSubjectLine() . "\n\n" . $details . "\n\n";
                $details .= 'Raised from ServerGuard on ' . date(DATE_MYSQL_DATETIME);

                $this->raiseNewRequestFromImport(
                    $automatedRequest,
                    $details,
                    true
                );

                return true;       // nothing more to do
            }
        }
    }

    function getAlertContact($customerID,
                             $postcode
    )
    {
        $db = new dbSweetcode(); // database connection for query
        /* get siteno from postcode */
        $queryString = "
      SELECT
        add_siteno
      FROM
        address
      WHERE
        add_postcode = '" . $postcode . "' and add_custno ='" . $customerID . "' ";
        $db->query($queryString);
        $db->next_record();
        $ret['siteNo'] = $db->Record[0];

        if (!$ret['siteNo']) {
            $ret['siteNo'] = 0;
        }
        /* use main support contact */
        $queryString = "
      SELECT
        primaryMainContactID
      FROM
        customer    
      WHERE
          cus_custno = $customerID";

        $db->query($queryString);
        $db->next_record();

        $ret['contactID'] = $db->Record[0];

        $ret['customerID'] = $customerID;
        return $ret;

    }

    function isWhitelistedUtilityEmail($email)
    {
        $dbeUtilityEmail = new DBEUtilityEmail($this);

        $dbeUtilityEmail->getRowsByEmail($email);

        return $dbeUtilityEmail->rowCount;
    }

    /**
     * New request from import process
     *
     * @param mixed $record
     * @param null $forcedDetails
     * @param bool $serverGuard
     * @return mixed
     * @throws Exception
     */
    function raiseNewRequestFromImport(AutomatedRequest $record,
                                       $forcedDetails = null,
                                       $serverGuard = false
    )
    {
        echo "<div>We are trying to raise a new request</div>";
        $customerID = $record->getCustomerID();
        $dbeProblem = new DBEProblem($this);

        $dbeContact = new DBEContact($this);
        $dbeContact->setValue(
            DBEContact::email,
            $record->getSenderEmailAddress()
        );
        $dbeContact->getRowsByColumn(DBEContact::email);
        $forceHidden = false;
        if (!$dbeContact->rowCount || $serverGuard) {
            echo "<div>The sender contact was not found, or this is a server Guard,  we need to pull the primary contact of the customer: " . $customerID . "</div>";
            $buCustomer = new BUCustomer($this);

            $dbeContact = $buCustomer->getPrimaryContact($customerID);

            if (!$serverGuard) {
                $prependMessage = '<div style="color: red">Utility alert sent from email ' . $record->getSenderEmailAddress(
                    ) . ' on ' . date(
                        DATE_MYSQL_DATETIME
                    ) . "</div>";

                if ($this->isWhitelistedUtilityEmail($record->getSenderEmailAddress())) {
                    $forceHidden = true;
                } else {
                    return $this->addCustomerRaisedRequest(
                        $record,
                        null,
                        null,
                        $prependMessage
                    );
                }
            }


            if (!$dbeContact || !$dbeContact->rowCount) {

                echo "<div>We couldn't find a primary contact, -> to be logged</div>";
                $prependMessage = '<div style="color: red">Failed to find primary contact associated with customer</div>';
                return $this->addCustomerRaisedRequest(
                    $record,
                    null,
                    null,
                    $prependMessage
                );
            }
            echo "<div>we have found a primary contact</div>";

        } else {
            echo "<div>The sender contact does exist</div>";
            $dbeContact->fetchNext();
            if ($dbeContact->getValue(DBEContact::customerID) != $record->getCustomerID()) {
                echo "<div>The sender contact does not belong to the same customer ID -> to be logged</div>";
                $prependMessage = '<div style="color: red">Update from email received from ' . $record->getSenderEmailAddress(
                    ) . ' on ' . date(
                        DATE_MYSQL_DATETIME
                    ) . "</div>";

                return $this->addCustomerRaisedRequest(
                    $record,
                    null,
                    null,
                    $prependMessage
                );
            }
        }


        $supportLevel = $dbeContact->getValue(DBEContact::supportLevel);

        echo "<div>The sender contact support level is : $supportLevel</div>";

        $allowedLevels = [
            DBEContact::supportLevelMain,
            DBEContact::supportLevelSupervisor,
            DBEContact::supportLevelSupport
        ];

        if (!in_array(
            $supportLevel,
            $allowedLevels
        )) {
            return $this->contactNotAuthorized(
                $record,
                $dbeContact
            );
        }


        $slaResponseHours = $this->getSlaResponseHours(
            $record->getPriority(),
            $customerID,
            $dbeContact->getValue(DBEContact::contactID)
        );

        /*
    Determine site to use.

    If postcode passed from import, attempt to use that.

    Otherwise use site of main contact.
    */
        $siteNo = false;

        if ($record->getPostcode()) {
            $siteNo = $this->getSiteNoByCustomerPostcode(
                $customerID,
                $record->getPostcode()
            );
        }

        if (!$siteNo) {
            $siteNo = $dbeContact->getValue(DBEContact::siteNo);
        }

        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $this->dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $this->dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::imLimitMinutes,
            $this->dsHeader->getValue(DBEHeader::imTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::slaResponseHours,
            $slaResponseHours
        );
        $dbeProblem->setValue(
            DBEProblem::customerID,
            $customerID
        );
        $dbeProblem->setValue(
            DBEProblem::status,
            'I'
        );
        $dbeProblem->setValue(
            DBEProblem::priority,
            $record->getPriority()
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            date(DATE_MYSQL_DATETIME)
        ); // default
        $dbeProblem->setValue(
            DBEProblem::contactID,
            $dbeContact->getValue(DBEContact::contactID)
        );

        /* @todo confirm with GL */
        if ($record->getSendEmail() == 'A') {
            $dbeProblem->setValue(
                DBEJProblem::hideFromCustomerFlag,
                'N'
            );
        } else {
            $dbeProblem->setValue(
                DBEJProblem::hideFromCustomerFlag,
                'Y'
            );
        }

        if ($forceHidden) {
            $dbeProblem->setValue(
                DBEJProblem::hideFromCustomerFlag,
                'Y'
            );
        }

        if (!$record->getQueueNo()) {
            $queueNo = 1;
        } else {
            $queueNo = $record->getQueueNo();
        }

        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            $queueNo
        );
        $dbeProblem->setValue(
            DBEJProblem::monitorName,
            $record->getMonitorName()
        );
        $dbeProblem->setValue(
            DBEJProblem::monitorAgentName,
            $record->getMonitorAgentName()
        );
        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            $record->getRootCauseID()
        );
        $dbeProblem->setValue(
            DBEJProblem::contractCustomerItemID,
            $record->getContractCustomerItemID()
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            null
        );        // not allocated
        $dbeProblem->insertRow();


        $dbeCallActivity = new DBECallActivity($this);

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            0
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $siteNo
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $dbeContact->getValue(DBEContact::contactID)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $startTime = date('H:i');
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            $startTime
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $startTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            $record->getServerGuardFlag()
        );

        $details = $record->getSubjectLine();

        if (!$forcedDetails) {
            $details .= $record->getTextBody();
        } else {
            $details .= $forcedDetails;
        }

        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            Controller::formatForHTML($details)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            USER_SYSTEM
        );

        $dbeCallActivity->insertRow();

        if ($record->getAttachment() == 'Y') {
            $this->processAttachment(
                $dbeProblem->getPKValue(),
                $record
            );
        }


        $dsCustomer = new DBECustomer($this);
        $dsCustomer->getRow($customerID);

        $testTime = (new DateTime($record->getCreateDateTime()))->format('H:i');

        $startTime = $this->dsHeader->getValue(DBEHeader::serviceDeskNotification24hBegin);
        $endTime = $this->dsHeader->getValue(DBEHeader::serviceDeskNotification24hEnd);
        echo '<br>test time is : ' . $testTime;
        echo '<br>startTime is : ' . $startTime;
        echo '<br>endTime is : ' . $endTime;


        $subCategory = null;
        if ($testTime < $startTime || $testTime > $endTime) {
            echo '<div>Should be special email</div>';
            $has24HourSupport = $dsCustomer->getValue(DBECustomer::support24HourFlag) == 'Y';
            $buCustomerItem = new BUCustomerItem($this);

            $hasServiceDesk = $buCustomerItem->customerHasServiceDeskContract($customerID);

            if ($has24HourSupport || $hasServiceDesk) {
                echo '<div>does have 24h support</div>';
                $subCategory = self::Initial24HSupport;
            } else {
                echo '<div>does NOT have 24h support</div>';
                $subCategory = self::InitialNot24HSupport;
            }
        } else {
            echo '<div>Should be normal email</div>';
        }

        $this->sendEmailToCustomer(
            $dbeProblem->getPKValue(),
            self::InitialCustomerEmailCategory,
            $subCategory
        );

        return true;
    }

    function getSiteNoByCustomerPostcode(
        $customerID,
        $postcode
    )
    {
        global $db;

        $postcode = trim($postcode);

        $sql = "
      SELECT 
        add_siteno
      
      FROM
        address 
         
      WHERE add_custno = $customerID 
        AND add_postcode = '$postcode'";

        $db->query($sql);
        if ($db->next_record()) {
            $ret = $db->Record['add_siteno'];
        } else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * @param $problemID
     * @param AutomatedRequest $record
     */
    function processAttachment($problemID,
                               AutomatedRequest $record
    )
    {

        $filePaths = explode(
            ',',
            $record->getAttachmentFilename()
        );

        foreach ($filePaths as $filePath) {

            if ($handle = fopen(
                $filePath,
                'r'
            )) {

                if ($finfo = finfo_open(FILEINFO_MIME_TYPE)) {
                    $attachmentMimeType = finfo_file(
                        $finfo,
                        $filePath
                    );
                } else {
                    $attachmentMimeType = null;   // failed to locate magic file for MimeTypes
                }

                $this->addDocument(
                    $problemID,
                    $filePath,
                    filesize($filePath),
                    'Imported',
                    basename($filePath),
                    $attachmentMimeType
                );

                fclose($handle);
                unlink($filePath);
            } else {
                $errorString = 'Failed to import attachment file ' . $filePath . '<BR/>';
                echo $errorString;
            }
        }
    }

    function getRequestByCustPostcodeMonitorNameAgentName(
        $customerID,
        $postcode,
        $monitorName,
        $monitorAgentName
    )
    {
        global $db;

        $postcode = trim($postcode);
        $monitorName = trim($monitorName);
        $monitorAgentName = trim($monitorAgentName);

        $sql = "
      SELECT 
        pro_problemno,
        pro_status,
        pro_contno
      FROM
        problem
        JOIN callactivity ON caa_problemno = pro_problemno
        JOIN address 
            ON caa_siteno = add_siteno 
            AND pro_custno = add_custno
         
      WHERE pro_custno = ?
        AND pro_monitor_name = ? 
        AND pro_monitor_agent_name = ? 
        AND pro_status NOT IN ('C') 
        AND add_postcode = ?
      ORDER BY pro_date_raised DESC";

        $parameters = [
            [
                'type'  => 'i',
                'value' => $customerID
            ],
            [
                'type'  => 's',
                'value' => $monitorName
            ],
            [
                'type'  => 's',
                'value' => $monitorAgentName
            ],
            [
                'type'  => 's',
                'value' => $postcode
            ],
        ];
        /**
         * @var mysqli_result $result
         */
        $result = $db->preparedQuery(
            $sql,
            $parameters
        );
        return $result->fetch_array();
    }

    /**
     * @param $callActivityID
     * @param bool $callActivityTypeID
     * @param bool $contactID for when we are creating from To Be Logged
     * @param bool $passedReason
     * @param bool $ifUnallocatedSetToCurrentUser
     * @param bool $setEndTimeToNow
     * @param int $userID
     * @param bool $moveToUsersQueue
     * @param bool $resetAwaitingCustomerResponse
     * @return string
     * @throws Exception
     */
    function createFollowOnActivity(
        $callActivityID,
        $callActivityTypeID = false,
        $contactID = false,
        $passedReason = false,
        $ifUnallocatedSetToCurrentUser = true,
        $setEndTimeToNow = false,
        $userID = USER_SYSTEM,
        $moveToUsersQueue = false,
        $resetAwaitingCustomerResponse = false
    )
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $reason = $passedReason;

        $isTravel = false;

        $endTime = null;                // default no end time

        if ($callActivityTypeID) {
            $dbeCallActType = new DBECallActType($this);
            $dbeCallActType->getRow($callActivityTypeID);
            if ($dbeCallActType->getValue(DBECallActType::travelFlag) == 'Y') {
                $isTravel = true;
            };

            if ($callActivityTypeID == CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID) {

                $endTime = $this->getEndtime(CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID);

                /*
        Pre-populate reason
        */
                $reason = "<!--suppress HtmlDeprecatedAttribute -->
<table border='1' style='border: solid black 1px'><thead><tr><td></td><td>Details</td></tr></thead><tbody><tr><td>System:</td><td></td></tr><tr><td>Summary of problem:</td><td></td></tr><tr><td>Change Requested:</td><td></td></tr><tr><td>Method to test change if successful:</td><td></td></tr><tr><td>Reversion plan if unsuccessful:</td><td></td></tr></tbody></table>";
            }
        }

//        if (!$serverGuard) {
//            $serverGuard = $dbeCallActivity->getValue(DBEJCallActivity::serverGuard);
//        }

        $problemID = $dbeCallActivity->getValue(DBEJCallActivity::problemID);

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);

        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($dbeProblem->getValue(DBEJProblem::customerID));
        /*
    if not already allocated to a user, set to current user
    */
        if ($ifUnallocatedSetToCurrentUser && !$dbeProblem->getValue(DBEJProblem::userID)) {

            $dbeProblem->setValue(
                DBEJProblem::userID,
                $userID
            );

            if ($moveToUsersQueue) {
                /*
        Set queue same as allocated user's teamLevel (max queue no = 3)
        */
                $teamLevel = $this->getLevelByUserID($userID);

                if ($teamLevel <= 3) {
                    $queueNo = $teamLevel;
                } else {
                    $queueNo = 5; // managers
                }

                $dbeProblem->setValue(
                    DBEJProblem::queueNo,
                    $queueNo
                );
            }

            $dbeProblem->updateRow();

        }
        /*
    When SR is currently at Initial status and a user other than System is logging
    an activity other than travel, record the Responded hours and set to In Progress status.
    */
        if (!$isTravel) {

            if ($dbeProblem->getValue(DBEJProblem::status) == 'I' & $userID != USER_SYSTEM) {

                $respondedHours = $dbeProblem->getValue(DBEJProblem::workingHours);

                $dbeProblem->setValue(
                    DBEJProblem::respondedHours,
                    $respondedHours
                );
                $dbeProblem->setValue(
                    DBEJProblem::startedUserID,
                    $userID
                );

                $dbeProblem->setValue(
                    DBEJProblem::status,
                    'P'
                );
                /*
        Send work started email except for Hide from Customer OR Sales Order related SRs
        */
                if (
                    $dbeProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'N' AND
                    !$dbeProblem->getValue(DBEJProblem::linkedSalesOrderID)
                ) {
                    $this->sendEmailToCustomer(
                        $problemID,
                        self::WorkStartedCustomerEmailCategory
                    );
                }
            } elseif ($dbeProblem->getValue(DBEJProblem::status) == 'F') {
                /*
        Reopen
        */
                $dbeProblem->setValue(
                    DBEJProblem::status,
                    'P'
                );    // in progress

                $dbeProblem->setValue(
                    DBEJProblem::reopenedFlag,
                    'Y'
                );

                $dbeProblem->setValue(
                    DBEJProblem::reopenedDate,
                    (new DateTime())->format('Y-m-d')
                );

                if ($dbeProblem->getValue(DBEJProblem::fixedUserID) != USER_SYSTEM) {
                    /*
         if priority = 1 then notify fixed user that it has been reopened WITHOUT
         reallocating.
         */
                    if ($dbeProblem->getValue(DBEJProblem::priority) == 1) {
                        $dbeProblem->setValue(
                            DBEJProblem::userID,
                            null
                        );              // ensure not assigned
                        $this->sendPriorityOneReopenedEmail($problemID);
                    } /*
         otherwise, reallocate to fixed user
         */
                    else {
                        $dbeProblem->setValue(
                            DBEJProblem::userID,
                            $dbeProblem->getValue(DBEJProblem::fixedUserID)
                        );
                    }
                }

                $reason = '<P>Reopened</P>' . $reason;

            }

            $dbeProblem->setValue(
                DBEJProblem::alarmDate,
                null
            );
            $dbeProblem->setValue(
                DBEJProblem::alarmTime,
                null
            );
            $dbeProblem->updateRow();

        }// if( !$isTravel )

        if ($setEndTimeToNow) {

            $endTime = date('H:i');      // Set to current time
        } elseif ($callActivityTypeID == CONFIG_INITIAL_ACTIVITY_TYPE_ID) {

            if ($userID != USER_SYSTEM) {

                $endTime = $this->getEndtime($callActivityTypeID);
            } else {
                $endTime = date('H:i');    // Set to current time if system user

            }
        }

        if ($endTime) {
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'C'
            ); // Checked if have an end time
        } else {
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'O'
            ); // Leave open
        }

        if ($callActivityTypeID == CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID) {
            //if this is a change request ..leave it open as in "pending change request"
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'O'
            );
        }


        $dbeCallActivity->setPKValue(null);

        $activityUserID = $userID;

        if (!$contactID) {
            $contactID = $dbeCallActivity->getValue(DBEJCallActivity::contactID);
        }

        $dbeCallActivity->setValue(
            DBEJCallActivity::hideFromCustomerFlag,
            'N'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $activityUserID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            $callActivityTypeID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $endTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $contactID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            str_replace(
                "\n",
                '<BR/>',
                $reason
            )
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            $dbeCallActivity->getValue(DBEJCallActivity::serverGuard)
        );

        if ($resetAwaitingCustomerResponse) {

            $dbeProblem->setValue(
                DBEJProblem::awaitingCustomerResponseFlag,
                'N'
            );
            $dbeProblem->updateRow();

            $dbeCallActivity->setValue(
                DBECallActivity::awaitingCustomerResponseFlag,
                'N'
            );
        }

        $dbeCallActivity->insertRow();

        $ret = $dbeCallActivity->getPKValue();

        $this->highActivityAlertCheck($dbeProblem->getValue(DBEJProblem::problemID));


        if ($passedReason) {
            $this->updatedByAnotherUser(
                $dbeProblem,
                $dbeCallActivity
            );
        }

        return $ret;
    }

    /**
     * Get team level of user
     *
     * @param mixed $userID
     * @return int Level or 0 if $userID is false
     */
    public function getLevelByUserID($userID)
    {
        if ($userID) {
            $this->dbeUser->getRow($userID);

            $dbeTeam = new DBETeam($this);
            $dbeTeam->getRow($this->dbeUser->getValue(DBEJUser::teamID));
            $ret = $dbeTeam->getValue(DBETeam::level);
        } else {
            $ret = 0;
        }
        return $ret;
    }

    function sendPriorityOneReopenedEmail($problemID)
    {
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $buMail = new BUMail($this);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $this->dbeUser->getRow($dbeJProblem->getValue(DBEJProblem::fixedUserID));

        $toEmail = $this->dbeUser->getValue(
                DBEJUser::username
            ) . '@' . CONFIG_PUBLIC_DOMAIN . ',' . 'srp1reopened@' . CONFIG_PUBLIC_DOMAIN;

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'PriorityOneReopenedEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $template->setVar(
            array(
                'activityRef'  => $activityRef,
                'reason'       => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'customerName' => $dbeJProblem->getValue(DBEJProblem::customerName),
                'urlActivity'  => $urlActivity,
                'CONFIG_SERVICE_REQUEST_DESC'
                               => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Priority 1 Reopened: SR ' . $problemID . ' ' . $dbeJProblem->getValue(
                    DBEJProblem::customerName
                ),
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    function countEngineerActivitiesInProblem($problemID)
    {

        $dbeCallActivity = new DBECallActivity($this);

        return $dbeCallActivity->countEngineerRowsByProblem($problemID);
    }

    /**
     * sets problem out of pause mode by un-setting flag on activity
     *
     * @param mixed $callactivityID
     * @param mixed $date
     * @param mixed $time
     * @return bool
     */
    function setActivityAwaitingCNC($callactivityID,
                                    $date = null,
                                    $time = null
    )
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callactivityID);
        $dbeCallActivity->setValue(
            DBEJCallActivity::awaitingCustomerResponseFlag,
            'N'
        );
        $dbeCallActivity->updateRow();

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeCallActivity->getValue(DBEJCallActivity::problemID));
        $dbeProblem->setValue(
            DBEJProblem::awaitingCustomerResponseFlag,
            'N'
        );
        $dbeProblem->updateRow();
        /*
    do we have an alarm time?
    */
        if ($date) {
            $this->setProblemAlarm(
                $dbeCallActivity->getValue(DBEJCallActivity::problemID),
                $date,
                $time
            );
        }
        return true;
    }

    /**
     * Set the problem to fixed
     *
     *    1. Calculate the time to fix field on the problem record
     *    2. set fixed engineer and remove allocated engineer
     *    3. Change the problem status to "F"
     *
     *   The workingHours field now represents the fix time because it will no
     *   longer be updated by the ServiceDesk Monitor routine.
     *
     * @param $problemID
     * @param $fixedUserID
     * @param $contractCustomerItemID
     * @param $rootCauseID
     * @param $resolutionSummary
     * @return bool
     * @throws Exception
     */
    function setProblemToFixed(
        $problemID,
        $fixedUserID = null,
        $contractCustomerItemID = null,
        $rootCauseID = null,
        $resolutionSummary = null
    )
    {
        /*
    Can't fix request with open activities
    */
        if ($this->countOpenActivitiesInRequest($problemID) > 0) {
            return false;
        }

        $dbeProblem = new DBEProblem(
            $this,
            $problemID
        );


        if (!$fixedUserID) {
            $fixedUserID = $this->loggedInUserID;
        }

        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($fixedUserID);


        $closingUserTeamID = $dbeUser->getValue(DBEUser::teamID);
        $minutesToAdd = $this->dsHeader->getValue(DBEHeader::closingSRBufferMinutes);


        switch ($closingUserTeamID) {
            case 1:
                $dbeProblem->setValue(
                    DBEProblem::hdLimitMinutes,
                    $dbeProblem->getValue(DBEProblem::hdLimitMinutes) + $minutesToAdd
                );
                break;
            case 2:
                $dbeProblem->setValue(
                    DBEProblem::esLimitMinutes,
                    $dbeProblem->getValue(DBEProblem::esLimitMinutes) + $minutesToAdd
                );
                break;
            case 4:
                $dbeProblem->setValue(
                    DBEProblem::imLimitMinutes,
                    $dbeProblem->getValue(DBEProblem::imLimitMinutes) + $minutesToAdd
                );
        }


        $dbeProblem->setValue(
            DBEJProblem::fixedUserID,
            $fixedUserID
        );
        $dbeProblem->setValue(
            DBEJProblem::fixedDate,
            date(DATE_MYSQL_DATETIME)
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            null
        );                  // problem no longer allocated
        $dbeProblem->setValue(
            DBEJProblem::status,
            'F'
        );
        $dbeProblem->setValue(
            DBEJProblem::awaitingCustomerResponseFlag,
            'N'
        );
        $dbeProblem->setValue(
            DBEJProblem::contractCustomerItemID,
            $contractCustomerItemID
        );
        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            $rootCauseID
        );


        $buProblemSLA = new BUProblemSLA($this);

        $dbeProblem->setValue(
            DBEJProblem::completeDate,
            $buProblemSLA->getCompleteDate()
        );
        $dbeProblem->updateRow();

        $this->closeActivitiesWithEndTime($problemID);

        $dbeJCallActivity = $this->getActivitiesByProblemID($problemID);

        while ($dbeJCallActivity->fetchNext()) {
            if ($dbeJCallActivity->getValue(DBEJCallActivity::callActTypeID) == 57) {
                $dbeCallActivity = new DBECallActivity($this);
                $dbeCallActivity->getRow($dbeJCallActivity->getValue(DBEJCallActivity::callActivityID));
                $dbeCallActivity->setValue(
                    DBEJCallActivity::callActTypeID,
                    11
                );

                $dbeCallActivity->setValue(
                    DBEJCallActivity::reason,
                    '<div>Fixed Explanation</div>' . $dbeJCallActivity->getValue(DBEJCallActivity::reason)
                );

                $dbeCallActivity->updateRow();
            }
        }

        $this->createFixedActivity(
            $problemID,
            $resolutionSummary,
            $fixedUserID == USER_SYSTEM
        );

        $this->sendMonitoringEmails(
            $this->getLastActivityInProblem($problemID)->getValue(DBEJCallActivity::callActivityID)
        );
        if ($dbeProblem->getValue(DBEJProblem::escalatedUserID)) {
            $this->sendNotifyEscalatorUserEmail($problemID);
        }
        /*
    email to client (last activity will contain the fix summary)
    */
        $this->sendEmailToCustomer(
            $problemID,
            self::FixedCustomerEmailCategory,
            self::Fixed
        );

        return true;
    }

    /**
     * @param $problemID
     * @param bool $exceptCallActivityID
     * @return int
     * @throws Exception
     */
    function countOpenActivitiesInRequest($problemID,
                                          $exceptCallActivityID = false
    )
    {
        $sql =
            "SELECT
        COUNT( * ) AS openActivityCount
      FROM
          callactivity
      WHERE
          caa_endtime is null
            AND
            caa_problemno = " . $problemID;

        if ($exceptCallActivityID) {
            $sql .= " AND caa_callactivityno <> " . $exceptCallActivityID;
        }

        $result = $this->db->query($sql);
        if (!$result) {
            throw new Exception('Failed to retrieve data:' . $this->db->error);
        }
        $count = +$result->fetch_assoc()['openActivityCount'];
        return $count;

    }

    public function closeActivitiesWithEndTime($problemID)
    {
        /** @var $db dbSweetcode */
        global $db;
        $starttime = microtime(true);
        /* do stuff here */


        $sql = "update callactivity  set caa_status  = 'C'  WHERE caa_problemno = ? and caa_endtime is not null";
        $result = $db->preparedQuery(
            $sql,
            [
                [
                    'type'  => 'i',
                    'value' => $problemID
                ],
            ]
        );
        $endtime = microtime(true);
        $timediff = $endtime - $starttime;
        return true;
    }

    function getActivitiesByProblemID($problemID)
    {
        $this->dbeJCallActivity->getRowsByProblemID(
            $problemID,
            false
        );

        return $this->dbeJCallActivity;

    }

    /**
     * @param $problemID
     * @param $resolutionSummary
     * @param bool $zeroTime
     * @throws Exception
     */
    function createFixedActivity($problemID,
                                 $resolutionSummary,
                                 $zeroTime = false
    )
    {
        /*
    Start with duplicate of last activity
    */

        $dbeLastActivity = $this->getLastActivityInProblem($problemID);
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($dbeLastActivity->getValue(DBEJCallActivity::callActivityID));

        $dbeCallActivity->setPKValue(null);
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );

        $endTime = $dbeCallActivity->getValue(DBEJCallActivity::startTime);

        if (!$zeroTime) {
            $endTime = $this->getEndtime(CONFIG_FIXED_ACTIVITY_TYPE_ID);
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->getRow($problemID);
            $dbeProblem->setValue(
                DBEProblem::esLimitMinutes,
                $dbeProblem->getValue(DBEProblem::esLimitMinutes) + 3
            );
            $dbeProblem->setValue(
                DBEProblem::hdLimitMinutes,
                $dbeProblem->getValue(DBEProblem::hdLimitMinutes) + 3
            );
            $dbeProblem->setValue(
                DBEProblem::imLimitMinutes,
                $dbeProblem->getValue(DBEProblem::imLimitMinutes) + 3
            );
            $dbeProblem->updateRow();
        }

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $endTime
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $this->loggedInUserID
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_FIXED_ACTIVITY_TYPE_ID
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $dbeLastActivity->getValue(DBEJCallActivity::siteNo)
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $resolutionSummary
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            'N'
        );
        $dbeCallActivity->insertRow();
    }

    /**
     * Sends email to the technician that escalated request to let them know request is fixed
     *
     * @param mixed $problemID
     */
    function sendNotifyEscalatorUserEmail($problemID)
    {

        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($dbeJProblem->getValue(DBEJProblem::escalatedUserID));
        $toEmail = $dbeUser->getValue(DBEJUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

        $activityRef = $problemID . ' ' . $dbeJProblem->getValue(DBEJProblem::customerName);

        $template = new Template (
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'NotifyEscalatorEmail.inc.html'
        );

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);
        $originalReason = $dbeJCallActivity->getValue(DBEJCallActivity::reason);
        $customerName = $dbeJCallActivity->getValue(DBEJCallActivity::customerName);
        $initialID = $dbeJCallActivity->getPKValue();

        $dbeJCallActivity = $this->getLastActivityInProblem($problemID);
        $fixedBy = $dbeJCallActivity->getValue(DBEJCallActivity::userName);
        $fixSummary = $dbeJCallActivity->getValue(DBEJCallActivity::reason);

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $initialID;


        $template->setVar(
            array(
                'activityRef'  => $activityRef,
                'reason'       => $originalReason,
                'customerName' => $customerName,
                'fixSummary'   => $fixSummary,
                'urlActivity'  => $urlActivity,
                'fixedBy'      => $fixedBy,
                'CONFIG_SERVICE_REQUEST_DESC'
                               => CONFIG_SERVICE_REQUEST_DESC

            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Your Escalated ' . CONFIG_SERVICE_REQUEST_DESC . ' for ' . $dbeJProblem->getValue(
                    DBEJProblem::customerName
                ) . ' Was Fixed By ' . $fixedBy,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /**
     * @param $problemID
     */
    public function createPurchaseOrderCompletedSalesActivity($problemID)
    {

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);

        $dbeProblem->setValue(DBEProblem::awaitingCustomerResponseFlag, 'N');
        $dbeProblem->setValue(DBEProblem::alarmDate, null);
        $dbeProblem->setValue(DBEProblem::alarmTime, null);
        $dbeProblem->updateRow();

        $firstActivity = $this->getFirstActivityInProblem($problemID);

        $contactID = $firstActivity->getValue(DBECallActivity::contactID);

        $dbeContact = new DBEContact($this);
        $siteNo = $dbeContact->getValue(DBEContact::siteNo);
        $dbeCallActivity = new DBECallActivity($this);

        $dbeCallActivity->setValue(DBEJCallActivity::awaitingCustomerResponseFlag, 'N');

        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $siteNo
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $contactID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_SALES_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $time = date('H:i');
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            $time
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $time
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            "The entire purchase order for this service request has been received"
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            USER_SYSTEM
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getValue(DBEProblem::problemID)
        );

        $dbeCallActivity->insertRow();

        $this->updatedByAnotherUser(
            $dbeProblem,
            $dbeCallActivity
        );
    }

    function getContactInfo($record)
    {
        global $db;
        $sql = "select con_contno, con_custno, con_siteno, supportLevel, (SELECT customer.primaryMainContactID FROM customer WHERE customer.`cus_custno` = con_custno) = con_contno AS isPrimaryMain from contact where con_email = $record[senderEmailAddress] and con_custno <> 0";


        $db->query($sql);

        if ($db->next_record()) {
            return [
                "contactID"     => $db->Record[0],
                "customerID"    => $db->Record[1],
                "siteNo"        => $db->Record[2],
                "supportLevel"  => $db->Record[3],
                "isPrimaryMain" => $db->Record[4]
            ];
        }

        //we haven't found a guy we need to extract the domain from email and try to find a matching customer

        $sender = trim(
            strtolower(
                preg_replace(
                    "/([\w\s]+)<([\S@._-]*)>/",
                    " $2",
                    $record['senderEmailAddress']
                )
            )
        );

        $pieces = explode(
            '@',
            $sender
        );
        $emailDomain = strtolower(trim($pieces[1]));


        //try to find a specific contact by email
        $sql = "
            SELECT
              con_contno,
              con_custno,
              con_siteno
            FROM
              contact
            WHERE
              con_email = '" . mysqli_real_escape_string(
                $db->link_id(),
                $record['senderEmailAddress']
            ) . "'
              AND con_custno <> 0 
              AND (supportLevel = 'main' or supportLevel = 'support' or supportLevel = 'delegate')";

        $db->query($sql);
        if ($db->next_record()) {
            $ret['isSupportContact'] = true;
            $ret['isMainContact'] = false;
            $ret['contactID'] = $db->Record[0];
            $ret['customerID'] = $db->Record[1];
            $ret['siteNo'] = $db->Record[2];
        } /*


        /*
    Try to match email domain against any customer
    */
        $sql = "
          SELECT 
            con_contno,
            con_custno,
            con_siteno,
            supportLevel,
            (SELECT 
              customer.primaryMainContactID 
            FROM
              customer 
            WHERE customer.`cus_custno` = con_custno) = con_contno AS isPrimaryMain 
          FROM
            contact 
            LEFT JOIN customer ON customer.primaryMainContactID = con_contno
          WHERE con_email LIKE '%$emailDomain%' 
            AND con_custno <> 0
            AND (SELECT 
              customer.primaryMainContactID 
            FROM
              contact
            WHERE
              con_email = '" . mysqli_real_escape_string(
                $db->link_id(),
                $record['senderEmailAddress']
            ) . "'
              AND con_custno <> 0 
              AND (supportLevel = 'main' or supportLevel = 'support')";

        $db->query($sql);

        if ($db->next_record()) {
            return [
                "contactID"     => $db->Record[0],
                "customerID"    => $db->Record[1],
                "siteNo"        => $db->Record[2],
                "supportLevel"  => $db->Record[3],
                "isPrimaryMain" => $db->Record[4]
            ];
        }


        //we could not identify a specific contact but we might be able to identify the customer

        $sql = "
          SELECT 
            con_custno,
            con_siteno
          FROM
            contact 
          WHERE con_email LIKE '%$emailDomain%' 
            AND con_custno <> 0";

        $db->query($sql);

        if ($db->next_record()) {
            return [
                "contactID"     => null,
                "customerID"    => $db->Record[0],
                "siteNo"        => $db->Record[1],
                "supportLevel"  => null,
                "isPrimaryMain" => null
            ];
        }


        return null;
    }

//    function processIsSenderAuthorised($details,
//                                       $contact,
//                                       $record,
//                                       &$errorString
//    )
//    {
//
//
//        if ($contact && $contact['supportLevel']) {
//
//            $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
//            $details .= 'New request from email received from ' . $record['senderEmailAddress'] . ' on ' . date(
//                    CONFIG_MYSQL_DATETIME
//                );
//
//            $this->raiseNewRequestFromImport(
//                $record,
//                $details,
//                $contact
//            );
//
//            return true;
//        }
//
//        if ($contact) {
//            if ($contact['isMainContact']) {
//                $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
//                $details .= 'This email is from an unauthorised contact and needs to be confirmed' . "\n\n";
//                $details .= 'New request from ' . $record['senderEmailAddress'] . ' on ' . date(CONFIG_MYSQL_DATETIME);
//
//                $this->raiseNewRequestFromImport(
//                    $record,
//                    $details,
//                    $contact
//                );
//                return true;
//            } else {
//                $errorString = 'Domain for ' . $record['senderEmailAddress'] . ' matches customer ' . $contact['customerID'] . ' but no main contact assigned for customer<br/>';
//
//                echo $errorString;
//
//                $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
//                $details .= 'Email received from ' . $record['senderEmailAddress'] . ' on ' . date(
//                        CONFIG_MYSQL_DATETIME
//                    );
//
//                $this->addCustomerRaisedRequest(
//                    $record,
//                    $contact,
//                    false,
//                    $details,
//                    'C'
//                );
//                return false;
//            }
//        } else {
//            /* unknown domain */
//            $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
//            $details .= 'Email received from ' . $record['senderEmailAddress'] . ' on ' . date(CONFIG_MYSQL_DATETIME);
//
//            $this->addCustomerRaisedRequest(
//                $record,
//                $contact,
//                false,
//                $details,
//                'C'
//            );
//            return true;
//        }
//    }


    function addCustomerRaisedRequest(AutomatedRequest $record,
                                      $contact = null,
                                      $updateExistingRequest = false,
                                      $prependMessage = false,
                                      $source = 'S'
    )
    {
        $db = new dbSweetcode(); // database connection for query

        $reason = '<div>' . $record->getSubjectLine() . '</div>';

        if ($record->getHtmlBody()) {
            $reason .= $record->getHtmlBody();
        } else {
            $reason .= $record->getTextBody();
        }

        if ($prependMessage) {
            $reason = $prependMessage . $reason;
        }


        $queryString = "
      INSERT INTO
        customerproblem
      SET
        cpr_date =  NOW(),
        cpr_custno = ?,
        cpr_contno = ?,
        cpr_problemno = ?,
        cpr_update_existing_request = ?,
        cpr_source = ?,
        cpr_siteno = ?,
        cpr_serverguard_flag = ?,
        cpr_send_email = ?,
        cpr_priority = ?,
        cpr_reason = ?";

        $parameters = [
            [
                'type'  => 'i',
                'value' => $record->getCustomerID()
            ],
            [
                'type'  => 'i',
                'value' => $contact['contactID']
            ],
            [
                'type'  => 'i',
                'value' => $record->getServiceRequestID()
            ],
            [
                'type'  => 'i',
                'value' => $updateExistingRequest
            ],
            [
                'type'  => 's',
                'value' => $source
            ],
            [
                'type'  => 'i',
                'value' => $contact['siteNo']
            ],
            [
                'type'  => 's',
                'value' => $record->getServerGuardFlag()
            ],
            [
                'type'  => 's',
                'value' => $record->getSendEmail()
            ],
            [
                'type'  => 's',
                'value' => $record->getPriority()
            ],
            [
                'type'  => 's',
                'value' => $reason
            ]
        ];

        $db->preparedQuery(
            $queryString,
            $parameters
        );
        return true;
    } // end clearSystemSRQueue

    function getManagerComment($problemID)
    {

        global $db;

        $sql = "
          SELECT
            pro_manager_comment
          FROM
            problem
          WHERE
            pro_problemno = $problemID";

        $db->query($sql);
        $db->next_record();
        return $db->Record[0];
    }

    function updateManagerComment($problemID,
                                  $details
    )
    {

        global $db;

        $sql = "
          UPDATE
            problem
          SET
            pro_manager_comment = ?
          WHERE
            pro_problemno = ?";

        $parameters = [
            [
                'type'  => 's',
                'value' => $details,
            ],
            [
                'type'  => 'i',
                'value' => $problemID,
            ]
        ];

        $db->preparedQuery(
            $sql,
            $parameters
        );
    }

    /**
     * @param DataSet $dbeProblem
     * @param integer $siteNo
     * @param integer $contactID
     * @param string $reason
     * @param bool $oldProblemID
     * @throws Exception
     */
    function addInitialActivityToNewRequest($dbeProblem,
                                            $siteNo,
                                            $contactID,
                                            $reason,
                                            $oldProblemID = false
    )
    {

        if ($oldProblemID) {

            $reason .= 'This incident refers to incident ' . $oldProblemID . ' which has already been completed.';

        }

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $siteNo
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $contactID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );

        $endTime = $this->getEndtime(CONFIG_INITIAL_ACTIVITY_TYPE_ID);

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $endTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            Controller::formatForHTML($reason)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            USER_SYSTEM
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getValue(DBEProblem::problemID)
        );
        $dbeCallActivity->insertRow();
    }

    function sendSiteVisitEmail($callActivityID)
    {
        $buMail = new BUMail($this);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'SiteVisitEmail.inc.html'
        );
        $dsCallActivity = new DataSet($this);
        $this->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $buSite = new BUSite($this);
        $dsSite = new DataSet($this);

        $buSite->getSiteByID(
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            $dsCallActivity->getValue(DBEJCallActivity::siteNo),
            $dsSite
        );
        $buCustomer = new BUCustomer($this);

        $callRef = $dsCallActivity->getValue(DBEJCallActivity::problemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = $dsCallActivity->getValue(DBEJCallActivity::contactEmail);

        if ($dsCallActivity->getValue(DBEJCallActivity::startTime) < '12:00') {
            $amOrPM = 'morning';
//            $startHHMM = '0900';
//            $endHHMM = '1200';
        } else {
            $amOrPM = 'afternoon';
//            $startHHMM = '1200';
//            $endHHMM = '1700';
        }

        $template->set_var(
            array(
                'scrRef'           => $callRef,
                'userName'         => $dsCallActivity->getValue(DBEJCallActivity::userName),
                'contactEmail'     => $toEmail,
                'senderEmail'      => $senderEmail,
                'senderName'       => $senderName,
                'contactFirstName' => $dsCallActivity->getValue(DBEJCallActivity::contactFirstName),
                'contactPhone'     => $buCustomer->getContactPhone(
                    $dsCallActivity->getValue(DBEJCallActivity::contactID)
                ),
                'date'             => Controller::dateYMDtoDMY($dsCallActivity->getValue(DBEJCallActivity::date)),
                'amOrPM'           => $amOrPM,
                'startTime'        => $dsCallActivity->getValue(DBEJCallActivity::startTime),
                'reason'           => trim($dsCallActivity->getValue(DBEJCallActivity::reason)),
                'add1'             => $dsSite->getValue(DBESite::add1),
                'add2'             => $dsSite->getValue(DBESite::add2),
                'add3'             => $dsSite->getValue(DBESite::add3),
                'town'             => $dsSite->getValue(DBESite::town),
                'county'           => $dsSite->getValue(DBESite::county),
                'postcode'         => $dsSite->getValue(DBESite::postcode)
            )
        );
        $template->parse(
            'output',
            'page',
            true
        );
        $body = $template->get_var('output');


        // cc to main customer support contact
        $buCustomer = new BUCustomer($this);

        $cc = false;

        if (
        $mainSupportEmailAddresses =
            $buCustomer->getMainSupportEmailAddresses(
                $dsCallActivity->getValue(DBEJCallActivity::customerID),
                $toEmail
            )
        ) {
            $cc = $mainSupportEmailAddresses;
        }

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $bcc = $dsCallActivity->getValue(DBEJCallActivity::userAccount) . '@cnc-ltd.co.uk' . ',' .
            CONFIG_SALES_EMAIL . ',' . "VisitConfirmation@cnc-ltd.co.uk";

        $recipients = $toEmail . ',' . $bcc . ',' . $cc;

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'On-Site Visit Confirmation for Service Request ' . $callRef,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        if ($cc) {
            $hdrs['Cc'] = $cc;
        }

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $recipients,
            $hdrs,
            $body
        );

    }

    /**
     * Remove all SRs assigned to system user
     *
     */
    public function clearSystemSrQueue()
    {
        global $db;

        $sql = "
          SELECT
            pro_problemno
          FROM
            problem
          WHERE
            pro_consno = " . USER_SYSTEM .
            " AND pro_status = 'I'";

        $db->query($sql);
        $ids = array();
        while ($db->next_record()) {
            $ids[] = $db->Record[0];
        }

        foreach ($ids as $id) {
            $this->sendServiceRemovedEmail(
                $id,
                true
            );
        }

        if (count($ids) > 0) {
            $idsAsString = implode(
                ',',
                $ids
            );
            $sql = "
            DELETE FROM
              problem
            WHERE
              pro_problemno IN(" . $idsAsString . ")";


            $db->query($sql);

            $sql = "
            DELETE FROM
              callactivity
            WHERE
              caa_problemno IN(" . $idsAsString . ")";

            $db->query($sql);

        }// end if

    }

    /**
     * New 2ndSite validation error request
     *
     * @param $customerID
     * @param $serverName
     * @param $serverCustomerItemID
     * @param $contractCustomerItemID
     * @param $missingLetters
     * @param $missingImages
     * @throws Exception
     */
    function raiseSecondSiteMissingImageRequest(
        $customerID,
        $serverName,
        $serverCustomerItemID,
        $contractCustomerItemID,
        $missingLetters,
        $missingImages
    )
    {
        $detailsWithoutDriveLetters = '<p><strong>The following image(s) have not been found for ' . $serverName;
        $details = $detailsWithoutDriveLetters . ': ' . implode(
                ',',
                $missingLetters
            ) . '</strong></p>';

        foreach ($missingImages as $image) {
            $details .= '<ul>' . $image . '</ul>';
        }

        $this->createSecondsiteSR(
            $customerID,
            $contractCustomerItemID,
            $detailsWithoutDriveLetters,
            $details,
            $serverName,
            $serverCustomerItemID
        );
    } // end sendPriorityOneReopenedEmail

    /**
     * @param $customerID
     * @param $contractCustomerItemID
     * @param $matchText
     * @param $details
     * @param $serverName
     * @param $serverCustomerItemID
     * @throws Exception
     */
    function createSecondsiteSR(
        $customerID,
        $contractCustomerItemID,
        $matchText,
        $details,
        $serverName,
        $serverCustomerItemID
    )
    {
        $priority = 2;

        $dbeProblem = new DBEProblem($this);

        $dbeContact = new DBEContact($this);

        $dbeContact->getMainSupportRowsByCustomerID($customerID);

        if (!$dbeContact->fetchNext()) {
            return; // no main support contact so abort
        }

        $dbeCallActivity = new DBECallActivity($this);
        /*
    Is there an existing activity for this exact problem?

    If so, we will append to that SR
    */
        $callActivityID = $this->getExisting2ndSiteActivityID(
            $customerID,
            $contractCustomerItemID,
            $matchText
        );

        $slaResponseHours =
            $this->getSlaResponseHours(
                $priority,
                $customerID,
                $dbeContact->getValue(DBEContact::contactID)
            );

        if (!$callActivityID) {
            /* create new issue */
            $dbeProblem->setValue(
                DBEProblem::slaResponseHours,
                $slaResponseHours
            );
            $dbeProblem->setValue(
                DBEProblem::customerID,
                $customerID
            );
            $dbeProblem->setValue(
                DBEProblem::status,
                'I'
            );
            $dbeProblem->setValue(
                DBEProblem::priority,
                $priority
            );
            $dbeProblem->setValue(
                DBEProblem::queueNo,
                2
            );
            $dbeProblem->setValue(
                DBEProblem::dateRaised,
                date(DATE_MYSQL_DATETIME)
            );
            $dbeProblem->setValue(
                DBEProblem::contactID,
                $dbeContact->getValue(DBEContact::contactID)
            );
            $dbeProblem->setValue(
                DBEProblem::hideFromCustomerFlag,
                'Y'
            );
            $dbeProblem->setValue(
                DBEProblem::contractCustomerItemID,
                $contractCustomerItemID
            );
            $dbeProblem->setValue(
                DBEProblem::hdLimitMinutes,
                $this->dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
            );
            $dbeProblem->setValue(
                DBEProblem::esLimitMinutes,
                $this->dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
            );
            $dbeProblem->setValue(
                DBEProblem::imLimitMinutes,
                $this->dsHeader->getValue(DBEHeader::imTeamLimitMinutes)
            );
            $dbeProblem->setValue(
                DBEProblem::userID,
                null
            );        // not allocated
            $dbeProblem->insertRow();

            $problemID = $dbeProblem->getPKValue();

            $dbeCallActivity->setValue(
                DBEJCallActivity::callActivityID,
                null
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::siteNo,
                $dbeContact->getValue(DBEContact::siteNo)
            ); // contact default siteno
            $dbeCallActivity->setValue(
                DBEJCallActivity::contactID,
                $dbeContact->getValue(DBEContact::contactID)
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::callActTypeID,
                CONFIG_INITIAL_ACTIVITY_TYPE_ID
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::date,
                date(DATE_MYSQL_DATE)
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::startTime,
                date('H:i')
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::endTime,
                date('H:i')
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'C'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::serverGuard,
                'Y'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::secondsiteErrorServer,
                $serverName
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::secondsiteErrorCustomerItemID,
                $serverCustomerItemID
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::reason,
                $details
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::problemID,
                $problemID
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::userID,
                USER_SYSTEM
            );

            $dbeCallActivity->insertRow();

        } else {

            $this->createFollowOnActivity(
                $callActivityID,
                CONFIG_2NDSITE_BACKUP_ACTIVITY_TYPE_ID,
                $dbeContact->getValue(DBEContact::contactID),
                $details,
                false,
                true
            );
        }

    }

    /**
     * Get existing activity that is in progress or fixed
     *
     * @param mixed $customerID
     * @param mixed $contractCustomerItemID
     * @param mixed $matchText
     * @return bool
     */
    private function getExisting2ndSiteActivityID($customerID,
                                                  $contractCustomerItemID,
                                                  $matchText
    )
    {

        global $db;

        $sql = "
        SELECT
          ca.caa_callactivityno
        FROM
          callactivity ca
          JOIN problem p ON p.pro_problemno = ca.caa_problemno
        WHERE
          p.pro_custno = " . $customerID . "
          AND p.pro_contract_cuino = " . $contractCustomerItemID . "
          AND p.pro_status IN ('P', 'F' )
          AND ca.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . "
          AND ca.reason LIKE '%" . trim(addslashes($matchText)) . "%'";

        $db->query($sql);
        if ($db->next_record()) {
            return $db->Record['caa_callactivityno'];
        } else {
            return false;
        }
    } // end sendServiceReallocatedEmail

    /**
     * @param $customerID
     * @param $serverName
     * @param $serverCustomerItemID
     * @param $contractCustomerItemID
     * @param $networkPath
     * @throws Exception
     */
    function raiseSecondSiteLocationNotFoundRequest(
        $customerID,
        $serverName,
        $serverCustomerItemID,
        $contractCustomerItemID,
        $networkPath
    )
    {
        $details = '<p><strong>Image Location ' . $networkPath . ' cannot be found for ' . $serverName . '</p>';

        $this->createSecondsiteSR(
            $customerID,
            $contractCustomerItemID,
            $details,
            $details,
            $serverName,
            $serverCustomerItemID
        );
    }

    public function updateLinkedSalesOrder($callActivityID,
                                           $salesOrderID
    )
    {
        $dsCallActivity = new DataSet($this);
        $this->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $problemID = $dsCallActivity->getValue(DBEJCallActivity::problemID);

        $dbeProblem = new DBEProblem($this);

        $dbeProblem->getRow($problemID);
        $dbeProblem->setValue(
            DBEJProblem::linkedSalesOrderID,
            $salesOrderID
        );
        $dbeProblem->updateRow();

        return;

    }

    /*
  Send email to SD Managers requesting more time to be allocated to SR
  */

    /**
     * @return array
     */
    public function getOpenSrsByUser()
    {
        global $db;

        $sql =
            "SELECT
          CONCAT( SUBSTR( firstName, 1, 1),SUBSTR( lastName, 1, 1 )) AS initials,
          COUNT(*) AS count
  
        FROM
          problem
          JOIN consultant ON `cns_consno` = pro_consno
          JOIN callactivity ON caa_problemno = pro_problemno 
  
        WHERE  
          pro_status IN ('I', 'P')
          AND caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " GROUP BY
          pro_consno order by pro_consno";
        $ret = [];
        $db->query($sql);
        while ($db->next_record()) {
            $ret[] = $db->Record;
        }
        return $ret;
    }

    /**
     * Allocate additional hours to SR
     *
     * Add hours to clock of team of allocated user
     *
     * @param mixed $problemID
     * @param $level
     * @param $minutes
     * @param $comments
     */
    public function allocateAdditionalTime($problemID,
                                           $level,
                                           $minutes,
                                           $comments
    )
    {
        $this->dbeProblem = new DBEProblem($this);
        $this->dbeProblem->getRow($problemID);

        if ($level == 1) {
            $this->dbeProblem->setValue(
                DBEProblem::hdLimitMinutes,
                $this->dbeProblem->getValue(DBEProblem::hdLimitMinutes) + $minutes
            );
            $this->dbeProblem->setValue(
                DBEProblem::hdTimeAlertFlag,
                'N'
            ); // reset alert flag
        } elseif ($level == 2) {
            $this->dbeProblem->setValue(
                DBEProblem::esLimitMinutes,
                $this->dbeProblem->getValue(DBEProblem::esLimitMinutes) + $minutes
            );
            $this->dbeProblem->setValue(
                DBEProblem::esTimeAlertFlag,
                'N'
            );
        } else {
            $this->dbeProblem->setValue(
                DBEProblem::imLimitMinutes,
                $this->dbeProblem->getValue(DBEProblem::imLimitMinutes) + $minutes
            );
            $this->dbeProblem->setValue(
                DBEProblem::imTimeAlertFlag,
                'N'
            );
        }

        $this->dbeProblem->updateRow();

        $this->sendTimeAllocatedEmail(
            $minutes,
            $comments
        );
    }

    private function sendTimeAllocatedEmail($minutes,
                                            $comments
    )
    {
        $buMail = new BUMail($this);

        $problemID = $this->dbeProblem->getValue(DBEJProblem::problemID);
        $dbeUser = new DBEUser($this);

        $assignedUser = $this->dbeProblem->getValue(DBEProblem::userID);

        if (!$assignedUser) {
            return;
        }

        $dbeUser->getRow($assignedUser);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $dbeJCallActivity = $this->getFirstActivityInProblem($problemID);
        $dbeJLastCallActivity = $this->getLastActivityInProblem($problemID);

        $toEmail = $dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'ServiceTimeAllocatedEmail.inc.html'
        );

        $urlDisplayActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJLastCallActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $userName = $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(DBEUser::lastName);

        $template->setVar(
            array(
                'problemID'          => $problemID,
                'reason'             => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'customerName'       => $dbeJCallActivity->getValue(DBEJCallActivity::customerName),
                'userName'           => $userName,
                'minutes'            => round(
                    $minutes,
                    2
                ),
                'comments'           => $comments,
                'urlDisplayActivity' => $urlDisplayActivity,
                'internalNotes'      => $this->dbeProblem->getValue(DBEJCallActivity::internalNotes)
            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');


        $subject = 'Additional ' . $minutes . ' minutes Allocated to SR ' . $problemID . ' ' . $dbeJLastCallActivity->getValue(
                DBEJCallActivity::customerName
            );

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    public function requestAdditionalTime($problemID,
                                          $reason,
                                          $callActivityID
    )
    {
        if ($callActivityID) {
            $dbeJCallActivity = new DBEJCallActivity($this);
            $dbeJCallActivity->getRow($callActivityID);
            $requesterID = $dbeJCallActivity->getValue(DBEJCallActivity::userID);
        } else {
            $requesterID = $GLOBALS['auth']->is_authenticated();
        }
        $this->createTimeRequestsActivity(
            $problemID,
            $reason,
            $requesterID
        );
    }

    function getUserPerformanceWeekToDate($userID)
    {
        return $this->getUserPerformanceDaysToDate(
            $userID,
            7
        );
    }

    function getUserPerformanceDaysToDate($userID,
                                          $daysToDate
    )
    {
        global $db;

        $sql =
            "SELECT
        SUM( loggedHours / dayHours ) * 100 as performancePercentage
      FROM
        user_time_log
      WHERE
        loggedDate >= DATE_SUB( DATE( NOW() ), INTERVAL $daysToDate DAY )
      AND userID = $userID";

        $db->query($sql);

        $db->next_record();
        return $db->Record[0];
    }

    function getUserPerformanceMonthToDate($userID)
    {
        return $this->getUserPerformanceDaysToDate(
            $userID,
            30
        );
    }

    /**
     * For every active user that did not log in today, create a default time log
     * record. Exclude public holidays.
     *
     * Called by a timed process last thing at night to ensure all active users
     * have a log entry for each working day.
     * @param string $date
     * @throws Exception
     */
    function createUserTimeLogsForMissingUsers($date = null)
    {
        if (!$date) {
            $date = new DateTime();
        } else {
            $date = new DateTime($date);
        }

        $bankHolidays = common_getUkBankHolidays($date->format('Y'));

        if (in_array(
                $date->format('Y-m-d'),
                $bankHolidays
            ) || $date->format('N') > 5) {
            return; // ignore holidays
        }


        $this->dbeUser->getRows(true);
        while ($this->dbeUser->fetchNext()) {
            $this->createUserTimeLogRecord(
                $this->dbeUser->getValue(DBEUser::userID),
                $date
            );
        }

    }

    /**
     * Create record on userTimeLog for given user
     *
     * @note: The startTime is set to zero because this function is being used
     * to generate holiday records at the end of the day. The user didn't have a
     * start time.
     *
     * @param mixed $userID
     * @param DateTime|null $date
     * @throws Exception
     */
    function createUserTimeLogRecord($userID,
                                     DateTime $date = null
    )
    {
        global $db;

        if (!$date) {
            $date = new DateTime();
        }
        $db->query(
            "SELECT
        team.level as teamLevel,
        consultant.standardDayHours
        
      FROM
        consultant
        JOIN team ON team.teamID = consultant.teamID
      WHERE
        cns_consno = $userID"
        );
        $db->next_record();
        $teamLevel = $db->Record['teamLevel'];
        $standardDayHours = $db->Record['standardDayHours'];
        /*
    Set logged hours to the target for the team
    */
        if ($teamLevel == 1) {
            $targetPercentage = $this->dsHeader->getValue(DBEJHeader::hdTeamTargetLogPercentage);
        } else {
            $targetPercentage = $this->dsHeader->getValue(DBEJHeader::esTeamTargetLogPercentage);
        }

        $loggedHours = $standardDayHours * ($targetPercentage / 100);

        $dateFormatted = $date->format('Y-m-d');
        $sql =
            "INSERT IGNORE INTO user_time_log
        (
        `userID`,
        `teamLevel`,
        `loggedDate`,
        `loggedHours`,
        `dayHours`,
        `startedTime` ,
        holiday
        ) 
      VALUES 
        (
          $userID,
          $teamLevel,
          '$dateFormatted',
          $loggedHours,
          $standardDayHours,
          '00:00:00',
          1
        )";

        $db->query($sql);
    }

    public function updateManagementReviewReason($problemID,
                                                 $text
    )
    {
        $dbeProblem = $this->getDbeProblem();
        $dbeProblem->getRow($problemID);
        $dbeProblem->setValue(
            DBEProblem::managementReviewReason,
            $text
        );
        $dbeProblem->updateRow();
        /*
    Send email to managers
    */
        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'ManagementReviewSummaryAddedEmail.inc.html'
        );

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJProblem->getValue(
                DBEJCallActivity::callActivityID
            );

        $template->setVar(
            array(
                'problemID'     => $problemID,
                'urlActivity'   => $urlActivity,
                'customerName'  => $dbeJProblem->getValue(DBEJProblem::customerName),
                'initialReason' => $dbeJProblem->getValue(DBEJProblem::reason),
                'fixSummary'    => $dbeJProblem->getValue(DBEJProblem::lastReason),
                'managementReviewReason'
                                => $dbeJProblem->getValue(DBEJProblem::managementReviewReason)
            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');
        $toEmail = 'managementreview@' . CONFIG_PUBLIC_DOMAIN;

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Management Review Summary Added ' . $dbeJProblem->getValue(
                    DBEJProblem::customerName
                ) . ' SR ' . $problemID,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);


        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

        $this->logOperationalActivity(
            $problemID,
            $text
        );
    }

    private function getDbeProblem()
    {
        if (!$this->dbeProblem) {
            $this->dbeProblem = new DBEProblem($this);
        }
        return $this->dbeProblem;
    }

    function getManagementReviewsInPeriod($customerID,
                                          DateTimeInterface $startDate,
                                          DateTimeInterface $endDate,
                                          &$dsResults
    )
    {
        $dbeProblem = $this->getDbeProblem();
        $dbeProblem->getManagementReviews(
            $customerID,
            $startDate,
            $endDate
        );

        return ($this->getData(
            $dbeProblem,
            $dsResults
        ));

    }

    function getSrPercentages($days = 30,
                              $fromDate = false,
                              $toDate = false
    )
    {
        global $db;

        /* count SRs */
        $sql =
            "SELECT 
        cus_name,
        SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 3600 AS hours,
        COUNT( DISTINCT pro_problemno ) AS srCount
      
      FROM
        problem
        JOIN callactivity ON caa_problemno = pro_problemno
        JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
        JOIN customer ON cus_custno = pro_custno

      WHERE
        callacttype.`travelFlag` = 'N'
        AND caa_endtime is not null";

        if ($days) {
            $sql .=
                " AND caa_date >= DATE_SUB( NOW(), INTERVAL $days DAY )";
        } else if ($fromDate) {
            $sql .= " AND caa_date >= '$fromDate' ";

            if ($toDate) {
                $sql .= " AND caa_date <= '$toDate' ";
            }
        }

        $sql .=

            " GROUP BY
        pro_custno

      ORDER BY 
        hours DESC;";

        $db->query($sql);
        /*
    Get grand total hours
    */
        $grandTotalHours = 0;

        $results = array();

        while ($db->next_record()) {

            $grandTotalHours += $db->Record['hours'];
            $results[] = $db->Record;
        }
        /*
    Calculate percentages
    */
        $ret = array();

        foreach ($results as $result) {
            $result['percentage'] = 0;
            if ($grandTotalHours) {
                $result['percentage'] = ($result['hours'] / $grandTotalHours) * 100;
            }
            $ret[] = $result;
        }
        return $ret;
    }

    public function getHDTeamUsedTime($problemID,
                                      $excludedActivityID = null
    )
    {
        return $this->getUsedTimeForProblemAndTeam(
            $problemID,
            1,
            $excludedActivityID
        );
    }

    public function getUsedTimeForProblemAndTeam($problemID,
                                                 $teamID,
                                                 $excludedActivityID = null
    )
    {
        global $db;

        $sql =
            "SELECT sum(time_to_sec(timediff(caa_endtime, caa_starttime)) / 60) AS amountOfTime
            FROM
              `problem`
              LEFT JOIN callactivity ON callactivity.`caa_problemno` = problem.`pro_problemno`
              LEFT JOIN consultant ON caa_consno = cns_consno
            WHERE  pro_problemno = $problemID AND teamID = $teamID AND caa_starttime AND caa_endtime and caa_callacttypeno in (4, 8, 11, 18)";

        if ($excludedActivityID) {
            $sql .= " and caa_callactivityno <> $excludedActivityID";
        }

        $db->query($sql);
        $db->next_record();
        return empty($db->Record['amountOfTime']) ? 0 : $db->Record['amountOfTime'];
    }

    public function getESTeamUsedTime($problemID,
                                      $excludedActivityID = null
    )
    {
        return $this->getUsedTimeForProblemAndTeam(
            $problemID,
            2,
            $excludedActivityID
        );
    }

    public function getIMTeamUsedTime($problemID,
                                      $excludedActivityID = null
    )
    {
        return $this->getUsedTimeForProblemAndTeam(
            $problemID,
            4,
            $excludedActivityID
        );
    }

    private function sendMonitoringEmails($callActivityID)
    {
        $buMail = new BUMail($this);

        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivityID);

        $validActivityTypeIDs = [
            4,
            7,
            8,
            11,
            18,
            57,
            55,
            59,
        ];

        if (!in_array(
            $dbeJCallActivity->getValue(DBEJCallActivity::callActTypeID),
            $validActivityTypeIDs
        )) {
            return;
        }

        $monitoringPeople = $this->getPeopleMonitoringProblem($dbeJCallActivity->getValue(DBEJCallActivity::problemID));


        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $activityRef = $dbeJCallActivity->getValue(DBEJCallActivity::problemID) . ' ' . $dbeJCallActivity->getValue(
                DBEJCallActivity::customerName
            );

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'MonitoringEmail.inc.html'
        );

        $urlActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJCallActivity->getPKValue(
            );

        $durationHours = common_convertHHMMToDecimal(
                $dbeJCallActivity->getValue(DBEJCallActivity::endTime)
            ) - common_convertHHMMToDecimal($dbeJCallActivity->getValue(DBEJCallActivity::startTime));

        $awaitingCustomerResponse = null;

        if ($dbeJCallActivity->getValue(DBEJCallActivity::requestAwaitingCustomerResponseFlag) == 'Y') {
            $awaitingCustomerResponse = 'Awaiting Customer';
        } else {
            $awaitingCustomerResponse = 'Awaiting CNC';
        }


        $template->setVar(
            array(
                'activityRef'                 => $activityRef,
                'activityDate'                => $dbeJCallActivity->getValue(DBEJCallActivity::date),
                'activityStartTime'           => $dbeJCallActivity->getValue(DBEJCallActivity::startTime),
                'activityEndTime'             => $dbeJCallActivity->getValue(DBEJCallActivity::endTime),
                'activityTypeName'            => $dbeJCallActivity->getValue(DBEJCallActivity::activityType),
                'urlActivity'                 => $urlActivity,
                'userName'                    => $dbeJCallActivity->getValue(DBEJCallActivity::userName),
                'durationHours'               => round(
                    $durationHours,
                    2
                ),
                'requestStatus'               => $this->problemStatusArray[$dbeJCallActivity->getValue(
                    DBEJCallActivity::problemStatus
                )],
                'awaitingCustomerResponse'    => $awaitingCustomerResponse,
                'customerName'                => $dbeJCallActivity->getValue(DBEJCallActivity::customerName),
                'reason'                      => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'CONFIG_SERVICE_REQUEST_DESC' => CONFIG_SERVICE_REQUEST_DESC
            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $buMail->mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);
        foreach ($monitoringPeople as $monitoringPerson) {
            $toEmail = $monitoringPerson['cns_logname'] . '@cnc-ltd.co.uk';

            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => 'Monitored SR ' . $dbeJCallActivity->getValue(
                        DBEJCallActivity::problemID
                    ) . ' For ' . $dbeJCallActivity->getValue(DBEJCallActivity::customerName),
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
        }


    }

    private function getPeopleMonitoringProblem($problemID)
    {
        global $db;

        $sql = "SELECT * FROM problem_monitoring left join consultant on problem_monitoring.cons_no = consultant.cns_consno WHERE problemId = $problemID";


        $db->query($sql);
        $data = [];
        while ($db->next_record()) {
            $data[] = $db->Record;
        }
        return $data;
    }

    public function toggleMonitoringFlag($problemID)
    {
        global $db;
        $userID = $this->loggedInUserID;
        if (!self::checkMonitoringFlag($problemID)) {
            //we need to enable it
            $db->query("insert into problem_monitoring(problemId, cons_no) values ($problemID, $userID)");
            return;
        }

        //we need to disable it
        $db->query("delete from problem_monitoring where problemId = $problemID and cons_no = $userID");
    }

    public function checkMonitoringFlag($problemID)
    {
        global $db;

        $userID = $this->loggedInUserID;

        $sql = "SELECT * FROM problem_monitoring WHERE problemId = $problemID and cons_no = $userID";


        $db->query($sql);
        return !!$db->num_rows();
    }

    public function unhideSR($problemID)
    {

        global $db;
        $sql = "update problem set pro_hide_from_customer_flag = 'N'  WHERE pro_problemno = $problemID";
        return $db->query($sql);
    }

    public function getSDDashBoardData($problems,
                                       $limit,
                                       $order = 'shortestSLARemaining',
                                       $isP5 = false,
                                       $showHelpDesk = true,
                                       $showEscalation = true,
                                       $showImplementation = true
    )
    {
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getDashBoardRows(
            $limit,
            $order,
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showImplementation
        );

        $this->getData(
            $dbeJProblem,
            $problems
        );

    }

    /**
     * @param $problemID
     * @param bool $ignoreOperation
     * @return int
     */
    public function getActivityCount($problemID,
                                     $ignoreOperation = true
    )
    {
        $DBEJCallActivity = new DBEJCallActivity($this);
        $DBEJCallActivity->getRowsByProblemID(
            $problemID,
            true,
            !$ignoreOperation
        );
        $count = 0;
        while ($thing = $DBEJCallActivity->fetchNext()) {
            $count++;
        }
        return $count;
    }

    public function getSDDashBoardEngineersInSRData($problems,
                                                    $engineersMaxCount = 3,
                                                    $pastHours = 24,
                                                    $limit = 5,
                                                    $isP5 = false,
                                                    $showHelpDesk = true,
                                                    $showEscalation = true,
                                                    $showImplementation = true
    )
    {
        $dbeJProblem = new DBEJProblem($this);

        $dbeJProblem->getDashBoardEngineersInSRRows(
            $engineersMaxCount,
            $pastHours,
            $limit,
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showImplementation
        );

        $this->getData(
            $dbeJProblem,
            $problems
        );

    }

    /**
     * @param $problemID
     * @param $message
     * @param $type
     * @throws Exception
     */
    public function sendSalesRequest($problemID,
                                     $message,
                                     $type
    )
    {
        // we have to create an open "sales activity"
        $salesRequestActivity = $this->createSalesRequestActivity(
            $problemID,
            $message,
            "O"
        );


        $buStandardText = new BUStandardText($this);

        $dbeStandardText = new DataSet($this);
        $buStandardText->getStandardTextByID(
            $type,
            $dbeStandardText
        );
        $destEmail = $dbeStandardText->getValue(DBEStandardText::salesRequestEmail);
        $problem = new DBEProblem($this);
        $problem->getRow($problemID);

        if ($dbeStandardText->getValue(DBEStandardText::salesRequestUnassignFlag) == 'Y') {
            $problem->setValue(
                DBEProblem::userID,
                null
            );
        }

        if ($type != "New Starter/Office 365 License") {
            $alarmDate = (new DateTime())->add(new DateInterval('P1D'));
            $problem->setValue(
                DBEProblem::alarmDate,
                $alarmDate->format('Y-m-d')
            );
            $problem->setValue(
                DBEProblem::alarmTime,
                $alarmDate->format('h:i')
            );
        }
        $problem->updateRow();

        $this->sendSalesRequestEmail(
            $salesRequestActivity,
            $destEmail
        );
    }

    private function sendSalesRequestEmail(DBEJCallActivity $salesRequestActivity,
                                           $email
    )
    {
        $buMail = new BUMail($this);
        $problemID = $salesRequestActivity->getValue(DBEJCallActivity::problemID);
        $dsInitialCallActivity = $this->getFirstActivityInProblem($problemID);
        $lastActivity = $this->getLastActivityInProblem($problemID);
        $this->dbeUser->getRow($this->loggedInUserID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->set_file(
            'page',
            'SalesRequestEmail.html'
        );

        $userName = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(DBEUser::lastName);


        $urlSalesRequestReview = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=salesRequestReview&callActivityID=' . $salesRequestActivity->getValue(
                DBEJCallActivity::callActivityID
            ) . '&fromEmail=true';

        $urlLastActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $lastActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $template->setVar(
            array(
                'problemID' => $problemID,

                'userName' => $userName,

                'urlSalesRequestControl' => $urlSalesRequestReview,

                'urlLastActivity' => $urlLastActivity,

                'requestReason' => $salesRequestActivity->getValue(DBEJCallActivity::reason)
            )
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $toEmail = $email;

        $subject = 'Sales Request submitted for ' . $dsInitialCallActivity->getValue(
                DBEJCallActivity::customerName
            ) . ' by ' . $userName . ' for SR' . $problemID;


        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    /**
     * @param $problemID
     * @param $message
     * @param string $status
     * @return DBEJCallActivity
     */
    private function createSalesRequestActivity($problemID,
                                                $message,
                                                $status = "C"
    )
    {
        $lastActivity = $this->getLastActivityInProblem($problemID);

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($lastActivity->getValue(DBEJCallActivity::callActivityID));
        $dbeCallActivity->setPKValue(null);
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $this->loggedInUserID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_SALES_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $message
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            'N'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::salesRequestStatus,
            $status
        );

        $dbeCallActivity->insertRow();

        $DBEJCallActivity = new DBEJCallActivity($this);
        $DBEJCallActivity->getRow($dbeCallActivity->getPKValue());

        return $DBEJCallActivity;
    }

    public function getProblemsByContact($contactID)
    {
        $dbeJProblem = new DBEJProblem($this);

        $dbeJProblem->getProblemsByContactID($contactID);

        $this->getData(
            $dbeJProblem,
            $dsResults
        );

        return $dsResults;
    }

    private function contactNotAuthorized(AutomatedRequest $record,
                                          DBEContact $dbeContact
    )
    {
        echo '<div>The sender was not authorized, send an email to the sender and to the primary contact</div>';

        $buCustomer = new BUCustomer($this);
        $primaryMainContactDS = $buCustomer->getPrimaryContact($record->getCustomerID());


        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $toEmail = $primaryMainContactDS->getValue(DBEContact::email);

        $contactName = $dbeContact->getValue(
                DBEContact::firstName
            ) . " " . $dbeContact->getValue(DBEContact::lastName);

        if ($primaryMainContactDS->rowCount) {
            $buMail = new BUMail($this);
            $template = new Template(
                EMAIL_TEMPLATE_DIR,
                "remove"
            );
            $template->set_file(
                'page',
                'NotAuthorisedPrimaryMainContactEmail.html'
            );


            $template->setVar(
                [
                    "primaryMainContactName" => $primaryMainContactDS->getValue(DBEContact::firstName),
                    "contactName"            => $contactName,
                    "contactSupportLevel"    => $dbeContact->getValue(DBEContact::supportLevel)
                ]
            );

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => $contactName . " is not authorised to initiate support calls",
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

            $fileName = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                ) . " Request At " . (new DateTime())->format('d-m-Y H:i') . '.html';
            $buMail->mime->addAttachment(
                $record->getHtmlBody(),
                'application/octet-stream',
                $fileName,
                false
            );

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $body = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );

        }
        $buMail = new BUMail($this);
        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            'NotAuthorisedContactEmail.html'
        );
        $toEmail = $dbeContact->getValue(DBEContact::email);

        $template->setVar(
            [
                "contactFirstName" => $dbeContact->getValue(DBEContact::firstName)
            ]
        );

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => "Not authorised to initiate support",
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );


        return true;
    }
}