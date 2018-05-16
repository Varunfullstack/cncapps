<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUEscalationReport.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTEscalationReport extends CTCNC
{
    private $dsPrintRange = '';
    private $dsSearchForm = '';
    private $buEscalationReport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "reports",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buEscalationReport = new BUEscalationReport($this);

        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsSearchForm->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->search();
    }

    function search()
    {

        $this->setMethodName('search');

        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {

                $this->setFormErrorOn();

            } else {
                $teamReport = $this->buEscalationReport->getTeamReport($this->dsSearchForm);
                $technicianReport = $this->buEscalationReport->getTechnicianReport($this->dsSearchForm);
            }

        }

        if ($this->dsSearchForm->getValue('fromDate') == '') {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue('fromDate', date('Y-m-d', strtotime("-1 month")));
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue('toDate')) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue('toDate', date('Y-m-d'));
            $this->dsSearchForm->post();
        }


        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'EscalationReport' => 'EscalationReport.inc'
            )
        );

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Escalation Report');

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'fromDate' => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('fromDate')),
                'fromDateMessage' => $this->dsSearchForm->getMessage('fromDate'),
                'toDate' => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('toDate')),
                'toDateMessage' => $this->dsSearchForm->getMessage('toDate'),
                'urlSubmit' => $urlSubmit,
                'teamReport' => $teamReport,
                'technicianReport' => $technicianReport
            )
        );

        $this->template->parse('CONTENTS', 'EscalationReport', true);

        $this->parsePage();

    } // end function displaySearchForm

} // end of class
?>