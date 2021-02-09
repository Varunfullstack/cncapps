<?php
/**
 * Customer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Encryption;
use CNCLTD\Utils;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTCustomerFeedback extends CTCNC
{
    const CONST_SEARCH='search';
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
         
        if ( !self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(225);     
    }  

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {        
        switch ($this->getAction()) {
            case self::CONST_SEARCH:
                echo json_encode($this->getSearch(),JSON_NUMERIC_CHECK);
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
    function getSearch(){
        $from=$this->getParamOrNull('from');
        $to=$this->getParamOrNull('to');
        $customerID=$this->getParamOrNull('customerID');
        $engineerID=$this->getParamOrNull('engineerID');
        $query="SELECT       
                    f.id,
                    f.value,     
                    customer.`cus_name`,
                    f.`comments`,
                    DATE_FORMAT(f.`createdAt` , '%d/%m/%Y')   createdAt  ,
                    serviceRequestId problemID    ,
                    cons.cns_name engineer
                FROM `customerfeedback` f 
                    JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
                    JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId     
                    JOIN consultant cons on cons.cns_consno=cal.caa_consno
                    JOIN customer ON customer.`cus_custno`=problem.`pro_custno`

                WHERE cal.caa_callacttypeno=57                    
                    AND (:from  is null or f.`createdAt` >= :from )
                    AND (:to    is null or f.`createdAt` <= :to)
                    AND (:customerID is null or problem.`pro_custno`=:customerID)
                    AND (:engineerID is null or cal.caa_consno=:engineerID)
                order by f.`createdAt` desc";
        return DBConnect::fetchAll($query,['from'=>$from,'to'=>$to,'engineerID'=>$engineerID,'customerID'=>$customerID]);
        
    }
}
