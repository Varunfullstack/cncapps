<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class DBEJRenBroadband extends DBECustomerItem
{
    const allowDirectDebit = "allowDirectDebit";
    const contractExpiryDate = "contractExpiryDate";
    const itemDescription = "itemDescription";
    const customerName = "customerName";
    const invoiceFromDate = "invoiceFromDate";
    const invoiceToDate = "invoiceToDate";
    const siteName = "siteName";
    const itemTypeDescription = "itemTypeDescription";
    const invoiceFromDateYMD = "invoiceFromDateYMD";
    const invoiceToDateYMD = "invoiceToDateYMD";
    const contractExpireNotified = "contractExpireNotified";
    const itemTypeId = "itemTypeId";

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
            self::itemID,
            DA_STRING,
            DA_NOT_NULL,
            "itm_itemno"
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
            self::invoiceFromDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ), '%d/%m/%Y')"
        );
        $this->addColumn(
            self::invoiceToDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ), '%d/%m/%Y')"
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
            "DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ) as invoiceToDateYMD"
        );

        $this->addColumn(
            self::contractExpiryDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_ADD(installationDate, INTERVAL initialContractLength MONTH) AS contractExpiryDate"
        );

        $this->addColumn(
            self::allowDirectDebit,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::contractExpireNotified,
            DA_STRING,
            DA_NOT_NULL,
            "contractExpireNotified"
        );

        $this->addColumn(
            self::itemTypeId,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'itm_itemtypeno'
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
            " AND renewalTypeID = 1";

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
        AND renewalTypeID = 1";

        if ($orderBy) {
            $statement .= " ORDER BY $orderBy";
        } else {
            $statement .= " ORDER BY cus_name";
        }

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    function getLeasedLinesToExpire($lowerBoundDays = 59,
                                    $upperBoundDays = 67
    )
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			WHERE declinedFlag = 'N'
        AND renewalTypeID = 1 AND itm_desc NOT LIKE '%Broadband%'
  AND DATEDIFF(DATE_ADD(installationDate, INTERVAL initialContractLength MONTH), CURDATE()) > $lowerBoundDays AND 
  DATEDIFF(DATE_ADD(installationDate, INTERVAL initialContractLength MONTH), CURDATE()) < $upperBoundDays 
  AND salePricePerMonth>100
  order by contractExpiryDate ";
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
        AND renewalTypeID = 1
				AND cui_custno = $customerID		
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
		 WHERE CURDATE() >= DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` month) 
     AND renewalTypeID = 1
		 AND declinedFlag = 'N' and directDebitFlag <> 'Y' and item.itm_itemtypeno <> 57";
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
     WHERE cui_cuino IN ('" . implode(
                '\',\'',
                $ids
            ) . "')" .
            " AND declinedFlag = 'N'
        AND renewalTypeID = 1 and directDebitFlag <> 'Y'
      ORDER BY cui_custno
     ";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

}

?>