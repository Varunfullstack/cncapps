<?php
/**
 * Customer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBConnect;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTCustomerFeedback extends CTCNC
{
    const CONST_SEARCH = 'search';

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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(226);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::CONST_SEARCH:
                echo json_encode($this->getSearch(), JSON_NUMERIC_CHECK);
                break;
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {
        $this->setPageTitle('Customer Feedback');
        $this->setTemplateFiles(
            array('CustomerFeedback' => 'CustomerFeedback.rct')
        );
        $this->loadReactScript('CustomerFeedbackComponent.js');
        $this->loadReactCSS('CustomerFeedbackComponent.css');
        $this->template->parse(
            'CONTENTS',
            'CustomerFeedback',
            true
        );
        $this->parsePage();
    }

    function getSearch()
    {
        $from       = $this->getParamOrNull('from');
        $to         = $this->getParamOrNull('to');
        $customerID = $this->getParamOrNull('customerID');
        $engineerID = $this->getParamOrNull('engineerID');
        $hd         = $this->getParamOrNull('hd');
        $es         = $this->getParamOrNull('es');
        $sp         = $this->getParamOrNull('sp');
        $p          = $this->getParamOrNull('p');
        $query      = "SELECT       
                    f.id,
                    f.value,     
                    customer.`cus_name`,
                    f.`comments`,
                    createdAt  ,
                    serviceRequestId problemID    ,
                    cons.cns_name engineer,
                    concat(contact.con_first_name, ' ', contact.con_last_name) as contactName
                FROM `customerfeedback` f 
                    JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
                    JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId     
                    JOIN consultant cons on cons.cns_consno=cal.caa_consno
                    JOIN customer ON customer.`cus_custno`=problem.`pro_custno`
                    join contact on  contact.con_contno = cal.caa_contno
                WHERE cal.caa_callacttypeno=57                    
                    AND (:from  is null or date(f.`createdAt`) >= :from )
                    AND (:to    is null or date(f.`createdAt`) <= :to) 
                    AND (:customerID is null or problem.`pro_custno`=:customerID)
                    AND (:engineerID is null or cal.caa_consno=:engineerID)
";
        if (!$hd) {
            $query .= " and teamID <> 1 ";
        }
        if (!$es) {
            $query .= " and teamID <> 2 ";
        }
        if (!$sp) {
            $query .= " and teamID <> 4 ";
        }
        if (!$p) {
            $query .= " and teamID <> 5 ";
        }
        $query .= " order by f.`createdAt` desc";
        return DBConnect::fetchAll(
            $query,
            [
                'from'       => $from,
                'to'         => $to,
                'engineerID' => $engineerID,
                'customerID' => $customerID
            ]
        );

    }
}
