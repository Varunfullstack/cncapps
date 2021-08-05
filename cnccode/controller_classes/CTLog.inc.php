<?php

/**
 * Quotation template controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBConnect;
use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTLog extends CTCNC
{
    const CONST_LOG = "log";
    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg
    ) {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            SALES_PERMISSION
        ];
        if (!self::hasPermissions($roles)) {
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
            case self::CONST_LOG:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getLogs(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->store(), JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->delete(), JSON_NUMERIC_CHECK);
                        break;
                }
                exit;
            default:
                echo "Log";
                $this->displayForm();
                break;
        }
    }


    /**
     * Export expenses that have not previously been exported
     * @access private
     * @throws Exception     
     */
    function displayForm()
    {
        // $this->setPageTitle('Quote Templates');
        // $this->setTemplateFiles(
        //     'QuotationTemplateList',
        //     'QuotationTemplateList.inc'
        // );
        // $this->template->parse(
        //     'CONTENTS',
        //     'QuotationTemplateList',
        //     true
        // );
        // $this->loadReactScript('QuoteTemplatesComponent.js');
        // $this->loadReactCSS('QuoteTemplatesComponent.css');
        // $this->parsePage();
    }

    function getLogs()
    {
        $query="SELECT audit.id , userID, customerID, problemID, 
        pageID, oldValues, newValues, pcIp ,action,
        concat(c.firstName,' ',c.lastName) as userName,
        pages.name as pageName,
        createAt,
        action
                from audit
                left join consultant c on userID=c.cns_consno
                left join pages on pageID=pages.id
                where 1=1 
                ";
        $params = [];
        if(isset($_REQUEST["customerID"]))
        {
            $query .=" and customerID=:customerID";
            $params["customerID"]=$_REQUEST["customerID"];
        }
         $query .=" order by createAt desc";
        $result=DBConnect::fetchAll($query,$params);
        return $this->success($result);
    }

    function store()
    {
        $data = $this->only($this->getBody(), 
        [ "customerID", "problemID", "pageID", "oldValues", "newValues","action" ]);       
        $data["createAt"]= date("Y-m-d H:i:s");
        if ($data) {
            $data["userID"]=$this->userID;
            $data["pcIp"]= $_SERVER['REMOTE_ADDR'];
            $result = DBConnect::execute(
                "INSERT INTO audit (            
            userID,
            customerID,
            problemID,
            pageID,            
            oldValues,
            newValues,
            pcIp ,
            action,
            createAt       
          )
          VALUES
            (              
              :userID,
              :customerID,
              :problemID,
              :pageID,             
              :oldValues,
              :newValues,
              :pcIp,
              :action,
              :createAt
            )",
                $data
            );

            if ($result)
                return $this->success();            
        }
        return $this->fail(APIException::badRequest);
    }
    function delete()
    {
    }
}
