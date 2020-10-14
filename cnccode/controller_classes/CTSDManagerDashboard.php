<?php
global $cfg;
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
        if ($this->getParam('showP5')) {
            $this->setMenuId(201);
        } else {
            $this->setMenuId(222);
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'allocateUser':
                $options = [];


                if ($this->getSessionParam('HD')) {
                    $options['HD'] = true;
                }
                if ($this->getSessionParam('ES')) {
                    $options['ES'] = true;
                }
                if ($this->getSessionParam('SP')) {
                    $options['SP'] = true;
                }
                if ($this->getSessionParam('P')) {
                    $options['P'] = true;
                }
                if ($this->getSessionParam('showP5')) {
                    $options['showP5'] = true;
                }

                $this->allocateUser($options);
                break;
            default:
                $this->display();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function display()
    {

        $isP5 = isset($_REQUEST['showP5']);
        $showHelpDesk = isset($_REQUEST['HD']);
        $showEscalation = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects = isset($_REQUEST['P']);
        $this->setSessionParam('HD', $showHelpDesk);
        $this->setSessionParam('ES', $showEscalation);
        $this->setSessionParam('SP', $showSmallProjects);
        $this->setSessionParam('P', $showProjects);
        $this->setSessionParam('showP5', $isP5);

        $this->setPageTitle('SD Manager Dashboard' . ($isP5 ? ' Priority 5' : null));

        $this->setTemplateFiles(
            array('SDManagerDashboard' => 'SDManagerDashboard')
        );

        $openSrsByUser = $this->buActivity->getOpenSrsByUser();

        $this->template->set_block(
            'SDManagerDashboard',
            'userSrCountBlock',
            'userSrCount'
        );

        foreach ($openSrsByUser as $row) {

            $this->template->set_var(

                array(
                    'openSrInitials' => $row['initials'],
                    'openSrCount'    => $row['count']
                )
            );

            $this->template->parse(
                'userSrCount',
                'userSrCountBlock',
                true
            );

        }

        $buProblem = new BUActivity($this);
        $problems = new DataSet($this);
        $limit = 10;
        $buProblem->getSDDashBoardData(
            $problems,
            $limit,
            'shortestSLARemaining',
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );
        $shortestSLARemaining = $this->renderQueue(
            $problems,
            'Shortest_SLA_Remaining'
        );

        $buProblem->getSDDashBoardData(
            $problems,
            $limit,
            'currentOpenSRs',
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );
        $currentOpenSRs = $this->renderQueue(
            $problems,
            'Current_Open_SRs'
        );
        $shortestSLAFixRemaining = null;
        if (!$isP5) {
            $buProblem->getSDDashBoardData(
                $problems,
                $limit,
                'shortestSLAFixRemaining',
                false,
                $showHelpDesk,
                $showEscalation,
                $showSmallProjects,
                $showProjects
            );
            $shortestSLAFixRemaining = $this->renderQueue(
                $problems,
                'Shortest_SLA_Fix_Remaining'
            );
        }


        $buProblem->getSDDashBoardData(
            $problems,
            $limit,
            'critical',
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );

        $criticalSR = $this->renderQueue(
            $problems,
            'Critical_Service_Requests'
        );

        $buProblem->getSDDashBoardData(
            $problems,
            $limit,
            'currentOpenP1Requests',
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );

        $currentOpenP1Requests = $this->renderQueue(
            $problems,
            'Current_Open_P1_Requests'
        );

        $buProblem->getSDDashBoardData(
            $problems,
            5,
            'oldestUpdatedSR',
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );

        $oldestUpdatedSR = $this->renderQueue(
            $problems,
            'Oldest_Updated_SRs'
        );

        $buProblem->getSDDashBoardData(
            $problems,
            5,
            'longestOpenSR',
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );

        $longestOpenSR = $this->renderQueue(
            $problems,
            'Longest_Open_SR'
        );

        $buProblem->getSDDashBoardData(
            $problems,
            5,
            'mostHoursLogged',
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );

        $mostHoursLogged = $this->renderQueue(
            $problems,
            'Most_Hours_Logged'
        );


        $dbeHeader = new DBEHeader($this);

        $dbeHeader->getRow(1);

        $engineersMaxCount = $dbeHeader->getValue(DBEHeader::SDDashboardEngineersInSREngineersMaxCount);
        $pastHours = $dbeHeader->getValue(DBEHeader::SDDashboardEngineersInSRInPastHours);

        $buProblem->getSDDashBoardEngineersInSRData(
            $problems,
            $engineersMaxCount,
            $pastHours,
            5,
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );


        $activitiesByXXEngineersInXXHours = $this->renderQueue(
            $problems,
            'Activities_By_XX_Engineers_In_XX_Hours',
            "Activities By $engineersMaxCount or more engineers in $pastHours Hours"
        );

        $this->renderOpenSRByCustomer(
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );

        $this->template->setVar(
            [
                "helpDeskChecked"                  => $showHelpDesk ? "checked" : null,
                "escalationChecked"                => $showEscalation ? "checked" : null,
                "smallProjectsChecked"             => $showSmallProjects ? "checked" : null,
                "projectsChecked"                  => $showProjects ? "checked" : null,
                "shortestSLARemaining"             => $shortestSLARemaining,
                "currentOpenSRs"                   => $currentOpenSRs,
                "currentOpenP1Requests"            => $currentOpenP1Requests,
                "oldestUpdatedSR"                  => $oldestUpdatedSR,
                "longestOpenSR"                    => $longestOpenSR,
                "mostHoursLogged"                  => $mostHoursLogged,
                "activitiesByXXEngineersInXXHours" => $activitiesByXXEngineersInXXHours,
                "criticalServiceRequests"          => $criticalSR,
                'shortestSLAFixRemaining'          => $shortestSLAFixRemaining
            ]
        );

        $this->template->parse(
            'CONTENTS',
            'SDManagerDashboard',
            true
        );
        $this->parsePage();
    }


    /**
     * @param DataSet $problems
     * @param $name
     * @param null $title
     * @return mixed|void|null
     * @throws Exception
     */
    private function renderQueue(DataSet $problems,
                                 $name,
                                 $title = null
    )
    {

        global $cfg;
        $rowCount = 0;

        if (!$title) {
            $title = $this->humanize($name);
        }
        $templateName = 'SDManager' . str_replace(
                '_',
                '',
                $name
            ) . 'Section.html';

        $template = new Template (
            $cfg["path_templates"],
            "remove"
        );

        $template->set_file(
            'page',
            $templateName
        );
        $blockName = 'queue' . $name . 'Block';
        $template->set_block(
            'page',
            $blockName,
            'requests' . $name
        );

        if (!$problems->rowCount()) {
            return null;
        }

        while ($problems->fetchNext()) {
            $rowCount++;
            $urlViewActivity = Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'displayLastActivity',
                    'problemID' => $problems->getValue(DBEJProblem::problemID)
                )
            );

            $buActivity = new BUActivity($this);

            $urlAllocateAdditionalTime =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'allocateAdditionalTime',
                        'problemID' => $problems->getValue(DBEJProblem::problemID)
                    )
                );

            $linkAllocateAdditionalTime = '<a href="' . $urlAllocateAdditionalTime . ' " target="_blank" title="Allocate additional time"><img src="/images/clock.png" width="20px" alt="time">';

            $activityCount = $buActivity->getActivityCount($problems->getValue(DBEJProblem::problemID));

            $bgColour = $this->getResponseColour(
                $problems->getValue(DBEJProblem::status),
                $problems->getValue(DBEJProblem::priority),
                $problems->getValue(DBEJProblem::slaResponseHours),
                $problems->getValue(DBEJProblem::workingHours),
                $problems->getValue(DBEJProblem::respondedHours)
            );
            /*
            Updated by another user?
            */
            if (
                $problems->getValue(DBEJProblem::userID) &&
                $problems->getValue(DBEJProblem::userID) != $problems->getValue(DBEJProblem::lastUserID)
            ) {
                $updatedBgColor = self::PURPLE;
            } else {
                $updatedBgColor = self::CONTENT;
            }

            if ($problems->getValue(DBEJProblem::respondedHours) == 0 && $problems->getValue(
                    DBEJProblem::status
                ) == 'I') {
                /*
                Initial SRs that have not yet been responded to
                */
                $hoursRemainingBgColor = self::AMBER;
            } elseif ($problems->getValue(DBEJProblem::awaitingCustomerResponseFlag) == 'Y') {
                $hoursRemainingBgColor = self::GREEN;
            } else {
                $hoursRemainingBgColor = self::BLUE;
            }
            /* ------------------------------ */

            $urlCustomer =
                Controller::buildLink(
                    'SalesOrder.php',
                    array(
                        'action'     => 'search',
                        'customerID' => $problems->getValue(DBEJProblem::customerID)
                    )
                );

            $alarmDateTimeDisplay = null;
            if ($problems->getValue(DBEProblem::alarmDate)) {

                $alarmDateTimeDisplay = Controller::dateYMDtoDMY(
                        $problems->getValue(DBEJProblem::alarmDate)
                    ) . ' ' . $problems->getValue(DBEJProblem::alarmTime);

                /*
                Has an alarm date that is in the past, set updated BG Colour (indicates moved back into work queue from future queue)
                */
                if ($problems->getValue(DBEJProblem::alarmDate) <= date(DATE_MYSQL_DATE)) {
                    $updatedBgColor = self::PURPLE;
                }

            }
            /*
            If the dashboard is filtered by customer then the Work button opens
            Activity edit
            */
            if (
                $problems->getValue(DBEJProblem::lastCallActTypeID) == 0
            ) {
                $workBgColor = self::GREEN; // green = in progress
            } else {
                $workBgColor = self::CONTENT;
            }

            if ($problems->getValue(DBEJProblem::priority) == 1) {
                $priorityBgColor = self::ORANGE;
            } else {
                $priorityBgColor = self::CONTENT;
            }


            $problemID = $problems->getValue(DBEJProblem::problemID);
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->setValue(
                DBEProblem::problemID,
                $problemID
            );
            $dbeProblem->getRow();

            $totalActivityDurationHours = $problems->getValue(DBEJProblem::totalActivityDurationHours);
            $template->set_var(
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
                    'time'                       => $problems->getValue(DBEJProblem::lastStartTime),
                    'date'                       => Controller::dateYMDtoDMY(
                        $problems->getValue(DBEJProblem::lastDate)
                    ),
                    'problemID'                  => $problems->getValue(DBEJProblem::problemID),
                    'reason'                     => self::truncate(
                        $problems->getValue(DBEJProblem::reason),
                        150
                    ),
                    'urlProblemHistoryPopup'     => $this->getProblemHistoryLink(
                        $problems->getValue(DBEJProblem::problemID)
                    ),
                    'engineerDropDown'           => $this->getAllocatedUserDropdown(
                        $problems->getValue(DBEJProblem::problemID),
                        $problems->getValue(DBEJProblem::userID)
                    ),
                    'engineerName'               => $problems->getValue(DBEJProblem::engineerName),
                    'customerName'               => $problems->getValue(DBEJProblem::customerName),
                    'customerNameDisplayClass'
                                                 => $this->getCustomerNameDisplayClass(
                        $problems->getValue(DBEJProblem::specialAttentionFlag),
                        $problems->getValue(DBEJProblem::specialAttentionEndDate),
                        $problems->getValue(DBEJProblem::specialAttentionContactFlag)
                    ),
                    'urlViewActivity'            => $urlViewActivity,
                    'slaResponseHours'           => number_format(
                        $problems->getValue(DBEJProblem::slaResponseHours),
                        1
                    ),
                    'priority'                   => Controller::htmlDisplayText(
                        $problems->getValue(DBEJProblem::priority)
                    ),
                    'alarmDateTime'              => $alarmDateTimeDisplay,
                    'bgColour'                   => $bgColour,
                    'workBgColor'                => $workBgColor,
                    'activityCount'              => $activityCount,
                    'allocateAdditionalTimeLink' => $linkAllocateAdditionalTime

                )

            );

            $template->parse(
                'requests' . $name,
                $blockName,
                true
            );


        } // end while


        $template->set_var(
            array(
                'queueCount' => $rowCount,
                'queueName'  => $title,

            )
        );

        $template->parse(
            'OUTPUT',
            'page'
        );
        return $template->getVar('OUTPUT');

    } // end render queue

    function humanize($string)
    {
        return str_replace(
            '_',
            ' ',
            $string
        );
    }

    /**
     * @param bool $showHelpDesk
     * @param bool $showEscalation
     * @param bool $showSmallProjects
     * @param bool $showProjects
     * @throws Exception
     */
    private function renderOpenSRByCustomer($showHelpDesk = true,
                                            $showEscalation = true,
                                            $showSmallProjects = true,
                                            $showProjects = true
    )
    {
        $rowCount = 0;

        $blockName = 'OpenSRByCustomerBlock';

        $this->template->set_block(
            'SDManagerDashboard',
            $blockName,
            'requests' . $blockName
        );

        global $db;

        $query = 'SELECT 
              cus_custno,
              cus_name,
              (SELECT 
                COUNT(pro_problemno) 
              FROM
                problem 
              WHERE problem.`pro_custno` = customer.`cus_custno` 
                AND problem.`pro_status` IN ("I", "P") ';

        if (!$showHelpDesk) {
            $query .= ' and pro_queue_no <> 1 ';
        }

        if (!$showEscalation) {
            $query .= ' and pro_queue_no <> 2 ';
        }

        if (!$showSmallProjects) {
            $query .= ' and pro_queue_no <> 3 ';
        }

        if (!$showProjects) {
            $query .= ' and pro_queue_no <> 5 ';
        }

        $query .= " ) openSRCount 
            FROM
              customer WHERE cus_custno <> 282 ORDER BY openSRCount DESC LIMIT 10";

        /** @var mysqli_result $result */
        $result = $db->query($query);

        while ($row = $result->fetch_assoc()) {

            $rowCount++;

            $urlCustomer = Controller::buildLink(
                'SalesOrder.php',
                array(
                    'action'     => 'search',
                    'customerID' => $row['cus_custno']
                )
            );

            $this->template->set_var(

                array(
                    'urlCustomer'  => $urlCustomer,
                    'customerName' => $row['cus_name'],
                    'srCount'      => "<A href='CurrentActivityReport.php?action=setFilter&selectedCustomerID=" . $row['cus_custno'] . "'>" . $row["openSRCount"] . "</A>"
                )

            );

            $this->template->parse(
                'requests' . $blockName,
                $blockName,
                true
            );
        }
    }
}
