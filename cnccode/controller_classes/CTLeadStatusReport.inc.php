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
require_once($cfg['path_bu'] . '/BULeadStatus.inc.php');

// Actions
class CTLeadStatusReport extends CTCNC
{

    /**
     * @var BULeadStatus
     */
    public $buLeadStatus;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(402);
        $this->buLeadStatus = new BULeadStatus($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->displayReport();
    }

    /**
     * Display report
     * @access private
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles('LeadStatusReport', 'LeadStatusReport.inc');

        $this->setPageTitle('Lead Status');

        if ($this->getParam('orderHotAlpha')) {
            $this->setSessionParam('orderHotAlpha', $this->getParam('orderHotAlpha'));
        }
        if ($this->getParam('orderWarmAlpha')) {
            $this->setSessionParam('orderWarmAlpha', $this->getParam('orderWarmAlpha'));
        }
        if ($this->getParam('orderColdAlpha')) {
            $this->setSessionParam('orderColdAlpha', $this->getParam('orderColdAlpha'));
        }
        if ($this->getParam('orderDeadAlpha')) {
            $this->setSessionParam('orderDeadAlpha', $this->getParam('orderDeadAlpha'));
        }

        $becameCustomerArray = $this->buLeadStatus->getBecameCustomerCounts();

        $droppedCustomerArray = $this->buLeadStatus->getDroppedCustomerCounts();

        $this->template->set_block('LeadStatusReport', 'countBlock', 'counts');

        foreach ($becameCustomerArray as $year => $becameCount) {

            $droppedCount = $droppedCustomerArray[$year];


            $this->template->set_var(

                array(
                    'year'         => $year,
                    'becameCount'  => $becameCount,
                    'droppedCount' => $droppedCount
                )

            );

            $this->template->parse('counts', 'countBlock', true);

        }
        /*
        Hot leads
        */
        $hotLeads = $this->buLeadStatus->getLeadsByStatus(1, $this->getSessionParam('orderHotAlpha'));

        $this->template->set_block('LeadStatusReport', 'hotBlock', 'hots');

        while ($row = $hotLeads->fetch_array()) {

            $urlHot =

                Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $row['customerID']
                    )
                );

            $this->template->set_var(

                array(
                    'hotName' => $row['customerName'],
                    'urlHot'  => $urlHot
                )

            );

            $this->template->parse('hots', 'hotBlock', true);

        }
        /*
        Warm leads
        */
        $warmLeads = $this->buLeadStatus->getLeadsByStatus(2, $this->getSessionParam('orderWarmAlpha'));

        $this->template->set_block('LeadStatusReport', 'warmBlock', 'warms');

        while ($row = $warmLeads->fetch_array()) {

            $urlWarm =

                Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $row['customerID']
                    )
                );

            $this->template->set_var(

                array(
                    'warmName' => $row['customerName'],
                    'urlWarm'  => $urlWarm
                )

            );

            $this->template->parse('warms', 'warmBlock', true);

        }
        /*
        Cold leads
        */
        $coldLeads = $this->buLeadStatus->getLeadsByStatus(3, $this->getSessionParam('orderColdAlpha'));

        $this->template->set_block('LeadStatusReport', 'coldBlock', 'colds');

        while ($row = $coldLeads->fetch_array()) {

            $urlCold =

                Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $row['customerID']
                    )
                );

            $this->template->set_var(

                array(
                    'coldName' => $row['customerName'],
                    'urlCold'  => $urlCold
                )

            );

            $this->template->parse('colds', 'coldBlock', true);

        }


        /*
        Dead leads
        */
        $deadLeads = $this->buLeadStatus->getLeadsByStatus(4, $this->getSessionParam('orderDeadAlpha'));

        $this->template->set_block('LeadStatusReport', 'deadBlock', 'deads');

        while ($row = $deadLeads->fetch_array()) {

            $urlDead =

                Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $row['customerID']
                    )
                );

            $this->template->set_var(

                array(
                    'deadName' => $row['customerName'],
                    'urlDead'  => $urlDead
                )

            );

            $this->template->parse('deads', 'deadBlock', true);

        }

        $this->template->parse('CONTENTS', 'LeadStatusReport', true);
        $this->parsePage();

    }
}// end of class
