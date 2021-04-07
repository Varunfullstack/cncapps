<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemType.inc.php');
require_once($cfg['path_dbe'] . '/DBEStockcat.inc.php');

// Actions
class CTItemType extends CTCNC
{
    const SEARCH_BY_DESCRIPTION = "SEARCH_BY_DESCRIPTION";
    const CONST_ITEM_TYPES = "itemTypes";
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
        $this->setMenuId(805);
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
            case self::CONST_ITEM_TYPES:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getItemTypes(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->addItemType(),JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo  json_encode($this->updateItemType(),JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteItemType(),JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                exit;                 
            case 'getStockCat':               
                echo json_encode($this->getStockCat(), JSON_NUMERIC_CHECK);
                break;
            case 'getData':
                $DBEItemTypes = new DBEItemType($this);
                $DBEItemTypes->getRows(DBEItemType::sortOrder);
                $data = [];
                while ($DBEItemTypes->fetchNext()) {
                    $data[] = [
                        "id"                     => $DBEItemTypes->getValue(DBEItemType::itemTypeID),
                        "description"            => $DBEItemTypes->getValue(DBEItemType::description),
                        "active"                 => $DBEItemTypes->getValue(DBEItemType::active),
                        "reoccurring"            => $DBEItemTypes->getValue(DBEItemType::reoccurring),
                        "stockcat"               => $DBEItemTypes->getValue(DBEItemType::stockcat),
                        "showInCustomerReview"   => $DBEItemTypes->getValue(DBEItemType::showInCustomerReview),
                        "sortOrder"              => $DBEItemTypes->getValue(DBEItemType::sortOrder),
                        "allowGlobalPriceUpdate" => $DBEItemTypes->getValue(DBEItemType::allowGlobalPriceUpdate),
                    ];
                }
                echo json_encode($data, JSON_NUMERIC_CHECK);
                break;
            case self::SEARCH_BY_DESCRIPTION:
                $data        = $this->getJSONData();
                $description = "";
                if (!empty($data['description'])) {
                    $description = $data['description'];
                }
                $DBEItemTypes = new DBEItemType($this);
                $DBEItemTypes->getRowsByDescription($description);
                $toReturnData = [];
                while ($DBEItemTypes->fetchNext()) {
                    $toReturnData[] = $DBEItemTypes->getRowAsAssocArray();
                }
                echo json_encode(["status" => "ok", "data" => $toReturnData]);
                break;
            case 'displayForm':
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
        $this->setPageTitle('Item Types');
        $this->setTemplateFiles(
            'ItemType',
            'ItemTypes'
        );
        $this->template->parse(
            'CONTENTS',
            'ItemType',
            true
        );
        $this->loadReactScript('ItemTypeComponent.js');
        $this->loadReactCSS('ItemTypeComponent.css'); 
        $this->parsePage();       
    }

   
    //--------------------new 
    function getItemTypes()
    {
        $DBEItemTypes = new DBEItemType($this);
        $DBEItemTypes->getRows(DBEItemType::sortOrder);
        $data = [];
        while ($DBEItemTypes->fetchNext()) {
            $data[] = [
                "id"                     => $DBEItemTypes->getValue(DBEItemType::itemTypeID),
                "description"            => $DBEItemTypes->getValue(DBEItemType::description),
                "active"                 => $DBEItemTypes->getValue(DBEItemType::active),
                "reoccurring"            => $DBEItemTypes->getValue(DBEItemType::reoccurring),
                "stockcat"               => $DBEItemTypes->getValue(DBEItemType::stockcat),
                "showInCustomerReview"   => $DBEItemTypes->getValue(DBEItemType::showInCustomerReview),
                "sortOrder"              => $DBEItemTypes->getValue(DBEItemType::sortOrder),
                "allowGlobalPriceUpdate" => $DBEItemTypes->getValue(DBEItemType::allowGlobalPriceUpdate),
            ];
        }
       return $this->success($data);
    }

    function addItemType(){
        $body=$this->getBody();
        
        $DBEItemType = new DBEItemType($this);
        $DBEItemType->setValue(
            DBEItemType::description,
            $body->description
        );
        $DBEItemType->setValue(
            DBEItemType::stockcat,
            $body->stockcat
        );
        $DBEItemType->setValue(DBEItemType::active, $body->active);
        $DBEItemType->setValue(DBEItemType::reoccurring, $body->reoccurring);
        $DBEItemType->setValue(
            DBEItemType::allowGlobalPriceUpdate,
            $body->allowGlobalPriceUpdate
        );
        $DBEItemType->setValue(
            DBEItemType::showInCustomerReview,
            $body->showInCustomerReview
        );
        $DBEItemType->setValue($DBEItemType::sortOrder, $DBEItemType->getNextSortOrder());
        $DBEItemType->insertRow();
        return $this->success();
    }

    function updateItemType(){
        $body =$this->getBody();
        if(!isset($body->id))
            return $this->fail(APIException::badRequest,"Bad Request");

        $DBEItemType = new DBEItemType($this);
        $DBEItemType->getRow($body->id);

        if (!$DBEItemType->rowCount)             
            return $this->fail(APIException::notFound,"Not Found");

        $DBEItemType->setValue(
            DBEItemType::description,
            $body->description
        );
        $DBEItemType->setValue(
            DBEItemType::stockcat,
            $body->stockcat
        );
        $DBEItemType->setValue(DBEItemType::active, $body->active);
        $DBEItemType->setValue(DBEItemType::reoccurring,  $body->reoccurring);
        $DBEItemType->setValue(
            DBEItemType::allowGlobalPriceUpdate,
             $body->allowGlobalPriceUpdate
        );
        $DBEItemType->setValue(
            DBEItemType::showInCustomerReview,
            $body->showInCustomerReview
        );
        $DBEItemType->setValue(
            DBEItemType::sortOrder,
            $body->sortOrder
        );
        $DBEItemType->updateRow();
        return $this->success();        
    }
    function deleteItemType(){
        $id=@$_REQUEST['id'];
        
        if (!$id) 
            return $this->fail(APIException::notFound, "Id is Missing");

        $DBEItemType = new DBEItemType($this);
        $DBEItemType->getRow($id);
        if (!$DBEItemType->rowCount) {
            return $this->fail(APIException::notFound, "Not Found");
        }
        $DBEItemType->deleteRow();
        return $this->success();
    }
    function getStockCat()
    {
        $dbeStockCat = new DBEStockcat($this);
        $dbeStockCat->getRows(DBEStockcat::stockcat);
        $data = [];
        while ($dbeStockCat->fetchNext()) {
            $data[] = [
                "stockcat"      => $dbeStockCat->getValue(DBEStockcat::stockcat),
                "description"   => $dbeStockCat->getValue(DBEStockcat::description),
                "salNom"        => $dbeStockCat->getValue(DBEStockcat::salNom),
                "purCust"       => $dbeStockCat->getValue(DBEStockcat::purCust),
                "purSalesStk"   => $dbeStockCat->getValue(DBEStockcat::purSalesStk),
                "purMaintStk"   => $dbeStockCat->getValue(DBEStockcat::purMaintStk),
                "purAsset"      => $dbeStockCat->getValue(DBEStockcat::purAsset),
                "purOper"       => $dbeStockCat->getValue(DBEStockcat::purOper),
                "serialReqFlag" => $dbeStockCat->getValue(DBEStockcat::serialReqFlag),
                "postMovement"  => $dbeStockCat->getValue(DBEStockcat::postMovement),
            ];
        }
        return   $data ;
    }
}// end of class
