<?php /*
* Renewal hosting table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class DBEJRenHosting extends DBECustomerItem
{
    const customerName = "customerName";
    const siteName = "siteName";
    const itemDescription = "itemDescription";
    const itemTypeDescription = "itemTypeDescription";
    const itemID = "itemID";
    const invoiceFromDate = "invoiceFromDate";
    const invoiceToDate = "invoiceToDate";
    const invoiceFromDateYMD = "invoiceFromDateYMD";
    const invoiceToDateYMD = "invoiceToDateYMD";
    const curUnitSale = "curUnitSale";
    const curUnitCost = "curUnitCost";

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(
            "customerName",
            DA_STRING,
            DA_NOT_NULL,
            "cus_name"
        );
        $this->addColumn(
            "siteName",
            DA_STRING,
            DA_NOT_NULL,
            "CONCAT(add_add1, ' ', add_town, ' ' , add_postcode)"
        );
        $this->addColumn(
            "itemDescription",
            DA_STRING,
            DA_NOT_NULL,
            "itm_desc"
        );
        $this->addColumn(
            "itemTypeDescription",
            DA_STRING,
            DA_NOT_NULL,
            "ity_desc"
        );
        $this->addColumn(
            "itemID",
            DA_ID,
            DA_NOT_NULL,
            "itm_itemno"
        );
        $this->addColumn(
            "invoiceFromDate",
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ), '%d/%m/%Y')"
        );
        $this->addColumn(
            "invoiceToDate",
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
            "invoiceFromDateYMD",
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ), '%Y-%m-%d') as invoiceFromDateYMD"
        );
        $this->addColumn(
            "invoiceToDateYMD",
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ), '%Y-%m-%d') as invoiceToDateYMD"
        );
        $this->addColumn(
            "curUnitSale",
            DA_FLOAT,
            DA_NOT_NULL,
            'cui_sale_price'
        );
        $this->addColumn(
            "curUnitCost",
            DA_FLOAT,
            DA_NOT_NULL,
            'cui_cost_price'
        );
        $this->setAddColumnsOff();
    }

    function getRow()
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
		 WHERE " . $this->getPKWhere() .
            " AND renewalTypeID = 5";

        $this->setQueryString($statement);
        $ret = (parent::getRow());
    }

    function getRows($orderBy = false)
    {

        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			WHERE declinedFlag = 'N'
        AND renewalTypeID = 5";

        if ($orderBy) {
            $statement .= " ORDER BY $orderBy";
        } else {
            $statement .= " ORDER BY cus_name";
        }

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    function getRowsByCustomerID($customerID)
    {

        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
	      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
		JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			WHERE declinedFlag = 'N'
				AND cui_custno = $customerID		
        AND renewalTypeID = 5      
			ORDER BY cus_name";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
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

        $statement =
            "
			SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
		 WHERE CURDATE() >= ( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` - 1 MONTH ) )
		 AND declinedFlag = 'N' AND renewalTypeID = 5 and directDebitFlag <> 'Y'";
        $statement .= " ORDER BY cui_custno, autoGenerateContractInvoice asc";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    /**
     * Get all renewals by IDs
     */
    function getRenewalsRowsByID($ids)
    {

        $statement =
            "
      SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
      JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
     WHERE customerItemID IN ('" . implode(
                '\',\'',
                $ids
            ) . "')" .
            " AND declinedFlag = 'N'
        AND renewalTypeID = 5  and directDebitFlag <> 'Y'    
      ORDER BY cui_custno
     ";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

}

?>