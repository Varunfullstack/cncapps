<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 28/09/2018
 * Time: 14:50
 */
global $cfg;
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");


class DBEDirectDebitContracts extends DBECustomerItem

{
    const customerName = "customerName";
    const siteName = "siteName";
    const itemID = "itemID";
    const itemDescription = "itemDescription";
    const itemTypeDescription = "itemTypeDescription";
    const invoiceFromDate = "invoiceFromDate";
    const invoiceToDate = "invoiceToDate";
    const invoiceFromDateYMD = "invoiceFromDateYMD";
    const invoiceToDateYMD = "invoiceToDateYMD";
    const renewalTypeID = 'renewalTypeID';

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
            "DATE_FORMAT( date_sub(DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ), INTERVAL 1 day), '%d/%m/%Y')"
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
            self::renewalTypeID,
            DA_INTEGER,
            DA_NOT_NULL
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
		 WHERE CURDATE() >= ( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + 1 MONTH ) )
     AND (renewalTypeID = 1 or renewalTypeID = 2 or renewalTypeID = 5 )
		 AND declinedFlag = 'N' and directDebitFlag = 'Y' ORDER BY cui_custno, autoGenerateContractInvoice asc;";
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


