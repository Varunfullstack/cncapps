<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * If the logged in user is NOT in the group Maintenance then they will ONLY see problems assigned to themselves
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUProblemSLA.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

require_once("Mail.php");
require_once("Mail/mime.php");

// Actions

class CTTestHours extends CTCNC
{
    var $buActivity = '';
    var $customerItem = '';
    var $ukBankHolidays = '';
    var $user = array();
    public $buCustomerItem;


    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buActivity = new BUActivity($this);
        $this->buCustomerItem = new BUCustomerItem($this);

        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();

        while ($dbeUser->fetchNext()) {
            $this->user[] =
                array(
                    'userID' => $dbeUser->getValue('userID'),
                    'name' => $dbeUser->getValue('name')
                );
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {


            default:
                $this->displayReport();
                break;
        }
    }

    /**
     * Display search form
     * @access private
     */
    function displayReport()
    {
        $this->setMethodName('displayReport');

        $this->setTemplateFiles('TestHours', 'TestHours.inc');

        $this->setPageTitle('Test Elapsed Hours Calculation');

        $this->buActivity->getProblemsByStatus('I', '', $dsResults); // initial status

        $this->template->set_block('TestHours', 'awaitingResponseBlock', 'responses');

        while ($dsResults->fetchNext()) {

            $minResponseHours = $this->buCustomerItem->getMinumumContractResponseHours($dsResults->getValue('customerID'));

            $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($dsResults->getValue('problemID'));

            $firstActivityID = (string)$dbeFirstActivity->getValue('callActivityID');

            if ($firstActivityID) {

                $urlCreateFollowon =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action' => 'createFollowOnActivity',
                            'callActivityID' => $firstActivityID
                        )
                    );

                $urlReasonPopup =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action' => 'reasonPopup',
                            'problemID' => $dsResults->getValue('problemID'),
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );

