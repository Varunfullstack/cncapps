<?php /*
* Item table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEItem extends DBCNCEntity
{

    const itemID = "itemID";
    const description = "description";
    const manufacturerID = "manufacturerID";
    const stockcat = "stockcat";
    const itemTypeID = "itemTypeID";
    const curUnitSale = "curUnitSale";
    const curUnitCost = "curUnitCost";
    const curMaintStockCost = "curMaintStockCost";
    const serialNoFlag = "serialNoFlag";
    const salesStockQty = "salesStockQty";
    const maintStockQty = "maintStockQty";
    const discontinuedFlag = "discontinuedFlag";
    const partNo = "partNo";
    const warrantyID = "warrantyID";
    const notes = "notes";
    const servercareFlag = "servercareFlag";
    const contractResponseTime = "contractResponseTime";
    const renewalTypeID = "renewalTypeID";
    const allowDirectDebit = "allowDirectDebit";
    const excludeFromPOCompletion = "excludeFromPOCompletion";

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
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by description match
     * Excludes discontinued rows
     * @access public
     * @param bool $renewalTypeID
     * @return bool Success
     */
    function getRowsByDescriptionMatch($renewalTypeID = false)
    {
        $this->setMethodName("getRowsByDescriptionMatch");

        if (!$this->getValue(self::description)) {
            $this->raiseError('description not set');
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE 1=1";
        $queryString .=
            " AND MATCH (item.itm_desc, item.notes, item.itm_unit_of_sale)
				AGAINST ('" . $this->getValue(self::description) . "' IN BOOLEAN MODE)";

        if ($renewalTypeID) {
            $queryString .= " AND renewalTypeID = $renewalTypeID";
        }

        $queryString .=
            " AND " . $this->getDBColumnName(self::discontinuedFlag) . " <> 'Y'" .
            " ORDER BY " . $this->getDBColumnName(self::description) .
            " LIMIT 0,200";
        $this->setQueryString($queryString);

        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Get rows by part number match
     * Excludes discontinued rows
     * @access public
     * @param bool $renewalTypeID
     * @return bool Success
     */
    function getRowsByPartNoMatch($renewalTypeID = false)
    {
        $this->setMethodName("getRowsByPartNoMatch");
        if (!$this->getValue(self::partNo)) {
            $this->raiseError('partNo not set');
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::partNo) . " LIKE " . $this->getFormattedLikeValue(self::partNo) .
            " AND " . $this->getDBColumnName(self::discontinuedFlag) . " <> 'Y'";
        if ($renewalTypeID) {
            $queryString .= " AND renewalTypeID = $renewalTypeID";
        }

        $queryString .=
            " ORDER BY " . $this->getDBColumnName(self::partNo) .
            " LIMIT 0,200";

        $this->setQueryString($queryString);

        return (parent::getRows());
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
            "UPDATE " . $this->getTableName() .
            " SET " . $this->getDBColumnName(self::salesStockQty) . "=" . $value .
            " WHERE " . $this->getPKWhere()
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
            "UPDATE " . $this->getTableName() .
            " SET " . $this->getDBColumnName(self::maintStockQty) . "=" . $value .
            " WHERE " . $this->getPKWhere()
        );
        return (parent::updateRow());
    }

    function setRowsDiscontinued($discontinueItemIDArray)
    {
        $this->setMethodName('setRowsDiscontinued');

        if (!$discontinueItemIDArray) {
            $this->raiseError('$discontinueItemIDArray not set');
        }

        $this->setQueryString(
            "UPDATE " . $this->getTableName() .
            " SET " . $this->getDBColumnName(self::discontinuedFlag) . " = 'Y'" .
            " WHERE " . $this->getDBColumnName(self::itemID) . " IN ( " . implode(
                ',',
                $discontinueItemIDArray
            ) . ")"
        );

        return $this->runQuery();

    }

    function getRenewalTypeRows($renewalTypeID = false)
    {

        $this->setMethodName("getRenewalTypeRows");

        if (!$renewalTypeID) {
            $this->raiseError('renewalTypeID not set');
        }

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::renewalTypeID) . " = '" . $renewalTypeID . "'" .
            " AND " . $this->getDBColumnName(self::discontinuedFlag) . " <> 'Y'" .
            " ORDER BY " . $this->getDBColumnName(self::description);

        $this->setQueryString($queryString);

        $ret = (parent::getRows());
        return $ret;
    }

}
