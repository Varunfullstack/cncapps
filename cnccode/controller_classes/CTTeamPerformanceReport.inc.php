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
     */
    function defaultAction()
    {
        $this->search();
    }

    function search()
    {

        $this->setMethodName('search');

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $dsSearchForm = new DSForm ($this);

        $dsResults = new DataSet ($this);

        $this->buTeamPerformance->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('TeamPerformanceReport' => 'TeamPerformanceReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {

                $this->template->set_var(
                    array(
                        'esTeamTargetSlaPercentage'  => $dsHeader->getValue('esTeamTargetSlaPercentage'),
                        'esTeamTargetFixHours'       => $dsHeader->getValue('esTeamTargetFixHours'),
                        'esTeamTargetFixQtyPerMonth' => $dsHeader->getValue('esTeamTargetFixQtyPerMonth'),

                        'hdTeamTargetSlaPercentage'  => $dsHeader->getValue('hdTeamTargetSlaPercentage'),
                        'hdTeamTargetFixHours'       => $dsHeader->getValue('hdTeamTargetFixHours'),
                        'hdTeamTargetFixQtyPerMonth' => $dsHeader->getValue('hdTeamTargetFixQtyPerMonth'),

                        'imTeamTargetSlaPercentage'  => $dsHeader->getValue('imTeamTargetSlaPercentage'),
                        'imTeamTargetFixHours'       => $dsHeader->getValue('imTeamTargetFixHours'),
                        'imTeamTargetFixQtyPerMonth' => $dsHeader->getValue('imTeamTargetFixQtyPerMonth')

                    )
                );

                /* Extract data and build report */
                $results = $this->buTeamPerformance->getRecordsByYear($dsSearchForm->getValue('year'));

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

                    if ($result['imTeamActualSlaPercentage'] < $result['imTeamTargetSlaPercentage']) {

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

                    if ($result['imTeamActualFixQtyPerMonth'] < $result['imTeamTargetFixQtyPerMonth']) {

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

                    if ($result['imTeamActualFixHours'] > $result['imTeamTargetFixHours']) {

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
                'year'      => $dsSearchForm->getValue('year'),
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

} // end of class
?>