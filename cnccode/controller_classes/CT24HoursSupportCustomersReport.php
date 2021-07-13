<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

global

use CNCLTD\Exceptions\JsonHttpException;

$cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');

class CT24HoursSupportCustomersReport extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        if (!self::hasPermissions(TECHNICAL_PERMISSION)) {
            Header("Location: /NotAllowed.php");
            exit;
        }

        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'getCallOutYears':
            {

                global $db;
                $statement = $db->preparedQuery(
                    'SELECT DISTINCT YEAR(createdAt) as years FROM customercallouts GROUP BY YEAR(createdAt)',
                    []
                );
                $result = [];
                while ($row = $statement->fetch_array(MYSQLI_NUM)) {
                    $result[] = $row[0];
                }
                echo json_encode(
                    [
                        "status" => "ok",
                        "data"   => $result
                    ]
                );
                exit;
            }
            case 'getOutOfHoursData':
            {
                global $db;

                $query = "SELECT id,customerId, `cus_name` as customerName, createdAt, chargeable, salesOrderHeaderId FROM customerCallOuts LEFT JOIN customer ON customer.`cus_custno` = customerCallOuts.customerId where 1";
                $params = [];
                $startDate = new DateTime('first day of this year');
                $endDate = new DateTime('last day of this year');
                if (!empty($_REQUEST['startDate'])) {
                    $startDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $_REQUEST['startDate']);
                    if (!$startDate) {
                        throw new JsonHttpException(400,'startDate format should be YYYY-MM-DD');
                    }
                    $query .= " and createdAt >= ?";
                    $params[] = [
                        "type"  => "s",
                        "value" => $startDate->format(DATE_MYSQL_DATE)
                    ];
                }
                if (!empty($_REQUEST['endDate'])) {
                    $endDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $_REQUEST['endDate']);
                    if (!$endDate) {
                        throw new JsonHttpException(400,'endDate format should be YYYY-MM-DD');
                    }
                    $query .= " and createdAt <= ?";
                    $params[] = [
                        "type"  => "s",
                        "value" => $endDate->format(DATE_MYSQL_DATE)
                    ];
                }

                $statement = $db->preparedQuery($query, $params);
                echo json_encode(
                    [
                        "status" => "ok",
                        "data"   => $statement->fetch_all(MYSQLI_ASSOC),
                    ]
                );
                exit;
            }
            default:
                $this->display24HourSupportCustomers();
        }

    }

    /**
     * Displays list of customers with 24 Hour Support
     *
     * @throws Exception
     */
    function display24HourSupportCustomers()
    {
        $this->setMethodName('display24HourSupportCustomers');
        $this->setMenuId(111);
        $this->setPageTitle("24 Hour Support Customers");
        $dsCustomer = new DataSet($this);
        $buCustomer = new BUCustomer($this);
        if ($buCustomer->get24HourSupportCustomers($dsCustomer)) {

            $this->setTemplateFiles(
                'Customer24HourSupport',
                'Customer24HourSupport.inc'
            );

            $this->template->set_block(
                'Customer24HourSupport',
                'customerBlock',
                'customers'
            );

            $this->loadReactScript('OutOfHoursReportComponent.js');

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
                        "Customer.php",
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );


                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::name),
                        'linkURL'      => $linkURL
                    )
                );

                $this->template->parse(
                    'customers',
                    'customerBlock',
                    true
                );

            }

            $this->template->parse(
                'CONTENTS',
                'Customer24HourSupport',
                true
            );

        } else {

            $this->setTemplateFiles(
                'SimpleMessage',
                'SimpleMessage.inc'
            );
            $this->template->set_var(
                'message',
                'There are no 24 Hour Support customers'
            );
        }

        $this->parsePage();

        exit;
    }

}
