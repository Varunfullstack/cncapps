<?php
/**
 * Questionnaire Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUQuestionnaire.inc.php');
require_once($cfg ['path_bu'] . '/BUQuestionnaireReport.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class CTQuestionnaireReport extends CTCNC
{

    var $dsSearchForm = '';
    public $buQuestionnaireReport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buQuestionnaireReport = new BUQuestionnaireReport($this);

        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn('questionnaireID', DA_STRING, DA_NOT_NULL);
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

            if ($_REQUEST['CSV']) {
                $csv = true;
            } else {
                $csv = false;
            }

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {

                $this->setFormErrorOn();

            } else {
                $this->buQuestionnaireReport->startDate = $this->dsSearchForm->getValue('fromDate');
                $this->buQuestionnaireReport->endDate = $this->dsSearchForm->getValue('toDate');
                $this->buQuestionnaireReport->questionnaireID = $this->dsSearchForm->getValue('questionnaireID');

                $report = $this->buQuestionnaireReport->getReport($csv);

                /*
                If this wasn't a CSV report then email the HTML report to the current user
                */
                if (!$csv) {
                    $buMail = new BUMail($this);

                    $senderEmail = CONFIG_SUPPORT_EMAIL;
                    $senderName = 'CNC Support Department';

                    $dbeUser = new DBEUser($this);
                    $loggedInUserID = $GLOBALS ['auth']->is_authenticated();
                    $dbeUser->getRow($loggedInUserID);
                    $toEmail = $dbeUser->getValue('username') . '@' . CONFIG_PUBLIC_DOMAIN;

                    $hdrs = array(
                        'From' => $senderEmail,
                        'To' => $toEmail,
                        'Subject' => 'Questionnaire Report ' . $this->buQuestionnaireReport->getPeriod() . ' - ' . $this->buQuestionnaireReport->getQuestionnaireDescription(),
                        'Date' => date("r"),
                        'Content-Type' => 'text/html; charset=UTF-8'
                    );

                    $buMail->mime->setHTMLBody($report);

                    $body = $buMail->mime->get();

                    $hdrs = $buMail->mime->headers($hdrs);

                    $buMail->putInQueue(
                        $senderEmail,
                        $toEmail,
                        $hdrs,
                        $body
                    );
                } // if !$csv

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

        if (!$csv) {
            $this->setTemplateFiles(
                array(
                    'QuestionnaireReportPage' => 'QuestionnaireReportPage.inc'
                )
            );

            $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

            $this->setPageTitle('Questionnaire Report');

            $this->template->set_var(
                array(
                    'formError' => $this->formError,
                    'questionnaireIDMessage' => $this->dsSearchForm->getMessage('questionnaireID'),
                    'fromDate' => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('fromDate')),
                    'fromDateMessage' => $this->dsSearchForm->getMessage('fromDate'),
                    'toDate' => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('toDate')),
                    'toDateMessage' => $this->dsSearchForm->getMessage('toDate'),
                    'urlSubmit' => $urlSubmit,
                    'report' => $report
                )
            );

            /*
            Questionnaire Drop-down
            */

            $buQuestionnaire = new BUQuestionnaire($this);
            $buQuestionnaire->getAll($dsQuestionnaire);

            $this->template->set_block('QuestionnaireReportPage', 'questionnaireBlock', 'questionnaires');
            while ($dsQuestionnaire->fetchNext()) {

                $this->template->set_var(
                    array(
                        'questionnaireDescription' => $dsQuestionnaire->getValue('description'),
                        'questionnaireID' => $dsQuestionnaire->getValue('questionnaireID'),
                        'questionnaireSelected' => ($this->dsSearchForm->getValue('questionnaireID') == $dsQuestionnaire->getValue('questionnaireID')) ? CT_SELECTED : ''
                    )
                );
                $this->template->parse('questionnaires', 'questionnaireBlock', true);
            }

            $this->template->parse('CONTENTS', 'QuestionnaireReportPage', true);

            $this->parsePage();
        } else {
            Header('Content-type: text/plain');
            Header('Content-Disposition: attachment; filename=questionnaire.csv');
            echo $report;
        }

    } // end function displaySearchForm

} // end of class
?>