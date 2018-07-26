<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerCallActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJContract.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerCallActivityMonth.inc.php");
require_once($cfg ["path_dbe"] . "/DBECurrentActivity.inc.php");
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
require_once($cfg ["path_dbe"] . "/DBEEscalation.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg ["path_bu"] . "/BUSite.inc.php");
require_once($cfg ["path_bu"] . "/BUHeader.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_bu"] . "/BUContact.inc.php");
require_once($cfg ["path_bu"] . "/BUProblemSLA.inc.php");
require_once($cfg ["path_func"] . "/activity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBESite.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_dbe"] . "/DBEJPorhead.inc.php");

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
    /** @var Template */
    public $template;
    var $csvSummaryFileHandle;
    var $totalCost = 0;
    var $loggedInEmail = '';
    var $loggedInUserID = '';
    var $standardVatRate = 0;
    /**
     *
     * @var DBEJCallActivity
     */
    public $dbeJCallActivity = '';
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
            "5" => "Managers"
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
    private $dbeProblem = '';
    /**
     *
     * @var DBEUser
     */
    private $dbeUser = '';
    /**
     *
     * @var DBECallActivitySearch
     */
    private $dbeCallActivitySearch = '';
    /** @var DataSet */
    private $dsHeader;

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

        if ($GLOBALS ['auth']) {
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
            'status',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'customerID',
            DA_STRING,
            DA_ALLOW_NULL
        );
