<?php
require_once($cfg['path_ct'] . '/CTCurrentActivityReport.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTSDManagerDashboard extends CTCurrentActivityReport
{
    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg,
            false
        );
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case 'allocateUser':
                $this->allocateUser();
                break;
            default:
                $this->display();
                break;
        }
    }

    function display()
    {

        $this->setPageTitle('SD Manager Dashboard');

        $this->setTemplateFiles(
            array('SDManagerDashboard' => 'SDManagerDashboard')
        );

        $buProblem = new BUActivity($this);
        $problems = new DataSet($this);
        $limit = 10;
        $buProblem->getSDDashBoardData(
            $problems,
            $limit,
            'shortestSLARemaining'
        );

        $this->renderQueue(
            $problems,
            'Shortest_SLA_Remaining'
        );

        $buProblem->getSDDashBoardData(
        $problems,
        5,
        'oldestUpdatedSR'
    );

        $this->renderQueue(
            $problems,
            'Oldest_Updated_SRs'
        );

        $buProblem->getSDDashBoardData(
            $problems,
            5,
            'mostHoursLogged'
        );

        $this->renderQueue(
            $problems,
            'Most_Hours_Logged'
        );

        $this->template->parse(
            'CONTENTS',
            'SDManagerDashboard',
            true
        );
        $this->parsePage();
    }


    private function renderQueue($problems,
                                 $name
    )
    {
        $rowCount = 0;

        $blockName = 'queue' . $name . 'Block';

        $this->template->set_block(
            'SDManagerDashboard',
            $blockName,
            'requests' . $name
        );

        while ($problems->fetchNext()) {

            $rowCount++;
            $urlViewActivity =
                $this->buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'displayLastActivity',
                        'problemID' => $problems->getValue('problemID')
                    )
                );

            $buActivity = new BUActivity($this);

            $activityCount = $buActivity->getActivityCount($problems->getValue('problemID'));

            $bgColour = $this->getResponseColour(
                $problems->getValue('status'),
                $problems->getValue('priority'),
                $problems->getValue('slaResponseHours'),
                $problems->getValue('workingHours'),
                $problems->getValue('respondedHours')
            );
            /*
            Updated by another user?
            */
            if (
                $problems->getValue('userID') &&
                $problems->getValue('userID') != $problems->getValue('lastUserID')
            ) {
                $updatedBgColor = self::PURPLE;
            } else {
                $updatedBgColor = self::CONTENT;
            }

            if ($problems->getValue('respondedHours') == 0 && $problems->getValue('status') == 'I') {
                /*
                Initial SRs that have not yet been responded to
                */
                $hoursRemainingBgColor = self::AMBER;
            } elseif ($problems->getValue('awaitingCustomerResponseFlag') == 'Y') {
                $hoursRemainingBgColor = self::GREEN;
            } else {
                $hoursRemainingBgColor = self::BLUE;
            }
            /* ------------------------------ */

            $urlCustomer =
                $this->buildLink(
                    'SalesOrder.php',
                    array(
                        'action'     => 'search',
                        'customerID' => $problems->getValue('customerID')
                    )
                );

            if ($problems->getValue('alarmDate') && $problems->getValue('alarmDate') != '0000-00-00') {

                $alarmDateTimeDisplay = Controller::dateYMDtoDMY(
                        $problems->getValue('alarmDate')
                    ) . ' ' . $problems->getValue('alarmTime');

                /*
                Has an alarm date that is in the past, set updated BG Colour (indicates moved back into work queue from future queue)
                */
                if ($problems->getValue('alarmDate') <= date(CONFIG_MYSQL_DATE)) {
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
                $problems->getValue('lastCallActTypeID') == 0
            ) {
                $workBgColor = self::GREEN; // green = in progress
            } else {
                $workBgColor = self::CONTENT;
            }

            if ($problems->getValue('priority') == 1) {
                $priorityBgColor = self::ORANGE;
            } else {
                $priorityBgColor = self::CONTENT;
            }


            $problemID = $problems->getValue('problemID');
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->setValue(
                DBEProblem::problemID,
                $problemID
            );
            $dbeProblem->getRow();

            $totalActivityDurationHours = $problems->getValue('totalActivityDurationHours');
            $this->template->set_var(

                array(
                    'hoursRemaining'             => number_format(
                        $problems->getValue(DBEJProblem::hoursRemaining),
                        1
                    ),
                    'updatedBgColor'             => $updatedBgColor,
                    'priorityBgColor'            => $priorityBgColor,
                    'hoursRemainingBgColor'      => $hoursRemainingBgColor,
                    'totalActivityDurationHours' => $totalActivityDurationHours,
                    'urlCustomer'                => $urlCustomer,
                    'time'                       => $problems->getValue('lastStartTime'),
                    'date'                       => Controller::dateYMDtoDMY($problems->getValue('lastDate')),
                    'problemID'                  => $problems->getValue('problemID'),
                    'reason'                     => self::truncate(
                        $problems->getValue('reason'),
                        150
                    ),
                    'urlProblemHistoryPopup'     => $this->getProblemHistoryLink($problems->getValue('problemID')),
                    'engineerDropDown'           => $this->getAllocatedUserDropdown(
                        $problems->getValue('problemID'),
                        $problems->getValue('userID')
                    ),
                    'engineerName'               => $problems->getValue('engineerName'),
                    'customerName'               => $problems->getValue('customerName'),
                    'customerNameDisplayClass'
                                                 => $this->getCustomerNameDisplayClass(
                        $problems->getValue('specialAttentionFlag'),
                        $problems->getValue('specialAttentionEndDate')
                    ),
                    'urlViewActivity'            => $urlViewActivity,
                    'slaResponseHours'           => number_format(
                        $problems->getValue('slaResponseHours'),
                        1
                    ),
                    'priority'                   => Controller::htmlDisplayText($problems->getValue('priority')),
                    'alarmDateTime'              => $alarmDateTimeDisplay,
                    'bgColour'                   => $bgColour,
                    'workBgColor'                => $workBgColor,
                    'activityCount'              => $activityCount

                )

            );

            $this->template->parse(
                'requests' . $name,
                $blockName,
                true
            );


        } // end while


        $this->template->set_var(
            array(
                'queue' . $name . 'Count' => $rowCount,
                'queue' . $name . 'Name'  => $this->humanize($name),

            )
        );
    } // end render queue

    function humanize($string)
    {
        return str_replace(
            '_',
            ' ',
            $string
        );
    }
}// end of class
?>