<?php
/**
 * TeamPerformance Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUTeamPerformance.inc.php');
require_once($cfg ['path_bu'] . '/BUHeader.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTTeamPerformanceReport extends CTCNC
{

    public $buTeamPerformance;

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
            $roles = [
                "reports",
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
        }
        $this->buTeamPerformance = new BUTeamPerformance ($this);
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

        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $dsSearchForm = new DSForm ($this);

        $this->buTeamPerformance->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('TeamPerformanceReport' => 'TeamPerformanceReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {

                $this->template->set_var(
                    array(
                        'esTeamTargetSlaPercentage'  => $dsHeader->getValue(DBEJHeader::esTeamTargetSlaPercentage),
                        'esTeamTargetFixHours'       => $dsHeader->getValue(DBEJHeader::esTeamTargetFixHours),
                        'esTeamTargetFixQtyPerMonth' => $dsHeader->getValue(DBEJHeader::esTeamTargetFixQtyPerMonth),

                        'hdTeamTargetSlaPercentage'  => $dsHeader->getValue(DBEJHeader::hdTeamTargetSlaPercentage),
                        'hdTeamTargetFixHours'       => $dsHeader->getValue(DBEJHeader::hdTeamTargetFixHours),
                        'hdTeamTargetFixQtyPerMonth' => $dsHeader->getValue(DBEJHeader::hdTeamTargetFixQtyPerMonth),

                        'smallProjectsTeamTargetSlaPercentage'  => $dsHeader->getValue(DBEJHeader::smallProjectsTeamTargetSlaPercentage),
                        'smallProjectsTeamTargetFixHours'       => $dsHeader->getValue(DBEJHeader::smallProjectsTeamTargetFixHours),
                        'smallProjectsTeamTargetFixQtyPerMonth' => $dsHeader->getValue(DBEJHeader::smallProjectsTeamTargetFixQtyPerMonth)

                    )
                );

                /* Extract data and build report */
                $results = $this->buTeamPerformance->getRecordsByYear(
                    $dsSearchForm->getValue(BUTeamPerformance::searchFormYear)
                );

                foreach ($results as $result) {

                    if ($result['esTeamActualSlaPercentage'] < $result['esTeamTargetSlaPercentage']) {

                        $this->template->set_var(
                            'esTeamActualSlaPercentage' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if ($result['hdTeamActualSlaPercentage'] < $result['hdTeamTargetSlaPercentage']) {

                        $this->template->set_var(
                            'hdTeamActualSlaPercentage' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if ($result['imTeamActualSlaPercentage'] < $result['smallProjectsTeamTargetSlaPercentage']) {

                        $this->template->set_var(
                            'imTeamActualSlaPercentage' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if ($result['esTeamActualFixQtyPerMonth'] < $result['esTeamTargetFixQtyPerMonth']) {

                        $this->template->set_var(
                            'esTeamActualFixQtyPerMonth' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if ($result['hdTeamActualFixQtyPerMonth'] < $result['hdTeamTargetFixQtyPerMonth']) {

                        $this->template->set_var(
                            'hdTeamActualFixQtyPerMonth' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if ($result['imTeamActualFixQtyPerMonth'] < $result['smallProjectsTeamTargetFixQtyPerMonth']) {

                        $this->template->set_var(
                            'imTeamActualFixQtyPerMonth' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if ($result['esTeamActualFixHours'] > $result['esTeamTargetFixHours']) {

                        $this->template->set_var(
                            'esTeamActualFixHours' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }
                    if ($result['hdTeamActualFixHours'] > $result['hdTeamTargetFixHours']) {

                        $this->template->set_var(
                            'hdTeamActualFixHours' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if ($result['imTeamActualFixHours'] > $result['smallProjectsTeamTargetFixHours']) {

                        $this->template->set_var(
                            'imTeamActualFixHours' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    $this->template->set_var(
                        array(
                            'esTeamActualSlaPercentage' . $result['month']  => number_format(
                                $result['esTeamActualSlaPercentage'],
                                0
                            ),
                            'esTeamActualFixHours' . $result['month']       => $result['esTeamActualFixHours'],
                            'esTeamActualFixQtyPerMonth' . $result['month'] => $result['esTeamActualFixQtyPerMonth'],

                            'imTeamActualSlaPercentage' . $result['month']  => number_format(
                                $result['imTeamActualSlaPercentage'],
                                0
                            ),
                            'imTeamActualFixHours' . $result['month']       => $result['imTeamActualFixHours'],
                            'imTeamActualFixQtyPerMonth' . $result['month'] => $result['imTeamActualFixQtyPerMonth'],

                            'hdTeamActualSlaPercentage' . $result['month']  => number_format(
                                $result['hdTeamActualSlaPercentage'],
                                0
                            ),
                            'hdTeamActualFixHours' . $result['month']       => $result['hdTeamActualFixHours'],
                            'hdTeamActualFixQtyPerMonth' . $result['month'] => $result['hdTeamActualFixQtyPerMonth']
                        )
                    );

                }

            }

        }

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );

        $this->setPageTitle('Team Performance Report');

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'year'      => $dsSearchForm->getValue(BUTeamPerformance::searchFormYear),
                'urlSubmit' => $urlSubmit,
            )
        );

        $this->template->parse(
            'CONTENTS',
            'TeamPerformanceReport',
            true
        );
        $this->parsePage();
    }
}