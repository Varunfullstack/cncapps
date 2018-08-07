<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 07/08/2018
 * Time: 9:42
 */

class CTFirstTimeFixReport extends CTCNC
{
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
    }

    public function defaultAction()
    {
        switch (@$_REQUEST['action']) {
            case  'search':
            default:
                $this->search();
        }
    }

    private function search()
    {

        $this->setTemplateFiles(
            array(
                'FirstTimeFixReport' => 'FirstTimeFixReport'
            )
        );


        $hdUsers = (new BUUser($this))->getUsersByTeamLevel(1);

        $this->template->set_block(
            'FirstTimeFixReport',
            'userBlock',
            'hdUsers'
        );

        foreach ($hdUsers as $user) {


            $this->template->set_var(
                array(
                    'userName' => $user['userName'],
                    'userId'   => $user['cns_consno']
                )
            );

            $this->template->parse(
                'hdUsers',
                'userBlock',
                true
            );
        }

        $urlSubmit = $this->buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );

        $this->setPageTitle('Escalation Report');

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'fromDate'         => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('fromDate')),
                'fromDateMessage'  => $this->dsSearchForm->getMessage('fromDate'),
                'toDate'           => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('toDate')),
                'toDateMessage'    => $this->dsSearchForm->getMessage('toDate'),
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
    }
}