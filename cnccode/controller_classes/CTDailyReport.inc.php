<?php
/**
 * MIS Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUDailyReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_bu"] . "/BUHeader.inc.php");

class CTDailyReport extends CTCNC
{
    private $buDailyReport;
    private $daysAgo = 1;
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
        $permissions = [
            'technical'
        ];
        if (!self::hasPermissions($permissions)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buDailyReport = new BUDailyReport ($this);

        if ($this->getParam('daysAgo')) {
            $this->daysAgo = $this->getParam('daysAgo');
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case 'fixedIncidents' :
                $this->buDailyReport->fixedIncidents($this->daysAgo, true);
                break;
            case 'focActivities' :
                $this->buDailyReport->focActivities($this->daysAgo);
                break;
            case 'prepayOverValue' :
                $this->buDailyReport->prepayOverValue($this->daysAgo);
                break;
            case 'outstandingIncidents' :
                $onScreen = isset($_GET['onScreen']);
                $dashboard = isset($_GET['dashboard']);
                $generateLog = isset($_GET['generateLog']);
                $selectedYear = isset($_GET['selectedYear']) ? $_GET['selectedYear'] : null;
                $this->setMenuId(110);
                $html = $this->buDailyReport->outstandingIncidents(
                    $this->daysAgo,
                    null,
                    $onScreen,
                    $dashboard,
                    $generateLog,
                    $selectedYear
                );

                if ($dashboard) {
                    $this->setTemplateFiles(
                        '7DayersDashboard',
                        '7DayersDashboard'
                    );

                    $this->template->setVar(
                        [
                            "thing" => $html
                        ]
                    );

                    $this->template->parse(
                        'CONTENTS',
                        '7DayersDashboard',
                        true
                    );

                    $this->parsePage();
                }

                break;
            case 'outstandingPriorityFiveIncidents' :
                $this->buDailyReport->outstandingIncidents(
                    $this->daysAgo,
                    true
                );
                break;
            case 'p5SRWithoutSalesOrders':
                $this->buDailyReport->p5IncidentsWithoutSalesOrders();
                break;
            case 'p5SRWithSalesOrdersAndContract':
                $this->buDailyReport->p5WithSalesOrderAndContractAssigned();
                break;
            case 'contactOpenSRReport':
                $this->buDailyReport->contactOpenSRReport();
                break;
            case 'outstandingReportAvailableYears':
                echo json_encode($this->buDailyReport->getOutstandingReportAvailableYears(), JSON_NUMERIC_CHECK);
                break;
            case 'outstandingReportPerformanceDataForYear':
                if (!isset($_REQUEST['year'])) {
                    throw new Exception('Year is missing');
                }
                echo json_encode(
                    $this->buDailyReport->getOutstandingReportPerformanceDataForYear($_REQUEST['year']),
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'showGraphs':
                if(isset($_REQUEST['popup']))
                  $this->setHTMLFmt(CT_HTML_FMT_POPUP);
                $this->setTemplateFiles(['graphs' => 'SevenDayersGraphs']);
                $this->template->parse(
                    'CONTENTS',
                    'graphs',
                    true
                );
                $this->parsePage();

                break;
            case 'outstandingReportPerformanceDataBetweenDates':
                if (!isset($_REQUEST['startDate']) || !isset($_REQUEST['endDate'])) {
                    throw new Exception('startDate and endDate are mandatory fields');
                }
                $startDate = new DateTime($_REQUEST['startDate']);
                $endDate = new DateTime($_REQUEST['endDate']);

                echo json_encode(
                    $this->buDailyReport->getOutstandingReportPerformanceDataBetweenDates($startDate, $endDate),
                    JSON_NUMERIC_CHECK
                );
                break;            
            default :
                break;
        }
    }
    
    /**
     * @throws Exception
     */
    function fixedIncidents()
    {

        $this->setMethodName('fixedIncidents');

        $fixedRequests = $this->buDailyReport->getFixedRequests();
        $row = $fixedRequests->fetch_row();
        if ($row) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );

            $template->set_file(
                'page',
                'ServiceFixedReportEmail.inc.html'
            );

            $template->set_block(
                'page',
                'requestBlock',
                'requests'
            );

            do {

                $urlRequest =
                    Controller::buildLink(
                        'SRActivity.php',
                        array(
                            'serviceRequestId' => $row[1],
                        )
                    );

                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'fixedBy'          => $row[2],
                        'urlRequest'       => $urlRequest
                    )
                );

                $template->parse(
                    'requests',
                    'requestBlock',
                    true
                );

            } while ($row = $fixedRequests->fetch_row());

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            $emailTo = 'Projectteam' . '@' . CONFIG_PUBLIC_DOMAIN;

            $this->buDailyReport->sendByEmailTo(
                $emailTo,
                'Service requests fixed yesterday',
                $body
            );

            echo $body;

        }

    } // end function

    function focActivities()
    {

        $this->setMethodName('focActivities');

        $activities = $this->buDailyReport->getFocActivities();

        if ($row = $activities->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );

            $template->set_file(
                'page',
                'ServiceFocReportEmail.inc.html'
            );

            $template->set_block(
                'page',
                'activityBlock',
                'activities'
            );

            do {

                $urlRequest =
                    Controller::buildLink(
                        'SRActivity.php',
                        array(
                            'serviceRequestId' => $row[1],

                        )
                    );

                $urlActivity =
                    Controller::buildLink(
                        'SRActivity.php',
                        array(
                            'callActivityID' => $row[2],
                            'action'         => 'displayActivity'
                        )
                    );
                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID'       => $row[2],
                        'technician'       => $row[3],
                        'hours'            => number_format(
                            $row[4],
                            2
                        ),
                        'urlRequest'       => $urlRequest,
                        'urlActivity'      => $urlActivity
                    )
                );

                $template->parse(
                    'activities',
                    'activityBlock',
                    true
                );

            } while ($row = $activities->fetch_row());

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            $emailTo = CONFIG_CATCHALL_EMAIL;

            $this->buDailyReport->sendByEmailTo(
                $emailTo,
                'FOC activities logged yesterday',
                $body
            );

            echo $body;

        }

    } // end function

    function prepayOverValue()
    {

        $this->setMethodName('focActivities');

        $activities = $this->buDailyReport->getPrePayActivitiesOverValue();

        if ($row = $activities->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );

            $template->set_file(
                'page',
                'ServicePrepayOverValueReportEmail.inc.html'
            );

            $template->set_block(
                'page',
                'activityBlock',
                'activities'
            );

            do {

                $urlRequest =
                    Controller::buildLink(
                        'SRActivity.php',
                        array(
                            'serviceRequestId' => $row[1],

                        )
                    );

                $urlActivity =
                    Controller::buildLink(
                        'SRActivity.php',
                        array(
                            'callActivityID' => $row[2],
                            'action'         => 'displayActivity'
                        )
                    );
                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID'       => $row[2],
                        'value'            => number_format(
                            $row[3],
                            2
                        ),
                        'technician'       => $row[4],
                        'urlRequest'       => $urlRequest,
                        'urlActivity'      => $urlActivity
                    )
                );

                $template->parse(
                    'activities',
                    'activityBlock',
                    true
                );

            } while ($row = $activities->fetch_row());

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            echo $body;

            $emailTo = CONFIG_PREPAY_EMAIL;

            $this->buDailyReport->sendByEmailTo(
                $emailTo,
                'Pre-pay activities logged yesterday over £100 in value',
                $body
            );

        }


    } // end function
} // end of class
