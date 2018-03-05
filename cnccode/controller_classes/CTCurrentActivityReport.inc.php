<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

// Actions
class CTCurrentActivityReport extends CTCNC
{

    var $filterUser = array();
    var $allocatedUser = array();
    var $priority = array();
    var $prioritySelectArray = array();
    var $loggedInUserIsSdManager;

    var $customerFilterList;

    const AMBER = '#FFF5B3';
    const RED = '#F8A5B6';
    const GREEN = '#BDF8BA';
    const BLUE = '#b2daff';
    const CONTENT = '#F4f4f2';
    const PURPLE = '#dcbdff';
    const ORANGE = '#FFE6AB';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);

        $this->buActivity = new BUActivity($this);

        $this->buCustomerItem = new BUCustomerItem($this);

        $dbeUser = new DBEUser($this);

        $dbeUser->getRows('firstName');

        while ($dbeUser->fetchNext()) {

            $userRow =
                array(
                    'userID' => $dbeUser->getValue('userID'),
                    'userName' => $dbeUser->getValue('name'),
                    'fullName' => $dbeUser->getValue('firstName') . ' ' . $dbeUser->getValue('lastName'
                        ));

            $this->allocatedUser[$dbeUser->getValue('userID')] = $userRow;

            if ($dbeUser->getValue('appearInQueueFlag') == 'Y') {

                $this->filterUser[$dbeUser->getValue('userID')] = $userRow;
            }
        }

        if (!isset($_SESSION['priorityFilter'])) {

            foreach ($this->buActivity->priorityArray as $key => $value) {

                $_SESSION['priorityFilter'][] = $key;

            }

        }

        $buUser = new BUUser($this);
        $this->loggedInUserIsSdManager = $buUser->isSdManager($this->userID);


    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {


        if (!isset($_SESSION['displayToBeLoggedSectionFlag'])) {
            $_SESSION['displayToBeLoggedSectionFlag'] = 1;
        }
        if (!isset($_SESSION['displayQueue1Flag'])) {
            $_SESSION['displayQueue1Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue2Flag'])) {
            $_SESSION['displayQueue2Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue3Flag'])) {
            $_SESSION['displayQueue3Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue4Flag'])) {
            $_SESSION['displayQueue4Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue5Flag'])) {
            $_SESSION['displayQueue5Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue7Flag'])) {
            $_SESSION['displayQueue7Flag'] = 1;
        }

        switch ($_REQUEST['action']) {

            case 'allocateUser':
                //       $this->checkPermissions(PHPLIB_PERM_SUPERVISOR); is this required now?
                $this->allocateUser();
                break;

            case 'showMineOnly':
                $this->showMineOnly();
                break;

            case 'setFilter':
                $this->setFilter();
                break;

            case 'resetFilter':
                $this->resetFilter();
                break;

            case 'toggleDisplayToBeLoggedFlag':
                $this->toggleDisplayToBeLoggedFlag();
                break;

            case 'toggleDisplayQueue1Flag':
                $this->toggleDisplayQueue1Flag();
                break;

            case 'toggleDisplayQueue2Flag':
                $this->toggleDisplayQueue2Flag();
                break;

            case 'toggleDisplayQueue3Flag':
                $this->toggleDisplayQueue3Flag();
                break;

            case 'toggleDisplayQueue4Flag':
                $this->toggleDisplayQueue4Flag();
                break;

            case 'toggleDisplayQueue5Flag':
                $this->toggleDisplayQueue5Flag();
                break;

            case 'toggleDisplayQueue6Flag':
                $this->toggleDisplayQueue6Flag();
                break;

            case 'toggleDisplayQueue7Flag':
                $this->toggleDisplayQueue7Flag();
                break;

            case 'escalate':
                $this->escalate();
                break;

            case 'deescalate':
                $this->deescalate();
                break;

            case 'toggleDisplayFixedPendingClosureFlag':
                $this->checkPermissions(PHPLIB_PERM_SUPERVISOR);
                $this->toggleDisplayFixedPendingClosureFlag();
                break;
            case 'deleteCustomerRequest':
                $this->checkPermissions(PHPLIB_PERM_TECHNICAL);
                $this->deleteCustomerRequest();
                break;
            default:
                $this->displayReport();
                break;
        }
    }

    function toggleDisplayToBeLoggedFlag()
    {
        if ($_SESSION['displayToBeLoggedFlag']) {
            $_SESSION['displayToBeLoggedFlag'] = false;
        } else {
            $_SESSION['displayToBeLoggedFlag'] = true;
        }
    }

    function toggleDisplayQueue1Flag()
    {
        if ($_SESSION['displayQueue1Flag']) {
            $_SESSION['displayQueue1Flag'] = false;
        } else {
            $_SESSION['displayQueue1Flag'] = true;
        }
    }

    function toggleDisplayQueue2Flag()
    {
        if ($_SESSION['displayQueue2Flag']) {
            $_SESSION['displayQueue2Flag'] = false;
        } else {
            $_SESSION['displayQueue2Flag'] = true;
        }
    }

    function toggleDisplayQueue3Flag()
    {
        if ($_SESSION['displayQueue3Flag']) {
            $_SESSION['displayQueue3Flag'] = false;
        } else {
            $_SESSION['displayQueue3Flag'] = true;
        }
    }

    function toggleDisplayQueue4Flag()
    {
        if ($_SESSION['displayQueue4Flag']) {
            $_SESSION['displayQueue4Flag'] = false;
        } else {
            $_SESSION['displayQueue4Flag'] = true;
        }
    }

    function toggleDisplayQueue5Flag()
    {
        if ($_SESSION['displayQueue5Flag']) {
            $_SESSION['displayQueue5Flag'] = false;
        } else {
            $_SESSION['displayQueue5Flag'] = true;
        }
    }

    function toggleDisplayQueue6Flag()
    {
        if ($_SESSION['displayQueue6Flag']) {
            $_SESSION['displayQueue6Flag'] = false;
        } else {
            $_SESSION['displayQueue6Flag'] = true;
        }
    }

    function toggleDisplayQueue7Flag()
    {
        if ($_SESSION['displayQueue7Flag']) {
            $_SESSION['displayQueue7Flag'] = false;
        } else {
            $_SESSION['displayQueue7Flag'] = true;
        }
    }

    function showMineOnly()
    {
        unset($_SESSION['selectedUserID']);

        $_SESSION['selectedUserID'] = $GLOBALS['auth']->is_authenticated();

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
        exit;

    }

    function allocateUser()
    {
        $dbeUser = new DBEUser ($this);
        $dbeUser->setValue('userID', $this->userID);
        $dbeUser->getRow();

        $this->buActivity->allocateUserToRequest($_REQUEST['problemID'], $_REQUEST['userID'], $dbeUser);

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Remove all filters
     */
    function resetFilter()
    {

        unset($_SESSION['selectedUserID']);
        unset($_SESSION['selectedCustomerID']);
        unset($_SESSION['priorityFilter']);

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Set filtering
     */
    function setFilter()
    {

        if (isset($_REQUEST['selectedUserID'])) {
            $_SESSION['selectedUserID'] = $_REQUEST['selectedUserID'];
        }

        if (isset($_REQUEST['priorityFilter'])) {
            $_SESSION['priorityFilter'] = $_REQUEST['priorityFilter'];
        }

        if (isset($_REQUEST['selectedCustomerID'])) {
            $_SESSION['selectedCustomerID'] = $_REQUEST['selectedCustomerID'];
        }

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Display search form
     * @access private
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');

        unset($this->customerFilterList);   // for the customer filter drop-down (limited to customers with SRs)

        $this->setTemplateFiles(
            array(
                'CurrentActivityReport' => 'CurrentActivityReportEngineer.inc',
            )
        );


        $this->setTemplateFiles('CurrentActivityReport', 'CurrentActivityReport.inc');

        $openSrsByUser = $this->buActivity->getOpenSrsByUser();

        $this->template->set_block('CurrentActivityReport', 'userSrCountBlock', 'userSrCount');

        foreach ($openSrsByUser as $row) {

            $this->template->set_var(

                array(
                    'openSrInitials' => $row['initials'],
                    'openSrCount' => $row['count']
                )
            );

            $this->template->parse('userSrCount', 'userSrCountBlock', true);

        }

        $customerRaisedRequests = $this->buActivity->getCustomerRaisedRequests();
        /*
        Requests raised via the customer portal
        */
        $customerRaisedRequests->next_record();

        $count = 0;

        if ($customerRaisedRequests->Record) {

            $this->template->set_block('CurrentActivityReport', 'customerRequestsBlock', 'customerRequests');

            do {

                $urlCreateRequestFromCustomerRequest =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action' => 'createRequestFromCustomerRequest',
                            'cpr_customerproblemno' => $customerRaisedRequests->Record['cpr_customerproblemno']
                        )
                    );

                $createRequestOnClick = "document.location='" . $urlCreateRequestFromCustomerRequest . "'";

                $newButton = '
          <INPUT
          type="button"
          value="N"
          title="Create New"
          onClick="' . $createRequestOnClick . '">';


                if ($customerRaisedRequests->Record['cpr_update_existing_request'] == 1) {
                    $urlUpdateCustomerRequest =
                        $this->buildLink(
                            'Activity.php',
                            array(
                                'action' => 'updateRequestFromCustomerRequest',
                                'cpr_customerproblemno' => $customerRaisedRequests->Record['cpr_customerproblemno']
                            )
                        );

                    $updateRequestOnClick = "document.location='" . $urlUpdateCustomerRequest . "'";

                    $updateButton = '
            <INPUT
            type="button"
            value="U"
            title="Update Existing"
            onClick="' . $updateRequestOnClick . '">';
                } else {
                    $updateButton = '';
                }


                $urlDeleteCustomerRequest =
                    $this->buildLink(
                        'CurrentActivityReport.php',
                        array(
                            'action' => 'deleteCustomerRequest',
                            'cpr_customerproblemno' => $customerRaisedRequests->Record['cpr_customerproblemno']
                        )
                    );

                $urlCustomer =
                    $this->buildLink(
                        'SalesOrder.php',
                        array(
                            'action' => 'search',
                            'customerID' => $customerRaisedRequests->Record['con_custno']
                        )
                    );

                if ($customerRaisedRequests->Record['cpr_problemno'] > 0) {
                    $urlServiceRequest =
                        $this->buildLink(
                            'Activity.php',
                            array(
                                'action' => 'displayServiceRequest',
                                'problemID' => $customerRaisedRequests->Record['cpr_problemno']
                            )
                        );
                    $txtServiceRequestID = $customerRaisedRequests->Record['cpr_problemno'];
                } else {
                    $txtServiceRequestID = '';
                    $urlServiceRequestID = '';

                }


                $truncatedReason = $this->truncate($customerRaisedRequests->Record['cpr_reason'], 150);

                if ($customerRaisedRequests->Record['cpr_source'] == 'S') {

                    $bgColour = self::CONTENT;

                } else {

                    $bgColour = self::RED;    // customer raised

                }
                $count++;

                $urlDetailsPopup =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action' => 'customerProblemPopup',
                            'customerProblemID' => $customerRaisedRequests->Record['cpr_customerproblemno'],
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );


                $this->template->set_var(

                    array(
                        'urlDetailsPopup' => $urlDetailsPopup,
                        'cpCustomerProblemID' => $customerRaisedRequests->Record['cpr_customerproblemno'],
                        'cpNewButton' => $newButton,
                        'cpUpdateButton' => $updateButton,
                        'cpCustomerName' => $customerRaisedRequests->Record['cus_name'],
                        'cpContactName' => $customerRaisedRequests->Record['con_first_name'] . ' ' . $customerRaisedRequests->Record['con_last_name'],
                        'cpDate' => $customerRaisedRequests->Record['cpr_date'],
                        'cpServiceRequestID' => $txtServiceRequestID,
                        'cpUrlServiceRequest' => $urlServiceRequest,
                        'cpPriority' => $customerRaisedRequests->Record['cpr_priority'],
                        'cpTruncatedReason' => $truncatedReason,
                        'cpFullReason' => $customerRaisedRequests->Record['cpr_reason'],
                        'cpUrlCustomer' => $urlCustomer,
                        'urlDeleteCustomerRequest' => $urlDeleteCustomerRequest,
                        'cpBgColor' => $bgColour,
                        'cpCount' => $count
                    )

                );

                $this->template->parse('customerRequests', 'customerRequestsBlock', true);

            } while ($customerRaisedRequests->next_record());

        }

        $this->template->set_var('customerPortalCount', $count);

        $this->template->set_var('displayToBeLoggedFlag', ($_SESSION['displayToBeLoggedFlag'] == 0) ? '0' : '1');
        $this->template->set_var('displayQueue1Flag', ($_SESSION['displayQueue1Flag'] == 0) ? '0' : '1');
        $this->template->set_var('displayQueue2Flag', ($_SESSION['displayQueue2Flag'] == 0) ? '0' : '1');
        $this->template->set_var('displayQueue3Flag', ($_SESSION['displayQueue3Flag'] == 0) ? '0' : '1');
        $this->template->set_var('displayQueue4Flag', ($_SESSION['displayQueue4Flag'] == 0) ? '0' : '1');
        $this->template->set_var('displayQueue5Flag', ($_SESSION['displayQueue5Flag'] == 0) ? '0' : '1');
        $this->template->set_var('displayQueue6Flag', ($_SESSION['displayQueue6Flag'] == 0) ? '0' : '1');
        $this->template->set_var('displayQueue7Flag', ($_SESSION['displayQueue7Flag'] == 0) ? '0' : '1');


        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . 's');


        $this->renderQueue(1);  // Helpdesk
        $this->renderQueue(2);  //Escalations
        $this->renderQueue(3);  // Sales
        $this->renderQueue(4);  //Implementations
        $this->renderQueue(5);  // Managers
        $this->renderQueue(6);  //Fixed
        $this->renderQueue(7); // Future

        $this->template->set_block('CurrentActivityReport', 'userFilterBlock', 'users');

        foreach ($this->filterUser as $key => $value) {

            if ($value['userID'] == $_SESSION['selectedUserID']) {
                $userSelected = 'SELECTED';
            } else {
                $userSelected = '';
            }

            $this->template->set_var(
                array(
                    'filterUserName' => $value['fullName'],
                    'filterUserID' => $value['userID'],
                    'filterUserSelected' => $userSelected
                )
            );

            $this->template->parse('users', 'userFilterBlock', true);
        }
        /*
        Priority Filter
        */
        $this->template->set_block('CurrentActivityReport', 'priorityFilterBlock', 'priorityFilters');

        foreach ($this->buActivity->priorityArray as $key => $value) {

            if (
            in_array($key, $_SESSION['priorityFilter'])
            ) {
                $checked = 'checked';
            } else {
                $checked = '';
            }

            $this->template->set_var(
                array(
                    'priority' => $key,
                    'priorityChecked' => $checked
                )
            );

            $this->template->parse('priorityFilters', 'priorityFilterBlock', true);

        }
        // end priority filter

        /*
        customer filter
        */
        $this->template->set_block('CurrentActivityReport', 'customerFilterBlock', 'customers');

        if ($this->customerFilterList) {
            asort($this->customerFilterList);
            foreach ($this->customerFilterList as $customerID => $customerName) {

                if ($_SESSION['selectedCustomerID'] == $customerID) {
                    $customerIDSelected = 'SELECTED';
                } else {
                    $customerIDSelected = '';
                }
                $this->template->set_var(
                    array(
                        'filterCustomerIDSelected' => $customerIDSelected,
                        'filterCustomerID' => $customerID,
                        'filterCustomerName' => $customerName
                    )
                );

                $this->template->parse('customers', 'customerFilterBlock', true);
            }
        }
        /*
        end customer filter
        */
        $urlSetFilter =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'setFilter'
                )
            );

        $urlResetFilter =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'resetFilter')
            );

        $urlShowMineOnly =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'showMineOnly')
            );

        $urlAllocateUser =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'allocateUser'
                )
            );
        $javascript = '
      <script language="JavaScript">
        function allocateUser( form ) { 
          var newIndex = form.userID.selectedIndex; 
          cururl = \'' . $urlAllocateUser . '\' + form.userID.options[ newIndex ].value; 
          window.location.assign( cururl ); 
        } 
      </script>';

        $this->template->set_var(

            array(
                'javaScript' => $javascript,
                'urlResetFilter' => $urlResetFilter,
                'urlShowMineOnly' => $urlShowMineOnly,
                'urlSetFilter' => $urlSetFilter
            )

        );


        $this->template->parse('CONTENTS', 'CurrentActivityReport', true);


        $this->parsePage();

    } // end function displayReport

    private function renderQueue($queueNo)
    {
        if ($queueNo == 6) {
            /* fixed awaiting closure */
            $this->buActivity->getProblemsByStatus('F', $dsResults, false);

        } elseif ($queueNo == 7) {
            /* future dated */
            $this->buActivity->getFutureProblems($dsResults);

        } else {
            $this->buActivity->getProblemsByQueueNo($queueNo, $dsResults);

        }

        $blockName = 'queue' . $queueNo . 'Block';

        $this->template->set_block('CurrentActivityReport', $blockName, 'requests' . $queueNo);

        $rowCount = 0;

        while ($dsResults->fetchNext()) {

            $this->customerFilterList[$dsResults->getValue('customerID')] = $dsResults->getValue('customerName');

            if (
            !in_array($dsResults->getValue('priority'), $_SESSION['priorityFilter'])
            ) {
                continue;
            }


            if ($_SESSION['selectedCustomerID'] && $_SESSION['selectedCustomerID'] != $dsResults->getValue('customerID')) {
                continue;
            }
            $userID = $dsResults->getValue('userID');

            $engineerName = $dsResults->getValue('engineerName');

            if (
                ($_SESSION['selectedUserID'] && $_SESSION['selectedUserID'] != $dsResults->getValue('userID')) AND

                $dsResults->getValue('userID') != '0'        // always show Unallocated
            ) {
                continue;
            }

            $rowCount++;

            $urlProblemDetailsPopup =
                $this->buildLink(
                    'Activity.php',
                    array(
                        'action' => 'reasonPopup',
                        'problemID' => $dsResults->getValue('problemID'),
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );

            $urlViewActivity =
                $this->buildLink(
                    'Activity.php',
                    array(
                        'action' => 'displayLastActivity',
                        'problemID' => $dsResults->getValue('problemID')
                    )
                );

            $urlAllocateUser =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'allocateUser'
                    )
                );

            if ($this->loggedInUserIsSdManager) {

                $urlAllocateAdditionalTime =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action' => 'allocateAdditionalTime',
                            'problemID' => $dsResults->getValue('problemID')
                        )
                    );

                $linkAllocateAdditionalTime = '<a href="' . $urlAllocateAdditionalTime . '" title="Allocate additional time"><img src="/images/clock.png" width="20px">';
            } else {
                $urlAllocateAdditionalTime = '';
            }

            $javascript = '<script language="JavaScript">

      function allocateUser( form ) { 

        var newIndex = form.userID.selectedIndex; 

        
        cururl = \'' . $urlAllocateUser . '\' + form.userID.options[ newIndex ].value; 
        window.location.assign( cururl ); 

      } 

      </script>';
            /* This code left here in case it is useful following testing:
            if (
              $dsResults->getValue( 'lastCallActTypeID' ) == 0
            ){
              $bgColor =  self::GREEN; // green = in progress
            }
            else{
              $bgColor = self::CONTENT;

            }
            */

            $bgColour = $this->getResponseColour(
                $dsResults->getValue('status'),
                $dsResults->getValue('priority'),
                $dsResults->getValue('slaResponseHours'),
                $dsResults->getValue('workingHours'),
                $dsResults->getValue('respondedHours')
            );
            /*
            Updated by another user?
            */
            if (
                $dsResults->getValue('userID') &&
                $dsResults->getValue('userID') != $dsResults->getValue('lastUserID')
            ) {
                $updatedBgColor = self::PURPLE;
            } else {
                $updatedBgColor = self::CONTENT;
            }

            if ($dsResults->getValue('respondedHours') == 0 && $dsResults->getValue('status') == 'I') {
                /*
                Initial SRs that have not yet been responded to
                */
                $hoursRemainingBgColor = self::AMBER;
            } elseif ($dsResults->getValue('awaitingCustomerResponseFlag') == 'Y') {
                $hoursRemainingBgColor = self::GREEN;
            } else {
                $hoursRemainingBgColor = self::BLUE;
            }
            /* ------------------------------ */

            $urlCustomer =
                $this->buildLink(
                    'SalesOrder.php',
                    array(
                        'action' => 'search',
                        'customerID' => $dsResults->getValue('customerID')
                    )
                );

            $escalateButton = '';
            $deEscalateButton = '';

            if ($dsResults->getValue('queueNo') < 5) {

                $urlEscalate =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'escalate',
                            'problemID' => $dsResults->getValue('problemID')
                        )
                    );

                $escalateButton = '
            <a
            href="' . $urlEscalate . '"
            title="Escalate"
            onClick="return confirm(\'Are you sure you want to escalate this SR?\')"
            ><img src="/images/up_arrow.png"></a>';

            }

            if ($dsResults->getValue('queueNo') > 1) {
                $urlDeEscalate =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'deescalate',
                            'problemID' => $dsResults->getValue('problemID')
                        )
                    );

                $deEscalateButton = '
            <a
            href="' . $urlDeEscalate . '"
            title="De-escalate" 
            onClick="return confirm(\'Are you sure you want to deescalate this SR?\')"
            ><img src="/images/down_arrow.png"></a>';

            }


            if ($dsResults->getValue('alarmDate') && $dsResults->getValue('alarmDate') != '0000-00-00') {

                $alarmDateTimeDisplay = Controller::dateYMDtoDMY($dsResults->getValue('alarmDate')) . ' ' . $dsResults->getValue('alarmTime');

                /*
                Has an alarm date that is in the past, set updated BG Colour (indicates moved back into work queue from future queue)
                */
                if ($dsResults->getValue('alarmDate') <= date(CONFIG_MYSQL_DATE)) {
                    $updatedBgColor = self::PURPLE;
                }

            } else {
                $alarmDateTimeDisplay = '';

            }
            /*
            If the dashboard is filtered by customer then the Work button opens
            Activity edit
            */
            if (
                $dsResults->getValue('lastCallActTypeID') == 0
            ) {
                $workBgColor = self::GREEN; // green = in progress
                $workOnClick = "alert( 'Another user is currently working on this SR' ); return false";
            } else {

                $workBgColor = self::CONTENT;

                if ($_SESSION['selectedCustomerID']) {
                    $urlWork =
                        $this->buildLink(
                            'Activity.php',
                            array(
                                'action' => 'createFollowOnActivity',
                                'callActivityID' => $dsResults->getValue('callActivityID')
                            )

                        );

                    $workOnClick = "if(confirm('Are you sure you want to start work on this SR? It will be automatically allocated to you UNLESS it is already allocated')) document.location='" . $urlWork . "'";
                } else {

                    /*
                    If the dashboard is not filtered by customer then the Work button filters by
                    this customer
                    */
                    $urlWork =
                        $this->buildLink(
                            'CurrentActivityReport.php',
                            array(
                                'action' => 'setFilter',
                                'selectedCustomerID' => $dsResults->getValue('customerID'),
                                'selectedUserID' => ''
                            )
                        );

                    $workOnClick = "if(confirm('Filter all SRs by this customer in preparation to start work')) document.location='" . $urlWork . "'";
                }
            }

            if ($dsResults->getValue('priority') == 1) {
                $priorityBgColor = self::ORANGE;
            } else {
                $priorityBgColor = self::CONTENT;
            }


            $problemID = $dsResults->getValue('problemID');
            $buActivity = new BUActivity($this);

            $hdUsedMinutes = $buActivity->getHDTeamUsedTime($problemID);
            $esUsedMinutes = $buActivity->getESTeamUsedTime($problemID);
            $imUsedMinutes = $buActivity->getIMTeamUsedTime($problemID);

            $dbeProblem = new DBEProblem($this);
            $dbeProblem->setValue(DBEProblem::problemID, $problemID);
            $dbeProblem->getRow();

            $hdAssignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
            $esAssignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
            $imAssignedMinutes = $dbeProblem->getValue(DBEProblem::imLimitMinutes);

            $hdRemaining = $hdAssignedMinutes - $hdUsedMinutes;
            $esRemaining = $esAssignedMinutes - $esUsedMinutes;
            $imRemaining = $imAssignedMinutes - $imUsedMinutes;


            $hoursRemaining = number_format($dsResults->getValue('workingHours') - $dsResults->getValue('slaResponseHours'), 1);
            $totalActivityDurationHours = $dsResults->getValue('totalActivityDurationHours');
            $this->template->set_var(

                array(
                    'escalateButton' => $escalateButton,
                    'deEscalateButton' => $deEscalateButton,
                    'workOnClick' => $workOnClick,
                    'hoursRemaining' => $hoursRemaining,
                    'updatedBgColor' => $updatedBgColor,
                    'priorityBgColor' => $priorityBgColor,
                    'hoursRemainingBgColor' => $hoursRemainingBgColor,
                    'totalActivityDurationHours' => $totalActivityDurationHours,
                    'hdRemaining' => $hdRemaining,
                    'esRemaining' => $esRemaining,
                    'imRemaining' => $imRemaining,
                    'hdColor' => $this->pickColor($hdRemaining),
                    'esColor' => $this->pickColor($esRemaining),
                    'imColor' => $this->pickColor($imRemaining),
                    'urlCustomer' => $urlCustomer,
                    'time' => $dsResults->getValue('lastStartTime'),
                    'date' => Controller::dateYMDtoDMY($dsResults->getValue('lastDate')),
                    'problemID' => $dsResults->getValue('problemID'),
                    'reason' => $this->truncate($dsResults->getValue('reason'), 150),
                    'urlProblemHistoryPopup' => $this->getProblemHistoryLink($dsResults->getValue('problemID')),
                    'engineerDropDown' => $this->getAllocatedUserDropdown($dsResults->getValue('problemID'), $dsResults->getValue('userID')),
                    'engineerName' => $dsResults->getValue('engineerName'),
                    'customerName' => $dsResults->getValue('customerName'),
                    'customerNameDisplayClass'
                    => $this->getCustomerNameDisplayClass($dsResults->getValue('specialAttentionFlag'), $dsResults->getValue('specialAttentionEndDate')),
                    'urlViewActivity' => $urlViewActivity,
                    'linkAllocateAdditionalTime' => $linkAllocateAdditionalTime,
                    'slaResponseHours' => number_format($dsResults->getValue('slaResponseHours'), 1),
                    'priority' => Controller::htmlDisplayText($dsResults->getValue('priority')),
                    'alarmDateTime' => $alarmDateTimeDisplay,
                    'bgColour' => $bgColour,
                    'workBgColor' => $workBgColor

                )

            );

            $this->template->parse('requests' . $queueNo, $blockName, true);


        } // end while

        $this->template->set_var(
            array(
                'queue' . $queueNo . 'Count' => $rowCount,
                'queue' . $queueNo . 'Name' => $this->buActivity->workQueueDescriptionArray[$queueNo],

            )
        );
    } // end render queue


    private function pickColor($value)
    {
        if ($value <= 5) {
            return 'red';
        } else if ($value >= 6 && $value <= 20) {
            return '#FFBF00';
        } else {
            return 'green';
        }
    }

    /**
     * Return the appropriate background colour for this problem
     *
     *
     * @param <type> $dsResult
     */
    function getResponseColour(
        $status,
        $priority,
        $slaResponseHours,
        $workingHours,
        $respondedHours
    )
    {
        /*
        Prevent divide by zero error
        */
        if ($slaResponseHours == 0) {
            $slaResponseHours = 1;
        }
        /*
        priority 5 always green
        */
        if ($priority == 5) {
            $bgColour = self::GREEN; /// green
        } else {
            if ($status == 'I') {
                /* initial status so calculate */

                $percentageSLA = ($workingHours / $slaResponseHours);

                if ($percentageSLA <= 0.75) {

                    $bgColour = self::GREEN; /// green

                } elseif ($percentageSLA > 0.75 AND $percentageSLA < 1) {

                    $bgColour = self::AMBER; // amber

                } else {

                    $bgColour = self::RED; // red

                }

            } else {
                // Status is beyond initial so use recorded
                if ($respondedHours <= $slaResponseHours) {
                    $bgColour = self::GREEN;  // within SLA
                } else {
                    $bgColour = self::RED;    // Missed SLA
                }
            }
        }

        return $bgColour;
    }

    function getAlarmColour($alarmDate, $alarmTime)
    {

        if ($alarmDate && $alarmDate != '0000-00-00') {
            $dateNow = strtotime(date('Y-m-d H:i'));
            $dateAlarm = strtotime($alarmDate . ' ' . $alarmTime);

            if ($dateNow >= $dateAlarm) {

                $bgColour = self::RED; // red = ready to start

            } else {

                $bgColour = self::AMBER; // amber = on hold

            }
        } else {
            $bgColour = self::GREEN; /// green = active

        }
        return $bgColour;
    }

    /**
     * return list of user options for dropdown
     *
     * @param mixed $selectedID
     */
    function getAllocatedUserDropdown($problemID, $selectedID)
    {
        // user selection
        $userSelected = ($selectedID == 0) ? CT_SELECTED : '';

        $string .= '<option ' . $userSelected . ' value="&userID=0&problemID=' . $problemID . '"></option>';

        foreach ($this->allocatedUser as $key => $value) {

            $userSelected = ($selectedID == $value['userID']) ? CT_SELECTED : '';

            $string .= '<option ' . $userSelected . ' value="&userID=' . $value['userID'] . '&problemID=' . $problemID . '">' . $value['userName'] . '</option>';

        }

        return $string;

    }

    function truncate($reason, $length = 100)
    {
        return substr(common_stripEverything($reason), 0, $length);

    }

    function getProblemHistoryLink($problemID)
    {
        $url = $this->buildLink(
            'Activity.php',
            array(
                'action' => 'problemHistoryPopup',
                'problemID' => $problemID,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        return $url;

    }

    function getCustomerNameDisplayClass($specialAttentionFlag, $specialAttentionEndDate)
    {
        if (
            $specialAttentionFlag == 'Y' &&
            $specialAttentionEndDate >= date('Y-m-d')
        ) {
            $ret = 'specialAttentionCustomer';
        } else {
            $ret = 'content';
        }
        return $ret;
    }

    function deleteCustomerRequest()
    {
        $customerproblemno = $_REQUEST['cpr_customerproblemno'];

        $this->buActivity->deleteCustomerRaisedRequest($customerproblemno);
        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
        exit;

    }

    function escalate()
    {

        $problemID = $_REQUEST['problemID'];

        $this->buActivity->escalateProblemByProblemID($problemID);

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
        exit;
    }

    function deescalate()
    {

        $problemID = $_REQUEST['problemID'];

        $this->buActivity->deEscalateProblemByProblemID($problemID);

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
        exit;
    }
}// end of class
?>