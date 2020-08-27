<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemType.inc.php');
require_once($cfg['path_dbe'] . '/DBEStockcat.inc.php');

// Actions
class CTItemType extends CTCNC
{
    const SEARCH_BY_DESCRIPTION = "SEARCH_BY_DESCRIPTION";

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
            case 'top':
            case 'bottom':
            case 'down':
            case 'up':
                $this->changeOrder();
                echo json_encode(["status" => "ok"]);
                break;
            case 'delete':
                if (!$this->getParam('id')) {
                    http_response_code(400);
                    throw new Exception('ID is missing');
                }

                $DBEItemType = new DBEItemType($this);

                $DBEItemType->getRow($this->getParam('id'));

                if (!$DBEItemType->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $DBEItemType->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':

                if (!$this->getParam('id')) {
                    throw new Exception('ID is missing');
                }

                $DBEItemType = new DBEItemType($this);

                $DBEItemType->getRow($this->getParam('id'));

                if (!$DBEItemType->rowCount) {
                    http_response_code(404);
                    exit;
                }

                $DBEItemType->setValue(
                    DBEItemType::description,
                    $this->getParam('description')
                );
                $DBEItemType->setValue(
                    DBEItemType::stockcat,
                    $this->getParam('stockcat')
                );
                $DBEItemType->setValue(DBEItemType::active, !!json_decode($this->getParam('active')));
                $DBEItemType->setValue(DBEItemType::reoccurring, !!json_decode($this->getParam('reoccurring')));
                $DBEItemType->setValue(
                    DBEItemType::showInCustomerReview,
                    !!json_decode($this->getParam('showInCustomerReview'))
                );
                $DBEItemType->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $DBEItemType = new DBEItemType($this);

                $DBEItemType->setValue(
                    DBEItemType::description,
                    $this->getParam('description')
                );
                $DBEItemType->setValue(
                    DBEItemType::stockcat,
                    $this->getParam('stockcat')
                );

                $DBEItemType->setValue(DBEItemType::active, !!$this->getParam('active'));
                $DBEItemType->setValue(DBEItemType::reoccurring, !!$this->getParam('reoccurring'));
                $DBEItemType->setValue(
                    DBEItemType::showInCustomerReview,
                    !!json_decode($this->getParam('showInCustomerReview'))
                );
                $DBEItemType->setValue($DBEItemType::sortOrder, $DBEItemType->getNextSortOrder());
                $DBEItemType->insertRow();

                echo json_encode(
                    [
                        "id"                   => $DBEItemType->getValue(DBEItemType::itemTypeID),
                        "description"          => $DBEItemType->getValue(DBEItemType::description),
                        "active"               => $DBEItemType->getValue(DBEItemType::active),
                        "reoccurring"          => $DBEItemType->getValue(DBEItemType::reoccurring),
                        "stockcat"             => $DBEItemType->getValue(DBEItemType::stockcat),
                        "showInCustomerReview" => $DBEItemType->getValue(DBEItemType::showInCustomerReview),
                        "sortOrder"            => $DBEItemType->getValue(DBEItemType::sortOrder)
                    ],
                    JSON_NUMERIC_CHECK
                );

                break;
            case 'getStockCat':
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
                echo json_encode($data, JSON_NUMERIC_CHECK);
                break;
            case 'getData':
                $DBEItemTypes = new DBEItemType($this);

                $DBEItemTypes->getRows(DBEItemType::sortOrder);
                $data = [];
                while ($DBEItemTypes->fetchNext()) {
                    $data[] = [
                        "id"                   => $DBEItemTypes->getValue(DBEItemType::itemTypeID),
                        "description"          => $DBEItemTypes->getValue(DBEItemType::description),
                        "active"               => $DBEItemTypes->getValue(DBEItemType::active),
                        "reoccurring"          => $DBEItemTypes->getValue(DBEItemType::reoccurring),
                        "stockcat"             => $DBEItemTypes->getValue(DBEItemType::stockcat),
                        "showInCustomerReview" => $DBEItemTypes->getValue(DBEItemType::showInCustomerReview),
                        "sortOrder"            => $DBEItemTypes->getValue(DBEItemType::sortOrder)
                    ];
                }
                echo json_encode($data, JSON_NUMERIC_CHECK);
                break;
            case self::SEARCH_BY_DESCRIPTION:
                $data = $this->getJSONData();
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

    function changeOrder()
    {
        if (!$this->getParam('itemTypeID')) {
            return;
        }
        $dbeItemType = new DBEItemType($this);
        switch ($this->action) {
            case 'top':
                $dbeItemType->moveItemToTop($this->getParam('itemTypeID'));
                break;
            case 'bottom':
                $dbeItemType->moveItemToBottom($this->getParam('itemTypeID'));
                break;
            case 'down':
                $dbeItemType->moveItemDown($this->getParam('itemTypeID'));
                break;
            case 'up':
                $dbeItemType->moveItemUp($this->getParam('itemTypeID'));
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

        $URLDeleteItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'delete'
            ]
        );

        $URLUpdateItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'update'
            ]
        );

        $URLCreateItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'create'
            ]
        );

        $URLGetData = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'getData'
            ]
        );
        $this->template->setVar(
            [
                "URLDeleteItem" => $URLDeleteItem,
                "URLUpdateItem" => $URLUpdateItem,
                "URLAddItem"    => $URLCreateItem,
                "URLGetData"    => $URLGetData
            ]
        );

        $this->parsePage();
    }

    function update()
    {
        $this->defaultAction();
    }
}// end of class
