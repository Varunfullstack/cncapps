<?php /*
* Ordhead table join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEOrdhead.inc.php");

class DBEJOrdhead extends DBEOrdhead
{
    const customerName   = "customerName";
    const contract       = "contract";
    const customerItemID = "customerItemID";
    const lastQuoteSent  = "lastQuoteSent";
    const firstComment   = "firstComment";
//
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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_NOT_NULL,
            'cus_name'
        );
        $this->addColumn(
            self::contract,
            DA_STRING,
            DA_ALLOW_NULL,
            'itm_desc'
        );
        $this->addColumn(
            self::customerItemID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'cui_cuino'
        );
        $this->addColumn(
            self::lastQuoteSent,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "( SELECT MAX(sentDateTime) FROM quotation WHERE ordheadID=Ordhead.odh_ordno AND sentDateTime IS NOT NULL) "
        );
        $this->addColumn(
            self::firstComment,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "(SELECT `odl_desc` FROM ordline WHERE  `odl_type`='C' AND `odl_ordno`=Ordhead.odh_ordno  LIMIT 1)"
        );
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by operative and date
     * @access public
     * @param $customerID
     * @param $orderType
     * @param $custPORef
     * @param $lineText
     * @param $fromDate
     * @param $toDate
     * @param $userID
     * @return bool Success
     */
    function getRowsBySearchCriteria($customerID,
                                     $orderType,
                                     $custPORef,
                                     $lineText,
                                     $fromDate,
                                     $toDate,
                                     $userID
    )
    {

        $defaultLimit = " LIMIT 0,200";
        $this->setMethodName("getRowsBySearchCriteria");
        if ($lineText != '') {
            $statement = "SELECT DISTINCT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
                ) . " JOIN ordline ON " . $this->getTableName() . "." . $this->getDBColumnName(
                    self::ordheadID
                ) . "= ordline.odl_ordno" . " JOIN customer ON " . $this->getTableName() . "." . $this->getDBColumnName(
                    self::customerID
                ) . "= customer.cus_custno";
        } else {
            $statement = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
                ) . " JOIN customer ON " . $this->getTableName() . "." . $this->getDBColumnName(
                    self::customerID
                ) . "= customer.cus_custno";
        }
        $statement .= " LEFT JOIN custitem ON renewalOrdheadID = odh_ordno" . " LEFT JOIN item ON cui_itemno = itm_itemno";
        $statement = $statement . " WHERE 1=1";
        if ($customerID != '') {
            $statement = $statement . " AND " . $this->getDBColumnName(self::customerID) . "=" . $customerID;
        }
        if ($userID != '') {
            $statement = $statement . " AND (
            SELECT
              COUNT(*)
            FROM
              quotation
            WHERE
              ordheadID = ordhead.odh_ordno
              AND userID = " . $userID . " ) > 0";
        }
        switch ($orderType) {
            case 'B':
                $statement    = $statement . " AND " . $this->getDBColumnName(self::type) . " IN('I','P')";
                $defaultLimit = null;
                break;
            case '':                                            // all types
                break;
            default:
                if (in_array($orderType, ['I', 'P'])) {
                    $defaultLimit = null;
                }
                $statement = $statement . " AND " . $this->getDBColumnName(
                        self::type
                    ) . "='" . mysqli_real_escape_string(
                        $this->db->link_id(),
                        $orderType
                    ) . "'";
                break;
        }
        if ($lineText != '') {
            $statement = $statement . " AND ( MATCH (ordline.odl_desc)
					AGAINST ('" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $lineText
                ) . "' IN BOOLEAN MODE)";
            $statement = $statement . " OR MATCH (item.notes, item.itm_unit_of_sale)
					AGAINST ('" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $lineText
                ) . "' IN BOOLEAN MODE) )";
        }
        if ($custPORef != '') {
            $statement = $statement . " AND " . $this->getDBColumnName(
                    self::custPORef
                ) . " LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $custPORef
                ) . "%'";
        }
        if ($fromDate != '') {
            $statement = $statement . " AND " . $this->getDBColumnName(self::date) . ">='" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $fromDate
                ) . "'";
        }
        if ($toDate != '') {
            $statement = $statement . " AND " . $this->getDBColumnName(self::date) . "<='" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $toDate
                ) . "'";
        }
        $statement = $statement . " ORDER BY " . $this->getDBColumnName(self::date) . " DESC";
        $statement = $statement . $defaultLimit;
        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * @param $ordheadID
     * @return bool
     */
    function getRow($ordheadID = null)
    {
        $this->setMethodName("getRow");
        if (!$this->getValue(DBEOrdhead::ordheadID)) {
            $this->setValue(
                DBEOrdhead::ordheadID,
                $ordheadID
            );
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " JOIN customer ON " . $this->getTableName() . "." . $this->getDBColumnName(
                self::customerID
            ) . "= customer.cus_custno" . " LEFT JOIN custitem ON renewalOrdheadID = odh_ordno" . " LEFT JOIN item ON cui_itemno = itm_itemno" . " WHERE " . $this->getPKWhere(
            )
        );
        return (parent::getRow());
    }

    function getDespatchRowByOrdheadID($ordheadID)
    {
        $this->setMethodName("getDespatchRowByOrdheadID");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " JOIN customer ON " . $this->getTableName() . "." . $this->getDBColumnName(
                self::customerID
            ) . "= customer.cus_custno" . " LEFT JOIN custitem ON renewalOrdheadID = odh_ordno" . " LEFT JOIN item ON cui_itemno = itm_itemno" . " WHERE " . $this->getDBColumnName(
                self::ordheadID
            ) . "=" . $ordheadID . " AND " . $this->getDBColumnName(
                self::type
            ) . " IN('I','P')" . " AND " . $this->getDBColumnName(
                self::customerID
            ) . " NOT IN(" . CONFIG_ASSET_STOCK_CUSTOMERID . "," . CONFIG_MAINT_STOCK_CUSTOMERID . "," . CONFIG_SALES_STOCK_CUSTOMERID . "," . CONFIG_OPERATING_STOCK_CUSTOMERID . ")"
        );
        return (parent::getRow());
    }

    function getDespatchRows($customerID = '')
    {
        $this->setMethodName("getDespatchRows");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " JOIN customer ON " . $this->getTableName() . "." . $this->getDBColumnName(
                self::customerID
            ) . "= customer.cus_custno" . " LEFT JOIN custitem ON renewalOrdheadID = odh_ordno" . " LEFT JOIN item ON cui_itemno = itm_itemno" . " WHERE 1=1";
        if ($customerID != '') {
            $queryString .= " AND " . $this->getDBColumnName(self::customerID) . "=" . $customerID;
        }
        $queryString .= " AND " . $this->getDBColumnName(
                self::type
            ) . " IN('I','P')" . " AND " . $this->getDBColumnName(
                self::customerID
            ) . " NOT IN(" . CONFIG_ASSET_STOCK_CUSTOMERID . "," . CONFIG_MAINT_STOCK_CUSTOMERID . "," . CONFIG_SALES_STOCK_CUSTOMERID . "," . CONFIG_OPERATING_STOCK_CUSTOMERID . ")";
        $this->setQueryString($queryString);
        return (parent::getRows());
    }
}
