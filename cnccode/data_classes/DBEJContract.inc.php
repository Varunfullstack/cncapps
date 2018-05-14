<?php /*
* Customer Item join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");

class DBEJContract extends DBECustomerItem
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn("itemDescription", DA_STRING, DA_ALLOW_NULL, "itm_desc");
        $this->addColumn("itemTypeID", DA_ID, DA_ALLOW_NULL, "itm_itemtypeno");
        $this->addColumn("renewalTypeID", DA_STRING, DA_ALLOW_NULL, "renewalType.renewalTypeID");
        $this->addColumn("renewalType", DA_STRING, DA_ALLOW_NULL, "renewalType.description");
        $this->addColumn("postcode", DA_STRING, DA_ALLOW_NULL, "add_postcode");
        $this->addColumn("adslPhone", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("invoiceFromDate",
                         DA_DATE,
                         DA_NOT_NULL,
                         "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths MONTH ), '%d/%m/%Y')");
        $this->addColumn("invoiceToDate",
                         DA_DATE,
                         DA_NOT_NULL,
                         "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths + custitem.invoicePeriodMonths MONTH ), '%d/%m/%Y')");

        $this->addColumn("invoiceFromDateYMD",
                         DA_DATE,
                         DA_NOT_NULL,
                         "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths MONTH ), '%Y-%m-%d') as invoiceFromDateYMD");

        $this->addColumn("invoiceToDateYMD",
                         DA_DATE,
                         DA_NOT_NULL,
                         "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths + custitem.invoicePeriodMonths MONTH ), '%Y-%m-%d') as invoiceToDateYMD");
        $this->setAddColumnsOff();
    }

    function getRowsByCustomerID($customerID, $includeExpired = FALSE)
    {
        $this->setMethodName('getRowsByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON cui_itemno = itm_itemno" .
            " JOIN renewalType ON renewalType.renewalTypeID = item.renewalTypeID" .
            " JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno " .
            " WHERE " . $this->getDBColumnName('customerID') . "=" . $customerID .
            "  AND renewalType.allowSrLogging = 'Y'
         AND declinedFlag <> 'Y'
       ORDER BY renewalType.description, itm_desc";
        /*
            if (!$includeExpired){
                $queryString .=
                    " AND " . $this->getDBColumnName('expiryDate') . ">=  CURRENT_DATE()";
            }
        */

        $this->setQueryString($queryString);
        return (parent::getRows());
    }

    function getRowsByCustomerItemID($customerItemID)
    {
        $this->setMethodName('getRowsByCustomerItemID');
        if ($customerItemID == '') {
            $this->raiseError('customerItemID not passed');
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM custitem_contract " .
            " JOIN custitem ON cic_contractcuino = cui_cuino" .
            " JOIN item ON cui_itemno = itm_itemno" .
            " JOIN renewalType ON renewalType.renewalTypeID = item.renewalTypeID" .
            " JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno " .
            " WHERE cic_cuino=" . $customerItemID .
            " AND " . $this->getDBColumnName('itemID') . "<>" . CONFIG_DEF_SERVERGUARD_ANNUAL_CHARGE_ITEMID .
            "   AND renewalType.allowSrLogging = 'Y'
       ORDER BY renewalType.description, itm_desc";

        $this->setQueryString($queryString);
        return (parent::getRows());
    }

    function getRowByContractID($contractID)
    {
        $this->setMethodName('getRowsByContractID');
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON cui_itemno = itm_itemno" .
            " JOIN renewalType ON renewalType.renewalTypeID = item.renewalTypeID" .
            " JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno " .
            " WHERE " . $this->getDBColumnName('customerItemID') . "=" . $contractID;

        $this->setQueryString($queryString);
        return (parent::getRow());
    }

    /**
     * Get prepay contracts
     * @param $customerID
     * @return bool : Success
     * @access public
     */
    function getPrePayContracts($customerID)
    {
        $buHeader = new BUHeader ($this);
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();

        $this->setMethodName('getPrePayContracts');
        $queryString = "
      SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON cui_itemno = itm_itemno
            JOIN renewalType ON renewalType.renewalTypeID = item.renewalTypeID
            JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
	  	WHERE
	  		cui_custno = " . $customerID .
            " AND cui_itemno = " . $dsHeader->getValue('gscItemID') .
            " AND cui_expiry_date >= now()" . // and is not expired
            " AND renewalStatus  <> 'D' and declinedFlag <> 'Y'";

        $this->setQueryString($queryString);

        return (parent::getRows());
    }

    function getServerCareContracts($customerID, $valid = true)
    {
        $queryString = "
      SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON cui_itemno = itm_itemno
             JOIN renewalType ON renewalType.renewalTypeID = item.renewalTypeID
             JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
	  	WHERE
	  		cui_custno = " . $customerID . " and itm_servercare_flag = 'Y' AND itm_desc LIKE '%ServerCare%'";
        if ($valid) {
            $queryString .=
                " AND cui_expiry_date >= now() AND	renewalStatus  <> 'D' and declinedFlag <> 'Y'";
        }
        $this->setQueryString($queryString);

        return (parent::getRows());
    }

    function getServiceDeskContracts($customerID, $valid = true)
    {
        $queryString = "
      SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON cui_itemno = itm_itemno
             JOIN renewalType ON renewalType.renewalTypeID = item.renewalTypeID
             JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
	  	WHERE
	  		cui_custno = " . $customerID . " and itm_servercare_flag = 'Y' AND itm_desc LIKE '%ServiceDesk%'";
        if ($valid) {
            $queryString .=
                " AND cui_expiry_date >= now() AND	renewalStatus  <> 'D' and declinedFlag <> 'Y'";
        }
        $this->setQueryString($queryString);

        return (parent::getRows());
    }

    public function getServerWatchContracts($customerID, $valid = true)
    {
        $queryString = "
      SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON cui_itemno = itm_itemno
             JOIN renewalType ON renewalType.renewalTypeID = item.renewalTypeID
             JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
	  	WHERE
	  		cui_custno = " . $customerID . " and itm_servercare_flag = 'Y' AND itm_desc LIKE '%ServerWatch%'";
        if ($valid) {
            $queryString .=
                " AND renewalStatus  <> 'D' and declinedFlag <> 'Y'";
        }
        $this->setQueryString($queryString);
        return (parent::getRows());
    }
}

?>