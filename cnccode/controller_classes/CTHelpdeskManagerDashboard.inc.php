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
require_once($cfg['path_bu'] . '/BUHelpdeskManagerDashboard.inc.php');

// Actions
class CTHelpdeskManagerDashboard extends CTCNC
{
    var $buHelpdeskManagerDashboard = '';

    const AMBER = '#FFF5B3';
    const RED = '#F8A5B6';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buHelpdeskManagerDashboard = new BUHelpdeskManagerDashboard($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {

            default:
                $this->displayReport();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');

        unset($userArray);

        $this->setTemplateFiles('HelpdeskManagerDashboard', 'HelpdeskManagerDashboard.inc');

        $this->setPageTitle('Helpdesk Manager Dashboard');

        $priority1WithinSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'priority'      => '1',
                    'notFixed'      => 'Y',
                    'withinSla'     => 'Y'
                )
            );


        $priority1ApproachingSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract'  => 'Y',
                    'priority'       => '1',
                    'notFixed'       => 'Y',
                    'approachingSla' => 'Y'
                )
            );
        $priority1ExceededSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'priority'      => '1',
                    'notFixed'      => 'Y',
                    'exceededSla'   => 'Y'
                )
            );
        $priority2WithinSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'priority'      => '2',
                    'notFixed'      => 'Y',
                    'withinSla'     => 'Y'
                )
            );

        $priority2ApproachingSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract'  => 'Y',
                    'priority'       => '2',
                    'notFixed'       => 'Y',
                    'approachingSla' => 'Y'
                )
            );
        $priority2ExceededSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'priority'      => '2',
                    'notFixed'      => 'Y',
                    'exceededSla'   => 'Y'
                )
            );
        $priority3WithinSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'priority'      => '3',
                    'notFixed'      => 'Y',
                    'withinSla'     => 'Y'
                )
            );

        $priority3ApproachingSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract'  => 'Y',
                    'priority'       => '3',
                    'notFixed'       => 'Y',
                    'approachingSla' => 'Y'
                )
            );
        $priority3ExceededSla =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'priority'      => '3',
                    'notFixed'      => 'Y',
                    'exceededSla'   => 'Y'
                )
            );
        $priority4 =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'priority'      => '4',
                    'notFixed'      => 'Y'
                )
            );
        $tAndM =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'N',
                    'notFixed'      => 'Y'
                )
            );

        $this->buHelpdeskManagerDashboard->getCountIncidents(
            array(
                'inLastMonth' => 'Y'
            )
        );

        $incidentCountSlaMtd =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'inLastMonth'   => 'Y',
                    'notPriority4'  => 'Y'
                )
            );

        $fixedIncidentCountSlaMtd =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract' => 'Y',
                    'inLastMonth'   => 'Y',
                    'fixed'         => 'Y',
                    'notPriority4'  => 'Y'

                )
            );

        $totalFixedHoursSlaMtd =
            $this->buHelpdeskManagerDashboard->getTotalFixTime(
                array(
                    'underContract' => 'Y',
                    'fixed'         => 'Y',
                    'inLastMonth'   => 'Y',
                    'notPriority4'  => 'Y'
                )
            );


        $respondedWithinSlaMtd =
            $this->buHelpdeskManagerDashboard->getCountIncidents(
                array(
                    'underContract'      => 'Y',
                    'respondedWithinSla' => 'Y',
                    'inLastMonth'        => 'Y',
                    'notPriority4'       => 'Y'
                )
            );

        $totalResponseHoursMtd =
            $this->buHelpdeskManagerDashboard->getTotalResponseTime(
                array(
                    'underContract' => 'Y',
                    'inLastMonth'   => 'Y',
                    'notPriority4'  => 'Y'
                )

            );

        $aveResponseHoursMtd = $totalResponseHoursMtd / $incidentCountSlaMtd->count;

        $aveFixHoursMtd = $totalFixedHoursSlaMtd / $fixedIncidentCountSlaMtd->count;

        $respondedWithinSlaMtdPerc = $respondedWithinSlaMtd->count / $incidentCountSlaMtd->count * 100;

        $urlPriority1WithinSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority1WithinSla->idList
                )
            );
        $urlPriority2WithinSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority2WithinSla->idList
                )
            );
        $urlPriority3WithinSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority3WithinSla->idList
                )
            );
        $urlPriority1ExceededSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority1ExceededSla->idList
                )
            );
        $urlPriority2ExceededSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority2ExceededSla->idList
                )
            );
        $urlPriority3ExceededSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority3ExceededSla->idList
                )
            );
        $urlPriority1ApproachingSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority1ApproachingSla->idList
                )
            );
        $urlPriority2ApproachingSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority2ApproachingSla->idList
                )
            );
        $urlPriority3ApproachingSla =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority3ApproachingSla->idList
                )
            );
        $urlPriority4 =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $priority4->idList
                )
            );
        $urlTAndM =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'                     => 'search',
                    'activity[1][callActTypeID]' => CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                    'activity[1][problemID]'     => $tAndM->idList
                )
            );


        $this->template->set_var(

            array(
                'priority1WithinSla'    => $priority1WithinSla->count,
                'urlPriority1WithinSla' => $urlPriority1WithinSla,
                'priority2WithinSla'    => $priority2WithinSla->count,
                'urlPriority2WithinSla' => $urlPriority2WithinSla,
                'priority3WithinSla'    => $priority3WithinSla->count,
                'urlPriority3WithinSla' => $urlPriority3WithinSla,

                'priority1ApproachingSla'    => $priority1ApproachingSla->count,
                'urlPriority1ApproachingSla' => $urlPriority1ApproachingSla,
                'priority2ApproachingSla'    => $priority2ApproachingSla->count,
                'urlPriority2ApproachingSla' => $urlPriority2ApproachingSla,
                'priority3ApproachingSla'    => $priority3ApproachingSla->count,
                'urlPriority3ApproachingSla' => $urlPriority3ApproachingSla,
                'priority1ExceededSla'       => $priority1ExceededSla->count,
                'urlPriority1ExceededSla'    => $urlPriority1ExceededSla,
                'priority2ExceededSla'       => $priority2ExceededSla->count,
                'urlPriority2ExceededSla'    => $urlPriority2ExceededSla,
                'priority3ExceededSla'       => $priority3ExceededSla->count,
                'urlPriority3ExceededSla'    => $urlPriority3ExceededSla,
                'priority4'                  => $priority4->count,
                'urlPriority4'               => $urlPriority4,
                'tAndM'                      => $tAndM->count,
                'urlTAndM'                   => $urlTAndM,
                'incidentCountSlaMtd'        => $incidentCountSlaMtd->count,
                'respondedWithinSlaMtdPerc'  => number_format($respondedWithinSlaMtdPerc, 2),
                'aveResponseHoursMtd'        => number_format($aveResponseHoursMtd, 2),
                'aveFixHoursMtd'             => number_format($aveFixHoursMtd, 2)
            )

        );

        $this->template->parse('CONTENTS', 'HelpdeskManagerDashboard', true);
        $this->parsePage();


    }
}// end of class
