<?php
/**
 * Password service controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPasswordService.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define(
    'CTPASSWORDSERVICE_ACT_DISPLAY_LIST',
    'passwordServiceList'
);

class CTPasswordServices extends CTCNC
{
    public $dsPasswordService;
    /** @var BUPasswordService */
    public $buPasswordService;
    const CONST_PASSWORD_SERVICES = "passwordServices";
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
        $this->setMenuId(222);
        $this->buPasswordService = new BUPasswordService($this);
        $this->dsPasswordService = new DSForm($this);
        $this->dsPasswordService->copyColumnsFrom($this->buPasswordService->dbePasswordService);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(MAINTENANCE_PERMISSION);

        switch ($this->getAction()) {
            case self::CONST_PASSWORD_SERVICES:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getPasswordServices(),JSON_NUMERIC_CHECK);
                        break;                   
                    case 'PUT':
                        echo  json_encode($this->updatePasswordService(),JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deletePasswordService(),JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                exit;    
            case CTPASSWORDSERVICE_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Password service');
        
        $this->setTemplateFiles(
            'PasswordServiceList',
            'PasswordServiceList.inc'
        );
        $this->template->parse(
            'CONTENTS',
            'PasswordServiceList',
            true
        );
        $this->loadReactScript('PasswordServicesComponent.js');
        $this->loadReactCSS('PasswordServicesComponent.css'); 
        $this->parsePage(); 
    }
    //------------------------new
    function getPasswordServices(){
        
        $dbePasswordService = new DBEPasswordService($this);
        $dbePasswordService->getRows(DBEPasswordService::sortOrder);       
        $data = [];
        while ($dbePasswordService->fetchNext()) {
            $passwordServiceID = $dbePasswordService->getValue(DBEPasswordService::passwordServiceID);
            $data []= array(
                    'passwordServiceID' => $passwordServiceID,
                    'description'       => Controller::htmlDisplayText(
                        $dbePasswordService->getValue(DBEPasswordService::description)
                    ),
                    'onePerCustomer'    => $dbePasswordService->getValue(DBEPasswordService::onePerCustomer) 
                    ,
                    'defaultLevel'      => $dbePasswordService->getValue(DBEPasswordService::defaultLevel),
                    'sortOrder'         => $dbePasswordService->getValue(DBEPasswordService::sortOrder),
                );          
        }
      return $this->success($data);
    }
    function updatePasswordService(){
        $this->setMethodName('update');
        $body=$this->getBody(true);
        $this->formError = (!$this->dsPasswordService->populateFromArray(["body"=>$body]));
        if ($this->formError) {
            return $this->fail(APIException::badRequest,"Bad Request");            
        }
        $this->buPasswordService->updatePasswordService($this->dsPasswordService);
        return $this->success();
    }
    function deletePasswordService(){
        $this->setMethodName('delete');
        try {
            $passwordServiceID=$this->getParam('passwordServiceID');
            $this->buPasswordService->deletePasswordService($passwordServiceID);
            return $this->success();
        } catch (Exception $exception) {
            return $this->fail(APIException::conflict,"Password service can't be delete.");
        }
    }
}
