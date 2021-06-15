<?php
/**
 * TeamPerformance Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUTeamPerformance.inc.php');
require_once($cfg ['path_bu'] . '/BUHeader.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTSLAPerformance extends CTCNC
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
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buTeamPerformance = new BUTeamPerformance ($this);
        $this->setMenuId(208);
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

        $this->setTemplateFiles(array('SLAPerformance' => 'SLAPerformance.inc'));
        $this->loadReactCSS('SpinnerHolderComponent.css');
        $this->loadReactScript('SpinnerHolderComponent.js');

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

                        'smallProjectsTeamTargetSlaPercentage'  => $dsHeader->getValue(
                            DBEJHeader::smallProjectsTeamTargetSlaPercentage
                        ),
                        'smallProjectsTeamTargetFixHours'       => $dsHeader->getValue(
                            DBEJHeader::smallProjectsTeamTargetFixHours
                        ),
                        'smallProjectsTeamTargetFixQtyPerMonth' => $dsHeader->getValue(
                            DBEJHeader::smallProjectsTeamTargetFixQtyPerMonth
                        )

                    )
                );

                /* Extract data and build report */
                $results = $this->buTeamPerformance->getRecordsByYear(
                    $dsSearchForm->getValue(BUTeamPerformance::searchFormYear)
                );

                foreach ($results as $result) {

                    if (round($result['esTeamActualSlaPercentage'], 1) < round(
                            $result['esTeamTargetSlaPercentage'],
                            1
                        )) {

                        $this->template->set_var(
                            'esTeamActualSlaPercentage' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if (round($result['hdTeamActualSlaPercentage'], 1) < round(
                            $result['hdTeamTargetSlaPercentage'],
                            1
                        )) {

                        $this->template->set_var(
                            'hdTeamActualSlaPercentage' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    if (round($result['imTeamActualSlaPercentage'], 1) < round(
                            $result['imTeamTargetSlaPercentage'],
                            1
                        )) {

                        $this->template->set_var(
                            'smallProjectsTeamActualSlaPercentage' . $result['month'] . 'Class',
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

                    if ($result['imTeamActualFixQtyPerMonth'] < $result['imTeamTargetFixQtyPerMonth']) {

                        $this->template->set_var(
                            'smallProjectsTeamActualFixQtyPerMonth' . $result['month'] . 'Class',
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

                    if ($result['imTeamActualFixHours'] > $result['imTeamTargetFixHours']) {

                        $this->template->set_var(
                            'smallProjectsTeamActualFixHours' . $result['month'] . 'Class',
                            'performance-warn'
                        );
                    }

                    $this->template->set_var(
                        array(
                            'esTeamActualSlaPercentage' . $result['month']  => number_format(
                                $result['esTeamActualSlaPercentage'],
                                1
                            ),
                            'esTeamActualFixHours' . $result['month']       => $result['esTeamActualFixHours'],
                            'esTeamActualFixQtyPerMonth' . $result['month'] => $result['esTeamActualFixQtyPerMonth'],

                            'smallProjectsTeamActualSlaPercentage' . $result['month']  => number_format(
                                $result['imTeamActualSlaPercentage'],
                                1
                            ),
                            'smallProjectsTeamActualFixHours' . $result['month']       => $result['imTeamActualFixHours'],
                            'smallProjectsTeamActualFixQtyPerMonth' . $result['month'] => $result['imTeamActualFixQtyPerMonth'],

                            'hdTeamActualSlaPercentage' . $result['month']  => number_format(
                                $result['hdTeamActualSlaPercentage'],
                                1
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

        $this->setPageTitle('SLA Performance Report');

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'year'      => $dsSearchForm->getValue(BUTeamPerformance::searchFormYear),
                'urlSubmit' => $urlSubmit,
            )
        );

        $this->template->parse(
            'CONTENTS',
            'SLAPerformance',
            true
        );
        $this->parsePage();
    }
}