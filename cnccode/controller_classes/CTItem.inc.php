<?php
/**
 * Item controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\ChildItem\ChildItemRepository;
use CNCLTD\Data\DBConnect;
use CNCLTD\Data\DBEItem;
use CNCLTD\Exceptions\APIException;
use CNCLTD\Exceptions\JsonHttpException;

global $cfg;
require_once($cfg['path_bu'] . '/BUItem.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEWarranty.inc.php');
require_once($cfg['path_dbe'] . '/DBERenewalType.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemBillingCategory.php');
require_once($cfg['path_func'] . '/Common.inc.php');
// Messages
define(
    'CTITEM_MSG_NONE_FND',
    'No items found'
);
define(
    'CTITEM_MSG_ITEM_NOT_FND',
    'Item not found'
);
define(
    'CTITEM_MSG_ITEMID_NOT_PASSED',
    'ItemID not passed'
);
define(
    'CTITEM_MSG_ITEM_ARRAY_NOT_PASSED',
    'Item array not passed'
);
// Actions
define(
    'CTITEM_ACT_ITEM_INSERT',
    'insertItem'
);
define(
    'CTITEM_ACT_ITEM_UPDATE',
    'updateItem'
);
// Page text
define(
    'CTITEM_TXT_NEW_ITEM',
    'Create Item'
);
define(
    'CTITEM_TXT_UPDATE_ITEM',
    'Update Item'
);

class CTItem extends CTCNC
{
    const ADD_CHILD_ITEM                           = "ADD_CHILD_ITEM";
    const REMOVE_CHILD_ITEM                        = "REMOVE_CHILD_ITEM";
    const GET_CHILD_ITEMS                          = "GET_CHILD_ITEMS";
    const GET_PARENT_ITEMS                         = "GET_PARENT_ITEMS";
    const SEARCH_ITEMS                             = "SEARCH_ITEMS";
    const CHECK_ITEM_RECURRING                     = "CHECK_ITEM_RECURRING";
    const DATA_TABLE_GET_DATA                      = "DATA_TABLE_GET_DATA";
    const SEARCH_ITEMS_JSON                        = "SEARCH_ITEMS_JSON";
    const GET_ITEM                                 = 'GET_ITEM';
    const UPDATE_CONTRACTS_PRICE                   = 'updateContractsPrice';
    const UPDATE_CHILD_ITEM_QUANTITY               = 'UPDATE_CHILD_ITEM_QUANTITY';
    const CONST_ITEMS                              = 'items';
    const CONST_WARRANTY                           = "warranty";
    const CONST_RENEWAL_TYPES                      = 'renewalTypes';
    const CONST_ITEM_BILLING_CATEGORY              = 'itemBillingCategory';
    const CONST_CHILD_ITEMS                        = 'childItems';
    const CONST_SALESSTOCK_QTY                     = 'salesStockQty';
    const ITEM_ID_IS_MANDATORY_ERROR_MESSAGE       = 'Item Id is mandatory';
    const CHILD_ITEM_ID_IS_MANDATORY_ERROR_MESSAGE = 'child item id is mandatory';
    const UPDATE_ITEM_FROM_SALES_ORDER             = 'updateItemFromSalesOrder';
    /** @var DSForm */
    public $dsItem;
    /**
     * @var BUItem
     */
    private $buItem;

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
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buItem = new BUItem($this);
        $this->dsItem = new DSForm($this);    // new specialised dataset with form message support
        $this->dsItem->copyColumnsFrom($this->buItem->dbeItem);
        $this->setMenuId(304);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::UPDATE_ITEM_FROM_SALES_ORDER:
                echo json_encode($this->updateItemFromSalesOrder());
                exit;
            case self::CONST_ITEMS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getItems(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo json_encode($this->addItem());
                        break;
                    case 'PUT':
                        echo json_encode($this->updateItem());
                        break;
                    // case 'DELETE':
                    //     echo json_encode($this->deleteProjectIssue());
                    //     break;
                }
                exit;
            case self::CONST_CHILD_ITEMS:
                switch ($this->requestMethod) {
                    case 'GET':
                    case 'POST':
                        echo json_encode($this->updateChildItems());
                        break;
                }
                break;
            case self::CONST_SALESSTOCK_QTY:
                echo json_encode($this->updateSalesStockQty());
                break;
            case self::CONST_WARRANTY:
                echo json_encode($this->getWarranties(), JSON_NUMERIC_CHECK);
                break;
            case self::CONST_RENEWAL_TYPES:
                echo json_encode($this->getRenewalTypes(), JSON_NUMERIC_CHECK);
                break;
            case self::CONST_ITEM_BILLING_CATEGORY:
                echo json_encode($this->getItemBillingCategory(), JSON_NUMERIC_CHECK);
                break;
            case self::ADD_CHILD_ITEM:
                $data = json_decode(file_get_contents('php://input'), true);
                if (!key_exists('itemId', $data) || !isset($data['itemId'])) {
                    throw new JsonHttpException(400, self::ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                if (!key_exists('childItemId', $data) || !isset($data['childItemId'])) {
                    throw new JsonHttpException(400, self::CHILD_ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                $this->addChildItem($data['itemId'], $data['childItemId']);
                $dbeItem = new DBEItem($this);
                $dbeItem->getRow($data['childItemId']);
                echo json_encode(["status" => "ok", "childItem" => $dbeItem->getRowAsAssocArray()]);
                break;
            case self::REMOVE_CHILD_ITEM:
                $data = json_decode(file_get_contents('php://input'), true);
                if (!key_exists('itemId', $data) || !isset($data['itemId'])) {
                    throw new JsonHttpException(400, self::ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                if (!key_exists('childItemId', $data) || !isset($data['childItemId'])) {
                    throw new JsonHttpException(400, self::CHILD_ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                $this->removeChildItem($data['itemId'], $data['childItemId']);
                echo json_encode(["status" => "ok"]);
                break;
            case self::UPDATE_CONTRACTS_PRICE:
                $data = $this->getJSONData();
                $type = @$data['type'];
                if (!$type) {
                    throw new JsonHttpException(400, 'type is required');
                }
                $value  = @$data['value'];
                $itemId = @$data['itemId'];
                if (!$itemId) {
                    throw new JsonHttpException(400, 'itemId is required');
                }
                global $db;
                $costQuery = "UPDATE
  custitem
  LEFT JOIN customer
    ON custitem.`cui_custno` = customer.`cus_custno` SET custitem.`costPricePerMonth` = ?, custitem.`cui_cost_price` = ? * custitem.`cui_users` * 12
WHERE custitem.`cui_itemno` = ?
  AND renewalStatus <> 'D'
  AND declinedFlag <> 'Y'
  AND not customer.`isReferred` ";
                $saleQuery = "UPDATE
  custitem
  LEFT JOIN customer
    ON custitem.`cui_custno` = customer.`cus_custno` SET custitem.`salePricePerMonth` = ?, custitem.`cui_sale_price` = ? * custitem.`cui_users` * 12
WHERE custitem.`cui_itemno` = ?
  AND renewalStatus <> 'D'
  AND declinedFlag <> 'Y'
  AND not customer.`isReferred`";
                $query     = $costQuery;
                $item      = new DBEItem($this);
                $item->getRow($itemId);
                $column = DBEItem::curUnitCost;
                if ($type == 'sale') {
                    $query  = $saleQuery;
                    $column = DBEItem::curUnitSale;
                }
                $oldPrice = $item->getValue($column);
                $item->setValue($column, $value);
                $item->updateRow();
                $db->preparedQuery(
                    $query,
                    [
                        [
                            "type"  => "d",
                            "value" => $value
                        ],
                        [
                            "type"  => "d",
                            "value" => $value
                        ],
                        [
                            "type"  => "i",
                            "value" => $itemId
                        ]
                    ]
                );
                $buMail = new BUMail($this);
                global $twig;
                $body      = $twig->render(
                    '@internal/ContractPricingChangedEmail.html.twig',
                    [
                        "oldPrice"        => $oldPrice,
                        "newPrice"        => $value,
                        "type"            => $type,
                        "itemDescription" => $item->getValue(DBEItem::description),
                        "engineerName"    => $this->dbeUser->getValue(DBEUser::name)
                    ]
                );
                $subject   = "Global Price Update Performed";
                $recipient = "sales@" . CONFIG_PUBLIC_DOMAIN;
                $buMail->sendSimpleEmail(
                    $body,
                    $subject,
                    $recipient,
                );
                echo json_encode(["status" => "ok"]);
                exit;
            case self::GET_CHILD_ITEMS:
                $parentItemId = $this->getParam('itemId');
                if (!$parentItemId) {
                    throw new JsonHttpException(400, self::ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                global $db;
                $repo = new ChildItemRepository($db);
                echo json_encode(["status" => "ok", "data" => $repo->getChildItemsForItem($parentItemId)]);
                break;
            case self::GET_PARENT_ITEMS:
                if (!$this->getParam('itemId')) {
                    throw new JsonHttpException(400, self::ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                $dbeItem = new DBEItem($this);
                $dbeItem->getParentItems($this->getParam('itemId'));
                $rows = [];
                while ($dbeItem->fetchNext()) {
                    $rows[] = $dbeItem->getRowAsAssocArray();
                }
                echo json_encode(["status" => "ok", "data" => $rows]);
                break;
            case self::GET_ITEM:
                if (!$this->getParam('itemId')) {
                    throw new JsonHttpException(400, self::ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                $dbeItem = new DBEItem($this);
                if (!$dbeItem->getRow($this->getParam('itemId'))) {
                    throw new JsonHttpException(404, 'Item Not Found');
                }
                echo json_encode(
                    [
                        "status" => "ok",
                        "data"   => $dbeItem->getRowAsAssocArray(),
                    ]
                );
                break;
            case self::SEARCH_ITEMS_JSON:
                $data  = self::getJSONData();
                $term  = '';
                $limit = null;
                if (!empty($data['term'])) {
                    $term = $data['term'];
                }
                if (!empty($data['limit'])) {
                    $limit = $data['limit'];
                }
                $this->setParam('term', $term);
                $this->setParam('limit', $limit);
            case self::SEARCH_ITEMS:
                $dbeItem = new DBEItem($this);
                $dbeItem->getRowsByDescriptionOrPartNoSearch($this->getParam('term'), null, $this->getParam('limit'));
                $rows = [];
                while ($dbeItem->fetchNext()) {
                    $rows[] = $dbeItem->getRowAsAssocArray();
                }
                echo json_encode(["status" => "ok", "data" => $rows]);
                break;
            case self::DATA_TABLE_GET_DATA:
            case 'getData':
                $dbeItem           = new DBEItem($this);
                $dbeItemType       = new DBEItemType($this);
                $dbeManufacturer   = new DBEManufacturer($this);
                $draw              = $_REQUEST['draw'];
                $columns           = $_REQUEST['columns'];
                $order             = $_REQUEST['order'];
                $offset            = $_REQUEST['start'];
                $limit             = $_REQUEST['length'];
                $columnsNames      = [
                    "description",
                    "costPrice",
                    "salePrice",
                    "partNumber",
                    "itemCategory",
                    "renewalType",
                    "manufacturer",
                    "discontinued",
                    'renewalTypeID'
                ];
                $columnsDefinition = [
                    "description"   => 'item.itm_desc',
                    "costPrice"     => 'item.itm_sstk_cost',
                    "salePrice"     => 'item.itm_sstk_price',
                    "partNumber"    => 'item.itm_unit_of_sale',
                    "itemCategory"  => 'itemtype.ity_desc',
                    "renewalType"   => 'renewalTypeID',
                    "manufacturer"  => 'man_name',
                    "discontinued"  => 'item.itm_discontinued',
                    "renewalTypeId" => 'renewalTypeID'
                ];
                $columnsTypes      = [
                    "description"   => 'like',
                    "costPrice"     => 'like',
                    "salePrice"     => 'like',
                    "partNumber"    => 'like',
                    "itemCategory"  => 'like',
                    "renewalType"   => 'like',
                    "renewalTypeId" => 'explicitInt',
                    "manufacturer"  => 'like',
                    "discontinued"  => 'explicitString'
                ];
                /** @var dbSweetcode $db */ global $db;
                $countQuery       = "select count(*) FROM {$dbeItem->getTableName()}
         left join {$dbeItemType->getTableName()} on {$dbeItem->getDBColumnName(DBEItem::itemTypeID)} = {$dbeItemType->getDBColumnName(DBEItemType::itemTypeID)}
         left join {$dbeManufacturer->getTableName()} on {$dbeItem->getDBColumnName(DBEItem::manufacturerID)} = {$dbeManufacturer->getDBColumnName(DBEManufacturer::manufacturerID)}";
                $totalCountResult = $db->query($countQuery);
                $totalCount       = $totalCountResult->fetch_row()[0];
                $defaultQuery     = "select 
                    {$dbeItem->getDBColumnName(DBEItem::itemID)} as 'id',
                    {$dbeItem->getDBColumnName(DBEItem::description)} as 'description',
                    {$dbeItem->getDBColumnName(DBEItem::curUnitCost)} as 'costPrice',
                    {$dbeItem->getDBColumnName(DBEItem::curUnitSale)} as 'salePrice',
                    {$dbeItem->getDBColumnName(DBEItem::partNo)} as 'partNumber',
                    {$dbeItemType->getDBColumnName(DBEItemType::description)} as 'itemCategory',
                    case {$dbeItem->getDBColumnName(DBEItem::renewalTypeID)}
                        when 1 then 'Broadband'
    when 2 then 'Renewals'
    when 3 then 'Quotation'
    when 4 then 'Domain'
    when 5 then 'Hosting'
    end as 'renewalType',
       {$dbeItem->getDBColumnName(DBEItem::renewalTypeID)} as renewalTypeId,
                    {$dbeManufacturer->getDBColumnName(DBEManufacturer::name)} as 'manufacturer',
                    {$dbeItem->getDBColumnName(DBEItem::discontinuedFlag)} as 'discontinued'
                FROM {$dbeItem->getTableName()}
         left join {$dbeItemType->getTableName()} on {$dbeItem->getDBColumnName(DBEItem::itemTypeID)} = {$dbeItemType->getDBColumnName(DBEItemType::itemTypeID)}
         left join {$dbeManufacturer->getTableName()} on {$dbeItem->getDBColumnName(DBEItem::manufacturerID)} = {$dbeManufacturer->getDBColumnName(DBEManufacturer::manufacturerID)} where 1 ";
                $columnSearch     = [];
                $parameters       = [];
                foreach ($columns as $column) {
                    if (!isset($columnsDefinition[$column['data']])) {
                        continue;
                    }
                    if ($column['search']['value']) {
                        switch ($columnsTypes[$column['data']]) {
                            case 'explicitInt':
                                $columnSearch[] = $columnsDefinition[$column['data']] . " = ?";
                                $parameters[]   = [
                                    "type"  => "i",
                                    "value" => $column['search']['value']
                                ];
                                break;
                            case 'explicitString':
                                $columnSearch[] = $columnsDefinition[$column['data']] . " = ?";
                                $parameters[]   = [
                                    "type"  => "s",
                                    "value" => $column['search']['value']
                                ];
                                break;
                            case 'like':
                                $columnSearch[] = $columnsDefinition[$column['data']] . " like ?";
                                $parameters[]   = [
                                    "type"  => "s",
                                    "value" => "%" . $column['search']['value'] . "%"
                                ];
                                break;
                            default:

                        }
                    }
                }
                if (count($columnSearch)) {
                    $wherePart    = " and " . implode(" and ", $columnSearch);
                    $defaultQuery .= $wherePart;
                    $countQuery   .= $wherePart;
                }
                $orderBy = [];
                if (count($order)) {
                    foreach ($order as $orderItem) {
                        if (!isset($columnsNames[(int)$orderItem['column']])) {
                            continue;
                        }
                        $orderBy[] = $columnsDefinition[$columnsNames[(int)$orderItem['column']]] . " " . mysqli_real_escape_string(
                                $db->link_id(),
                                $orderItem['dir']
                            );
                    }
                    if (count($orderBy)) {
                        $defaultQuery .= (" order by " . implode(' , ', $orderBy));
                    }
                }
                $countResult   = $db->preparedQuery(
                    $countQuery,
                    $parameters
                );
                $filteredCount = $countResult->fetch_row()[0];
                $defaultQuery  .= " limit ?,?";
                $parameters[]  = ["type" => "i", "value" => $offset];
                $parameters[]  = ["type" => "i", "value" => $limit];
                $result        = $db->preparedQuery(
                    $defaultQuery,
                    $parameters
                );
                $data          = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(
                    [
                        "draw"            => +$draw,
                        "recordsTotal"    => +$totalCount,
                        "recordsFiltered" => $filteredCount,
                        "data"            => $data
                    ]
                );
                break;
            case self::CHECK_ITEM_RECURRING:
            {
                $data = json_decode(file_get_contents('php://input'), true);
                if (array_key_exists('itemId', $data) || !isset($data['itemId'])) {
                    throw new JsonHttpException(400, self::ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                $dbeItem = new DBEItem($this);
                $dbeItem->getRow($data['itemId']);
                $itemTypeId  = $dbeItem->getValue(DBEItem::itemTypeID);
                $dbeItemType = new DBEItemType($this);
                $dbeItemType->getRow($itemTypeId);
                echo json_encode(["status" => "ok", "data" => $dbeItemType->getValue(DBEItemType::reoccurring)]);
                break;
            }
            case self::UPDATE_CHILD_ITEM_QUANTITY:
            {
                $data = $this->getJSONData();
                if (!isset($data['parentItemId'])) {
                    throw new JsonHttpException(400, 'parentItemId is mandatory');
                }
                if (!isset($data['childItemId'])) {
                    throw new JsonHttpException(400, self::CHILD_ITEM_ID_IS_MANDATORY_ERROR_MESSAGE);
                }
                if (!isset($data['quantity'])) {
                    throw new JsonHttpException(400, 'Quantity should be 1 or more...');
                }
                $this->updateChildItemQuantity($data['parentItemId'], $data['childItemId'], $data['quantity']);
                echo json_encode(["status" => "ok"]);
                break;
            }
            default:
                $this->showItemList();
        }
    }

    function addChildItem($parentItemId, $childItemId)
    {
        global $db;
        $query = "insert ignore into childItem values(?,?, 1) ";
        $db->preparedQuery(
            $query,
            [
                [
                    "type"  => "i",
                    "value" => $parentItemId,
                ],
                [
                    "type"  => "i",
                    "value" => $childItemId,
                ],
            ]
        );
    }

    function removeChildItem($parentItemId, $childItemId)
    {
        global $db;
        $query = "delete from childItem where parentItemId = ? and childItemId = ?";
        $db->preparedQuery(
            $query,
            [
                [
                    "type"  => "i",
                    "value" => $parentItemId,
                ],
                [
                    "type"  => "i",
                    "value" => $childItemId,
                ],
            ]
        );
    }

    function showItemList()
    {
        $this->setTemplateFiles(
            'ItemList',
            'ItemList'
        );
        $this->setPageTitle('Items');
        $this->loadReactScript('ItemsComponent.js');
        $this->loadReactCSS('ItemsComponent.css');
        $this->template->parse('CONTENTS', 'ItemList');
        $this->parsePage();
    }

    private function updateChildItemQuantity($parentItemId, $childItemId, $quantity)
    {
        global $db;
        $repo = new ChildItemRepository($db);
        $repo->updateChildItemQuantity($parentItemId, $childItemId, $quantity);
    }

    function getItems()
    {
        $limit              = @$_REQUEST["limit"] ?? 50;
        $page               = @$_REQUEST["page"] ?? 1;
        $offset             = $limit * ($page - 1);
        $dbeItem            = new DBEItem($this);
        $dbeItemType        = new DBEItemType($this);
        $orderColumns       = [
            "description"   => "itm_desc",
            "costPrice"     => "itm_sstk_cost",
            "curUnitSale"   => "itm_sstk_price",
            "partNumber"    => "itm_unit_of_sale",
            "renewalTypeID" => "renewalTypeID",
            "discontinued"  => "itm_discontinued",
            "itemCategory"  => "ity_desc",
            "manufacturer"  => "man_name",
            "salesStockQty" => "itm_sstk_qty",
            "supplierName"  => "supplierName"
        ];
        $orderBy            = $orderColumns[(@$_REQUEST["orderBy"] ?? "description")];
        $orderDir           = @$_REQUEST["orderDir"] ?? 'asc';
        $q                  = '%' . (@$_REQUEST["q"] ?? "") . '%';
        $discontinuedFilter = "";
        if (isset($_REQUEST["discontinued"])) {
            if ($_REQUEST['discontinued'] == "true") {
                $discontinuedFilter = " and  item.itm_discontinued = 'Y'";
            } else {
                $discontinuedFilter = " and  item.itm_discontinued = 'N'";
            }
        }
        $query = "SELECT 
          
         itm_desc  as description , 
         itm_sstk_cost as curUnitCost,
         itm_sstk_price as curUnitSale,
         itm_unit_of_sale as partNo,
         renewalTypeID,
         itm_discontinued as discontinuedFlag,
         ity_desc as itemCategory,
         man_name as manufacturerName,  
         {$dbeItem->getDBColumnName($dbeItem::itemID)} as itemID,       
         {$dbeItem->getDBColumnName($dbeItem::itemTypeID)} as itemTypeID,
         {$dbeItem->getDBColumnName($dbeItem::warrantyID)} as warrantyID,
         {$dbeItem->getDBColumnName($dbeItem::partNoOld)} as partNoOld,
         {$dbeItem->getDBColumnName($dbeItem::serialNoFlag)} as serialNoFlag,
         {$dbeItem->getDBColumnName($dbeItem::discontinuedFlag)} as discontinuedFlag,
         {$dbeItem->getDBColumnName($dbeItem::servercareFlag)} as servercareFlag,
         {$dbeItem->getDBColumnName($dbeItem::renewalTypeID)} as renewalTypeID,
         {$dbeItem->getDBColumnName($dbeItem::allowDirectDebit)} as allowDirectDebit,
         {$dbeItem->getDBColumnName($dbeItem::itemBillingCategoryID)} as itemBillingCategoryID,
         {$dbeItem->getDBColumnName($dbeItem::contractResponseTime)} as contractResponseTime,
         {$dbeItem->getDBColumnName($dbeItem::allowSRLog)} as allowSRLog,
         {$dbeItem->getDBColumnName($dbeItem::isStreamOne)} as isStreamOne,
         {$dbeItem->getDBColumnName($dbeItem::excludeFromPOCompletion)} as excludeFromPOCompletion,
         {$dbeItem->getDBColumnName($dbeItem::manufacturerID)} as manufacturerID,
         {$dbeItem->getDBColumnName($dbeItem::notes)} as notes, 
         {$dbeItem->getDBColumnName($dbeItem::stockcat)} as stockcat,
         {$dbeItem->getDBColumnName($dbeItem::salesStockQty)} as salesStockQty,
         {$dbeItemType->getDBColumnName($dbeItemType::allowGlobalPriceUpdate)} as allowGlobalPriceUpdate,
         {$dbeItem->getDBColumnName($dbeItem::supplierId)} as supplierId,
         sup_name as supplierName
        FROM Item
        left JOIN itemtype on Item.itm_itemtypeno   = itemtype.ity_itemtypeno
        LEFT JOIN manufact on Item.itm_manno        = manufact.man_manno
        left join supplier on Item.supplierId       = supplier.sup_suppno
        where 
            (itm_desc            like :q OR
            itm_sstk_cost       like :q OR
            itm_sstk_price      like :q OR
            itm_unit_of_sale    like :q OR
            ity_desc            like :q OR
            man_name            like :q
            )
            $discontinuedFilter
        ORDER BY $orderBy $orderDir
        LIMIT $limit OFFSET $offset
        ";
        $data  = DBConnect::fetchAll($query, ['q' => $q]);
        return $this->success($data);
    }

    function getWarranties()
    {
        // Manufacturer selector
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows();
        $data = [];
        while ($dbeWarranty->fetchNext()) {
            $data [] = array(
                'name' => $dbeWarranty->getValue(DBEWarranty::description),
                'id'   => $dbeWarranty->getValue(DBEWarranty::warrantyID),
            );

        }
        return $this->success($data);
    }

    function getRenewalTypes()
    {
        $dbeRenewalType = new DBERenewalType($this);
        $dbeRenewalType->getRows();
        $data                       = [];
        $allowedDirectDebitRenewals = [1, 2, 5];
        while ($dbeRenewalType->fetchNext()) {
            $data [] = array(
                'name'              => $dbeRenewalType->getValue(DBERenewalType::description),
                'id'                => $dbeRenewalType->getValue(DBERenewalType::renewalTypeID),
                'allowsDirectDebit' => in_array(
                    $dbeRenewalType->getValue(DBERenewalType::renewalTypeID),
                    $allowedDirectDebitRenewals
                ) ? 'data-allows-direct-debit="true"' : null,
            );

        }
        return $this->success($data);
    }

    function getItemBillingCategory()
    {
        $dbeItemBillingCategory = new DBEItemBillingCategory($this);
        $dbeItemBillingCategory->getRows(DBEItemBillingCategory::name);
        $data = [];
        while ($dbeItemBillingCategory->fetchNext()) {
            $data [] = array(
                'name' => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::name),
                'id'   => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::id),
            );
        }
        return $this->success($data);
    }

    function updateItem()
    {
        try {
            $body = $this->getBody(true);
            if (!$body) {
                return $this->fail(APIException::badRequest, "Bad Request");
            }
            unset($body['allowGlobalPriceUpdate']);
            if (!$this->dsItem->populateFromArray(["item" => $body])) {
                $this->setFormErrorOn();
                $this->setAction(CTCNC_ACT_ITEM_EDIT);
                $this->setParam('itemID', $this->dsItem->getValue(DBEItem::itemID));
                return $this->fail(APIException::badRequest, $this->getFormErrorMessage());
            }
            $this->setAction(CTCNC_ACT_ITEM_EDIT);
            $this->buItem->updateItem($this->dsItem, $this->getDbeUser());
            return $this->success();
        } catch (Exception $ex) {
            return $this->fail($ex->getMessage());
        }
    }

    function addItem()
    {
        try {
            $body = $this->getBody(true);
            if (!$body) {
                return $this->fail(APIException::badRequest, "Bad Request");
            }
            if (!$this->dsItem->populateFromArray(['item' => $body])) {
                $this->setFormErrorOn();
                $this->setAction(CTCNC_ACT_ITEM_ADD);
                return $this->fail(APIException::badRequest, $this->getFormErrorMessage());
            }
            $this->buItem->updateItem($this->dsItem, $this->getDbeUser());
            return $this->success(["itemId" => $this->dsItem->getPKValue()]);
        } catch (Exception $ex) {
            return $this->fail(APIException::badRequest, $ex->getMessage());
        }
    }

    function updateChildItems()
    {
        $itemId = @$_REQUEST["itemId"];
        if (!$itemId) {
            return $this->fail(APIException::badRequest, "Missing ItemID");
        }
        $items  = $this->getBody();
        $childs = DBConnect::fetchAll(
            "SELECT * FROM item JOIN childItem ON childItem.`childItemId` = item.`itm_itemno` WHERE childItem.`parentItemId` = :itemId",
            ["itemId" => $itemId]
        );
        //update current childs quantity
        foreach ($items as $item) {
            DBConnect::execute(
                "UPDATE childItem set quantity=:quantity where parentItemId=:parentId and childItemId=:childId",
                ["quantity" => $item->quantity, "parentId" => $itemId, "childId" => $item->id]
            );
        }
        //deleted items
        foreach ($childs as $child) {   //childItemId
            $deleted = true;
            foreach ($items as $item) {
                if ($child["childItemId"] == $item->id) {
                    $deleted = false;
                }
            }
            if ($deleted) {
                DBConnect::execute(
                    "DELETE from childItem where childItemId=:childId and parentItemId=:parentId",
                    ["childId" => $child["childItemId"], "parentId" => $itemId]
                );
            }

        }
        // add new items
        foreach ($items as $item) {
            $found = false;
            foreach ($childs as $child) {
                if ($child["childItemId"] == $item->id) {
                    $found = true;
                }
            }
            if (!$found) {
                DBConnect::execute(
                    "INSERT into childItem(childItemId,parentItemId,quantity) values(:childId,:parentId,:quantity)",
                    ["quantity" => $item->quantity, "parentId" => $itemId, "childId" => $item->id]
                );
            }
        }
        return $this->success();
    }

    function updateSalesStockQty()
    {
        $itemID = @$_REQUEST["id"];
        $value  = @$_REQUEST["value"];
        if (empty($itemID)) {
            return $this->fail(APIException::badRequest, "missing item id");
        }
        $dbeItem = new DBEItem($this);
        $dbeItem->getRow($itemID);
        if (!$dbeItem->rowCount) {
            return $this->fail(APIException::notFound, "not found");
        }
        $dbeItem->setValue(DBEItem::salesStockQty, $value);
        $dbeItem->updateRow();
        return $this->success();
    }

    private function updateItemFromSalesOrder()
    {
        $body = $this->getBody(true);
        if (!isset($body['itemId'])) {
            throw new JsonHttpException(251, "Item id required");
        }
        if (!isset($body['cost'])) {
            throw new JsonHttpException(251, "Cost required");
        }
        if (!isset($body['sale'])) {
            throw new JsonHttpException(251, "Sale required");
        }
        if (!isset($body['supplierId'])) {
            throw new JsonHttpException(251, "Supplier id required");
        }
        $dbeItem = new DBEItem($this);
        $dbeItem->getRow($body['itemId']);
        if (!$dbeItem->rowCount) {
            throw new JsonHttpException(515, 'Item not found');
        }
        $dbeItem->setValue(DBEItem::curUnitCost, $body['cost']);
        $dbeItem->setValue(DBEItem::curUnitSale, $body['sale']);
        $dbeItem->setValue(DBEItem::supplierId, $body['supplierId']);
        $dbeItem->setValue(DBEItem::updatedBy, $this->getDbeUser()->getFullName());
        $dbeItem->setValue(DBEItem::updatedAt, (new DateTimeImmutable())->format(DATE_MYSQL_DATETIME));
        $dbeItem->updateRow();
        return ["status" => "ok"];
    }
}
