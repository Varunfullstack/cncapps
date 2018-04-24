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
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTDailyReport extends CTCNC
{
    var $buDailyReport = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        if (!self::canAccess($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buDailyReport = new BUDailyReport ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST ['action']) {

            case 'fixedIncidents' :
                $this->fixedIncidents();
                break;
            case 'focActivities' :
                $this->focActivities();
                break;
            case 'prepayOverValue' :
                $this->prepayOverValue();
                break;
            default :
                break;
        }
    }

    function fixedIncidents()
    {

        $this->setMethodName('fixedIncidents');

        $fixedRequests = $this->buDailyReport->getFixedRequests();
        $row = $fixedRequests->fetch_row();
        if ($row) {

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'ServiceFixedReportEmail.inc.html');

            $template->set_block('page', 'requestBlock', 'requests');

            do {

                $urlRequest =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'fixedBy' => $row[2],
                        'urlRequest' => $urlRequest
                    )
                );

                $template->parse('requests', 'requestBlock', true);

            } while ($row = $fixedRequests->fetch_row());

            $template->parse('output', 'page', true);

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

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'ServiceFocReportEmail.inc.html');

            $template->set_block('page', 'activityBlock', 'activities');

            do {

                $urlRequest =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $urlActivity =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'callActivityID' => $row[2],
                            'action' => 'displayActivity'
                        )
                    );
                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID' => $row[2],
                        'technician' => $row[3],
                        'hours' => number_format($row[4], 2),
                        'urlRequest' => $urlRequest,
                        'urlActivity' => $urlActivity
                    )
                );

                $template->parse('activities', 'activityBlock', true);

            } while ($row = $activities->fetch_row());

            $template->parse('output', 'page', true);

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

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'ServicePrepayOverValueReportEmail.inc.html');

            $template->set_block('page', 'activityBlock', 'activities');

            do {

                $urlRequest =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $urlActivity =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'callActivityID' => $row[2],
                            'action' => 'displayActivity'
                        )
                    );
                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID' => $row[2],
                        'value' => number_format($row[3], 2),
                        'technician' => $row[4],
                        'urlRequest' => $urlRequest,
                        'urlActivity' => $urlActivity
                    )
                );

                $template->parse('activities', 'activityBlock', true);

            } while ($row = $activities->fetch_row());

            $template->parse('output', 'page', true);

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
?>