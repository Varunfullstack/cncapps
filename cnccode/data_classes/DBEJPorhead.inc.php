<?php /*
* Porhead table join for descriptions: purchase order header
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBEPorhead.inc.php");

class DBEJPorhead extends DBEPorhead
{
    const supplierName  = "supplierName";
    const supplierPhone = "supplierPhone";
    const customerName  = "customerName";
    const contactName   = "contactName";
    const contactPhone  = "contactPhone";
    const contactEmail  = "contactEmail";
    const orderedByName = "orderedByName";
    const raisedByName  = "raisedByName";
    const webSiteURL    = "webSiteURL";

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
            self::supplierName,
            DA_STRING,
            DA_NOT_NULL,
            'sup_name'
        );
        $this->addColumn(
            self::supplierPhone,
            DA_STRING,
            DA_NOT_NULL,
            'supplierContact.phone'
        );
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_NOT_NULL,
            'cus_name'
        );
        $this->addColumn(
            self::contactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(supplierContact.firstName,' ',supplierContact.lastName)"
        );
        $this->addColumn(
            self::contactPhone,
            DA_STRING,
            DA_ALLOW_NULL,
            'supplierContact.phone'
        );
        $this->addColumn(
            self::contactEmail,
            DA_STRING,
            DA_ALLOW_NULL,
            'supplierContact.email'
        );
        $this->addColumn(
            self::orderedByName,
            DA_STRING,
            DA_ALLOW_NULL,
            'ob.cns_name'
        );
        $this->addColumn(
            self::raisedByName,
            DA_STRING,
            DA_ALLOW_NULL,
            'rb.cns_name'
        );
        $this->addColumn(
            self::webSiteURL,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_web_site_url"
        );
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by operative and date
     * @access public
     * @param $supplierID
     * @param $ordheadID
     * @param $orderType
     * @param $supplierRef
     * @param $lineText
     * @param string $partNo
     * @param string $fromDate
     * @param string $toDate
     * @param string $context
     * @param bool $noLimit
     * @return bool Success
     */
    function getRowsBySearchCriteria($supplierID = null,
                                     $ordheadID = null,
                                     $orderType = null,
                                     $supplierRef = null,
                                     $lineText = null,
                                     $partNo = null,
                                     $fromDate = null,
                                     $toDate = null,
                                     $context = 'PO',
        // PI = Purchase Invoice 'PO' = Purchase Order GI = Goods In
                                     bool $noLimit = false
    )
    {
        $this->setMethodName("getRowsBySearchCriteria");
        if ($lineText || $partNo) {
            $statement = "SELECT DISTINCT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName(
                )} JOIN porline ON {$this->getTableName()}.{$this->getDBColumnName(
                    self::porheadID
                )}= porline.pol_porno JOIN item ON porline.pol_itemno= item.itm_itemno JOIN supplier ON {$this->getTableName(
                )}.{$this->getDBColumnName(
                    self::supplierID
                )}= supplier.sup_suppno LEFT JOIN ordhead ON {$this->getTableName(
                )}.{$this->getDBColumnName(
                    self::ordheadID
                )}= ordhead.odh_ordno LEFT JOIN supplierContact ON {$this->getDBColumnName(
                    self::supplierContactId
                )} = supplierContact.id LEFT JOIN consultant AS rb ON {$this->getDBColumnName(
                    self::userID
                )} = rb.cns_consno LEFT JOIN consultant AS ob ON {$this->getDBColumnName(
                    self::orderUserID
                )} = ob.cns_consno LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno";
        } else {
            $statement = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName(
                )} JOIN supplier ON {$this->getTableName()}.{$this->getDBColumnName(
                    self::supplierID
                )}= supplier.sup_suppno LEFT JOIN supplierContact ON {$this->getDBColumnName(
                    self::supplierContactId
                )} = supplierContact.id LEFT JOIN consultant AS rb ON {$this->getDBColumnName(
                    self::userID
                )} = rb.cns_consno LEFT JOIN consultant AS ob ON {$this->getDBColumnName(
                    self::orderUserID
                )} = ob.cns_consno LEFT JOIN ordhead ON {$this->getTableName(
                )}.{$this->getDBColumnName(
                    self::ordheadID
                )}= ordhead.odh_ordno LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno";
        }
        $statement = $statement . " WHERE 1=1";
        if ($supplierID) {
            $statement = $statement . " AND " . $this->getDBColumnName(self::supplierID) . "=" . $supplierID;
        }
        if ($ordheadID) {
            $statement = $statement . " AND " . $this->getDBColumnName(self::ordheadID) . "=" . $ordheadID;
        }
        if ($fromDate) {
            $statement = $statement . " AND " . $this->getDBColumnName(self::date) . ">= '" . $fromDate . "'";
        }
        if ($toDate) {
            $statement = $statement . " AND " . $this->getDBColumnName(self::date) . "<= '" . $toDate . "'";
        }
        if ($orderType) {
            if ($orderType == 'B') {
                $statement = $statement . " AND " . $this->getDBColumnName(self::type) . " IN('I','P')";
            } else {
                $statement = $statement . " AND " . $this->getDBColumnName(
                        self::type
                    ) . "='" . mysqli_real_escape_string(
                        $this->db->link_id(),
                        $orderType
                    ) . "'";
            }
        }
        // context of search
        if ($context == 'PI') {                // For purchase invoices exclude authorised POs and
            $statement .=                                // stock suppliers
                " AND " . $this->getDBColumnName(
                    self::supplierID
                ) . " NOT IN(" . CONFIG_SALES_STOCK_SUPPLIERID . "," . CONFIG_MAINT_STOCK_SUPPLIERID . ")" . " AND (" . " (" . $this->getDBColumnName(
                    self::type
                ) . " IN ('P', 'C') AND " . $this->getDBColumnName(
                    self::directDeliveryFlag
                ) . "= 'N' )" . " OR" . " (" . $this->getDBColumnName(
                    self::type
                ) . " IN ('P', 'C', 'I') AND " . $this->getDBColumnName(
                    self::directDeliveryFlag
                ) . "= 'Y' )" . ")";
        } elseif ($context == 'GI') {        // for goods in exclude direct delivery
            $statement .= " AND " . $this->getDBColumnName(self::directDeliveryFlag) . "<> 'Y'";
        }
        if ($lineText) {
            $statement .= " AND MATCH (item.itm_desc, item.notes, item.itm_unit_of_sale)
					AGAINST ('" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $lineText
                ) . "' IN BOOLEAN MODE)";
        }
        if ($partNo) {
            $statement .= " AND item.itm_unit_of_sale LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $partNo
                ) . "%'";
        }
        if ($supplierRef) {
            $statement .= " AND " . $this->getDBColumnName(self::supplierRef) . " LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $supplierRef
                ) . "%'";
        }
        if (!$noLimit) {
            $statement = $statement . " ORDER BY " . $this->getDBColumnName(self::porheadID) . " DESC" . " LIMIT 0,200";
        }
        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }

    function getRow($porheadID = null)
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName(
            )} JOIN supplier ON {$this->getTableName()}.{$this->getDBColumnName(
                self::supplierID
            )}= supplier.sup_suppno LEFT JOIN ordhead ON {$this->getTableName(
            )}.{$this->getDBColumnName(
                self::ordheadID
            )}= ordhead.odh_ordno LEFT JOIN consultant AS rb ON {$this->getDBColumnName(
                self::userID
            )} = rb.cns_consno LEFT JOIN consultant AS ob ON {$this->getDBColumnName(
                self::orderUserID
            )} = ob.cns_consno LEFT JOIN supplierContact ON {$this->getDBColumnName(
                self::supplierContactId
            )} = supplierContact.id LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno WHERE {$this->getPKWhere(
            )}"
        );
        return (parent::getRow());
    }

    function getPurchaseInvoiceRow()
    {
        $this->setMethodName("getPurchaseInvoiceRow");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " JOIN supplier ON " . $this->getTableName() . "." . $this->getDBColumnName(
                self::supplierID
            ) . "= supplier.sup_suppno LEFT JOIN ordhead ON " . $this->getTableName() . "." . $this->getDBColumnName(
                self::ordheadID
            ) . "= ordhead.odh_ordno LEFT JOIN consultant AS rb ON " . $this->getDBColumnName(
                self::userID
            ) . " = rb.cns_consno LEFT JOIN consultant AS ob ON " . $this->getDBColumnName(
                self::orderUserID
            ) . " = ob.cns_consno LEFT JOIN supplierContact ON " . $this->getDBColumnName(
                self::supplierContactId
            ) . " = supplierContact.id LEFT JOIN customer ON ordhead.odh_custno= customer.cus_custno WHERE " . $this->getPKWhere(
            ) . " AND " . $this->getDBColumnName(
                self::supplierID
            ) . " NOT IN(" . CONFIG_SALES_STOCK_SUPPLIERID . "," . CONFIG_MAINT_STOCK_SUPPLIERID . ") AND ( (" . $this->getDBColumnName(
                self::type
            ) . " IN ('P', 'C') AND " . $this->getDBColumnName(
                self::directDeliveryFlag
            ) . "= 'N' ) OR (" . $this->getDBColumnName(
                self::type
            ) . " IN ('P', 'C', 'I') AND " . $this->getDBColumnName(
                self::directDeliveryFlag
            ) . "= 'Y' ))"
        );
        return (parent::getRow());
    }
}
