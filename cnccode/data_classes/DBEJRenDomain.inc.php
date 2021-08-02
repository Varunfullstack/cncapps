<?php /*
* Renewal Domain table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class DBEJRenDomain extends DBECustomerItem
{

    const customerName        = "customerName";
    const siteName            = "siteName";
    const itemDescription     = "itemDescription";
    const itemTypeDescription = "itemTypeDescription";
    const invoiceFromDate     = "invoiceFromDate";
    const invoiceToDate       = "invoiceToDate";
    const invoiceFromDateYMD  = "invoiceFromDateYMD";
    const invoiceToDateYMD    = "invoiceToDateYMD";
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
            "DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ) as invoiceFromDateYMD"
        );
        $this->addColumn(
            self::invoiceToDateYMD,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_SUB(DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ),INTERVAL 1 DAY) as invoiceToDateYMD"
        );
        $this->addColumn(
            self::salePrice,
            DA_FLOAT,
            DA_NOT_NULL,
            "itm_sstk_price"
        );
        $this->addColumn(
            self::costPrice,
            DA_FLOAT,
            DA_NOT_NULL,
            "itm_sstk_cost"
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
		 WHERE " . $this->getPKWhere() . " AND renewalTypeID = 4";
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
        AND renewalTypeID = 4";
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
        AND renewalTypeID = 4
			ORDER BY cus_name";
        $this->setQueryString($statement);
        return parent::getRows();
    }

    /**
     * Get all renewals due next month
     */
    function getRenewalsDueRows()
    {

        $statement = "
			SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			WHERE
					DATE_FORMAT( DATE_ADD( CURDATE(), INTERVAL 1 MONTH ), '%Y%m' ) >=
				DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ), '%Y%m' )
			 	AND declinedFlag = 'N'
        AND renewalTypeID = 4 and directDebitFlag <> 'Y' and item.itm_itemtypeno <> 57";
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
     WHERE cui_cuino ID IN ('" . implode(
                '\',\'',
                $ids
            ) . "')" . " AND declinedFlag = 'N'
      AND renewalTypeID = 4 and directDebitFlag <> 'Y'
     ORDER BY cui_custno";
        $this->setQueryString($statement);
        return parent::getRows();
    }

}

?>
