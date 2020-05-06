<?php
/**
 * Incident Analysis Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUIncidentAnalysisReport.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTIncidentAnalysisReport extends CTCNC
{
    /** @var DSForm */
    public $dsSearchForm;
    /** @var DataSet */
    public $dsResults;
    /**@var bool|mysqli_result */
    public $results;
    /**@var BUIncidentAnalysisReport */
    public $buIncidentAnalysisReport;

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
        $this->buIncidentAnalysisReport = new BUIncidentAnalysisReport ($this);
        $this->dsSearchForm = new DSForm ($this);
        $this->dsResults = new DataSet ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case CTCNC_ACT_SEARCH :
                $this->search();
                break;
            default :
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function search()
    {

        $this->setMethodName('search');

        $this->buIncidentAnalysisReport->initialiseSearchForm($this->dsSearchForm);
        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
                $this->displaySearchForm();
                exit ();
            }

        }

        if (!$this->dsSearchForm->getValue(BUIncidentAnalysisReport::searchFormFromDate)) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                BUIncidentAnalysisReport::searchFormFromDate,
                date('Y-m-d', strtotime("-1 year"))
            );
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue(BUIncidentAnalysisReport::searchFormToDate)) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(BUIncidentAnalysisReport::searchFormToDate, date('Y-m-d'));
            $this->dsSearchForm->post();
        }

        $this->results = $this->buIncidentAnalysisReport->search($this->dsSearchForm);

        $this->displaySearchForm();
        exit ();
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global


        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(array('IncidentAnalysisReport' => 'IncidentAnalysisReport.inc'));

        $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Incident Analysis Report');

        if ($dsSearchForm->rowCount() == 0) {
            $this->buIncidentAnalysisReport->initialiseSearchForm($dsSearchForm);
        }
        /*
        Search Form
        */
        $this->template->set_var(
            array(
                'formError'       => $this->formError,
                'fromDate'        => $dsSearchForm->getValue(BUIncidentAnalysisReport::searchFormFromDate),
                'fromDateMessage' => $dsSearchForm->getMessage(BUIncidentAnalysisReport::searchFormFromDate),
                'toDate'          => $dsSearchForm->getValue(BUIncidentAnalysisReport::searchFormToDate),
                'toDateMessage'   => $dsSearchForm->getMessage(BUIncidentAnalysisReport::searchFormToDate),
                'urlSubmit'       => $urlSubmit,
            )
        );
        /*
        Results
        */
        if ($this->results) {

            $this->template->set_block(
                'IncidentAnalysisReport',
                'resultBlock',
                'results'
            );

            while ($row = $this->results->fetch_object()) {

                $this->template->set_var(
                    array(
                        'year'                 => $row->year,
                        'month'                => $row->month,
                        'incidentsTotalCount'
                                               => $row->incidentsTotalCount,
                        'activityTotalHours'   => $row->activityTotalHours,
                        'fixAverageHours'      => number_format($row->fixAverageHours, 2),
                        'responseAverageHours' => number_format($row->responseAverageHours, 2)
                    )
                );

                $this->template->parse('results', 'resultBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'IncidentAnalysisReport', true);

        $this->parsePage();
    }
}