//        $dsData->addColumn('project', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn(
            'userID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'rootCauseID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'contractCustomerItemID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'priority',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'customerName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'activityText',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'serviceRequestSpentTime',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'individualActivitySpentTime',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'fromDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'toDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'callActivityID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'problemID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'callActTypeID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'contractType',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'linkedSalesOrderID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'managementReviewOnly',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'breachedSlaOption',
            DA_STRING,
            DA_ALLOW_NULL
        );


        $dsData->setValue(
            'customerID',
            ''
        );
        $dsData->setValue(
            'userID',
            ''
        );
        $dsData->setValue(
            'contractType',
            ''
        );
        $dsData->setValue(
            'rootCauseID',
            ''
        );
        $dsData->setValue(
            'contractCustomerItemID',
            '99'
        ); // all(blank is used for T&M)
        $dsData->setValue(
            'priority',
            ''
        );
        $dsData->setValue(
            'customerName',
            ''
        );
        $dsData->setValue(
            'status',
            'U'
        );
        $dsData->setValue(
            'callActTypeID',
            ''
        );
        $dsData->setValue(
            'linkedSalesOrderID',
            ''
        );
        $dsData->setValue(
            'managementReviewOnly',
            'N'
        );
        $dsData->setValue(
            'breachedSlaOption',
            ''
        );
    }

    function initialiseCustomerActivityMonthForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            'customerID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'fromDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'toDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'customerID',
            ''
        );
    }

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
            trim($dsSearchForm->getValue('callActivityID')),
            trim($dsSearchForm->getValue('problemID')),
            trim($dsSearchForm->getValue('customerID')),
            trim($dsSearchForm->getValue('userID')),
            trim($dsSearchForm->getValue('status')),
            trim($dsSearchForm->getValue('rootCauseID')),
            trim($dsSearchForm->getValue('priority')),
            trim($dsSearchForm->getValue('activityText')),
            trim($dsSearchForm->getValue('serviceRequestSpentTime')),
            trim($dsSearchForm->getValue('individualActivitySpentTime')),
            trim($dsSearchForm->getValue('fromDate')),
            trim($dsSearchForm->getValue('toDate')),
            trim($dsSearchForm->getValue('contractCustomerItemID')),
            trim($dsSearchForm->getValue('callActTypeID')),
            trim($dsSearchForm->getValue('linkedSalesOrderID')),
            trim($dsSearchForm->getValue('managementReviewOnly')),
            trim($dsSearchForm->getValue('breachedSlaOption')),
            $sortColumn,
            $sortDirection,
            $limit
        );
        $this->dbeCallActivitySearch->fetchNext();

        $dsResults->replicate($this->dbeCallActivitySearch); // into a dataset for return
    }

    function sendEmailToSales($callActivityID,
                              $message
    )
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($GLOBALS['auth']->is_authenticated());

        $this->sendInternalFurtherActionEmail(
            $callActivityID,
            $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(DBEUser::lastName),
            date(CONFIG_MYSQL_DATE),
            $message
        );

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeCallActivity->getValue(DBECallActivity::problemID));
        $dbeProblem->setValue(
            DBEJProblem::internalNotes,
            $dbeProblem->getValue(DBEProblem::internalNotes) . '<BR/><BR/><STRONG>' .
            'Request to Sales on ' . date('d/m/Y H:i') . ' from  ' .
            $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(DBEUser::lastName) .
            '</STRONG><BR/><BR/>' .
            $message
        );

        $dbeProblem->updateRow();
    }

    /**
     * Send an email alert to the internal email address against given further action type
     */
    function sendInternalFurtherActionEmail(
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

        $body = '';    // initialise

        if (!$emailBody) { // if there is a body then don't display activity details
            $body .= 'REASON:' . "<BR/><BR/>";
            $body .= $dbeJCallActivity->getValue(DBECallActivity::reason) . "<BR/><BR/>";
            if ($dbeJCallActivity->getValue(DBECallActivity::internalNotes)) {
                $body .= 'NOTES:' . "<BR/><BR/>";
                $body .= $dbeJCallActivity->getValue(DBECallActivity::internalNotes) . "<BR/><BR/>";
            }
        } else {
            $body = $emailBody . "<BR/><BR/>";
        }

        $body .= "Generated by " . $engineerName . " on " . $dateCreated . "<BR/><BR/>";

        $body .= 'Open the activity: http://' . $_SERVER ['HTTP_HOST'];

        $body .= Controller::formatForHTML(
            '/Activity.php?action=displayActivity&callActivityID=' . $callActivityID,
            1
        );

        $subject = 'Activity reqd for ' . $dbeJCallActivity->getValue(
                DBEJCallActivity::customerName
            ) . ' against activity ' . $callActivityID . ' today(' . date('d/m/Y') . ')';

        $body .= "<BR/><BR/>";

        if ($dbeJCallActivity->getValue(DBEJCallActivity::contractCustomerItemID)) {
            $contractDescription = Controller::formatForHTML(
                $dbeJCallActivity->getValue(DBEJCallActivity::contractDescription),
                1
            );
        } else {
            $contractDescription = 'T&M';
        }

        $body .= 'Contract: ' . $contractDescription . "<BR/><BR/>";

        if ($dbeJCallActivity->getValue(DBECallActivity::serverGuard) == 'Y') {
            $body .= "ServerGuard related <BR/><BR/>";
        } else {
            $body .= "Not ServerGuard related <BR/><BR/>";
        }

        if ($dbeJCallActivity->getValue(DBEJCallActivity::projectID)) {
            $body .= 'Related to project:  ' . $dbeJCallActivity->getValue(
                    DBEJCallActivity::projectDescription
                ) . "<BR/><BR/>";
        }

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
    }

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
            ''
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::date,
            date(CONFIG_MYSQL_DATE)
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::endTime,
            ''
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::status,
            'O'
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::reason,
            ''
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::siteDesc,
            $dsSite->getValue(DBESite::add1) .
            ' ' . $dsSite->getValue(DBESite::add2) .
            ' ' .
            $dsSite->getValue(DBESite::town)
        );
        $dsCallActivity->post();
    } // end sendServiceReallocatedEmail

    /**
     *
     * Set the activity tyoe to customer contact
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

    } // end sendSalesRequestAlertEmail

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
            $body,
            true
        );
    } // end sendServiceRemovedEmail

    /*
  Email to GL when a new activity is created that has same description/customerID
  as initial activity of a recently removed Request
  */

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
    } // end sendSpecialAttentionEmail

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
                ''
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
    } // end sendCritcalEmail

    /**
     * Create an operational activity using passed description
     *
     * @param mixed $description
     */
    function logOperationalActivity($problemID,
                                    $description
    )
    {
        $lastActivity = $this->getLastActivityInProblem($problemID);

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($lastActivity->getValue(DBEJCallActivity::callActivityID));
        $dbeCallActivity->setPKValue('');
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(CONFIG_MYSQL_DATE)
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
    } // end sendFutureVisitEmail

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
                ''
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
    }

    /**
     * reopen problem that has previously been fixed
     *
     * @param mixed problemID
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
        }

        $dbeProblem->updateRow();

        $this->sendEmailToCustomer(
            array(
                'problemID'     => $problemID,
                'templateName'  => 'ServiceReopenedEmail',
                'subjectSuffix' => 'Reopened'
            )
        );

        $this->logOperationalActivity(
            $problemID,
            'Reopened'
        );

    } // end sendActivityLoggedEmail

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
            $body,
            true
        );
    }

    /**
     * Create or update activity
     *
     * @param mixed $dsCallActivity
     * @param mixed $isFixed Indicates whether SR is being set to fixed
     */
    function updateCallActivity(&$dsCallActivity,
                                $isFixed = false
    )
    {
        $this->setMethodName('updateCallActivity');
        $dbeCallActivity = new DBECallActivity($this);
        $oldEndTime = ''; // new activity
        if ($dsCallActivity->getValue(DBEJCallActivity::callActivityID) != 0) {
            $dbeCallActivity->getRow($dsCallActivity->getValue(DBEJCallActivity::callActivityID));
            $oldEndTime = $dbeCallActivity->getValue(DBEJCallActivity::endTime);
            $oldReason = $dbeCallActivity->getValue(DBEJCallActivity::reason);
        }

        $dbeCallActType = new DBECallActType($this);
        $dbeCallActType->getRow($dsCallActivity->getValue(DBEJCallActivity::callActTypeID));

        // if this activity will now have an end time and the type specifies that we do not need to check it, set status to checked
        if ($oldEndTime == '' && $dsCallActivity->getValue(DBEJCallActivity::endTime) != '') {
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

        $this->updateDataaccessObject(
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
            '0000-00-00 00:00:00'
        );

        // if amended initial call activity date/time then set the problem date raised field to match
        if ($dsCallActivity->getValue(DBEJCallActivity::callActTypeID) == CONFIG_INITIAL_ACTIVITY_TYPE_ID) {
            $dbeProblem->setValue(
                DBEJProblem::dateRaised,
                $dsCallActivity->getValue(DBEJCallActivity::date) . ' ' . $dsCallActivity->getValue(
                    DBEJCallActivity::startTime
                )
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
                $sql =
                    "UPDATE contact
              SET con_notes = '" . $dsCallActivity->getValue(DBEJCallActivity::contactNotes) .
                    "' WHERE con_contno = " . $dsCallActivity->getValue(DBEJCallActivity::contactID);

                $this->db->query($sql);
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
                    $dbeProblem->getValue(DBEJProblem::customerID)
                );

            $dbeProblem->setValue(
                DBEJProblem::slaResponseHours,
                $slaResponseHours
            );
            $dbeProblem->updateRow();

            $this->sendEmailToCustomer(
                array(
                    'problemID'     => $dsCallActivity->getValue(DBEJProblem::problemID),
                    'templateName'  => 'ServicePriorityChangedEmail',
                    'subjectSuffix' => 'Priority Changed'
                )
            );

            $this->logOperationalActivity(
                $dsCallActivity->getValue(DBEJCallActivity::problemID),
                'Priority Changed from ' . $oldPriority . ' to ' . $dbeProblem->getValue(DBEJProblem::priority)
            );
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

            if (
                $dbeCallActivity->getValue(DBEJCallActivity::callActTypeID) != CONFIG_INITIAL_ACTIVITY_TYPE_ID &
                $dbeCallActivity->getValue(DBEJCallActivity::reason) != '' &
                $oldReason != $dbeCallActivity->getValue(DBEJCallActivity::reason) &
                $dbeCallActivity->getValue(DBEJCallActivity::endTime) != ''
            ) {
                $this->sendActivityLoggedEmail(
                    $dbeCallActivity->getValue(DBEJCallActivity::callActivityID),
                    false,
                    $isFixed
                );
            }
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dbeProblem->getValue(DBEJProblem::customerID),
                $dsCustomer
            );
            if (
                $dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
                $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')
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
                $dbeCallActivity->getValue(DBEJCallActivity::endTime) == '' &
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
            $body,
            true
        );
    } // end sendNotifyEscalatorUserEmail

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
                ) != ''                  // exclude future scheduled activity
            )
        ) {
            $this->sendUpdatedByAnotherUserEmail($dbeProblem->getValue(DBEJProblem::problemID));
        }

    }

    function sendUpdatedByAnotherUserEmail($problemID)
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

        $dbeJCallActivity = $this->getLastActivityInProblem($problemID);

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
     * Sends email to client when an activity has been logged
     *
     * @param mixed $callActivityID
     * @param mixed $SCRFile
     * @param boolean $isFixed whether SR is being fixed
     */
    function sendActivityLoggedEmail(
        $callActivityID,
        $SCRFile = false,
        $isFixed = false
    )
    {

        $buMail = new BUMail($this);

        $dbeJCallActivity = new DBEJCallActivity($this);
        $dbeJCallActivity->getRow($callActivityID);

        $dbeCallActType = new DBECallActType($this);
        $dbeCallActType->getRow($dbeJCallActivity->getValue(DBEJCallActivity::callActTypeID));

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($dbeJCallActivity->getValue(DBEJCallActivity::problemID));

        /**
         * activity type email option off or never send email for this request
         */
        if (
            $dbeCallActType->getValue(DBEJCallActType::customerEmailFlag) != 'Y' ||
            $dbeJCallActivity->getValue(DBEJCallActivity::hideFromCustomerFlag) == 'Y' ||
            $dbeJCallActivity->getValue(DBEJCallActivity::problemHideFromCustomerFlag) == 'Y'
        ) {

            return;


        }

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = $dbeJCallActivity->getValue(DBEJCallActivity::contactEmail);

        $activityRef = $dbeJCallActivity->getValue(DBEJCallActivity::problemID);

        ob_start();
        ?>
        <html>
        <head>
            <style type="text/css">
                <!--
                BODY, P, TD, TH {
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 10pt;
                }

                h2 {
                    font-size: 11pt;
                    font-weight: bold;

                }

                a {
                    font-weight: bold;
                }

                .singleBorder {
                    border: #e1e1f0 2px solid;
                }

                TABLE {
                    border-spacing: 1px;
                }

                -->
            </style>
        </head>
        <body>
        <?php

        if ($SCRFile) {

            echo $SCRFile;

            echo '<P>Following this visit, ';

        }
        else{
        ?>
            <p><?php print $dbeJCallActivity->getValue(DBEJCallActivity::contactFirstName) . ',' ?></span></p>
            <p>We have carried out work on your <?php echo strtolower(CONFIG_SERVICE_REQUEST_DESC) . ' reference ' ?> <a
                        href="http://www.cnc-ltd.co.uk/portal/request/<?= $activityRef ?>/view"
                ><?= $activityRef ?></a>
                as detailed below.</p>
            <h2>Details</h2>
            <p class="singleBorder"><?php print $dbeJCallActivity->getValue(DBEJCallActivity::reason); ?></p>
        <P>The technician responsible for this was <?php echo $dbeJCallActivity->getValue(DBEJCallActivity::userName) ?>
            .
            <?php
            }
            if (!$isFixed){
            $subjectSuffix = '';
            if (
            $dbeJCallActivity->getValue(DBEJCallActivity::awaitingCustomerResponseFlag) == 'Y'
            ){

                $subjectSuffix .= 'Requires YOUR Attention';

                ?>
                the <?php echo strtolower(
                    CONFIG_SERVICE_REQUEST_DESC
                ) ?> requires YOUR attention as detailed above. Please note that we will take NO further action until we hear from you.
                <?php
            }
            else {
            /*
            Requires CNC attention
            */
            $subjectSuffix = 'Requires Further Action By CNC';

            ?>
            the <?= strtolower(CONFIG_SERVICE_REQUEST_DESC) ?> requires further action by CNC as detailed above.
            We will contact you when this commences or if we need futher information.</p>
        <?php
        }
        }

        print common_getHTMLEmailFooter(
            $senderName,
            $senderEmail
        );

        ?>
        </body>
        </html>
        <?php
        $body = ob_get_contents();
        ob_end_clean();

        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $dbeProblem->getValue(DBEJProblem::customerID),
            $dsCustomer
        );
        /*
    Send the email to all the main support email addresses at the client but exclude them if they were the reporting contact.
    */
        if (
            $dsCustomer->getValue(DBECustomer::othersEmailMainFlag) == 'Y' &&
            $mainSupportEmailAddresses = $buCustomer->getMainSupportEmailAddresses(
                $dbeJCallActivity->getValue(DBEJCallActivity::customerID),
                $toEmail
            )
        ) {

            $toEmail .= ',' . $mainSupportEmailAddresses;

        }

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => CONFIG_SERVICE_REQUEST_DESC . ' ' . $dbeJCallActivity->getValue(
                    DBEJCallActivity::problemID
                ) . ' - ' . $subjectSuffix,
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
     * Sends email to service desk managers when activity logged against  customer
     *
     * @param mixed $activityID
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
            $body,
            true
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
            $body,
            true
        );
    } // end sendRequestCompletedEarlyEmail

    /**
     * Sends email to sales when future on-site activity logged
     *
     * @param mixed $activityID
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

        switch ($response) {

            case 'A':
                $reason = '<p>The following change request has been approved by ' . $userName . '</p>';

                $subject = 'Change Request approved';

                break;

            case 'D':
                $reason = '<p>The following change request has been denied by ' . $userName . '</p>';

                $subject = 'Change Request denied';

                break;

            case 'I':
                $reason = '<p>Further details/discusssion requested by ' . $userName . '</p>';

                $subject = 'More information/discussion required for change request';

                break;
        }

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

    function resetProblemAlarm($problemID)
    {
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        $dbeProblem->setValue(
            DBEJProblem::alarmDate,
            ''
        );
        $dbeProblem->setValue(
            DBEJProblem::alarmTime,
            ''
        );
        return ($dbeProblem->updateRow());
    }

    function createChangeRequestActivity($callActivityID,
                                         $reason,
                                         $userID
    )
    {

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $dbeNewActivity = $dbeCallActivity;

        $dbeNewActivity->setPKValue('');

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
  Send an alert email if number of activities per SR per day exceeds system max
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

    } // end sendUpdatedByAnotherUserEmail

//    function reenterEndTime()
//    {
//        $this->setMethodName('reenterEndTime');
//
//        if (!$_REQUEST['callActivityID']) {
//
//            $this->raiseError(' callactivityID not passed ');
//
//        }
//
//        $this->buActivity->getActivityByID(
//            $_REQUEST['callActivityID'],
//            $dsCallActivity
//        );
//
//        $this->setTemplateFiles(
//            array(
//                'ActivityReenterEndTime'             => 'ActivityReenterEndTime.inc',
//                'ActivityWizardHeader'               => 'ActivityWizardHeader.inc',
//                'ActivityReenterEndTimeCreateTravel' => 'ActivityReenterEndTimeCreateTravel.inc'
//            )
//        );
//
//        $this->setPageTitle("Review End Time");
//
//        $dbeCallActType = new DBECallActType($this);
//
//        $dbeCallActType->getRow($dsCallActivity->getValue(DBEJCallActivity::callActTypeID));
//
//        /*
//      * get site row for checking travel time
//      */
//        $dbeSite = new DBESite($this);
//        $dbeSite->setValue(
//            DBESite::customerID,
//            $dsCallActivity->getValue(DBEJCallActivity::customerID)
//        );
//        $dbeSite->setValue(
//            DBESite::siteNo,
//            $dsCallActivity->getValue(DBEJCallActivity::siteNo)
//        );
//        $dbeSite->getRowByCustomerIDSiteNo();
//
//        /*
//      * validate if this is a POST request
//      */
//        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//
//            if (!$_REQUEST['endTime'] && $dbeCallActType->getValue(DBECallActType::requireCheckFlag) == 'N') {
//                $error['endTime'] = 'Required';
//            } else if (!DataSet::isTime($_REQUEST['endTime'])) {
//                $error['endTime'] = 'Please enter a valid time';
//            }
//
//            if (count($error) == 0) {
//
//                $this->buActivity->finaliseOnSiteActivity(
//                    $_REQUEST['callActivityID'],
//                    $_REQUEST['endTime']
//                );
//
//                if (!$_REQUEST['skipCreateTravelActivity']) {
//
//                    if (
//                        strpos(
//                            $dbeCallActType->getValue(DBECallActType::description),
//                            'FOC'
//                        ) === FALSE &&
//                        $dbeSite->getValue(DBESite::maxTravelHours) > 0
//                    ) {
//                        $dbeCallActivity = new DBECallActivity($this);
//
//                        $dbeCallActivity->getRow($_REQUEST['callActivityID']);
//
//                        $this->buActivity->createTravelActivity($dbeCallActivity);
//                    }
//
//                }
//
//                $this->redirectToDisplay($_REQUEST['callActivityID']);
//                exit;
//
//            }  // end if( count($error) == 0 )
//
//        }// end IF POST
//
//
//        $submitURL =
//            $this->buildLink(
//                $_SERVER['PHP_SELF'],
//                array('action' => 'reenterEndTime')
//            );
//
//
//        $buCustomerItem = new BUCustomerItem($this);
//        $minResponseTime = $buCustomerItem->getMinResponseTime($dsCallActivity->getValue(DBEJCallActivity::customerID));
//
//        $this->template->set_var(
//            array(
//                'callActivityID' => $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
//                'customerName'   => $dsCallActivity->getValue(DBEJCallActivity::customerName),
//                'endTime'        => $dsCallActivity->getValue(DBEJCallActivity::endTime),
//                'endTimeMessage' => $error['endTime'],
//                'submitURL'      => $submitURL
//            )
//        );
//
//        $this->template->parse(
//            'activityWizardHeader',
//            'ActivityWizardHeader',
//            true
//        );
//        if (
//            $this->buActivity->travelActivityForCustomerEngineerTodayExists(
//                $dsCallActivity->getValue(DBEJCallActivity::customerID),
//                $dsCallActivity->getValue(DBEJCallActivity::siteNo),
//                $dsCallActivity->getValue(DBEJCallActivity::userID),
//                $dsCallActivity->getValue(DBEJCallActivity::date)
//            )
//            && $dbeSite->getValue(DBESite::maxTravelHours) > 0    // the site has travel hours
//        ) {
//            $this->template->parse(
//                'activityReenterEndTimeCreateTravel',
//                'ActivityReenterEndTimeCreateTravel',
//                true
//            );
//        }
//
//        $this->template->parse(
//            'CONTENTS',
//            'ActivityReenterEndTime',
//            true
//        );
//        $this->parsePage();
//
//    } // end sendUpdatedByAnotherUserEmail

    /**
     * Create travel activities using site maxTravelHours field from address
     *
     * 1: starttime - maxTravelTime
     * 2: endtime + maxTravelTime
     *
     * GL:
     * "The travel activity start time will be the on-site activity start time less the agreed travel time and the end time as per the on-site start time
     *
     * Updated 15/4/2009:
     * zero is now a valid travel time and means that a travel activity is not created
     * -1 now means the travel time has not been set for this site and blocks the creation of an on-site activity
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

        $dbeTravelActivity->setPKValue('');

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
            ''
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

    } // end sendChangeRequestEmail

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
                'repondedHours'  => common_convertDecimalToHHMM($dbeJProblem->getValue(DBEJProblem::respondedHours)),
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
            $body,
            $sendToSDManagers
        );
    }


    /*
  Update total hours worked by activity user today
  */

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

    /*
  Historically recalculate and update total hours worked by day/user for
  all records
  */

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


    /*
  does a travel activity for this customer and engineer already exists for today
  */

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

    /*
  @todo incorporate this functionality
  */

    /**
     * This is called from CTActivity when sales order production is skipped
     * and we still want to set the checked activities to authorised.
     * @param $activityIDArray
     * @return bool
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
    }// end reenter end time

    /**
     * Set the problem to completed
     *
     *    2. Send an email to the client
     *    3. Change the problem status to "C"
     *
     * @param $problemID
     * @return string
     */
    function setProblemToCompleted($problemID)
    {

        $dbeFirstCallActivity = $this->getFirstActivityInProblem($problemID);

        if ($dbeFirstCallActivity->getValue(DBEJCallActivity::problemStatus) == 'C') {
            return null;
        }
        /*
    This should be the fixed summary
    */
        $dbeFixedCallActivity = $this->getLastActivityInProblem($problemID);

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($dbeFirstCallActivity->getValue(DBEJCallActivity::callActivityID));


        $reason = '<P>Completed</P>';
        $userID = $this->loggedInUserID;
        // create a completion activity
        $dbeCallActivity->setPKValue('');

        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $problemID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(CONFIG_MYSQL_DATE)
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
        /*
    email to client
    */
        if ($dbeProblem->getValue(DBEJProblem::hideFromCustomerFlag) != 'Y') {

            $dbeRootCause = new DBERootCause($this);
            $dbeRootCause->getRow($dbeProblem->getValue(DBEJProblem::rootCauseID));

            $parameters =
                array(
                    'problemID'     => $problemID,
                    'templateName'  => 'ServiceCompletedEmail',
                    'subjectSuffix' => 'Now Closed',
                    'fields'        =>
                        array(
                            'reason'              => $dbeFirstCallActivity->getValue(DBEJCallActivity::reason),
                            'rootCause'           => $dbeRootCause->getValue(DBERootCause::description),
                            'fixedActivityReason' => $dbeFixedCallActivity->getValue(DBEJCallActivity::reason)
                        )

                );

            $parameters['fields']['urlQuestionnaire'] = 'http://www.cnc-ltd.co.uk/questionnaire/index.php?problemno=' . $problemID . '&questionnaireno=1';

            $this->sendEmailToCustomer($parameters);

        }

        return $newActivityID;

    }

    /*
  Check to se whether this site record requires travel hours added to the site record.
  i.e. is this a chargeable activity and does this site have zero travel hours.
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
    Get a list of the associated problemnos
    */
        $select =
            "SELECT
        DISTINCT caa_problemno
      FROM
        callactivity
      WHERE
        caa_callactivityno IN( $activityIDsAsString )";

        $db->query($select);

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
        caa_problemno IN( $problemIDsAsString )
        AND pro_contract_cuino = 0";

        $db->query($select);

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
        $consultantName = '';

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
                        DBEJOrdhead::payMethod,
                        CONFIG_PAYMENT_TERMS_30_DAYS
                    );
                    $dsOrdhead->post();
                    $buSalesOrder->updateHeader(
                        $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                        $dsOrdhead->getValue(DBEOrdhead::custPORef),
                        $dsOrdhead->getValue(DBEOrdhead::payMethod),
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
                    ''
                );
                $dbeOrdline->setValue(
                    DBEJOrdline::stockcat,
                    ''
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
                $dbeJCallActivity->getValue(DBEJCallActivity::underContractFlag),
                $this->dsHeader,
                $normalHours,
                $beforeHours,
                $afterHours,
                $outOfHoursRate,
                $normalRate,
                'N'
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
    }

    function completeSRs($activityIDArray)
    {
        $this->setMethodName('completeSRs');

        $dbeCallActivity = new DBECallActivity($this);

        foreach ($activityIDArray as $activityID) {
            $dbeCallActivity->getRow($activityID);
            $this->setProblemToCompleted($dbeCallActivity->getValue(DBEJCallActivity::problemID));

        }
    }

    function initialiseExportDataset(&$dsData)
    {
        $this->setMethodName('initialiseExportDataset');
        $dsData = new DSForm($this);
        $dsData->addColumn(
            'endDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'previewRun',
            DA_YN_FLAG,
            DA_ALLOW_NULL
        );
        $dsData->setUpdateModeUpdate();
        $dsData->setValue(
            'previewRun',
            'Y'
        );
        $dsData->post();
    }

    /**
     * @param DataSet $dsData
     * @param $failList
     * @return bool
     */
    function checkDefaultSiteContacts(&$dsData,
                                      &$failList
    )
    {

        $this->setMethodName('checkDefaultSiteContacts');

        $db = new dbSweetcode(); // database connection for query


        /* get a list of valid PrePay support contracts */
        $queryString = "
  SELECT
    pro_custno,
    caa_siteno,
    cus_name,
    add_town,
    add_postcode
  FROM callactivity
    JOIN problem ON pro_problemno = caa_problemno
    JOIN custitem
      ON pro_contract_cuino = cui_cuino
    JOIN customer
      ON pro_custno = cus_custno
    JOIN address
      ON add_custno = cui_custno
        AND add_siteno = cui_siteno

      WHERE cui_itemno = " . $this->dsHeader->getValue(DBEHeader::gscItemID) . "
        AND cui_expiry_date >=  '" . $dsData->getValue('endDate') . "'
        AND cui_desp_date <= '" . $dsData->getValue('endDate') . "'
        AND cui_expiry_date >= NOW()
        AND pro_custno <> " . CONFIG_SALES_STOCK_CUSTOMERID . "
        AND renewalStatus <> 'D'
    GROUP BY
        add_postcode";

        $db->query($queryString);

        $buCustomer = new BUCustomer($this);

        $failList = '';

        while ($db->next_record()) {

            $dsSite = new DataSet($this);
            $buCustomer->getSiteByCustomerIDSiteNo(
                $db->Record['pro_custno'],
                $db->Record['caa_siteno'],
                $dsSite
            );

            if (!$dsSite->getValue(DBESite::invoiceContactID)) {

                $failList .= '<BR/>' . $db->Record['cus_name'] . ', Site: ' . $db->Record['add_town'] . ',' . $db->Record['add_postcode'];

            }

        }

        if ($failList) {

            return false;
        } else {

            return true;

        }

    }

    /**
     * @param DataSet $dsData
     * @param bool $update
     * @return bool|DataSet
     */
    function exportPrePayActivities(&$dsData,
                                    $update = false
    )
    {

        $this->setMethodName('exportPrePayActivities');

        $dsResults = new DataSet($this);
        $dsResults->addColumn(
            'customerName',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsResults->addColumn(
            'previousBalance',
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $dsResults->addColumn(
            'currentBalance',
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $dsResults->addColumn(
            'expiryDate',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsResults->addColumn(
            'topUp',
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $dsResults->addColumn(
            'contacts',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsResults->addColumn(
            'contractType',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsResults->addColumn(
            'webFileLink',
            DA_STRING,
            DA_ALLOW_NULL
        ); // link to statement


        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatCode = $this->dsHeader->getValue(DBEHeader::stdVATCode);
        $this->standardVatRate = $dbeVat->getValue((integer)$vatCode[1]); // use second part of code as column no

        $db = new dbSweetcode(); // database connection for query


        /* get a list of valid support customer items */
        $queryString = "  SELECT cui_cuino
        FROM custitem
        JOIN customer ON customer.cus_custno = custitem.cui_custno
        WHERE cui_itemno = " . $this->dsHeader->getValue(
                DBEHeader::gscItemID
            ) . " AND cui_expiry_date >= '" . $dsData->getValue(
                'endDate'
            ) . "'" . " AND cui_desp_date <= '" . $dsData->getValue('endDate') . "'" . // and the contract has started
            " AND cui_expiry_date >= now()" . // and is not expired
            " AND  cus_custno <> " . CONFIG_SALES_STOCK_CUSTOMERID . " AND  renewalStatus  <> 'D'";

// The following code is used when there has been a crash to exclude already processed custs
//    $queryString .= " AND cus_custno NOT IN( 1000, 823, 820, 520 , 203, 117)";


        $db->query($queryString);
        while ($db->next_record()) {
            $validContracts [$db->Record ['cui_cuino']] = 0; // initialise to no activity
        }

        $dbeCallActivity = new DBECallActivity($this); // for update of status


        $queryString = "SELECT
        caa_callactivityno,
        caa_date,
        DATE_FORMAT(callactivity.caa_date, '%d/%m/%Y') AS activityDate,
        caa_starttime,
        caa_endtime,
        reason,
        cns_name,
        cat_desc,
        callacttype.curValueFlag,
        callacttype.travelFlag,
        address.add_max_travel_hours,
        cat_min_hours,
        cat_ooh_multiplier,
        caa_callacttypeno,
        cat_itemno,
        cus_name,
        add_postcode,
        con_first_name,
        con_last_name,
        caa_under_contract,
        callactivity.curValue,
        cui_desp_date,
        cui_expiry_date,
        cui_cuino,
        curGSCBalance,
        cus_custno AS custno,
        itm_sstk_price,
        ity_desc,
        customer.gscTopUpAmount
      FROM
        callactivity
        JOIN problem ON pro_problemno = caa_problemno
        JOIN consultant ON caa_consno = cns_consno
        JOIN callacttype ON cat_callacttypeno=caa_callacttypeno
        JOIN custitem ON pro_contract_cuino = cui_cuino
        JOIN customer ON pro_custno = cus_custno
        JOIN address ON add_custno = pro_custno AND add_siteno = caa_siteno
        JOIN contact ON con_contno = caa_contno
        JOIN item ON cui_itemno = itm_itemno
        JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
      WHERE
        itm_itemno = " . $this->dsHeader->getValue(DBEHeader::gscItemID) . " AND caa_endtime IS NOT NULL
        AND caa_status = 'C'
        AND caa_date <= '" . $dsData->getValue('endDate') . "'" . // include activities before statement date
            " AND cui_desp_date <= '" . $dsData->getValue('endDate') . "'" . // and the contract has started
            " AND cui_expiry_date >= now()" . // and is not expired
            " AND  cus_custno <> " . CONFIG_SALES_STOCK_CUSTOMERID . " AND  renewalStatus  <> 'D'" . // not declined
            " AND  caa_callacttypeno  <> " . CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID . " AND  caa_callacttypeno  <> " . CONFIG_PROACTIVE_SUPPORT_ACTIVITY_TYPE_ID .  // not declined
            " AND pro_status = 'C'";     // only completed problems
// The following code is used when there has been a crash to exclude already processed custs
//    $queryString .= " AND cus_custno NOT IN( 1000, 823, 820, 520 , 203, 117)";

        $queryString .= " ORDER BY pro_custno, caa_date, caa_starttime";

        $db->query($queryString);
        $ret = FALSE; // indicates there were no statements to export


        $buContact = new BUContact($this);
        $buCustomer = new BUCustomer($this);

        // ensure all customers have at least one statement contact
        $last_custno = '9999';

        while ($db->next_record()) {

            if ($db->Record ['custno'] != $last_custno) {
                if ($last_custno != '9999') {
                    /** @var DataSet $dsStatementContact */
                    $buContact->getGSCContactByCustomerID(
                        $db->Record ['custno'],
                        $dsStatementContact
                    );
                    if (!is_object($dsStatementContact)) {
                        $this->raiseError(
                            'Customer ' . $db->Record ['cns_name'] . ' needs at least one Pre-pay statement contact.'
                        );
                        exit();
                    }
                }
            }
            $last_custno = $db->Record ['custno'];
        }

        // create CSV summary file
        $filepath = SAGE_EXPORT_DIR . '/PP-SUMMARY-' . Controller::dateYMDtoDMY(
                $dsData->getValue('endDate'),
                '-'
            );
        $this->csvSummaryFileHandle = fopen(
            $filepath . '.csv',
            'wb'
        );

        $db->query($queryString);

        $last_custno = '9999';
        $htmlFileHandle = null;
        $csvFileHandle = null;
        while ($db->next_record()) {

            $validContracts [$db->Record ['cui_cuino']] = 1; // flag contract as having activity

            $ret = TRUE; // there was at least one statement to export


            // new customer so create new csv and html files
            if ($db->Record ['custno'] != $last_custno) {

                if ($last_custno != '9999') {
                    $topupValue = $this->doTopUp(
                        $lastRecord,
                        $update
                    );
                    $newBalance = $lastRecord ['curGSCBalance'] + $this->totalCost;
                    $this->template->set_var(
                        array(
                            'totalCost'        => common_numberFormat($this->totalCost),
                            'previousBalance'  => common_numberFormat($lastRecord ['curGSCBalance']),
                            'remainingBalance' => common_numberFormat($newBalance)
                        )
                    );

                    $this->template->parse(
                        'output',
                        'page',
                        true
                    );
                    fwrite(
                        $htmlFileHandle,
                        $this->template->get_var('output')
                    );
                    fclose($htmlFileHandle); // close previous html file


                    $this->postRowToSummaryFile(
                        $lastRecord,
                        $dsResults,
                        $dsStatementContact,
                        $newBalance,
                        $topupValue,
                        $dsData->getValue('endDate')
                    );

                    $dsStatementContact->initialise();

                    if ($update) {
                        $this->sendGSCStatement(
                            $filepath . '.html',
                            $dsStatementContact,
                            $newBalance,
                            $dsData->getValue('endDate'),
                            $topupValue
                        );
                    }
                    fclose($csvFileHandle); // close previous csv file
                } // end if( $last_custno != '9999' )


                $this->totalCost = 0; // reset cost


                $filepath = SAGE_EXPORT_DIR . '/PP_' . substr(
                        $db->Record ['cus_name'],
                        0,
                        10
                    ) . $dsData->getValue('endDate');

                $csvFileHandle = fopen(
                    $filepath . '.csv',
                    'wb'
                );
                if (!$csvFileHandle) {
                    $this->raiseError("Unable to open csv file " . $filepath);
                }

                $htmlFileHandle = fopen(
                    $filepath . '.html',
                    'wb'
                );
                if (!$htmlFileHandle) {
                    $this->raiseError("Unable to open html file " . $filepath);
                }

                // set up new html file template
                $this->template = new Template(
                    $GLOBALS ["cfg"] ["path_templates"],
                    "remove"
                );
                $this->template->set_file(
                    'page',
                    'GSCReport.inc.html'
                );
                // get GSC contact record
                $buContact->getGSCContactByCustomerID(
                    $db->Record ['custno'],
                    $dsStatementContact
                );
                /** @var DataSet $dsSite */
                $buCustomer->getSiteByCustomerIDSiteNo(
                    $dsStatementContact->getValue(DBEContact::customerID),
                    $dsStatementContact->getValue(DBEContact::siteNo),
                    $dsSite
                );

                // Set header fields
                $this->template->set_var(
                    array(
                        'companyName'   => $db->Record ['cus_name'],
                        'customerRef'   => $db->Record ['cui_cuino'],
                        'startDate'     => Controller::dateYMDtoDMY($db->Record ['cui_desp_date']),
                        'endDate'       => Controller::dateYMDtoDMY($db->Record ['cui_expiry_date']),
                        'statementDate' => Controller::dateYMDtoDMY($dsData->getValue('endDate')),
                        'add1'          => $dsSite->getValue(DBESite::add1),
                        'add2'          => $dsSite->getValue(DBESite::add2),
                        'add3'          => $dsSite->getValue(DBESite::add3),
                        'town'          => $dsSite->getValue(DBESite::town),
                        'county'        => $dsSite->getValue(DBESite::county),
                        'postcode'      => $dsSite->getValue(DBESite::postcode),
                        'cnc_name'      => $this->dsHeader->getValue(DBEHeader::name),
                        'cnc_add1'      => $this->dsHeader->getValue(DBEHeader::add1),
                        'cnc_add2'      => $this->dsHeader->getValue(DBEHeader::add2),
                        'cnc_add3'      => $this->dsHeader->getValue(DBEHeader::add3),
                        'cnc_town'      => $this->dsHeader->getValue(DBEHeader::town),
                        'cnc_county'    => $this->dsHeader->getValue(DBEHeader::county),
                        'cnc_postcode'  => $this->dsHeader->getValue(DBEHeader::postcode),
                        'cnc_phone'     => $this->dsHeader->getValue(DBEHeader::phone)
                    )
                );

                $this->template->set_block(
                    'page',
                    'lineBlock',
                    'lines'
                );

                $last_custno = $db->Record ['custno'];
                $ret = TRUE; // indicates there were statements to export


            } // end if( $db->Record['custno'] != $last_custno )


            $posted = FALSE;

            if ($db->Record ['curValueFlag'] == 'Y') { // This is a monetary value activity such as top-up or adjustment
                $this->postRowToPrePayExportFile(
                    $csvFileHandle,
                    'M',
                    // Type = Monetary
                    $db->Record,
                    1,
                    // set hours = 1 for calculation
                    $db->Record ['curValue']
                );
                $posted = TRUE;
            } else {

                /* mantis 359: Apply maximum travel hours to travel type activities */
                if ($db->Record ['travelFlag'] == 'Y') {
                    $max_hours = $db->Record ['MaxTravelHours'];
                } else {
                    $max_hours = 0;
                }


                getRatesAndHours(
                    $db->Record ['caa_date'],
                    $db->Record ['caa_starttime'],
                    $db->Record ['caa_endtime'],
                    $db->Record ['cat_min_hours'],
                    $max_hours,
                    $db->Record ['cat_ooh_multiplier'],
                    $db->Record ['cat_itemno'],
                    'Y',
                    $this->dsHeader,
                    $normalHours,
                    $beforeHours,
                    $afterHours,
                    $outOfHoursRate,
                    $normalRate,
                    'N'
                );

                if ($beforeHours > 0) {
                    $this->postRowToPrePayExportFile(
                        $csvFileHandle,
                        'O',
                        // out of hours
                        $db->Record,
                        $beforeHours,
                        $outOfHoursRate
                    );
                    $posted = TRUE;
                }
                if ($normalHours > 0) {
                    $this->postRowToPrePayExportFile(
                        $csvFileHandle,
                        'I',
                        // in hours
                        $db->Record,
                        $normalHours,
                        $normalRate
                    );
                    $posted = TRUE;
                }
                if ($afterHours > 0) {
                    $this->postRowToPrePayExportFile(
                        $csvFileHandle,
                        'O',
                        // out of hours
                        $db->Record,
                        $afterHours,
                        $outOfHoursRate
                    );
                    $posted = TRUE;
                }
            }

            if ($posted == FALSE) { // No hours to post but need a line
                $this->postRowToPrePayExportFile( // e.g. for top-up activity or value activity
                    $csvFileHandle,
                    'I',
                    $db->Record,
                    0,
                    0
                );
            }

            if ($update) {
                // update status on call activity to Authorised and statement date to today


                $dbeCallActivity->getRow($db->Record ['caa_callactivityno']);
                $dbeCallActivity->setValue(
                    DBEJCallActivity::status,
                    'A'
                );
                $dbeCallActivity->setValue(
                    DBEJCallActivity::statementYearMonth,
                    date('Y-m')
                );
                $dbeCallActivity->updateRow();
            }
            $lastRecord = $db->Record;
        }

        if ($ret == TRUE) {
            fclose($csvFileHandle);

            $topupValue = $this->doTopUp(
                $lastRecord,
                $update
            );
            $newBalance = $lastRecord ['curGSCBalance'] + $this->totalCost;
            $this->template->set_var(
                array(
                    'totalCost'        => common_numberFormat($this->totalCost),
                    'previousBalance'  => common_numberFormat($lastRecord ['curGSCBalance']),
                    'remainingBalance' => common_numberFormat($newBalance)
                )
            );
            $this->template->parse(
                'output',
                'page',
                true
            );
            fwrite(
                $htmlFileHandle,
                $this->template->get_var('output')
            );
            fclose($htmlFileHandle);

            $this->postRowToSummaryFile(
                $lastRecord,
                $dsResults,
                $dsStatementContact,
                $newBalance,
                $topupValue,
                $dsData->getValue('endDate')
            );

            if ($update) {
                $dsStatementContact->initialise();
                $this->sendGSCStatement(
                    $filepath . '.html',
                    $dsStatementContact,
                    $newBalance,
                    $dsData->getValue('endDate'),
                    $topupValue
                );
            }
        }

        /*
  Now produce statements for contracts that had no activity
*/
        $this->totalCost = 0; // there is no balance of activity cost
        reset($validContracts);
        foreach ($validContracts as $key => $value) {
            if ($value == 0) {

                $ret = true;

                $queryString = "SELECT
            cus_name,
            cui_desp_date,
            cui_expiry_date,
            cui_cuino,
            curGSCBalance,
            cui_custno AS custno,
            gscTopUpAmount,
            ity_desc
          FROM
            custitem
            JOIN customer ON cui_custno = cus_custno
            JOIN item ON cui_itemno = itm_itemno
            JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
          WHERE
            cui_cuino = " . $key . " AND  cus_custno <> 2511" . " AND  renewalStatus  <> 'D'";
// The following code is used when there has been a crash to exclude already processed custs
//    $queryString .= " AND cus_custno NOT IN( 1000, 823, 820, 520 , 203, 117)";

                $db->query($queryString);
                $db->next_record();
                // get GSC contact record
                $buContact->getGSCContactByCustomerID(
                    $db->Record ['custno'],
                    $dsStatementContact
                );
                $buCustomer->getSiteByCustomerIDSiteNo(
                    $dsStatementContact->getValue(DBEContact::customerID),
                    $dsStatementContact->getValue(DBEContact::siteNo),
                    $dsSite
                );

                // set up new html file template
                $filepath = SAGE_EXPORT_DIR . '/PP_' . substr(
                        $db->Record ['cus_name'],
                        0,
                        10
                    ) . $dsData->getValue('endDate');
                $htmlFileHandle = fopen(
                    $filepath . '.html',
                    'wb'
                );
                if (!$htmlFileHandle) {
                    $this->raiseError("Unable to open html file " . $filepath);
                }
                $this->template = new Template(
                    $GLOBALS ["cfg"] ["path_templates"],
                    "remove"
                );
                $this->template->set_file(
                    'page',
                    'GSCReport.inc.html'
                );

                // Set header fields
                $this->template->set_var(
                    array(
                        'companyName'   => $db->Record ['cus_name'],
                        'customerRef'   => $key,
                        'startDate'     => Controller::dateYMDtoDMY($db->Record ['cui_desp_date']),
                        'endDate'       => Controller::dateYMDtoDMY($db->Record ['cui_expiry_date']),
                        'statementDate' => Controller::dateYMDtoDMY($dsData->getValue('endDate')),
                        'add1'          => $dsSite->getValue(DBESite::add1),
                        'add2'          => $dsSite->getValue(DBESite::add2),
                        'add3'          => $dsSite->getValue(DBESite::add3),
                        'town'          => $dsSite->getValue(DBESite::town),
                        'county'        => $dsSite->getValue(DBESite::county),
                        'postcode'      => $dsSite->getValue(DBESite::postcode),
                        'cnc_name'      => $this->dsHeader->getValue(DBEHeader::name),
                        'cnc_add1'      => $this->dsHeader->getValue(DBEHeader::add1),
                        'cnc_add2'      => $this->dsHeader->getValue(DBEHeader::add2),
                        'cnc_add3'      => $this->dsHeader->getValue(DBEHeader::add3),
                        'cnc_town'      => $this->dsHeader->getValue(DBEHeader::town),
                        'cnc_county'    => $this->dsHeader->getValue(DBEHeader::county),
                        'cnc_postcode'  => $this->dsHeader->getValue(DBEHeader::postcode),
                        'cnc_phone'     => $this->dsHeader->getValue(DBEHeader::phone)
                    )
                );
                $this->template->set_block(
                    'page',
                    'lineBlock',
                    'lines'
                );

                $this->template->set_var(
                    array(
                        'activityDate'     => '',
                        'activityPostcode' => '',
                        'activityRef'      => '',
                        'activityContact'  => '',
                        'activityType'     => '',
                        'activityHours'    => '',
                        'activityCost'     => '',
                        'activityDetails'  => 'No activity for this period'
                    )
                );

                $this->template->parse(
                    'lines',
                    'lineBlock',
                    true
                );
                $this->totalCost += $value;
                $this->template->set_var(
                    array(
                        'totalCost'        => 0,
                        'previousBalance'  => common_numberFormat($db->Record ['curGSCBalance']),
                        'remainingBalance' => common_numberFormat($db->Record ['curGSCBalance'])
                    )
                );
                $this->template->parse(
                    'output',
                    'page',
                    true
                );
                fwrite(
                    $htmlFileHandle,
                    $this->template->get_var('output')
                );
                fclose($htmlFileHandle);

                $dsStatementContact->initialise();
                $topupValue = $this->doTopUp(
                    $db->Record,
                    $update
                );

                $this->postRowToSummaryFile(
                    $db->Record,
                    $dsResults,
                    $dsStatementContact,
                    $db->Record ['curGSCBalance'],
                    $topupValue,
                    $dsData->getValue('endDate')
                );

                if ($update) {
                    $this->sendGSCStatement(
                        $filepath . '.html',
                        $dsStatementContact,
                        $db->Record ['curGSCBalance'],
                        $dsData->getValue('endDate'),
                        $topupValue
                    );
                }
            }
        }

        fclose($this->csvSummaryFileHandle);

        if ($ret) {
            return $dsResults;
        } else {
            return false;
        }
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
            $topupValue = (0 - $newBalance) + $Record ['gscTopUpAmount'];
        } else {
            $topupValue = $Record ['gscTopUpAmount']; // just the top-up amount
        }
        //   Create sales order
        if ($update) {
            $this->createTopupSalesOrder(
                $Record,
                $topupValue
            );
        }

        return $topupValue;
    }

    function createTopupSalesOrder(&$Record,
                                   $topupValue
    )
    {
        $this->setMethodName('createTopupSalesOrder');

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
            DBEOrdhead::payMethod,
            CONFIG_PAYMENT_TERMS_30_DAYS
        );
        $dsOrdhead->post();
        $buSalesOrder->updateHeader(
            $dsOrdhead->getValue(DBEOrdhead::ordheadID),
            $dsOrdhead->getValue(DBEOrdhead::custPORef),
            $dsOrdhead->getValue(DBEOrdhead::payMethod),
            $dsOrdhead->getValue(DBEOrdhead::partInvoice),
            $dsOrdhead->getValue(DBEOrdhead::addItem)
        );

        $ordheadID = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
        $sequenceNo = 1;

        // get topup item details
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
            $topupValue
        );
        $dbeOrdline->setValue(
            DBEJOrdline::curTotalSale,
            $topupValue
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
     * @param $topupAmount
     * @param $endDate
     */
    function postRowToSummaryFile(&$Record,
                                  &$dsResults,
                                  &$dsStatementContact,
                                  $newBalance,
                                  $topupAmount,
                                  $endDate
    )
    {
        $contacts = '';
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
            '"' . common_numberFormat($topupAmount) . '"' . // value
            "\r\n"
        );
        $webFileLink = 'export/PP_' . substr(
                $Record ['cus_name'],
                0,
                10
            ) . $endDate . '.html';

        $dsResults->setUpdateModeInsert();
        $dsResults->setValue(
            'customerName',
            $Record ['cus_name']
        );
        $dsResults->setValue(
            'previousBalance',
            $Record ['curGSCBalance']
        );
        $dsResults->setValue(
            'currentBalance',
            common_numberFormat($newBalance)
        );
        $dsResults->setValue(
            'expiryDate',
            Controller::dateYMDtoDMY($Record ['cui_expiry_date'])
        );
        $dsResults->setValue(
            'topUp',
            common_numberFormat($topupAmount)
        );
        $dsResults->setValue(
            'contacts',
            $contacts
        );
        $dsResults->setValue(
            'contractType',
            $Record ['ity_desc']
        );
        $dsResults->setValue(
            'webFileLink',
            $webFileLink
        );
        $dsResults->post();
    }

    /**
     * @param $statementFilepath
     * @param DataSet $dsContact
     * @param $balance
     * @param $date
     * @param $topupValue
     */
    function sendGSCStatement($statementFilepath,
                              &$dsContact,
                              $balance,
                              $date,
                              $topupValue
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
            $message = '<body><p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'Dear ' . $dsContact->getValue(DBEContact::firstName) . ',';
            $message .= '<o:p></o:p></span></font></p>';
            $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            // Temporary:
            $message .= 'Please find attached your latest Pre-Pay Contract statement, on which there
is currently a balance of ';
            $message .= '&pound;' . common_numberFormat($balance) . ' + VAT.';
            $message .= '</p>';

            $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'If you have any queries relating to any of the items detailed on this statement, then please notify us within 7 days so that we can make any adjustments if applicable.';
            $message .= '</p>';

            if ($balance <= 100) {
                $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
                $message .= 'If no response to the contrary is received within 7 days of this statement, then we will automatically raise an invoice for &pound;' . common_numberFormat(
                        $topupValue * (1 + ($this->standardVatRate / 100))
                    ) . ' Inc VAT.';
                $message .= '</p>';
            }

            $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'Are you aware that you can receive up to &pound;500 for the referral of any company made to CNC that results in the purchase of a support contract?  Please call us for further information.';
            $message .= '</p>';

            $subject = 'Pre-Pay Contract Statement: ' . Controller::dateYMDtoDMY($date);

            $toEmail = $dsContact->getValue(DBEContact::firstName) . ' ' .
                $dsContact->getValue(DBEContact::lastName) . '<' . $dsContact->getValue(DBEContact::email) . '>';

            $senderName = '';

            // create mime
            $html = '<html>' . $message . '</html>';
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

        if ($timeFrameFlag == 'O') {
            $timeFrameDesc = ' - Out of hours';
        } else {
            $timeFrameDesc = '';
        }
        if ($Record ['reason'] == '') {
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

        if ($timeFrameFlag == 'M') { // Monetary value like topup
            $contacts = '';
            $postcode = '';
            $activityRef = '';
            $hours = '';
        } else {
            $postcode = $Record ['add_postcode'];
            $activityRef = $Record ['caa_callactivityno'];
            $hours = common_numberFormat($hours);
        }

        // don't display zero values
        if ($value == 0) {
            $displayValue = '';
        } else {
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
            // set fields to topup
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

//end completeSRs

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
            date(CONFIG_MYSQL_DATETIME)
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
            date(CONFIG_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            ''
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'O'
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            ''
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $userID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );

        $dbeCallActivity->insertRow();

        $callActivityID = $dbeCallActivity->getPKValue();

        return $callActivityID;
    }

    function createActivityFromSession($sessionKey)
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dsCallActivity = new DataSet($this);
        $dsCallActivity->copyColumnsFrom($dbeCallActivity);

        $dateTimeRaised = $_SESSION [$sessionKey] ['dateRaised'] . ' ' . $_SESSION [$sessionKey] ['timeRaised'] . ':00';

        $slaResponseHours = $this->getSlaResponseHours(
            $_SESSION [$sessionKey] ['priority'],
            $_SESSION [$sessionKey] ['customerID']
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
            $_SESSION [$sessionKey] ['customerID']
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            $dateTimeRaised
        );
        $dbeProblem->setValue(
            DBEProblem::userID,
            $_SESSION [$sessionKey] ['userID']
        );
        $dbeProblem->setValue(
            DBEProblem::rootCauseID,
            $_SESSION [$sessionKey] ['rootCauseID']
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
            $_SESSION [$sessionKey] ['priority']
        );
        $dbeProblem->setValue(
            DBEProblem::hideFromCustomerFlag,
            $_SESSION [$sessionKey] ['hideFromCustomerFlag']
        );
        $dbeProblem->setValue(
            DBEProblem::internalNotes,
            $_SESSION [$sessionKey] ['internalNotes']
        );
        $dbeProblem->setValue(
            DBEProblem::contactID,
            $_SESSION [$sessionKey] ['contactID']
        );
        $dbeProblem->setValue(
            DBEProblem::contractCustomerItemID,
            $_SESSION [$sessionKey] ['contractCustomerItemID']
        );
        $dbeProblem->setValue(
            DBEProblem::projectID,
            $_SESSION [$sessionKey] ['projectID']
        );
        $dbeProblem->insertRow();

        $endTime = $this->getEndtime(
            $_SESSION [$sessionKey] ['callActTypeID'],
            $_SESSION [$sessionKey] ['timeRaised']
        );

        $dsCallActivity->setUpdateModeInsert();
        $dsCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            0
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $_SESSION [$sessionKey] ['siteNo']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::contactID,
            $_SESSION [$sessionKey] ['contactID']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            $_SESSION [$sessionKey] ['callActTypeID']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::date,
            $_SESSION [$sessionKey] ['dateRaised']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::startTime,
            $_SESSION [$sessionKey] ['timeRaised']
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
            $_SESSION [$sessionKey] ['reason']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::serverGuard,
            $_SESSION [$sessionKey] ['serverGuard']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::curValue,
            $_SESSION [$sessionKey] ['curValue']
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::statementYearMonth,
            ''
        );
        $dsCallActivity->setValue(
            DBEJCallActivity::customerItemID,
            ''
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

        $this->updateDataaccessObject(
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
                array(
                    'problemID'     => $dbeProblem->getPKValue(),
                    'templateName'  => 'ServiceLoggedEmail',
                    'subjectSuffix' => 'New Request Logged',
                    'fields'        => $fields
                )
            );

        }


//        /*
//If there is a row on the activity_archive table with the same 200 chars of description for this
//customer with the past 8 hours email to GL
//*/
//        $shortReason = substr($_SESSION[$sessionKey]['reason'], 0, 200);
//
//        $queryString =
//            "SELECT caa_problemno
//      FROM callactivity_archive
//      WHERE
//        length(trim(reason)) > 0" .
//            " AND  trim(substr( reason, 0, 200 )) = TRIM( substr( '" . addslashes($shortReason) . "',0,200))" .
//            " AND DATE_ADD(CONCAT(caa_date, ' ', caa_starttime ) , INTERVAL 8 HOUR ) >= NOW()";
//
//
//        $resultSet = $this->db->query($queryString);
//        if ($record = $resultSet->fetch_assoc()) {
//            $this->sendServiceReAddedEmail($dbeProblem->getPKValue(), $record['caa_problemno']);
//            $resultSet->close();
//        }

        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $_SESSION[$sessionKey]['customerID'],
            $dsCustomer
        );

        if ($dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
            $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')) {
            $this->sendSpecialAttentionEmail($dbeCallActivity->getPKValue());
        }

        unset($_SESSION[$sessionKey]);

        return $dsCallActivity;
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
            $body,
            true
        );
    } // end check default site contacts exists

    function sendUncheckedActivityEmail()
    {
        $buMail = new BUMail($this);

        $this->setMethodName('sendUncheckedActivityEmail');

        $this->dbeCallActivitySearch->getRowsBySearchCriteria(
            '',
            '',
            '',
            'UC',
            '',
            '',
            date('Y-m-d'),
            '',
            '',
            '',
            '',
            '',
            '',
            'N'
        );

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $toEmail = false;

        ob_start();
        ?>
        <html>

        <body style="font-family: arial, helvetica, sans-serif; font-size: 10pt">
        <p>Unchecked Support Activities</p>

        <TABLE bordercolor="#0000FF">
            <TR>
                <TD bgcolor="#CCCCCC"><strong> Ref </strong></TD>
                <TD bgcolor="#CCCCCC"><strong> Customer </strong></TD>
                <TD bgcolor="#CCCCCC"><strong> Details </strong></TD>
                <TD bgcolor="#CCCCCC"><strong> Engineer </strong></TD>
                <TD bgcolor="#CCCCCC"><strong> Date </strong></TD>
            </TR>
            <?php
            while ($this->dbeCallActivitySearch->fetchNext()) {
                ?>
                <TR>
                    <TD nowrap
                        bgcolor="#E0DFE3"
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
                        bgcolor="#E0DFE3"
                    >
                        <?php
                        echo $this->dbeCallActivitySearch->getValue(DBEJCallActivity::customerName) ?>        </TD>
                    <TD nowrap
                        bgcolor="#E0DFE3"
                    >
                        <?php
                        echo $this->dbeCallActivitySearch->getValue(DBEJCallActivity::userName) ?>        </TD>
                    <TD nowrap
                        bgcolor="#E0DFE3"
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
            $body,
            true
        );

    }

    /*
    work out whether a top-up is required and if so then generate one
    We generate a top-up T&M call so that this can later be amended and/or checked and used to generate a sales
    order for the top-up amount.
    This call will now appear on
  */

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
        if ($problemID == '') {
            $this->raiseError('problemID not passed');
        }
        if ($description == '') {
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
        $dbeCallDocument->setPKValue('');
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
            date(CONFIG_MYSQL_DATETIME)
        );
        $dbeCallDocument->setValue(
            DBEJCallDocument::fileMIMEType,
            ( string )$mimeType
        );

        return ($dbeCallDocument->insertRow());
    }

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
            'customerID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'userID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'contractType',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'customerName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'fromDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'toDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'customerID',
            ''
        );
        $dsData->setValue(
            'userID',
            ''
        );
        $dsData->setValue(
            'customerName',
            ''
        );
    }

    /**
     * @param DataSet $dsSearchForm
     * @param DataSet $dsServiceDesk
     * @param DataSet $dsTAndM
     * @param DataSet $dsServerCare
     * @param DataSet $dsPrePay
     * @param DataSet $dsActivityType
     * @param DataSet $dsActivityEngineer
     */
    function getCustomerActivities(
        &$dsSearchForm,
        &$dsServiceDesk,
        &$dsTAndM,
        &$dsServerCare,
        &$dsPrePay,
        &$dsActivityType,
        &$dsActivityEngineer
    )
    {

        $dbeCustomerCallActivity = new DBECustomerCallActivity($this);

        $customerID = trim($dsSearchForm->getValue('customerID'));
        $userID = trim($dsSearchForm->getValue('userID'));
        $fromDate = trim($dsSearchForm->getValue('fromDate'));
        $toDate = trim($dsSearchForm->getValue('toDate'));
        $contractType = trim($dsSearchForm->getValue('contractType'));

        $dbeCustomerCallActivity->getMonthyActivityByContract(
            'ServiceDesk',
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivity,
            $dsServiceDesk
        );

        $dbeCustomerCallActivity->getMonthyActivityByContract(
            false,
            // t and m
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivity,
            $dsTAndM
        );

        $dbeCustomerCallActivity->getMonthyActivityByContract(
            'ServerCare',
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivity,
            $dsServerCare
        );

        $dbeCustomerCallActivity->getMonthyActivityByContract(
            'Pre-Pay',
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivity,
            $dsPrePay
        );

        $dbeCustomerCallActivity->getActivityType(
            $contractType,
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );
        $this->getData(
            $dbeCustomerCallActivity,
            $dsActivityType
        );

        $dbeCustomerCallActivity->getActivityEngineer(
            $contractType,
            $customerID,
            $userID,
            $fromDate,
            $toDate
        );
        $this->getData(
            $dbeCustomerCallActivity,
            $dsActivityEngineer
        );
    }

    function getCustomerActivitiesForExport(
        $fromDate,
        $toDate,
        $customerID,
        &$dsServiceDesk,
        &$dsTAndM,
        &$dsServerCare,
        &$dsPrePay,
        &$dsSite,
        &$dsStaff
    )
    {

        $dbeCustomerCallActivityMonth = new DBECustomerCallActivityMonth($this);

        $dbeCustomerCallActivityMonth->getMonthyActivityByContract(
            'ServiceDesk',
            $customerID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivityMonth,
            $dsServiceDesk
        );

        $dbeCustomerCallActivityMonth->getMonthyActivityByContract(
            'ServerCare',
            $customerID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivityMonth,
            $dsServerCare
        );

        $dbeCustomerCallActivityMonth->getMonthyActivityByContract(
            'Pre-Pay',
            $customerID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivityMonth,
            $dsPrePay
        );

        $dbeCustomerCallActivityMonth->getMonthyActivityByContract(
            'T & M',
            $customerID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivityMonth,
            $dsTAndM
        );

        $dbeCustomerCallActivityMonth->getMonthyActivityByContract(
            'T & M',
            $customerID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivityMonth,
            $dsTAndM
        );

        $dbeCustomerCallActivityMonth->getMonthyActivityByStaff(
            $customerID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivityMonth,
            $dsStaff
        );

        $dbeCustomerCallActivityMonth->getMonthyActivityBySite(
            $customerID,
            $fromDate,
            $toDate
        );

        $this->getData(
            $dbeCustomerCallActivityMonth,
            $dsSite
        );

    }

    function getCurrentActivities(&$dsActivityEngineer)
    {
        $dbeCurrentActivity = new DBECurrentActivity($this);

        $dbeCurrentActivity->getRows();

        return $this->getData(
            $dbeCurrentActivity,
            $dsActivityEngineer
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

        while ($dsCallActivity->fetchNext()) {

            $rowCount++;

            $thisID = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);

            if (!$lastID) { // first actvivity in set


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

        if (!$navigateLinksArray ['next']) {

            $navigateLinksArray ['last'] = false;

        }

        if ($thisID !== $callActivityID) {

            $navigateLinksArray ['last'] = $thisID;

        }

        if ($callActivityID == $navigateLinksArray ['first']) {

            $navigateLinksArray ['first'] = false;

        }

        if ($navigateLinksArray ['next'] == $navigateLinksArray ['last']) {

            $navigateLinksArray ['next'] = false;

        }

        if ($navigateLinksArray ['first'] == $navigateLinksArray ['previous']) {

            $navigateLinksArray ['previous'] = false;

        }

        if (!$navigateLinksArray ['thisRowNumber']) {

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
            trim($reason) <> '' &&
            $reason <> $dbeCallActivity->getValue(DBEJCallActivity::reason)
        ) {
            $dbeCallActivity->setValue(
                DBEJCallActivity::reason,
                $reason
            );
            $dbeCallActivity->updateRow();


        }

        if (
            trim($internalNotes) <> '' &&
            $internalNotes <> $dbeProblem->getValue(DBEJProblem::internalNotes)
        ) {

            $dbeProblem->setValue(
                DBEJProblem::internalNotes,
                $internalNotes
            );
            $dbeProblem->updateRow();

        }

    }

    /*
    Create sales order for top-up
  */
    /**
     * @param DataSet $dsCallActivity
     * @return bool
     */
    function canEdit($dsCallActivity)
    {
        if ($this->owner->hasPermissions(PHPLIB_PERM_SUPERVISOR)) {
            $ret = true;
        }

        if (  // status is NOT Authorised AND NOT Checked
            $dsCallActivity->getValue(DBEJCallActivity::status) != 'A' AND $dsCallActivity->getValue(
                DBEJCallActivity::status
            ) != 'C'
        ) {
            $ret = true;
        }

        return $ret;

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

            $this->sendActivityLoggedEmail(
                $callActivityID,
                false
            );


        }
        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $dbeProblem->getValue(DBEJProblem::customerID),
            $dsCustomer
        );
        if (
            $dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
            $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')
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
     * Allocate an tecnician to a request, sending an email to the engineer if this request
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
        if ($userID > 0) { // not deallocating
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

    /**
     * Get problems by status
     *
     * @param mixed $status
     * @param mixed $dsResults
     * @param boolean $future Return future scheduled requests ONLY?
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

    /*
  get first last next and previous activities in this chain
  */
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
            'dashboardSortColumn',
            SORT_NUMERIC
        );

        $dbeJProblem->getRowsByQueueNo($queueNo);       // then assigned

        $dsAssignedResults = new DataSet($this);
        $this->getData(
            $dbeJProblem,
            $dsAssignedResults
        );

        $dsAssignedResults->sortAscending(
            'dashboardSortColumn',
            SORT_NUMERIC
        );

        $dsResults->setClearRowsBeforeReplicateOff();

        $dsResults->replicate($dsAssignedResults);
    }


    /**
     * Get acvtive problems by customer
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

    function toggleDoNextFlag($problemID)
    {
        if (!$this->dbeProblem) {
            $this->dbeProblem = new DBEProblem($this);
        }

        $this->dbeProblem->getRow($problemID);

        if ($this->dbeProblem->getValue(DBEJProblem::doNextFlag) == 'Y') {
            $this->dbeProblem->setValue(
                DBEJProblem::doNextFlag,
                'N'
            );
        } else {
            $this->dbeProblem->setValue(
                DBEJProblem::doNextFlag,
                'Y'
            );
        }
        $this->dbeProblem->updateRow();

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

    }

    /*
  Engineer activity is one not raised by system
  */
    /**
     * @param $ordheadID
     * @param DataSet $dsInput
     * @param bool|int $selectedOrderLine
     * @return string
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


        $dateRaised = date(CONFIG_MYSQL_DATE . ' ' . CONFIG_MYSQL_TIME);
        $timeRaised = date(CONFIG_MYSQL_TIME);

        $internalNotes = '<P>' . str_replace(
                "\r\n",
                "",
                $dsInput->getValue('serviceRequestText')
            ) . '</P>';

        if ($dsInput->getValue('etaDate')) {
            $internalNotes .=
                '<P>ETA: ' . Controller::dateYMDtoDMY($dsInput->getValue('etaDate')) . '</P><BR/>';

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
                $dsInput->getValue('serviceRequestPriority'),
                $dsOrdhead->getValue(DBEOrdhead::customerID)
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
            0
        );
        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            3
        );      //Managers
        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            0
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
            $dsInput->getValue('serviceRequestPriority')
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
            $dsInput->getValue('serviceRequestCustomerItemID')
        );
        $dbeProblem->setValue(
            DBEJProblem::internalNotes,
            $internalNotes
        );
        $dbeProblem->setValue(
            DBEJProblem::linkedSalesOrderID,
            $ordheadID
        );
        $dbeProblem->insertRow();

        /* Use type of first SO line as first line of reason */
        while ($dsOrdline->fetchNext()) {

            if (
                $dsOrdline->getValue(DBEOrdline::itemID) &&

                (!$selectedOrderLine OR

                    ($selectedOrderLine &&
                        in_array(
                            $dsOrdline->getValue(DBEOrdline::sequenceNo),
                            $selectedOrderLine
                        )
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
                    $reason .= '&nbsp';

                }


                $reason .= '</td><td>' . $dsOrdline->getValue(DBEOrdline::description) . '</td></tr>';
            }

        } // end while

        $reason .= '</table>';

        $dbeCallActivity = new DBECallActivity($this);

        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            0
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
            ''
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::customerItemID,
            ''
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
      odh_service_request_text = '',
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

        $template->setVar(
            array(
                'activityRef'   => $activityRef,
                'urlActivity'   => $urlActivity,
                'customerName'  => $dbeJProblem->getValue(DBEJProblem::customerName),
                'reason'        => $dbeJCallActivity->getValue(DBEJCallActivity::reason),
                'internalNotes' => str_replace(
                    '&nbsp',
                    '&nbsp;',
                    $dbeJCallActivity->getValue(DBEJCallActivity::internalNotes)
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

    }

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
     *
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
     *
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

    function processAutomaticRequest($record,
                                     &$errorString
    )
    {

        $details = $record['textBody'];

        $db = new dbSweetcode(); // database connection for query

        if ($record['serverGuardFlag'] == 'Y') {
            return $this->processServerGuard($record);
        }
        /* All below is for non server-guard */

        $contact = $this->getContactInfo($record);

        if ($record['serviceRequestID']) {
            /* find request */
            $queryString = "
        SELECT
          pro_problemno,
          pro_status
        FROM
          problem
        WHERE
          pro_problemno = '" . $record['serviceRequestID'] . "'";


            $db->query($queryString);

            if ($db->next_record()) {              // find
                if ($db->Record['pro_status'] == 'C') {   // is request completed?

                    $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
                    $details .= 'Reopened from SR ' . $record['serviceRequestID'];

                    return $this->processIsSenderAuthorised(
                        $details,
                        $contact,
                        $record,
                        $errorString
                    );

                } else {                               //not completed

                    if ($contact) {
                        $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";

                        if (!$contact['isSupportContact']) {
                            $details .= 'This email is from an unauthorised contact and needs to be confirmed' . "\n\n";
                        }

                        $details .= 'Update from email received from ' . $record['senderEmailAddress'] . ' on ' . date(
                                CONFIG_MYSQL_DATETIME
                            );

                        $dbeLastActivity = $this->getLastActivityInProblem($record['serviceRequestID']);
                        $this->createFollowOnActivity(
                            $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                            CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID,
                            $contact['contactID'],
                            $details,
                            false,
                            true,
                            USER_SYSTEM,
                            false,
                            true
                        );

                        if ($record['attachment'] == 'Y') {
                            $this->processAttachment(
                                $record['serviceRequestID'],
                                $record
                            );
                        }

                        return true;

                    } else {
                        /*
            Contact not resolved to customer domain OR contact

            Still create an activity from the contact from the last activity
            on the SR but with a notice
            */
                        $dbeLastActivity = $this->getLastActivityInProblem($record['serviceRequestID']);

                        $details = 'THIS MESSAGE IS FROM UNRECOGNISED EMAIL ADDRESS ' . $record['senderEmailAddress'] . '. Confirm with the customer that you may deal with it' . "\n\n" . $details;

                        $this->createFollowOnActivity(
                            $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                            CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID,
                            $dbeLastActivity->getValue(DBEJCallActivity::userID),
                            $details,
                            false,
                            true,
                            USER_SYSTEM
                        );

                        if ($record['attachment'] == 'Y') {
                            $this->processAttachment(
                                $record['serviceRequestID'],
                                $record
                            );
                        }

                        return true;
                    }

                }
            } else {
                $errorString = 'Can not find Service Request  ' . $record['serviceRequestID'] . '<BR/>';
                echo $errorString;
                return false;
            }
        } else { // no SR number
            return $this->processIsSenderAuthorised(
                $details,
                $contact,
                $record,
                $errorString
            );
        }
    }

    function processServerGuard($record)
    {
        $details = $record['textBody'];

        $contact = $this->getAlertContact(
            $record['customerID'],
            $record['postcode']
        );
        /*
    No monitor status = create new request
    */
        if ($record['monitorStatus'] == '') {
            /* Create new request */
            $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
            $details .= 'Raised from ServerGuard on ' . date(CONFIG_MYSQL_DATETIME);

            $this->raiseNewRequestFromImport(
                $record,
                $details,
                $contact
            );

            return true;       // nothing more to do
        }

        if ($record['monitorStatus'] == 'S') { // success

            $request =
                $this->getRequestByCustPostcodeMonitorNameAgentName(
                    $record['customerID'],
                    $record['postcode'],
                    $record['monitorName'],
                    $record['monitorAgentName']
                );

            if ($request) {

                $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
                $details .= 'Issue resolved - from ServerGuard';

                $dbeLastActivity = $this->getLastActivityInProblem($request['pro_problemno']);

                $this->createFollowOnActivity(
                    $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                    CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID,
                    $contact['contactID'],
                    $details,
                    false,
                    true,
                    USER_SYSTEM
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
                        $record['contractCustomerItemID'],
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
                    $record['customerID'],
                    $record['postcode'],
                    $record['monitorName'],
                    $record['monitorAgentName']
                );
            if ($request && $request['pro_status'] != 'C') { // request exists that is not completed

                $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
                $details .= 'Updated from ServerGuard';

                $dbeLastActivity = $this->getLastActivityInProblem($request['pro_problemno']);
                $callActivityID =
                    $this->createFollowOnActivity(
                        $dbeLastActivity->getValue(DBEJCallActivity::callActivityID),
                        CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID,
                        $contact['contactID'],
                        $details,
                        false,
                        true,
                        USER_SYSTEM
                    );
                $this->setActivityAwaitingCNC($callActivityID);

                if ($record['attachment'] == 'Y') {
                    $this->processAttachment(
                        $request['pro_problemno'],
                        $record
                    );
                }

                return true;
            } else {
                /* Create new request */
                $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
                $details .= 'Raised from ServerGuard on ' . date(CONFIG_MYSQL_DATETIME);

                $this->raiseNewRequestFromImport(
                    $record,
                    $details,
                    $contact
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
        add_postcode = '" . $postcode . "'";
        $db->query($queryString);
        $db->next_record();
        $ret['siteNo'] = $db->Record[0];

        if (!$ret['siteNo']) {
            $ret['siteNo'] = 0;
        }

        /* use main support contact */
        $queryString = "
      SELECT
        con_contno
      FROM
        contact
      WHERE
        con_custno = '" . $customerID . "'
        AND con_mailflag10 = 'Y'";

        $db->query($queryString);
        $db->next_record();

        $ret['contactID'] = $db->Record[0];

        /*
    if no main support contact set then use first support contact found

    SHOULD NEVER BE THE CASE but have to do this in case!
    */
        if (!$ret['contactID']) {
            $queryString = "
        SELECT
          con_contno
        FROM
          contact
        WHERE
          con_custno = '" . $customerID . "'
          AND con_mailflag5 = 'Y'";

            $db->query($queryString);
            $db->next_record();

            $ret['contactID'] = $db->Record[0];
        }

        $ret['customerID'] = $customerID;

        return $ret;

    }

    /**
     * New request from import process
     *
     * @param mixed $record
     * @param mixed $contact
     */
    function raiseNewRequestFromImport($record,
                                       $details,
                                       $contact
    )
    {

        $dbeProblem = new DBEProblem($this);

        /* if customer-raised then we derive customer from email address */
        if (!$record['customerID']) {
            $customerID = $contact['customerID'];
        } else {
            $customerID = $record['customerID'];
        }

        $slaResponseHours =
            $this->getSlaResponseHours(
                $record['priority'],
                $customerID
            );

        /*
    Determine site to use.

    If postcode passed from import, attempt to use that.

    Otherwise use site of main contact.
    */
        $siteNo = false;

        if ($record['postcode']) {
            $siteNo = $this->getSiteNoByCustomerPostcode(
                $customerID,
                $record['postcode']
            );
        }

        if (!$siteNo) {
            $siteNo = $contact['siteNo'];
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
            $record['priority']
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            date(CONFIG_MYSQL_DATETIME)
        ); // default
        $dbeProblem->setValue(
            DBEProblem::contactID,
            $contact['contactID']
        );

        /* @todo confirm with GL */
        if ($record['sendEmail'] == 'A') {
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

        if (!$record['queueNo']) {
            $queueNo = 1;
        } else {
            $queueNo = $record['queueNo'];
        }

        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            $queueNo
        );
        $dbeProblem->setValue(
            DBEJProblem::monitorName,
            $record['monitorName']
        );
        $dbeProblem->setValue(
            DBEJProblem::monitorAgentName,
            $record['monitorAgentName']
        );
        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            $record['rootCauseID']
        );
        $dbeProblem->setValue(
            DBEJProblem::contractCustomerItemID,
            $record['contractCustomerItemID']
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            ''
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
            $contact['contactID']
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(CONFIG_MYSQL_DATE)
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
            DBEJCallActivity::serverGuard,
            $record['serverGuardFlag']
        );
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

        if ($record['attachment'] == 'Y') {
            $this->processAttachment(
                $dbeProblem->getPKValue(),
                $record
            );
        }

        if ($dbeProblem->getValue(DBEJProblem::priority) == 5) {
            $fields['submittedTo'] = 'Project Team';
        } else {
            $fields['submittedTo'] = 'Service Desk';
        }

        $dsCustomer = new DBECustomer($this);
        $dsCustomer->getRow($customerID);

        $template = 'ServiceLoggedEmail';


        $testTime = (new \DateTime($record['createDateTime']))->format('H:i');

        $startTime = $this->dsHeader->getValue(DBEHeader::serviceDeskNotification24hBegin);
        $endTime = $this->dsHeader->getValue(DBEHeader::serviceDeskNotification24hEnd);
        echo '<br>test time is : ' . $testTime;
        echo '<br>startTime is : ' . $startTime;
        echo '<br>endTime is : ' . $endTime;


        if ($testTime < $startTime || $testTime > $endTime) {
            echo '<div>Should be special email</div>';
            $has24HourSupport = $dsCustomer->getValue(DBECustomer::support24HourFlag) == 'Y';
            $buCustomerItem = new BUCustomerItem($this);

            $hasServiceDesk = $buCustomerItem->customerHasServiceDeskContract($customerID);

            if ($has24HourSupport || $hasServiceDesk) {
                echo '<div>does have 24h support</div>';
                $template = 'ServiceLoggedEmail24h';
            } else {
                echo '<div>does NOT have 24h support</div>';
                $template = 'ServiceLoggedEmailNot24h';
            }
        } else {
            echo '<div>Should be normal email</div>';
        }

        $this->sendEmailToCustomer(
            array(
                'problemID'     => $dbeProblem->getPKValue(),
                'templateName'  => $template,
                'subjectSuffix' => 'New Request Logged',
                'fields'        => $fields
            )
        );


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

    function processAttachment($problemID,
                               $record
    )
    {

        $filePaths = explode(
            ',',
            $record['attachmentFilename']
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
                    $attachmentMimeType = '';   // failed to locate magic file for MimeTypes
                }

                $this->addDocument(
                    $problemID,
                    $filePath,
                    filesize($filePath),
                    'Imported',
                    $filePath,
                    $attachmentMimeType
                );

                fclose($handle);
                //unlink( $filePath );
                $ret = true;
            } else {
                $errorString = 'Failed to import attachment file ' . $filePath . '<BR/>';
                echo $errorString;
                $ret = false;
            }
        }
        return $ret;
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
        pro_status
      FROM
        problem
        JOIN callactivity ON caa_problemno = pro_problemno
        JOIN address 
            ON caa_siteno = add_siteno 
            AND pro_custno = add_custno
         
      WHERE pro_custno = $customerID 
        AND pro_monitor_name = ? 
        AND pro_monitor_agent_name = ? 
        AND pro_status NOT IN ('C') 
        AND add_postcode = '$postcode'
      ORDER BY pro_date_raised DESC";

        $parameters = [
            [
                'type'  => 's',
                'value' => $monitorName
            ],
            [
                'type'  => 's',
                'value' => $monitorAgentName
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
     * @param $callActivityID
     * @param bool $callActivityTypeID
     * @param bool $contactID for when we are creating from To Be Logged
     * @param bool $passedReason
     * @param bool $ifUnallocatedSetToCurrentUser
     * @param bool $setEndTimeToNow
     * @param $userID
     * @param bool $moveToUsersQueue
     * @param bool $resetAwaitingCustomerResponse
     * @return string
     */
    function createFollowOnActivity(
        $callActivityID,
        $callActivityTypeID = false,
        $contactID = false,
        $passedReason = false,
        $ifUnallocatedSetToCurrentUser = true,
        $setEndTimeToNow = false,
        $userID,
        $moveToUsersQueue = false,
        $resetAwaitingCustomerResponse = false
    )
    {
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($callActivityID);

        $reason = $passedReason;

        $isTravel = false;

        $endTime = '';                // default no end time

        if ($callActivityTypeID) {
            $dbeCallActType = new DBECallActType($this);
            $dbeCallActType->getRow($callActivityTypeID);
            if ($dbeCallActType->getValue(DBECallActType::travelFlag) == 'Y') {
                $isTravel = true;
            };

            if ($callActivityTypeID == CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID) {

                $endTime = $this->getEndtime(CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID);

                /*
        Prepopulate reason
        */
                $reason = "<table border='1' style='border: solid black 1px'><thead><tr><td></td><td>Details</td></tr></thead><tbody><tr><td>System:</td><td></td></tr><tr><td>Summary of problem:</td><td></td></tr><tr><td>Change Requested:</td><td></td></tr><tr><td>Method to test change if successful:</td><td></td></tr><tr><td>Reversion plan if unsuccessful:</td><td></td></tr></tbody></table>";
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

                if ($teamLevel < 3) {

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
                        array(
                            'problemID'     => $problemID,
                            'templateName'  => 'WorkCommencedEmail',
                            'subjectSuffix' => 'Work Started'
                        )
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
                            ''
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
                ''
            );
            $dbeProblem->setValue(
                DBEJProblem::alarmTime,
                ''
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

        $dbeCallActivity->setPKValue('');

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
            date(CONFIG_MYSQL_DATE)
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
            $ret = $dbeTeam->getValue(DBETeam::Level);
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
            $body,
            true
        );
    } // end email to customer

    function countEngineerActivitiesInProblem($problemID)
    {

        $dbeCallActivity = new DBECallActivity($this);

        return $dbeCallActivity->countEngineerRowsByProblem($problemID);
    }

    /**
     * sets problem out of pause mode by unsetting flag on activity
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
     */
    function setProblemToFixed(
        $problemID,
        $fixedUserID,
        $contractCustomerItemID,
        $rootCauseID,
        $resolutionSummary
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

        $dbeProblem->setValue(
            DBEJProblem::fixedUserID,
            $fixedUserID
        );
        $dbeProblem->setValue(
            DBEJProblem::fixedDate,
            date(CONFIG_MYSQL_DATETIME)
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            ''
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
            $resolutionSummary
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
        if (
            $fixedUserID != USER_SYSTEM &&
            $dbeProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'N'
        ) {

            $dbeRootCause = new DBERootCause($this);
            $dbeRootCause->getRow($rootCauseID);

            $parameters =
                array(
                    'problemID'     => $problemID,
                    'templateName'  => 'ServiceFixedEmail',
                    'subjectSuffix' => 'Fixed',
                    'fields'        =>
                        array(
                            'completeDate' => Controller::dateYMDtoDMY(
                                $dbeProblem->getValue(DBEJProblem::completeDate)
                            ),
                            'rootCause'    => $dbeRootCause->getValue(DBERootCause::description)
                        )
                );

            $this->sendEmailToCustomer($parameters);

        }

        return true;
    }

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
          caa_endtime = ''
            AND
            caa_problemno = " . $problemID;

        if ($exceptCallActivityID) {
            $sql .= " AND caa_callactivityno <> " . $exceptCallActivityID;
        }

        return $this->db->query($sql)->fetch_object()->openActivityCount;

    }

    function createFixedActivity($problemID,
                                 $resolutionSummary
    )
    {
        /*
    Start with duplicate of last activity
    */
        $dbeLastActivity = $this->getLastActivityInProblem($problemID);
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($dbeLastActivity->getValue(DBEJCallActivity::callActivityID));

        $dbeCallActivity->setPKValue('');
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(CONFIG_MYSQL_DATE)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            date('H:i')
        );

        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $this->getEndtime(CONFIG_FIXED_ACTIVITY_TYPE_ID)
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
    }

    /**
     * Calculate end time from start time for special types of activity
     *
     * @param mixed $callActTypeID
     * @param mixed $startTime Optional. If false then use current time
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

    /**
     * Sends email to the tecnician that escalated request to let them know request is fixed
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
            $body,
            false
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

    /**
     * Sends email to client when a service request it's priority changed
     *
     * @param mixed $callActivityID
     */
    function sendEmailToCustomer(
        $parameters
    )
    {
        /*
    $problemID,
    $templateName,
    $overrideServerGuard = false,
    $subjectSuffix = false
  */
        if (!isset($parameters['problemID'])) {
            $this->raiseError('No problemID passed');
        }
        if (!isset($parameters['templateName'])) {
            $this->raiseError('No templateName passed');
        }

        $buMail = new BUMail($this);

        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($parameters['problemID']);

        $dbeFirstActivity = $this->getFirstActivityInProblem($parameters['problemID']);
        $dbeLastActivity = $this->getLastActivityInProblem($parameters['problemID']);

        $dbeCallActType = new DBECallActType($this);

        if ($dbeLastActivity) {
            $dbeCallActType->getRow($dbeLastActivity->getValue(DBEJCallActivity::callActTypeID));
        } else {
            $dbeCallActType->getRow($dbeFirstActivity->getValue(DBEJCallActivity::callActTypeID));

        }

        if (
            $dbeJProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'Y' ||
            (
                $dbeFirstActivity->getValue(DBEJCallActivity::serverGuard) == 'Y' &
                !isset($parameters ['overrideServerGuard'])
            )
        ) {
            return; // no email to customer for this request
        }
        /*
    See whether to copy in the main contact
    */
        $copyEmailToMainContact = true;

        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($dbeJProblem->getValue(DBEJProblem::customerID));

        if ($dbeCustomer->getValue(DBECustomer::othersEmailMainFlag) == 'N') {

            $copyEmailToMainContact = false;

        } else {

            if (
                $parameters['templateName'] == 'WorkCommencedEmail' &&
                $dbeCustomer->getValue(DBECustomer::workStartedEmailMainFlag) == 'N'
            ) {
                $copyEmailToMainContact = false;
            }

        }
        /*
    End see whether to copy in main contact
    */

        /*
    See whether to send an email to the last activity contact
    */
        $sendEmailToLastActivityContact = true;

        if (
            $parameters['templateName'] == 'WorkCommencedEmail' &&
            $dbeLastActivity->getValue(DBEJCallActivity::workStartedEmailFlag) == 'N'
        ) {
            $sendEmailToLastActivityContact = false;
        }
        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $buCustomer = new BUCustomer($this);

        if ($sendEmailToLastActivityContact) {
            $toEmail = $dbeLastActivity->getValue(DBEJCallActivity::contactEmail);
        }
        /**
         * Send the email to all main support email addresses at the client but exclude them if
         * $copyEmailToMainContact set to exclude main contacts.
         */
        if (
            $copyEmailToMainContact &&
            $mainSupportEmailAddresses =
                $buCustomer->getMainSupportEmailAddresses(
                    $dbeLastActivity->getValue(DBEJCallActivity::customerID),
                    $toEmail
                )
        ) {

            if ($toEmail) {
                $toEmail .= ',';
            }
            $toEmail .= $mainSupportEmailAddresses;

        }

        if (!$toEmail) {
            return;                     // no email recipients so abort
        }

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );
        $template->set_file(
            'page',
            $parameters['templateName'] . '.inc.html'
        );

        $template->setVar(
            array(
                'contactFirstName'   => $dbeLastActivity->getValue(DBEJCallActivity::contactFirstName),
                'activityRef'        => $parameters['problemID'],
                'CONFIG_SERVICE_REQUEST_DESC'
                                     => CONFIG_SERVICE_REQUEST_DESC,
                'priority'           => $this->priorityArray[$dbeJProblem->getValue(DBEJProblem::priority)],
                'reason'             => $dbeFirstActivity->getValue(DBEJCallActivity::reason),
                'lastActivityReason' => $dbeLastActivity->getValue(DBEJCallActivity::reason),
                'responseDetails'    => strtolower(
                    $this->getResponseDetails($dbeFirstActivity)
                ),
                'technicianResponsible'
                                     => $dbeJProblem->getValue(DBEJProblem::engineerName)
            )
        );

        /*
    Any additional fields passed in $parameters['fields'] array
    */
        if (isset($parameters['fields'])) {

            foreach ($parameters['fields'] as $key => $value) {

                $template->setVar(
                    $key,
                    $value
                );

            }
        }

        $template->parse(
            'output',
            'page',
            true
        );

        $body = $template->get_var('output');

        $subject = CONFIG_SERVICE_REQUEST_DESC . ' ' . $parameters['problemID'];

        if ($parameters['subjectSuffix']) {

            $subject .= ' - ' . $parameters['subjectSuffix'];

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
    }

    function getSlaResponseHours($priority,
                                 $customerID
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
        /*
    Special attention customers get half of normal SLA
    */
        if (
            $dbeCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
            $dbeCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')
        ) {
            $slaHours = $slaHours / 2;
        }

        return $slaHours;
    }

    function getContactInfo($record)
    {
        $ret = false;
        /*
    try to find customer & support contact from email address
    */
        global $db;

        /*
    Extract just lower-case email address
    */
        $sender = trim(
            strtolower(
                preg_replace(
                    "/([\w\s]+)<([\S@._-]*)>/",
                    " $2",
                    $record['senderEmailAddress']
                )
            )
        );
        /*
    Extract email domain
    */
        $pieces = explode(
            '@',
            $sender
        );
        $emailDomain = strtolower(trim($pieces[1]));
        /*
    Try to match email domain against any customer
    */
        $sql = "
          SELECT
            con_contno,
            con_custno,
            con_siteno
          FROM
            contact
          WHERE
            con_email LIKE '%$emailDomain%'
            AND con_custno <> 0
            AND con_mailflag5 = 'Y'";

        $db->query($sql);

        if ($db->next_record()) {
            /*
      This is the default. i.e. Found at least one contact with matching email domain
      */
            $ret['isSupportContact'] = false;
            $ret['isMainContact'] = false;
            $ret['contactID'] = $db->Record[0];
            $ret['customerID'] = $db->Record[1];
            $customerID = $db->Record[1]; // use this in sebsequent queries
            $ret['siteNo'] = $db->Record[2];
            /*
      Try to find an exact support contact match
      */
            $sql = "
            SELECT
              con_contno,
              con_custno,
              con_siteno,
              con_mailflag5
            FROM
              contact
            WHERE
              con_email = '" . mysqli_real_escape_string(
                    $db->link_id(),
                    $record['senderEmailAddress']
                ) . "'
              AND con_custno <> 0 
              AND con_mailflag5 = 'Y'";

            $db->query($sql);
            if ($db->next_record()) {
                $ret['isSupportContact'] = true;
                $ret['isMainContact'] = false;
                $ret['contactID'] = $db->Record[0];
                $ret['customerID'] = $db->Record[1];
                $ret['siteNo'] = $db->Record[2];
            } /*
      No support contact found so try to find a main support contact at this customer
      unless it is in the list of excluded domains (e.g. gmail.com).

      This is a catchall in case the message is from a contact from a known customer who doesn't yet have an account.
      */
            elseif (
                !in_array(
                    $emailDomain,
                    $GLOBALS['exclude_sr_email_domains']
                ) &&
                $customerID
            ) {

                $sql = "
              SELECT
                con_contno,
                con_custno,
                con_siteno
              FROM
                contact
              WHERE
                con_custno = $customerID
                and con_mailflag10 = 'Y'";

                $db->query($sql);

                if ($db->next_record()) {
                    $ret['isSupportContact'] = false;
                    $ret['isMainContact'] = true;
                    $ret['contactID'] = $db->Record[0];
                    $ret['customerID'] = $db->Record[1];
                    $ret['siteNo'] = $db->Record[2];
                }
            } else {
                $ret = false;       // the email domain is in the excluded list or we
                // don't have a customerID
            }
        } // if( $db->next_record() )

        return $ret;          // false if nothing matched

    }

    function processIsSenderAuthorised($details,
                                       $contact,
                                       $record,
                                       &$errorString
    )
    {
        if ($contact && $contact['isSupportContact']) {

            $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
            $details .= 'New request from email received from ' . $record['senderEmailAddress'] . ' on ' . date(
                    CONFIG_MYSQL_DATETIME
                );

            $this->raiseNewRequestFromImport(
                $record,
                $details,
                $contact
            );

            return true;
        }

        if ($contact) {
            if ($contact['isMainContact']) {
                $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
                $details .= 'This email is from an unauthorised contact and needs to be confirmed' . "\n\n";
                $details .= 'New request from ' . $record['senderEmailAddress'] . ' on ' . date(CONFIG_MYSQL_DATETIME);

                $this->raiseNewRequestFromImport(
                    $record,
                    $details,
                    $contact
                );
                return true;
            } else {
                $errorString = 'Domain for ' . $record['senderEmailAddress'] . ' matches customer ' . $contact['customerID'] . ' but no main contact assigned for customer<br/>';

                echo $errorString;

                $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
                $details .= 'Email received from ' . $record['senderEmailAddress'] . ' on ' . date(
                        CONFIG_MYSQL_DATETIME
                    );

                $this->addCustomerRaisedRequest(
                    $contact,
                    $record,
                    false,
                    $details,
                    'C'
                );
                return false;
            }
        } else {
            /* unknown domain */
            $details = $record['subjectLine'] . "\n\n" . $details . "\n\n";
            $details .= 'Email received from ' . $record['senderEmailAddress'] . ' on ' . date(CONFIG_MYSQL_DATETIME);

            $this->addCustomerRaisedRequest(
                $contact,
                $record,
                false,
                $details,
                'C'
            );
            return true;
        }
    }

    function addCustomerRaisedRequest($contact,
                                      $record,
                                      $updateExistingRequest,
                                      $details = false,
                                      $source = 'S'
    )
    {
        $db = new dbSweetcode(); // database connection for query

        $queryString = "
      INSERT INTO
        customerproblem
      SET
        cpr_date =  NOW(),
        cpr_custno = '" . $contact['customerID'] . "',
        cpr_contno = '" . $contact['contactID'] . "',
        cpr_problemno = '" . $record['serviceRequestID'] . "',
        cpr_update_existing_request = '" . $updateExistingRequest . "',
        cpr_source = '$source' " . ",
        cpr_siteno = '" . $contact['siteNo'] . "',
        cpr_serverguard_flag = '" . $record['serverGuardFlag'] . "',
        cpr_send_email = '" . $record['sendEmail'] . "',
        cpr_priority = '" . $record['priority'] . "',
        cpr_reason = ?";

        $parameters = [
            [
                'type'  => 's',
                'value' => $details
            ]
        ];

        $db->preparedQuery(
            $queryString,
            $parameters
        );
    }

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
            pro_problemno = $problemID";

        $parameters = [
            [
                'type'  => 's',
                'value' => $details,
            ],
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
            date(CONFIG_MYSQL_DATE)
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

        $bcc =
            $dsCallActivity->getValue(DBEJCallActivity::userAccount) . '@cnc-ltd.co.uk' . ',' .
            CONFIG_SALES_EMAIL;

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

    } // end clearSystemSRQueue

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
     * @param mixed $record
     * @param mixed $contact
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
    }

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
                $customerID
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
                date(CONFIG_MYSQL_DATETIME)
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
                ''
            );        // not allocated
            $dbeProblem->insertRow();

            $problemID = $dbeProblem->getPKValue();

            $dbeCallActivity->setValue(
                DBEJCallActivity::callActivityID,
                0
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
                date(CONFIG_MYSQL_DATE)
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
                true,
                USER_SYSTEM
            );
        }

    }

    /**
     * Get existing activity that is in progress or fixed
     *
     * @param mixed $customerID
     * @param mixed $contractCustomerItemID
     * @param mixed $matchText
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
    }

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

    } // end sendPriorityOneReopenedEmail

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
          pro_consno";

        $db->query($sql);
        while ($db->next_record()) {
            $ret[] = $db->Record;
        }
        return $ret;

    }


    private function sendRequestAdditionalTimeEmail($problemID,
                                                    $reason,
                                                    $requestorID
    )
    {
        $buMail = new BUMail($this);

        $this->dbeUser->getRow($requestorID);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
//        $senderName = 'CNC Support Department';

        $dbeJLastCallActivity = $this->getLastActivityInProblem($problemID);

        $template = new Template(
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->set_file(
            'page',
            'ServiceAdditionalTimeEmail.inc.html'
        );

        $userName = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(DBEUser::lastName);

        $teamID = $this->dbeUser->getValue(DBEUser::teamID);

        $leftOnBudget = null;
        $usedMinutes = 0;
        $assignedMinutes = 0;

        $dbeProblem = new DBEJProblem($this);
        $dbeProblem->getRow($problemID);

        switch ($teamID) {
            case 1:
                $usedMinutes = $this->getHDTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                $toEmail = 'hdtimerequest@' . CONFIG_PUBLIC_DOMAIN;
                break;
            case 2:
                $usedMinutes = $this->getESTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                $toEmail = 'eqtimerequest@' . CONFIG_PUBLIC_DOMAIN;
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 4:
                $usedMinutes = $this->getIMTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::imLimitMinutes);
            default:
                $toEmail = 'imptimerequest@' . CONFIG_PUBLIC_DOMAIN;
        }

        $leftOnBudget = $assignedMinutes - $usedMinutes;
        $subject = 'Time Requested: ' . CONFIG_SERVICE_REQUEST_DESC . ' ' . $problemID . ' ' . $dbeJLastCallActivity->getValue(
                DBEJCallActivity::customerName
            ) . ' allocated to ' . $userName;

        $requestedReason = $reason;

        $urlAllocateAdditionalTime = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=allocateAdditionalTime&problemID=' . $problemID;

        $urlLastActivity = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayActivity&callActivityID=' . $dbeJLastCallActivity->getValue(
                DBEJCallActivity::callActivityID
            );

        $template->setVar(
            array(
                'problemID' => $problemID,

                'allocatedUserName' => $userName,

                'urlAllocateAdditionalTime' => $urlAllocateAdditionalTime,

                'urlLastActivity' => $urlLastActivity,

                'internalNotes' => $dbeProblem->getValue(DBEJProblem::internalNotes),

                'requestedReason' => $requestedReason,

                'chargeableActivityDurationHours' => $dbeProblem->getValue(
                    DBEJProblem::chargeableActivityDurationHours
                ),

                'totalActivityDurationHours' => $dbeProblem->getValue(DBEJProblem::totalActivityDurationHours),
                'timeLeftOnBudget'           => $leftOnBudget
            )
        );

        /* start history */
        $dsActivities = $this->getActivitiesByProblemID($problemID);

        $template->set_block(
            'page',
            'activityBlock',
            'rows'
        );

        while ($dsActivities->fetchNext()) {

            $template->set_var(
                array(
                    'reason'       => $dsActivities->getValue(DBEJCallActivity::reason),
                    'date'         => Controller::dateYMDtoDMY($dsActivities->getValue(DBEJCallActivity::date)),
                    'startTime'    => $dsActivities->getValue(DBEJCallActivity::startTime),
                    'endTime'      => $dsActivities->getValue(DBEJCallActivity::endTime),
                    'activityType' => $dsActivities->getValue(DBEJCallActivity::activityType),
                    'contactName'  => $dsActivities->getValue(DBEJCallActivity::contactName),
                    'duration'     => number_format(
                        $dsActivities->getValue(DBEJCallActivity::durationMinutes) / 60,
                        2
                    ),
                    'userName'     => $dsActivities->getValue(DBEJCallActivity::userName),
                )
            );

            $template->parse(
                'rows',
                'activityBlock',
                true
            );

        }

        /* end history */

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
            $body,
            true
        );


        $text = 'Additional time requested - Reason: ' . $reason;

        $this->logOperationalActivity(
            $problemID,
            $text
        );

    } // end sendServiceReallocatedEmail

    function getActivitiesByProblemID($problemID)
    {
        $this->dbeJCallActivity->getRowsByProblemID(
            $problemID,
            false
        );

        return $this->dbeJCallActivity;

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

        $this->logOperationalActivity(
            $problemID,
            '<p>Additional time allocated: ' . $minutes . ' minutes</p><p>' . $comments . '</p>'
        );

        $this->sendTimeAllocatedEmail(
            $minutes,
            $comments
        );
    }

    /*
  Send email to SD Managers requesting more time to be allocated to SR
  */

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

        $this->sendRequestAdditionalTimeEmail(
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
     */
    function createUserTimeLogsForMissingUsers($date = null)
    {
        ?>
        <div>
            Date received is <?= $date ?>
        </div>
        <?php

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

            ?>
            <div>
                Date received is a bank holiday or a weekend
            </div>
            <?php
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
     */
    function createUserTimeLogRecord($userID,
                                     DateTime $date = null
    )
    {
        global $db;

        if (!$date) {
            $date = new DateTime();
        }
        var_dump($date);
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
        var_dump($dateFormatted);
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
          $dateFormatted,
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

        $hdrs = array(
            'From'         => $senderEmail,
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

        $toEmail = 'managementreview@' . CONFIG_PUBLIC_DOMAIN;

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body,
            true
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
        AND caa_endtime > ''";

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

        $body = preg_replace(
            '/[\x00-\x1F\x7F-\xFF]/',
            '',
            $body
        );
        $body = preg_replace(
            '/[\x00-\x1F\x7F]/',
            '',
            $body
        );
        $body = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            $body
        );

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
                $body,
                true
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

    public function closeActivitiesWithEndTime($problemID)
    {
        global $db;
        $sql = "update callactivity  set caa_status  = 'C'  WHERE caa_problemno = $problemID and caa_endtime <> ''";
        $db->query($sql);
        return true;
    }

    public function getSDDashBoardData($problems,
                                       $limit,
                                       $order = 'shortestSLARemaining',
                                       $isP5 = false
    )
    {
        $dbeJProblem = new DBEJProblem($this);

        $dbeJProblem->getDashBoardRows(
            $limit,
            $order,
            $isP5
        );

        $this->getData(
            $dbeJProblem,
            $problems
        );
    }

    public function getActivityCount($problemID,
                                     $ignoreOperation = true
    )
    {
        $dbejCallActivity = new DBEJCallActivity($this);
        $dbejCallActivity->getRowsByProblemID(
            $problemID,
            true,
            !$ignoreOperation
        );
        $count = 0;
        while ($thing = $dbejCallActivity->fetchNext()) {
            $count++;
        }
        return $count;
    }

    public function getSDDashBoardEngineersInSRData($problems,
                                                    $engineersMaxCount = 3,
                                                    $pastHours = 24,
                                                    $limit = 5,
                                                    $isP5 = false
    )
    {
        $dbeJProblem = new DBEJProblem($this);

        $dbeJProblem->getDashBoardEngineersInSRRows(
            $engineersMaxCount,
            $pastHours,
            $limit,
            $isP5
        );

        $this->getData(
            $dbeJProblem,
            $problems
        );

    }


} // End of class
?>