                $urlRejectAllocation =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'rejectAllocation',
                            'problemID' => $dsResults->getValue('problemID')
                        )
                    );

                $elapsedHours = $buProblemSLA->getWorkingHours(
                    $dsResults->getValue('problemID')
                );


                $bgColour = $this->getResponseColour($dsResults, $minResponseHours);

                $this->template->set_var(

                    array(
                        'aTime' => $dsResults->getValue('timeRaised'),
                        'aDate' => $dsResults->getValue('dateRaisedDMY'),
                        'aHoursElapsed' => $dsResults->getValue('hoursElapsed'),
                        'aReason' => $this->truncate($dbeFirstActivity->getValue('reason')),
                        'aFullHtmlReason' => $dbeFirstActivity->getValue('reason'),
                        'aMinResponseHours' => $minResponseHours,
                        'aElapsedHours' => $elapsedHours,
                        'aProblemID' => $dsResults->getValue('problemID'),
                        'aEngineerDropDown' => $this->getEngineerList($dsResults->getValue('problemID'),
                                                                      $dsResults->getValue('userID')),
                        'aCustomerName' => $dsResults->getValue('customerName'),
                        'aUrlCreateFollowon' => $urlCreateFollowon,
                        'aUrlRejectAllocation' => $urlRejectAllocation,
                        'aUrlReasonPopup' => $urlReasonPopup,
                        'aBgColour' => $bgColour
                    )

                );

                $this->template->parse('responses', 'awaitingResponseBlock', true);
            }
        }
        /*
    In Progress
        */
        $this->buActivity->getProblemsByStatus('P', $userID, $dsResults);

        $buProblemSLA = new BUProblemSLA($this);

        $this->template->set_block('TestHours', 'inProgressBlock', 'inProgress');

        while ($dsResults->fetchNext()) {

            $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($dsResults->getValue('problemID'));

            $firstActivityID = (string)$dbeFirstActivity->getValue('callActivityID');

            if ($firstActivityID) {

                $urlReasonPopup =
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
                            'action' => 'displayActivity',
                            'callActivityID' => $firstActivityID
                        )
                    );

                $elapsedHours = $buProblemSLA->getWorkingHours(
                    $dsResults->getValue('problemID')
                );

                $this->template->set_var(

                    array(
                        'pTime' => $dsResults->getValue('timeRaised'),
                        'pDate' => $dsResults->getValue('dateRaisedDMY'),
                        'pProblemID' => $dsResults->getValue('problemID'),
                        'pReason' => $this->truncate($dbeFirstActivity->getValue('reason')),
                        'pUrlReasonPopup' => $urlReasonPopup,
                        'pEngineerDropDown' => $this->getEngineerList($dsResults->getValue('problemID'),
                                                                      $dsResults->getValue('userID')),
                        'pElapsedHours' => $elapsedHours,
                        'pEngineerName' => $dsResults->getValue('engineerName'),
                        'pCustomerName' => $dsResults->getValue('customerName'),
                        'pUrlViewActivity' => $urlViewActivity,
                        'pBgColour' => $bgColour
                    )

                );

                $this->template->parse('inProgress', 'inProgressBlock', true);
            }

        }

        /*
          Fixed by CNC but not approved by customer yet
        */
        $this->buActivity->getProblemsByStatus('F', $userID, $dsResults);

        $this->template->set_block('TestHours', 'completedBlock', 'completed');

        while ($dsResults->fetchNext()) {

            $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($dsResults->getValue('problemID'));

            $firstActivityID = (string)$dbeFirstActivity->getValue('callActivityID');

            if ($firstActivityID) {

                $urlViewActivity =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action' => 'displayActivity',
                            'callActivityID' => $firstActivityID
                        )
                    );


                $this->template->set_var(

                    array(
                        'cTime' => $dsResults->getValue('timeRaised'),
                        'cDate' => $dsResults->getValue('dateRaisedDMY'),
                        'cProblemID' => $dsResults->getValue('problemID'),
                        'cReason' => $this->truncate($dbeFirstActivity->getValue('reason')),
                        'cCustomerName' => $dsResults->getValue('customerName'),
                        'cUrlViewActivity' => $urlViewActivity,
                        'cBgColour' => $bgColour
                    )

                );

                $this->template->parse('completed', 'completedBlock', true);
            }

        }

        $this->template->set_var(

            array(
                'javaScript' => $javascript
            )

        );

        $this->template->parse('CONTENTS', 'TestHours', true);

        $this->parsePage();

        /*
     * Future activities
            $this->template->set_block('TestHours','futureActivityBlock','futureActivities');

            while ($dbeFutureAction->fetchNext()){

                $this->buActivity->getActivityByID( $dbeFutureAction->getValue( 'callActivityID' ), $dsActivity );


                $urlViewActivity =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action'=> 'displayActivity',
                            'callActivityID'	=> $dbeFutureAction->getValue( 'callActivityID' )
                        )
                    );

                $this->template->set_var(

                    array(
                        'futureActivityDate'				=> Controller::dateYMDtoDMY( $dbeFutureAction->getValue( 'date' ) ),
                        'futureActivityCallActivityID'	=> $dbeFutureAction->getValue( 'callActivityID' ),
                        'futureActivityCustomerName'		=> $dsActivity->getValue( 'customerName' ),
                        'futureActivityType'		=> $dsActivity->getValue( 'activityType' ),
                        'futureActivityUrlViewActivity' => $urlViewActivity
                    )

                );

                $this->template->parse('futureActivities', 'futureActivityBlock', true);
            }


        */

    } // end function displayReport

    /**
     * Return the appropriate background colour for this problem
     *
     *
     * @param <type> $dsResult
     */
    function getResponseColour($dsResult)
    {
        /*test*/

        /*
       get the response time for this customer
        */

        if ($minResponseHours == 0) {      // best endeavours
            $bgColour = '';
        } else {


            if ($dsResult->getValue('hoursElapsed') < $minResponseHours) {

                $bgColour = '#BDF8BA'; /// green

            } elseif ($diffMins < 30) {

                $bgColour = '#FFF5B3';

            } else {
                $bgColour = '#F8A5B6';
            }

        }

        return $bgColour;
    }

    /**
     * return list of user options for dropdown
     *
     * @param mixed $selectedID
     */
    function getEngineerList($problemID, $selectedID)
    {
        $string = '<option value="&userID=&problemID=' . $problemID . '">Unallocated</option>';


        // user selection
        foreach ($this->user as $key => $value) {

            $userSelected = ($selectedID == $value['userID']) ? CT_SELECTED : '';

            $string .= '<option ' . $userSelected . ' value="&userID=' . $value['userID'] . '&problemID=' . $problemID . '">' . $value['name'] . '</option>';

        }

        return $string;

    }

    function truncate($reason, $length = 50)
    {
        if (strpos($reason, ".") !== FALSE) {
            $reason = substr($reason, 0, strpos($reason, "."));
        }
        // then at 50 chars
        return common_stripEverything(substr($reason, 0, 50));

    }

}// end of class
?>