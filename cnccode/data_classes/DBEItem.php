<?php

namespace CNCLTD\Data;
global $cfg;

use DBCNCEntity;

require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEItem extends DBCNCEntity
{

    const itemID                  = "itemID";
    const description             = "description";
    const manufacturerID          = "manufacturerID";
    const stockcat                = "stockcat";
    const itemTypeID              = "itemTypeID";
    const curUnitSale             = "curUnitSale";
    const curUnitCost             = "curUnitCost";
    const curMaintStockCost       = "curMaintStockCost";
    const serialNoFlag            = "serialNoFlag";
    const salesStockQty           = "salesStockQty";
    const maintStockQty           = "maintStockQty";
    const discontinuedFlag        = "discontinuedFlag";
    const partNo                  = "partNo";
    const warrantyID              = "warrantyID";
    const notes                   = "notes";
    const servercareFlag          = "servercareFlag";
    const contractResponseTime    = "contractResponseTime";
    const stockInChannelLink      = "stockInChannelLink";
    const renewalTypeID           = "renewalTypeID";
    const allowDirectDebit        = "allowDirectDebit";
    const excludeFromPOCompletion = "excludeFromPOCompletion";
    const itemBillingCategoryID   = "itemBillingCategoryID";
    const allowSRLog              = "allowSRLog";
    const isStreamOne             = "isStreamOne";
    const partNoOld               = "partNoOld";
    const supplierId              = "supplierId";
    const updatedBy               = "updatedBy";
    const updatedAt               = "updatedAt";

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("Item");
        $this->addColumn(
            self::itemID,
            DA_ID,
            DA_NOT_NULL,
            "itm_itemno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "itm_desc"
        );
        $this->addColumn(
            self::manufacturerID,
            DA_ID,
            DA_NOT_NULL,
            "itm_manno"
        );
        $this->addColumn(
            self::stockcat,
            DA_STRING,
            DA_NOT_NULL,
            "itm_stockcat"
        );
        $this->addColumn(
            self::itemTypeID,
            DA_ID,
            DA_NOT_NULL,
            "itm_itemtypeno"
        );
        $this->addColumn(
            self::curUnitSale,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "itm_sstk_price"
        );
        $this->addColumn(
            self::curUnitCost,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "itm_sstk_cost"
        );
        $this->addColumn(
            self::curMaintStockCost,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "itm_mstk_cost"
        );
        $this->addColumn(
            self::serialNoFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "itm_serial_req"
        );
        $this->addColumn(
            self::salesStockQty,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "itm_sstk_qty"
        );
        $this->addColumn(
            self::maintStockQty,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "itm_mstk_qty"
        );
        $this->addColumn(
            self::discontinuedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "itm_discontinued"
        );
        $this->addColumn(
            self::partNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "itm_unit_of_sale"
        );
        $this->addColumn(
            self::warrantyID,
            DA_ID,
            DA_ALLOW_NULL,
            "itm_contno"
        );
        $this->addColumn(
            self::notes,
            DA_MEMO,
            DA_ALLOW_NULL,
            "notes"
        );
        $this->addColumn(
            self::servercareFlag,
            DA_YN,
            DA_ALLOW_NULL,
            'itm_servercare_flag'
        );
        $this->addColumn(
            self::contractResponseTime,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::stockInChannelLink,
            DA_STRING,
            DA_ALLOW_NULL,
            'itm_stock_in_link'
        );
        $this->addColumn(
            self::renewalTypeID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::allowDirectDebit,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::excludeFromPOCompletion,
            DA_YN,
            DA_NOT_NULL,
            null,
            'N'
        );
        $this->addColumn(
            self::itemBillingCategoryID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::allowSRLog,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::isStreamOne,
            DA_BOOLEAN,
            DA_ALLOW_NULL,
            "isStreamOne"
        );
        $this->addColumn(
            self::partNoOld,
            DA_STRING,
            DA_ALLOW_NULL,
            "partNoOld"
        );
        $this->addColumn(
            self::supplierId,
            DA_ID,
            DA_ALLOW_NULL,
        );
        $this->addColumn(
            self::updatedBy,
            DA_TEXT,
            DA_ALLOW_NULL,
        );
        $this->addColumn(
            self::updatedAt,
            DA_DATETIME,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by description match
     * Excludes discontinued rows
     * @access public
     * @param string|null $search
     * @param bool $renewalTypeID
     * @return bool Success
     */
    function getRowsByDescriptionMatch(string $search, $renewalTypeID = false)
    {

        $this->setMethodName("getRowsByDescriptionMatch");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " WHERE 1=1";
        if ($search) {
            $queryString .= " AND MATCH (item.itm_desc, notes, item.itm_unit_of_sale)
				AGAINST ('" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $search
                ) . "' IN BOOLEAN MODE)";
        }
        if ($renewalTypeID) {
            $queryString .= " AND renewalTypeID = $renewalTypeID";
        }
        $queryString .= " AND " . $this->getDBColumnName(
                self::discontinuedFlag
            ) . " <> 'Y'" . " ORDER BY " . $this->getDBColumnName(self::description) . " LIMIT 0,200";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    function getRowsByDescriptionOrPartNoSearch($search, $renewalTypeID = false, $limit = 200)
    {
        $this->setMethodName("getRowsByDescriptionMatch");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " WHERE 1=1";
        if ($search) {
            $searchEscaped = mysqli_real_escape_string(
                $this->db->link_id(),
                "%{$search}%"
            );
            $queryString   .= " AND (item.itm_desc like '{$searchEscaped}' or item.itm_unit_of_sale like '{$searchEscaped}') ";
        }
        if ($renewalTypeID) {
            $queryString .= " AND renewalTypeID = $renewalTypeID";
        }
        $queryString .= " AND " . $this->getDBColumnName(
                self::discontinuedFlag
            ) . " <> 'Y'" . " ORDER BY " . $this->getDBColumnName(self::description);
        if ($limit) {
            $queryString .= " LIMIT 0,$limit";
        }
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Update sales stock qty by given amount
     * @access public
     * @param $value
     * @return bool Success
     */
    function updateSalesStockQty($value)
    {
        $this->setMethodName("updateSalesStockQty");
        $this->setQueryString(
            "UPDATE " . $this->getTableName() . " SET " . $this->getDBColumnName(
                self::salesStockQty
            ) . "=" . $value . " WHERE " . $this->getPKWhere()
        );
        return (parent::updateRow());
    }

    /**
     * Update maint stock qty by given amount
     * @access public
     * @param $value
     * @return bool Success
     */
    function updateMaintStockQty($value)
    {
        $this->setMethodName("updateMaintStockQty");
        $this->setQueryString(
            "UPDATE " . $this->getTableName() . " SET " . $this->getDBColumnName(
                self::maintStockQty
            ) . "=" . $value . " WHERE " . $this->getPKWhere()
        );
        return (parent::updateRow());
    }

    function getRenewalTypeRows($renewalTypeID = false)
    {

        $this->setMethodName("getRenewalTypeRows");
        if (!$renewalTypeID) {
            $this->raiseError('renewalTypeID not set');
        }
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::renewalTypeID
            ) . " = '" . $renewalTypeID . "'" . " AND " . $this->getDBColumnName(
                self::discontinuedFlag
            ) . " <> 'Y'" . " ORDER BY " . $this->getDBColumnName(self::description);
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    public function getItemsByPartNoOrOldPartNo($sku)
    {
        $queryString = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE 
                              {$this->getDBColumnName(self::partNo)} = '{$sku}' or {$this->getDBColumnName(self::partNoOld)} = '{$sku}' limit 1";
        $this->setQueryString($queryString);
        return parent::getRow();
    }

    public function getChildItems($parentItemId)
    {
        global $db;
        $escapedParentItemId = mysqli_real_escape_string($db->link_id(), $parentItemId);
        $queryString         = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} 
                join childItem on parentItemId = '{$escapedParentItemId}' and childItemId =  {$this->getDBColumnName(self::itemID)}";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
    }

    public function getParentItems($itemId)
    {
        global $db;
        $escapedItemId = mysqli_real_escape_string($db->link_id(), $itemId);
        $queryString   = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} 
                join childItem on childItemId = '{$escapedItemId}' and parentItemId =  {$this->getDBColumnName(self::itemID)}";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
    }

}
