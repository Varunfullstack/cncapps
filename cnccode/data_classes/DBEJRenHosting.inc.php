<?php /*
* Renewal hosting table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class DBEJRenHosting extends DBECustomerItem
{
    const customerName        = "customerName";
    const siteName            = "siteName";
    const itemDescription     = "itemDescription";
    const itemTypeDescription = "itemTypeDescription";
    const invoiceFromDate     = "invoiceFromDate";
    const invoiceToDate       = "invoiceToDate";
    const invoiceFromDateYMD  = "invoiceFromDateYMD";
    const invoiceToDateYMD    = "invoiceToDateYMD";
    const allowDirectDebit    = "allowDirectDebit";
    const itemTypeId          = "itemTypeId";

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_NOT_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::siteName,
            DA_STRING,
            DA_NOT_NULL,
            "CONCAT(add_add1, ' ', add_town, ' ' , add_postcode)"
        );
        $this->addColumn(
            self::itemDescription,
            DA_STRING,
            DA_NOT_NULL,
            "itm_desc"
        );
        $this->addColumn(
            self::itemTypeDescription,
            DA_STRING,
            DA_NOT_NULL,
            "ity_desc"
        );
        $this->addColumn(
            self::itemID,
            DA_ID,
            DA_NOT_NULL,
            "itm_itemno"
        );
        $this->addColumn(
            self::invoiceFromDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ), '%d/%m/%Y')"
        );
        $this->addColumn(
            self::invoiceToDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT(
 				DATE_SUB(
 					DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ),
 					INTERVAL 1 DAY
 				)
 				, '%d/%m/%Y')"
        );
        $this->addColumn(
            self::invoiceFromDateYMD,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ), '%Y-%m-%d') as invoiceFromDateYMD"
        );
        $this->addColumn(
            self::invoiceToDateYMD,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ), '%Y-%m-%d') as invoiceToDateYMD"
        );
        $this->addColumn(
            self::curUnitSale,
            DA_FLOAT,
            DA_NOT_NULL,
            'cui_sale_price'
        );
        $this->addColumn(
            self::curUnitCost,
            DA_FLOAT,
            DA_NOT_NULL,
            'cui_cost_price'
        );
        $this->addColumn(
            self::allowDirectDebit,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::itemTypeId,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'itm_itemtypeno'
        );
        $this->setAddColumnsOff();
    }

    function getRow($pkValue = null)
    {
        $statement = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
		 WHERE " . $this->getPKWhere() . " AND renewalTypeID = 5";
        $this->setQueryString($statement);
        return parent::getRow();
    }

    function getRows($sortColumn = '', $orderDirection = '')
    {

        $statement = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			WHERE declinedFlag = 'N'
        AND renewalTypeID = 5";
        if ($sortColumn) {
            $statement .= " ORDER BY $sortColumn";
        } else {
            $statement .= " ORDER BY cus_name";
        }
        $this->setQueryString($statement);
        return parent::getRows();
    }

    function getRowsByCustomerID($customerID)
    {

        $statement = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " JOIN item ON  itm_itemno = cui_itemno
	      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
		JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			WHERE declinedFlag = 'N'
				AND cui_custno = $customerID		
        AND renewalTypeID = 5      
			ORDER BY cus_name";
        $this->setQueryString($statement);
        return parent::getRows();
    }

    /**
     * Get all renewals due in exactly 30 days time
     *
     * i.e. Installation date plus total number of months to invoice minus one month
     *
     * WHen the invoice has been generated, the total invoice months is increased by the invoice period months
     * so the renewal gets picked up again.
     */
    function getRenewalsDueRows()
    {

        $statement = "
			SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
WHERE CURDATE() >= ( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` - 1 MONTH ) )
		 AND declinedFlag = 'N' AND renewalTypeID = 5 and directDebitFlag <> 'Y' and item.itm_itemtypeno <> 57";
        $statement .= " ORDER BY cui_custno, autoGenerateContractInvoice asc,  ity_desc ASC";
        $this->setQueryString($statement);
        return parent::getRows();
    }

    /**
     * Get all renewals by IDs
     */
    function getRenewalsRowsByID($ids)
    {

        $statement = "
      SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
      JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
     WHERE customerItemID IN ('" . implode(
                '\',\'',
                $ids
            ) . "')" . " AND declinedFlag = 'N'
        AND renewalTypeID = 5  and directDebitFlag <> 'Y'    
      ORDER BY cui_custno
     ";
        $this->setQueryString($statement);
        return parent::getRows();
    }

}

?>
