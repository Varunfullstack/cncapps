<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUEscalationReport.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTTeamAndUserStatistics extends CTCNC
{
    const searchFormFromDate = 'fromDate';
    const searchFormToDate = 'toDate';

    private $dsSearchForm = '';
    private $buEscalationReport;

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
            $cfg
        );

        if (!$this->isUserSDManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(207);
        $this->buEscalationReport = new BUEscalationReport($this);

        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn(
            self::searchFormFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsSearchForm->addColumn(
            self::searchFormToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->search();
    }

    /**
     * @throws Exception
     */
    function search()
    {

        $this->setMethodName('search');
        $teamReport = null;
        $technicianReport = null;
        if (isset ($_REQUEST ['searchForm']) == 'POST') {
            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $teamReport = $this->buEscalationReport->getTeamReport($this->dsSearchForm);
                $technicianReport = $this->buEscalationReport->getTechnicianReport($this->dsSearchForm);
            }
        }

        if ($this->dsSearchForm->getValue(self::searchFormFromDate) == '') {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                self::searchFormFromDate,
                date(
                    'Y-m-d',
                    strtotime("-1 month")
                )
            );
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue(self::searchFormToDate)) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                self::searchFormToDate,
                date('Y-m-d')
            );
            $this->dsSearchForm->post();
        }


        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'EscalationReport' => 'EscalationReport.inc'
            )
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );

        $this->setPageTitle('Escalation Report');

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'fromDate'         => $this->dsSearchForm->getValue(self::searchFormFromDate),
                'fromDateMessage'  => $this->dsSearchForm->getMessage(self::searchFormFromDate),
                'toDate'           => $this->dsSearchForm->getValue(self::searchFormToDate),
                'toDateMessage'    => $this->dsSearchForm->getMessage(self::searchFormToDate),
                'urlSubmit'        => $urlSubmit,
                'teamReport'       => $teamReport,
                'technicianReport' => $technicianReport
            )
        );

        $this->template->parse(
            'CONTENTS',
            'EscalationReport',
            true
        );

        $this->parsePage();

    } // end function displaySearchForm

}
