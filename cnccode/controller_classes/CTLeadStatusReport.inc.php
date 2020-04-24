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
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');

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
        $this->buLeadStatus = new BULeadStatus($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        if ($this->action == 'getLeadData') {
            echo json_encode($this->getLeadData());
        }
        $this->displayReport();
    }

    private function getLeadData()
    {
        global $db;

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

        $this->template->parse('CONTENTS', 'LeadStatusReport', true);
        $this->parsePage();
    }
}// end of class
