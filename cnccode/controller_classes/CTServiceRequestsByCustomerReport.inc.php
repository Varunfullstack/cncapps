<?php
/**
 * Service request by customer report
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ['path_bu'] . '/BUMail.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTServiceRequestsByCustomerReport extends CTCNC
{

    const searchFormFromDate = 'fromDate';
    const searchFormToDate   = 'toDate';
    const searchFormDays     = 'days';

    private $buActivity;

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
        $this->setMenuId(213);
        $this->buActivity = new BUActivity($this);
    }

    /**
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {

            case 'email':
                $this->email();
                break;
            case 'search':
            default:
                if (!$this->isUserSDManager()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $this->search();
                break;

        }
    }

    function email()
    {
        $this->setMethodName('email');
        $dsSearchForm = $this->initialiseSearchForm();
        $days         = $this->getParam('days');
        $dsSearchForm->setValue(
            self::searchFormDays,
            $days
        );
        $results = $this->buActivity->getSrPercentages($days);
        if ($results) {
            $buMail      = new BUMail($this);
            $senderEmail = CONFIG_SUPPORT_EMAIL;
            $toEmail     = 'monthlysdreport@' . CONFIG_PUBLIC_DOMAIN;
            $this->template = new Template(
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $this->template->set_file(
                'page',
                'ServiceRequestsByCustomerReportEmail.inc.html'
            );
            $this->renderReport(
                'page',
                $results,
                $dsSearchForm
            );
            $this->template->parse(
                'output',
                'page',
                true
            );
            $body = $this->template->get_var('output');
            $subject = 'Service Requests By Customer - Days: ' . $days;
            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );
            $buMail->mime->setHTMLBody($body);
            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );
            $body        = $buMail->mime->get($mime_params);
            $hdrs = $buMail->mime->headers($hdrs);
            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
            echo 'email queued to be sent';
        }

    }

    function initialiseSearchForm()
    {
        $dsSearchForm = new DSForm ($this);
        $dsSearchForm->addColumn(
            self::searchFormFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsSearchForm->addColumn(
            self::searchFormDays,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsSearchForm->setValue(
            self::searchFormDays,
            7
        );
        return $dsSearchForm;

    }

    /**
     * @param $templateName
     * @param $results
     * @param DSForm $dsSearchForm
     */
    public function renderReport($templateName,
                                 $results,
                                 $dsSearchForm
    )
    {
        $totalPercentage = 0;
        $totalHours      = 0;
        $totalSrCount    = 0;
        $this->template->set_block(
            $templateName,
            'customersBlock',
            'customers'
        );
        foreach ($results as $row) {

            $this->template->set_var(
                array(
                    'customerName' => $row['cus_name'],
                    'hours'        => number_format(
                        $row['hours'],
                        1
                    ),
                    'srCount'      => $row['srCount'],
                    'percentage'   => number_format(
                        $row['percentage'],
                        1
                    )
                )
            );
            $this->template->parse(
                'customers',
                'customersBlock',
                true
            );
            $totalHours      += $row['hours'];
            $totalPercentage += $row['percentage'];
            $totalSrCount    += $row['srCount'];
        }
        $this->template->set_var(
            array(
                'days'            => $dsSearchForm->getValue(self::searchFormDays),
                'totalHours'      => number_format(
                    $totalHours,
                    1
                ),
                'totalSrCount'    => $totalSrCount,
                'totalPercentage' => number_format(
                    $totalPercentage,
                    1
                )
            )
        );


    } // end renderReport()
    /*
    Send email report for past $days
    */
    /**
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        $dsSearchForm = $this->initialiseSearchForm();
        $this->setTemplateFiles(array('ServiceRequestsByCustomerReport' => 'ServiceRequestsByCustomerReport.inc'));
        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $results = $this->buActivity->getSrPercentages(
                    $dsSearchForm->getValue(self::searchFormDays),
                    $dsSearchForm->getValue(self::searchFormFromDate),
                    $dsSearchForm->getValue(self::searchFormToDate)
                );
                if ($results) {

                    $this->renderReport(
                        'ServiceRequestsByCustomerReport',
                        $results,
                        $dsSearchForm
                    );

                }//end if $results
            }
        }
        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $this->setPageTitle('Service Requests By Customer Report');
        $this->template->set_var(
            array(
                'formError'       => $this->formError,
                'days'            => $dsSearchForm->getValue(self::searchFormDays),
                'daysMessage'     => $dsSearchForm->getMessage(self::searchFormDays),
                'fromDate'        => $dsSearchForm->getValue(self::searchFormFromDate),
                'fromDateMessage' => $dsSearchForm->getMessage(self::searchFormFromDate),
                'toDate'          => $dsSearchForm->getValue(self::searchFormToDate),
                'toDateMessage'   => $dsSearchForm->getMessage(self::searchFormToDate),
                'urlSubmit'       => $urlSubmit
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ServiceRequestsByCustomerReport',
            true
        );
        $this->parsePage();
    } // end email

}
