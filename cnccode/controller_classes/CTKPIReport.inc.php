<?php
global $cfg;

use CNCLTD\Data\DBConnect;
use CNCLTD\Exceptions\APIException;

require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUMISReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTKPIReport extends CTCNC
{
    const GET_SRFIXED                             = "SRFixed";
    const GET_PRIORITY_RAISED                     = "priorityRaised";
    const GET_SERVICE_REQUESTS_RAISED_BY_CONTRACT = "serviceRequestsRaisedByContract";
    const GET_QUOTATION_CONVERSION                = "quotationConversion";
    const GET_DAILY_STATS                         = "dailyStats";
    const GET_DAILY_SOURCE                        = 'dailySource';
    const GET_ENGINEER_MONTHLY_BILLING            = "engineerMonthlyBilling";
    const GET_DAILY_CONTACT                       = "dailyContact";
    const GET_GROSS_PROFIT                        = "grossProfit";
    /**
     * CTKPIReport constructor.
     */
    public function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->checkPermissions(REPORTS_PERMISSION);
    }


    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_SRFIXED:
                echo json_encode($this->getSRFixedData());
                exit;
            case self::GET_PRIORITY_RAISED:
                echo json_encode($this->getPriorityRaisedData());
                exit;
            case self::GET_SERVICE_REQUESTS_RAISED_BY_CONTRACT:
                echo json_encode($this->getServiceRequestsRaisedByContract(), JSON_NUMERIC_CHECK);
                exit;
            case self::GET_QUOTATION_CONVERSION:
                echo json_encode($this->getQuotationConversion());
                exit;
            case self::GET_ENGINEER_MONTHLY_BILLING:
                echo json_encode($this->getEngineerMonthlyBilling());
                exit;
            case self::GET_DAILY_STATS:
                echo json_encode($this->getDailyStats(), JSON_NUMERIC_CHECK);
                exit;
            case self::GET_DAILY_SOURCE:
                echo json_encode($this->getDailySource(), JSON_NUMERIC_CHECK);
                exit;
            case self::GET_DAILY_CONTACT:
                echo json_encode($this->getDailyContact(), JSON_NUMERIC_CHECK);
                exit;
            case self::GET_GROSS_PROFIT:
                echo json_encode($this->getGrossProfit(), JSON_NUMERIC_CHECK);
                exit;
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {
        $this->setMenuId(513);
        $this->setPageTitle('KPI Reports');
        $this->setTemplateFiles(
            array('KPIReport' => 'KPIReport.rct')
        );
        $this->loadReactScript('KPIReportComponent.js');
        $this->loadReactCSS('KPIReportComponent.css');
        $this->template->parse(
            'CONTENTS',
            'KPIReport',
            true
        );
        $this->parsePage();
    }

    function getSRFixedData()
    {
        $from       = $_REQUEST["from"];
        $to         = $_REQUEST["to"];
        $customerID = $_REQUEST["customerID"];
        $query      = "SELECT
        SUM(1) allTeams,
        SUM(consultant.`teamID` = 1) AS helpDeskFixedActivities,
        SUM(consultant.`teamID` = 2) AS escalationsFixedActivities,
        SUM(consultant.`teamID` = 4) AS smallProjectsActivities,
        SUM(consultant.`teamID` = 5) AS projectsActivities,
        callactivity.caa_date date
      FROM
        callactivity
        JOIN consultant
          ON consultant.`cns_consno` = callactivity.`caa_consno`
        JOIN problem p ON p.`pro_problemno` = callactivity.`caa_problemno`

      WHERE callactivity.`caa_callacttypeno` = 57
        AND p.`pro_custno`<>282
        AND consultant.`teamID` < 6
        AND consultant.`cns_consno` <> 67
        ";
        $params     = array();
        if ($from != '') {
            $query          .= "  AND callactivity.caa_date >= :from ";
            $params["from"] = $from;
        }
        if ($to != '') {
            $query        .= "  AND callactivity.caa_date <= :to ";
            $params["to"] = $to;
        }
        if ($customerID != '') {
            $query                .= "  AND p.pro_custno = :pro_custno ";
            $params["pro_custno"] = $customerID;
        }
        $query .= "GROUP BY callactivity.caa_date order by date";
        return DBConnect::fetchAll($query, $params);
    }

    function getPriorityRaisedData()
    {
        $from       = $_REQUEST["from"];
        $to         = $_REQUEST["to"];
        $customerID = $_REQUEST["customerID"];
        $query      = " SELECT
        
        date( problem.pro_date_raised) AS date,
        SUM(1) AS raisedAll,
        SUM(pro_priority = 1) AS P1,
        SUM(pro_priority = 2) AS P2,
        SUM(pro_priority = 3) AS P3,
        SUM(pro_priority = 4) AS P4
        FROM
        problem
        JOIN customer
        ON customer.cus_custno = problem.pro_custno

      WHERE     problem.pro_priority < 5
        AND problem.`pro_custno`<>282 ";
        $params     = array();
        if ($from != '') {
            $query          .= "  AND problem.pro_date_raised >= :from ";
            $params["from"] = $from;
        }
        if ($to != '') {
            $query        .= "  AND problem.pro_date_raised <= :to ";
            $params["to"] = $to;
        }
        if ($customerID != '') {
            $query                .= "  AND problem.pro_custno = :pro_custno ";
            $params["pro_custno"] = $customerID;
        }
        $query .= " GROUP BY date(problem.pro_date_raised) order by date";
        return DBConnect::fetchAll($query, $params);
    }

    function getServiceRequestsRaisedByContract()
    {
        $from       = $_REQUEST["from"];
        $to         = $_REQUEST["to"];
        $customerID = $_REQUEST["customerID"];
        $query      = " SELECT
  DATE(pro_date_raised) AS date,
  SUM(1) AS total,
  item.`itm_desc` AS contractDescription,
  item.itm_itemno AS contractItem
FROM
  problem
  JOIN customer
    ON customer.`cus_custno` = problem.`pro_custno`
  LEFT JOIN custitem
    ON pro_contract_cuino = cui_cuino
  LEFT JOIN item
    ON cui_itemno = itm_itemno
WHERE problem.`pro_date_raised` >= '2020-01-01'
  AND problem.`pro_date_raised` <= '2020-12-31'
  AND problem.`pro_priority` < 5
  AND problem.`pro_custno` <> 282
  AND allowsrlog = 1

        ";
        $params     = array();
        if ($from != '') {
            $query          .= "  AND problem.pro_date_raised >= :from ";
            $params["from"] = $from;
        }
        if ($to != '') {
            $query        .= "  AND problem.pro_date_raised <= :to ";
            $params["to"] = $to;
        }
        if ($customerID != '') {
            $query                .= "  AND problem.pro_custno = :pro_custno ";
            $params["pro_custno"] = $customerID;
        }
        $query .= " GROUP BY DATE(pro_date_raised),
  itm_itemno
  ORDER BY date, contractDescription";
        return DBConnect::fetchAll($query, $params);
    }

    function getQuotationConversion()
    {
        $from       = $_REQUEST["from"];
        $to         = $_REQUEST["to"];
        $customerID = $_REQUEST["customerID"];
        $query      = "SELECT 
        quote.odh_quotation_create_date date,
        COUNT(*) quote,        
        COUNT(order.odh_custno) conversion
        FROM ordhead AS `quote` 
        LEFT JOIN ordhead AS `order` 
            ON `order`.odh_quotation_ordno = `quote`.odh_ordno 
        WHERE 
        1=1 ";
        $params     = array();
        if ($from != '') {
            $query          .= "  AND `quote`.odh_quotation_create_date >= :from ";
            $params["from"] = $from;
        }
        if ($to != '') {
            $query        .= "  AND `quote`.odh_quotation_create_date <= :to ";
            $params["to"] = $to;
        }
        if ($customerID != '') {
            $query            .= "  AND quote.odh_custno = :custno ";
            $params["custno"] = $customerID;
        }
        $query .= " GROUP BY  date order by date";
        return DBConnect::fetchAll($query, $params);
    }

    function getDailyStats()
    {
        $from       = (@$_REQUEST['from'] ?? '') == '' ? null : $_REQUEST['from'];
        $to         = (@$_REQUEST['to'] ?? '') == '' ? null : $_REQUEST['to'];
        $customerID = (@$_REQUEST['customerID'] ?? '') == '' ? null : $_REQUEST['customerID'];
        $query      = "SELECT
        COUNT(DISTINCT pro_problemno ) AS total,
        DATE_FORMAT(pro_date_raised,'%Y-%m-%d') date,
        'raisedToday' AS type
      FROM problem p        
      WHERE pro_custno <> 282   
        AND (:customerID is null or pro_custno=:customerID)       
        AND (:from is null or pro_date_raised>=:from)
        AND (:to is null or pro_date_raised<=:to)
      GROUP BY type, date
      
      UNION 
      
      SELECT
        COUNT(DISTINCT p.`pro_problemno`) AS total,
        DATE_FORMAT(c.caa_date,'%Y-%m-%d') date,
        'fixedToday' AS type
      FROM
        callactivity c
        JOIN problem p ON c.caa_problemno = p.pro_problemno
      WHERE pro_custno <> 282
        AND c.`caa_consno` <> 67
        AND c.caa_callacttypeno = 57        
        AND pro_status in ('F','C')
        AND (:customerID is null or pro_custno=:customerID)       
        AND (:from is null or c.caa_date>=:from)
        AND (:to is null or c.caa_date<=:to)
      GROUP BY type, date
      
      UNION 
      
      SELECT
      COUNT(*)  AS total,
      DATE_FORMAT(pro_reopened_date,'%Y-%m-%d') date,
        'reopenToday' AS type
        FROM
        problem
        LEFT JOIN callactivity AS FIXED ON problem.`pro_problemno` = fixed.`caa_problemno`   
      WHERE pro_custno <> 282
        AND fixed.`caa_callacttypeno` = 57
        AND  DATE_FORMAT(fixed.`caa_date`,'%Y-%m-%d')  = DATE_FORMAT(`pro_reopened_date`,'%Y-%m-%d')        
        AND (fixed.`caa_callactivityno` IS NULL OR fixed.`caa_consno` <> 67)
        AND (:customerID is null or pro_custno=:customerID)       
        AND (:from is null or fixed.`caa_date`>=:from)
        AND (:to is null or fixed.`caa_date`<=:to)
         
      GROUP BY type, date
      
      UNION
      
      SELECT
        COUNT(pro_problemno) AS total,
        DATE_FORMAT(pro_date_raised,'%Y-%m-%d') date,
        'startedToday' AS type
      FROM
        `callactivity` c
        JOIN problem p
          ON c.`caa_problemno` = p.`pro_problemno`
      WHERE pro_custno <> 282
          AND caa_callacttypeno = 51
        AND pro_status <> 'I'        
        AND (:customerID is null or pro_custno=:customerID)       
        AND (:from is null or pro_date_raised>=:from)
        AND (:to is null or pro_date_raised<=:to)
       GROUP BY type, date
       
       UNION 
       
       SELECT
        COUNT(DISTINCT pro_custno) total,
        DATE_FORMAT(caa_date,'%Y-%m-%d') date,
        'uniqueCustomer' AS type
      FROM
        `callactivity` c
        JOIN problem
          ON problem.`pro_problemno` = c.`caa_problemno`
      WHERE pro_custno <> 282
        AND caa_callacttypeno = 51
        AND `caa_date` >'2020-10-08' 
        AND (:customerID is null or pro_custno=:customerID)       
        AND (:from is null or `caa_date`>=:from)
        AND (:to is null or `caa_date`<=:to)
       GROUP BY type, date";
        return DBConnect::fetchAll($query, ["from" => $from, "to" => $to, "customerID" => $customerID]);
    }

    /**
     * @param bool|string $today
     * @return array
     */
    private function getDailySource()
    {
        $from = (@$_REQUEST['from'] ?? '') == '' ? null : $_REQUEST['from'];
        $to         = (@$_REQUEST['to'] ?? '') == '' ? null : $_REQUEST['to'];
        $customerID = (@$_REQUEST['customerID'] ?? '') == '' ? null : $_REQUEST['customerID'];
        $query      = "SELECT r.`description` as type,
                    COUNT(*)  total, 
                    DATE_FORMAT(pro_date_raised ,'%Y-%m-%d') date
                FROM problem p LEFT JOIN `problemraisetype` r ON p.`raiseTypeId`=r.`id`
                WHERE    
                    pro_custno <> 282
                    AND (:customerID is null or pro_custno=:customerID)       
                    AND (:from is null or `pro_date_raised`>=:from)
                    AND (:to is null or `pro_date_raised`<=:to)                     
                GROUP BY r.`description`,DATE";
        return DBConnect::fetchAll($query, ["from" => $from, "to" => $to, "customerID" => $customerID]);
    }

    function getEngineerMonthlyBilling()
    {
        $from   = @$_REQUEST["from"] ?? '';
        $to     = @$_REQUEST["to"] ?? '';
        $query  = "SELECT
        inl_desc,
        inh_date_printed_yearmonth,
        SUM(`inl_qty` * `inl_unit_price`) AS amount
      FROM
        invline
        JOIN invhead
          ON invline.`inl_invno` = invhead.`inh_invno`
      WHERE inl_itemno IN (
          1502,
          1503,
          2237,
          16865,
          2325,
          9251,
          9637,
          10437, 
          10654
        )
        AND inl_desc LIKE '%- consultancy%'           
       ";
        $params = array();
        if ($from != '') {
            $query          .= "  AND inh_date_printed >= :from ";
            $params["from"] = $from;
        }
        if ($to != '') {
            $query        .= "  AND inh_date_printed <= :to ";
            $params["to"] = $to;
        }
        $query .= "  GROUP BY  invhead.`inh_date_printed_yearmonth`, inl_desc  order by inh_date_printed_yearmonth";
        return DBConnect::fetchAll($query, $params);
    }

    function getDailyContact()
    {
        $from = (@$_REQUEST['from'] ?? '') == '' ? null : $_REQUEST['from'];
        $to     = (@$_REQUEST['to'] ?? '') == '' ? null : $_REQUEST['to'];
        $where  = "";
        $params = [];
        if ($from != '') {
            $where          .= " AND create_at>=:from";
            $params["from"] = $from;
        }
        if ($to != '') {
            $where        .= " AND create_at<=:to";
            $params["to"] = $to;
        }
        $query = "SELECT 'Inbound' AS type ,COUNT(*) total,DATE_FORMAT(create_at,'%Y-%m-%d') date FROM callactivity_customer_contact 
      WHERE isInbound=1   $where
      GROUP BY DATE_FORMAT(create_at,'%Y-%m-%d')
      UNION
      SELECT 'Outbound' AS type ,COUNT(*) total,DATE_FORMAT(create_at,'%Y-%m-%d') date FROM callactivity_customer_contact 
      WHERE isInbound=0   $where
      GROUP BY DATE_FORMAT(create_at,'%Y-%m-%d')";
        //echo $query; exit;
        return DBConnect::fetchAll($query, $params);
    }
    function getGrossProfit(){
        $from=@$_REQUEST["from"];
        $to=@$_REQUEST["to"];
        $customerID=@$_REQUEST["customerID"];
        $stockCat=@$_REQUEST["stockCat"];
        if(!isset($from)||!isset($to))
            return $this->fail(APIException::badRequest,"Missing paramaters");
        $query="SELECT 
        customer.`cus_name` AS customer,
        DATE_FORMAT(inh_date_printed, '%Y-%m-01') AS date,
        ROUND(SUM(
            invline.`inl_cost_price` * invline.`inl_qty`
        ),2) AS totalCost,
        ROUND(SUM(
            invline.`inl_unit_price` * invline.`inl_qty`
        ),2) AS totalSale,
        invline.`inl_stockcat` AS stockCat
        FROM
        invline
        JOIN invhead
            ON invline.`inl_invno` = invhead.`inh_invno`
        JOIN customer
            ON invhead.`inh_custno` = customer.`cus_custno`
        WHERE 
        (:customerID is null or customer.cus_custno=:customerID)
        and inh_date_printed >= :from
        AND inh_date_printed <= :to
        AND inl_line_type <> 'C'
        AND (:stockCat is null or inl_stockcat=:stockCat)
        GROUP BY invline.`inl_stockcat`, invhead.`inh_custno`, inh_date_printed_yearmonth 
        ORDER BY customer, date, stockCat ";
        
        $result=DBConnect::fetchAll($query,
        ["from"=>$from,"to"=>$to,"customerID"=>$customerID??null,"stockCat"=>$stockCat??null]);

        return $this->success($result);
    }
}