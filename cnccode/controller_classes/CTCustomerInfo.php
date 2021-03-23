<?php
global $cfg;
require_once($cfg['path_ct'] . '/CTCurrentActivityReport.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_dbe'] . '/DBEJContactAudit.php');

class CTCustomerInfo extends CTCNC
{
    const CONST_SUPPORT_CUSTOMERS    = 'supportCustomers';
    const CONST_CALL_OUT_YEARS       = 'callOutYears';
    const CONST_OUT_OF_HOURS_DATA    = 'outOfHours';
    const CONST_SPECIAL_ATTENTION    = 'specialAttention';
    const CONST_SEARCH_CONTACT_AUDIT = 'searchContactAudit';

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
            $cfg,
            false
        );
        if (!self::hasPermissions(TECHNICAL_PERMISSION)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }


    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::CONST_SUPPORT_CUSTOMERS:
                echo json_encode($this->get24HourSupportCustomers());
                break;
            case self::CONST_CALL_OUT_YEARS:
                echo json_encode($this->getCallOutYears());
                break;
            case self::CONST_OUT_OF_HOURS_DATA:
                echo json_encode($this->getOutOfHoursData());
                break;
            case self::CONST_SPECIAL_ATTENTION:
                echo json_encode($this->getSpecialAttentionData());
                break;
            case self::CONST_SEARCH_CONTACT_AUDIT:
                echo json_encode($this->searchContactAudit());
                break;
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {
        $this->setPageTitle('Customer Information');
        $this->setTemplateFiles(
            array('CustomerInfo' => 'reactCustomerInfo.rct')
        );
        $this->loadReactScript('CustomerInfoComponent.js');
        $this->loadReactCSS('CustomerInfoComponent.css');
        $this->template->parse(
            'CONTENTS',
            'CustomerInfo',
            true
        );
        $this->setMenuId(113);
        $this->parsePage();
    }

    // 24 hour support customers    

    /**
     * Get list of customers with 24 Hour Support
     *
     * @throws Exception
     */
    function get24HourSupportCustomers()
    {
        $this->setMethodName('get24HourSupportCustomers');
        $dsCustomer = new DataSet($this);
        $buCustomer = new BUCustomer($this);
        $customers  = [];
        if ($buCustomer->get24HourSupportCustomers($dsCustomer, true)) {
            while ($dsCustomer->fetchNext()) {
                $customers [] = [
                    'customerName' => $dsCustomer->getValue(DBECustomer::name),
                    'customerID'   => $dsCustomer->getValue(DBECustomer::customerID)
                ];
            }
        }
        return $customers;
    }

    function getCallOutYears()
    {
        $years = DBConnect::fetchAll(
            'SELECT YEAR(createdAt) as years FROM customercallouts GROUP BY YEAR(createdAt) order by years desc ',
            []
        );
        return $years;
    }

    function getOutOfHoursData()
    {
        $from   = $_REQUEST['from'] ?? '';
        $to     = $_REQUEST['to'] ?? '';
        $query  = "SELECT id,customerId, `cus_name` as customerName, createdAt, chargeable, salesOrderHeaderId 
                FROM customerCallOuts 
                LEFT JOIN customer ON customer.`cus_custno` = customerCallOuts.customerId 
                where 1 ";
        $params = [];
        if ($from != '') {
            $query          .= ' and createdAt >= :from';
            $params["from"] = $from;
        }
        if ($to != '') {
            $query        .= ' and createdAt <= :to';
            $params["to"] = $to;
        }
        $data = DBConnect::fetchAll($query, $params);
        return $data;
    }

    /**
     * Get list of customers with Special Attention flag set
     *
     * @throws Exception
     */
    function getSpecialAttentionData()
    {
        $this->setMethodName('getSpecialAttentionData');
        $customers = [];
        $contacts  = [];
        $buContact = new BUContact($this);
        $dsContact = new DataSet($this);
        if ($buContact->getSpecialAttentionContacts($dsContact)) {
            $dbeCustomer = new DBECustomer($this);
            while ($dsContact->fetchNext()) {
                $linkURL = Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $dsContact->getValue(DBEContact::customerID)
                    )
                );
                if ($dbeCustomer->getValue(DBECustomer::customerID) != $dsContact->getValue(DBEContact::customerID)) {
                    $dbeCustomer->getRow($dsContact->getValue(DBEContact::customerID));
                }
                if ($dbeCustomer->getValue(DBECustomer::droppedCustomerDate) || !$dbeCustomer->getValue(
                        DBECustomer::becameCustomerDate
                    )) {
                    continue;
                }
                $contacts[] = array(
                    'contactName'  => ($dsContact->getValue(DBEContact::firstName) . " " . $dsContact->getValue(
                            DBEContact::lastName
                        )),
                    'linkURL'      => $linkURL,
                    'customerName' => $dbeCustomer->getValue(DBECustomer::name)
                );
            }

        }
        $dsCustomer = new DataSet($this);
        $buCustomer = new BUCustomer($this);
        if ($buCustomer->getSpecialAttentionCustomers($dsCustomer)) {
            while ($dsCustomer->fetchNext()) {

                $linkURL     = Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                    )
                );
                $customers[] = array(
                    'customerName'            => $dsCustomer->getValue(DBECustomer::name),
                    'specialAttentionEndDate' => $dsCustomer->getValue(DBECustomer::specialAttentionEndDate),
                    'linkURL'                 => $linkURL
                );
            }
        }
        return ['customers' => $customers, 'contacts' => $contacts];
    }

    function searchContactAudit()
    {
        $body      = $this->getBody();
        $test      = new DBEJContactAudit($this);
        $startDate = null;
        if ($body->from != '') {
            $startDate = DateTime::createFromFormat(
                DATE_MYSQL_DATE,
                $body->from
            );
        }
        $endDate = null;
        if ($body->to != '') {
            $endDate = DateTime::createFromFormat(
                DATE_MYSQL_DATE,
                $body->to
            );
        }
        $test->search(
            $body->customerID,
            $startDate,
            $endDate,
            $body->firstName,
            $body->lastName
        );
        $result = [];
        while ($test->fetchNext()) {
            $result[] = $test->getRowAsAssocArray();
        }
        return $result;

    }
}
