<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

class CTCreateSalesRequest extends CTCNC
{
    const CONST_SALES_REQUEST='salesRequest';
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
        $roles = [
            SALES_PERMISSION,
        ];
        $this->setMenuId(305);
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
            case self::CONST_SALES_REQUEST:
                switch ($this->requestMethod) {                    
                    case 'POST':
                        echo  json_encode($this->createSalesRequest(),JSON_NUMERIC_CHECK);
                        break;
                   
                }
                exit;
            default:
                $this->showPage();
                break;
        }
    }

    function createSalesRequest()
    {
        $body=json_decode($this->getParam("data"));
        
        if (!isset($body->customerId)) {
            return $this->fail(APIException::badRequest,"Customer ID is missing");            
        }
        if (!isset($body->message)) {
            return $this->fail(APIException::badRequest,"Message is missing");            
        }        
        if (!isset($body->type)) {
            return $this->fail(APIException::badRequest,"Type is missing");                        
        }        
         $files = @$_FILES['file']??null;        
        $buActivity = new BUActivity($this);
        $buActivity->sendSalesRequest(null, $body->message, $body->type, true, $body->customerId, $files );
        return $this->success(count($files));
    }

    /**
     * @throws Exception
     */
    function showPage()
    {
        $this->setTemplateFiles(
            'CreateSalesRequest',
            'CreateSalesRequest'
        );

        $this->setPageTitle('Create Sales Request');
        $this->loadReactScript('SalesRequestComponent.js');
        $this->loadReactCSS('SalesRequestComponent.css');     
        $this->template->parse(
            'CONTENTS',
            'CreateSalesRequest',
            true
        );
        $this->parsePage();


    }
}