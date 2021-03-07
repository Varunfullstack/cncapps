<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
  
class CTCustomerType extends CTCNC
{
    const CONST_TYPES="types";
    /** @var DSForm */
    public $dsCustomerType;
    /** @var BUCustomerType */
    public $buCustomerType;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = MAINTENANCE_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(809);
        $this->buCustomerType = new BUCustomerType($this);
        $this->dsCustomerType = new DSForm($this);
        $this->dsCustomerType->copyColumnsFrom($this->buCustomerType->dbeCustomerType);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(MAINTENANCE_PERMISSION);
        switch ($this->getAction()) {
            case self::CONST_TYPES:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getAllTypes(),JSON_NUMERIC_CHECK);
                        break;                    
                    case 'POST':
                        echo json_encode($this->addType(),JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo json_encode($this->updateType(),JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo json_encode($this->deleteType(),JSON_NUMERIC_CHECK);
                        break;
                }
                exit;         
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
        $this->setPageTitle('Referral Types');
        $this->setTemplateFiles(
            array('CustomerTypeList' => 'CustomerTypeList.inc')
        );
        $this->template->parse(
            'CONTENTS',
            'CustomerTypeList',
            true
        );
        $this->loadReactScript('CustomerTypeComponent.js');
        $this->loadReactCSS('CustomerTypeComponent.css'); 
        $this->parsePage();
    }
    //-----------------new 
    function getAllTypes()
    {
        try {
            $dsCustomerType = new DataSet($this);
            $this->buCustomerType->getAll($dsCustomerType);
            $data = [];
            if ($dsCustomerType->rowCount() > 0) {
                while ($dsCustomerType->fetchNext()) {
                    $customerTypeID = $dsCustomerType->getValue(DBECustomerType::customerTypeID);
                    $canDelete = false;                    
                    if ($this->buCustomerType->canDelete($customerTypeID)) {
                        $canDelete = true;
                    }
                    $data[] =
                        array(
                            'id' => $customerTypeID,
                            'description'    => Controller::htmlDisplayText(
                                $dsCustomerType->getValue(DBECustomerType::description)
                            ),
                            'canDelete'      => $canDelete
                        );
                }
            }
            return $this->success($data);
        } catch (Exception $ex) {
            return $this->fail(APIException::badRequest, $ex->getMessage());
        }    
    }
    function addType(){
        try {
            $body = $this->getBody();
            if (!isset($body->description))
                return $this->fail(APIException::badRequest, "Missing type description");

            $dbeCustomerType = new DBECustomerType($this);
            if($dbeCustomerType->hasType($body->description))
            {
                return $this->fail(APIException::conflict,"Conflicted Name");
            }
            $dbeCustomerType->setValue(DBECustomerType::description, $body->description);
            $dbeCustomerType->insertRow();
            return $this->success();
        } catch (Exception $ex) {
            return $this->fail(APIException::badRequest, $ex->getMessage());
        }  
    }
    function deleteType()
    {
        try {
            $this->setMethodName('delete');
            $id=@$_REQUEST["id"];
            if(!isset($id))
                return $this->fail(APIException::notFound,"Not found");
            if (!$this->buCustomerType->deleteCustomerType($id)) {
                return $this->fail(APIException::conflict,'Cannot delete this row');
            }
            return $this->success();
            
        } catch (Exception $ex) {
        }
    }
    function updateType(){
        try {
            $body = $this->getBody();
            if (!isset($body->description))
                return $this->fail(APIException::badRequest, "Missing type description");

            $dbeCustomerType = new DBECustomerType($this);
            $dbeCustomerType->getRow($body->id);
            if(!$dbeCustomerType->rowCount)
                return $this->fail(APIException::notFound, "Not Found");
            if($dbeCustomerType->hasType($body->description,$body->id))
            {
                return $this->fail(APIException::conflict,"Conflicted Name");
            }
            $dbeCustomerType->setValue(DBECustomerType::description, $body->description);
            $dbeCustomerType->updateRow();
            return $this->success();
        } catch (Exception $ex) {
            return $this->fail(APIException::badRequest, $ex->getMessage());
        }  
    }
}
