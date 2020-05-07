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
            echo json_encode($this->getLeadData(), JSON_NUMERIC_CHECK);
            return;
        }
        $this->displayReport();
    }

    private function getLeadData()
    {
        global $db;
        $query = "select leadStatusId, name as leadStatusName, cus_name as customerName, cus_custno as customerId
from customerleadstatus 
         left join customer  on leadStatusId = customerLeadStatus.id where appearOnScreen order by leadStatusId, customerName";
        /** @var mysqli_result $result */
        $result = $db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
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
