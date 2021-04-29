<?php

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerLeadStatus.php');

class CTLeadStatusTypes extends CTCNC
{
    const CONST_LEAD_STATUS_TYPES="leadStatusTypes";

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
        $roles = MAINTENANCE_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(811);

    }

    function delete()
    {
        $this->defaultAction();
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     * @throws Exception
     */
    function defaultAction()
    {
        
        
        switch ($this->getAction()) {
            case self::CONST_LEAD_STATUS_TYPES:
                switch($this->requestMethod)
                {
                    case 'GET':
                        echo json_encode($this->getTypes(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo json_encode($this->addType());                
                        break;
                    case 'PUT':
                        echo json_encode($this->updateType());
                        break;
                    case 'DELETE':
                        echo json_encode($this->deleteType());
                        break;
                }
                exit;                  
            default:
                $this->displayForm();
                break;
        }
    }
 

    /**
     * Export expenses that have not previously been exported
     * @access private
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function displayForm()
    {
        $this->setPageTitle('Lead Status');
        $this->setTemplateFiles(
            'LeadStatus',
            'LeadStatusTypes'
        );

        $this->template->parse(
            'CONTENTS',
            'LeadStatus',
            true
        );
        $this->loadReactScript('LeadStatusTypesComponent.js');
        $this->loadReactCSS('LeadStatusTypesComponent.css'); 
        $this->parsePage();
    }

    function update()
    {
        $this->defaultAction();
    }
    //-----------------new
    function getTypes()
    {
        $dbeCustomerLeadStatus = new DBECustomerLeadStatus($this);
        $dbeCustomerLeadStatus->getRows(DBECustomerLeadStatus::sortOrder);
        $data = [];
        while ($dbeCustomerLeadStatus->fetchNext()) {
            $data[] = [
                "id"             => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::id),
                "name"           => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::name),
                "appearOnScreen" => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::appearOnScreen),
                "sortOrder"      => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::sortOrder)
            ];
        }
        return $data;
    }
    function addType(){
        try {
            $body = $this->getBody();
            if (!isset($body->name))
            return $this->fail(APIException::badRequest,"Type Name Required");       

            $dBECustomerLeadStatus = new DBECustomerLeadStatus($this);
            if ($dBECustomerLeadStatus->hasName($body->name))                
                return $this->fail(APIException::conflict,"conflicted name");       

            else $dBECustomerLeadStatus = new DBECustomerLeadStatus($this);

            $dBECustomerLeadStatus->setValue(
                DBECustomerLeadStatus::name,
                $body->name
            );
            $dBECustomerLeadStatus->setValue(
                DBECustomerLeadStatus::appearOnScreen,
                $body->appearOnScreen ? 1 : 0
            );
            $dBECustomerLeadStatus->setValue(
                DBECustomerLeadStatus::sortOrder,
                $dBECustomerLeadStatus->getNextSortOrder()
            );

            $dBECustomerLeadStatus->insertRow();
            return $this->success();
        } catch (Exception $ex) {
            return $this->fail(APIException::badRequest,$ex->getMessage());       
        }
    }
    function updateType(){
        try
        {
            $body = $this->getBody();
            if (!isset($body->name))
                return ["status" => false, 'error' => "Type Name required"];

            $dBECustomerLeadStatus = new DBECustomerLeadStatus($this);
            $dBECustomerLeadStatus->getRow($body->id);
            if (!$dBECustomerLeadStatus->rowCount) {
                return $this->fail(APIException::notFound,"Not Found");
            }
            $obj=$dBECustomerLeadStatus->hasName($body->name);
            if (isset($obj["id"])&&$obj["id"]!=$body->id)
            {
                return $this->fail(APIException::conflict,"conflicted name");       
            }

            $dBECustomerLeadStatus->setValue(
                DBECustomerLeadStatus::name,
                $body->name
            );
            $dBECustomerLeadStatus->setValue(
                DBECustomerLeadStatus::appearOnScreen,
                $body->appearOnScreen ? 1 : 0
            );
            $dBECustomerLeadStatus->setValue(
                DBECustomerLeadStatus::sortOrder,
                $body->sortOrder
            );
            $dBECustomerLeadStatus->updateRow();
            //$dBECustomerLeadStatus->insertRow();
            return $this->success();
        }
        catch (Exception $ex) {
            return $this->fail(APIException::badRequest,$ex->getMessage());       
        }
    }
    function deleteType(){
        $id=@$_REQUEST["id"];
        if(!isset($id))
            return $this->fail(APIException::badRequest,"id required");       
        $dBECustomerLeadStatus = new DBECustomerLeadStatus($this);
        $dBECustomerLeadStatus->getRow($id);
        if (!$dBECustomerLeadStatus->rowCount) {
            return $this->fail(APIException::notFound,"Type not found");
        }
        $dBECustomerLeadStatus->deleteRow();
        return $this->success();
    }
}
