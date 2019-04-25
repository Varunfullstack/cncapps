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
    const searchFormQuestionnaireID = "questionnaireID";
    const searchFormFromDate = "fromDate";
    const searchFormToDate = "toDate";

    /** @var DSForm */
    public $dsSearchForm;
    /** @var BUQuestionnaireReport */
    public $buQuestionnaireReport;

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
        $this->buQuestionnaireReport = new BUQuestionnaireReport($this);

        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn(self::searchFormQuestionnaireID, DA_STRING, DA_NOT_NULL);
        $this->dsSearchForm->addColumn(self::searchFormFromDate, DA_DATE, DA_ALLOW_NULL);
        $this->dsSearchForm->addColumn(self::searchFormToDate, DA_DATE, DA_ALLOW_NULL);
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
        $csv = false;
        $report = null;
        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if ($this->getParam('CSV')) {
                $csv = true;
            }

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {

                $this->setFormErrorOn();

            } else {
                $this->buQuestionnaireReport->startDate = $this->dsSearchForm->getValue(self::searchFormFromDate);
                $this->buQuestionnaireReport->endDate = $this->dsSearchForm->getValue(self::searchFormToDate);
                $this->buQuestionnaireReport->questionnaireID = $this->dsSearchForm->getValue(
                    self::searchFormQuestionnaireID
                );

                $report = $this->buQuestionnaireReport->getReport($csv);

                /*
                If this wasn't a CSV report then email the HTML report to the current user
                */
                if (!$csv) {
                    $buMail = new BUMail($this);

                    $senderEmail = CONFIG_SUPPORT_EMAIL;
                    $dbeUser = new DBEUser($this);
                    $loggedInUserID = $GLOBALS ['auth']->is_authenticated();
                    $dbeUser->getRow($loggedInUserID);
                    $toEmail = $dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

                    $hdrs = array(
                        'From'         => $senderEmail,
                        'To'           => $toEmail,
                        'Subject'      => 'Questionnaire Report ' . $this->buQuestionnaireReport->getPeriod(
                            ) . ' - ' . $this->buQuestionnaireReport->getQuestionnaireDescription(),
                        'Date'         => date("r"),
                        'Content-Type' => 'text/html; charset=UTF-8'
                    );

                    $buMail->mime->setHTMLBody($report);

                    $mime_params = array(
                        'text_encoding' => '7bit',
                        'text_charset'  => 'UTF-8',
                        'html_charset'  => 'UTF-8',
                        'head_charset'  => 'UTF-8'
                    );
                    $body = $buMail->mime->get($mime_params);

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

        if (!$this->dsSearchForm->getValue(self::searchFormFromDate)) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(self::searchFormFromDate, date('Y-m-d', strtotime("-1 month")));
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue(self::searchFormToDate)) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(self::searchFormToDate, date('Y-m-d'));
            $this->dsSearchForm->post();
        }


        $this->setMethodName('displaySearchForm');

        if (!$csv) {
            $this->setTemplateFiles(
                array(
                    'QuestionnaireReportPage' => 'QuestionnaireReportPage.inc'
                )
            );

            $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

            $this->setPageTitle('Questionnaire Report');

            $this->template->set_var(
                array(
                    'formError'              => $this->formError,
                    'questionnaireIDMessage' => $this->dsSearchForm->getMessage(self::searchFormQuestionnaireID),
                    'fromDate'               => Controller::dateYMDtoDMY(
                        $this->dsSearchForm->getValue(self::searchFormFromDate)
                    ),
                    'fromDateMessage'        => $this->dsSearchForm->getMessage(self::searchFormFromDate),
                    'toDate'                 => Controller::dateYMDtoDMY(
                        $this->dsSearchForm->getValue(self::searchFormToDate)
                    ),
                    'toDateMessage'          => $this->dsSearchForm->getMessage(self::searchFormToDate),
                    'urlSubmit'              => $urlSubmit,
                    'report'                 => $report
                )
            );

            /*
            Questionnaire Drop-down
            */

            $buQuestionnaire = new BUQuestionnaire($this);
            $dsQuestionnaire = new DataSet($this);
            $buQuestionnaire->getAll($dsQuestionnaire);

            $this->template->set_block('QuestionnaireReportPage', 'questionnaireBlock', 'questionnaires');
            while ($dsQuestionnaire->fetchNext()) {

                $this->template->set_var(
                    array(
                        'questionnaireDescription' => $dsQuestionnaire->getValue(DBEQuestionnaire::description),
                        'questionnaireID'          => $dsQuestionnaire->getValue(DBEQuestionnaire::questionnaireID),
                        'questionnaireSelected'    => ($this->dsSearchForm->getValue(
                                self::searchFormQuestionnaireID
                            ) == $dsQuestionnaire->getValue(DBEQuestionnaire::questionnaireID)) ? CT_SELECTED : null
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
    }
}
