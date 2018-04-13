<?php /*
* Site table
* NOTE: There are all sorts of workarounds for the fact that there is not a single
* primary key column. The primary key is composite customerID and siteNo
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBESite extends DBCNCEntity
{

    const CustomerID = "CustomerID";
    const SiteNo = "SiteNo";
    const Add1 = "Add1";
    const Add2 = "Add2";
    const Add3 = "Add3";
    const Town = "Town";
    const County = "County";
    const Postcode = "Postcode";
    const InvoiceContactID = "InvoiceContactID";
    const DeliverContactID = "DeliverContactID";
    const DebtorCode = "DebtorCode";
    const SageRef = "SageRef";
    const Phone = "Phone";
    const MaxTravelHours = "MaxTravelHours";
    const ActiveFlag = "ActiveFlag";
    const NonUKFlag = "NonUKFlag";

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
        $this->setTableName("Address");
        $this->addColumn(self::CustomerID, DA_ID, DA_NOT_NULL, "add_custno");
        $this->addColumn(self::SiteNo, DA_ID, DA_ALLOW_NULL, "add_siteno");
        $this->addColumn(self::Add1, DA_STRING, DA_NOT_NULL, "add_add1");
        $this->addColumn(self::Add2, DA_STRING, DA_ALLOW_NULL, "add_add2");
        $this->addColumn(self::Add3, DA_STRING, DA_ALLOW_NULL, "add_add3");
        $this->addColumn(self::Town, DA_STRING, DA_NOT_NULL, "add_town");
        $this->addColumn(self::County, DA_STRING, DA_ALLOW_NULL, "add_county");
        $this->addColumn(self::Postcode, DA_STRING, DA_NOT_NULL, "add_postcode");
        $this->addColumn(self::InvoiceContactID, DA_ID, DA_ALLOW_NULL, "add_inv_contno");
        $this->addColumn(self::DeliverContactID, DA_ID, DA_ALLOW_NULL, "add_del_contno");
        $this->addColumn(self::DebtorCode, DA_STRING, DA_ALLOW_NULL, "add_debtor_code");
        $this->addColumn(self::SageRef, DA_STRING, DA_ALLOW_NULL, "add_sage_ref");
        $this->addColumn(self::Phone, DA_STRING, DA_ALLOW_NULL, "add_phone");
        $this->addColumn(self::MaxTravelHours, DA_INTEGER, DA_ALLOW_NULL, "add_max_travel_hours");
        $this->addColumn(self::ActiveFlag, DA_YN, DA_ALLOW_NULL, "add_active_flag");
        $this->addColumn(self::NonUKFlag, DA_YN, DA_ALLOW_NULL, "add_non_uk_flag");
        $this->setPK(1);        // NOTE: This is not really the PK, just the second element
        $this->setAddColumnsOff();
        $this->setNewRowValue(-9);        // This allows for fact that first siteNo is zero. Used in DataAccess->replicate()
    }

    /**
     * It is a workaround for the lack of a single PK column.
     * @access public
     * @return bool Success
     * function getPKValue(){
     * return $this->getValue('SiteNo');
     * }
     * Get string to be used as WHERE statement for update/get/delete statements.
     * @access public
     * @return string Where clause for update statements
     */
    function getPKWhere()
    {
        return (
            $this->getDBColumnName('CustomerID') . '=' . $this->getFormattedValue('CustomerID') .
            ' AND ' . $this->getDBColumnName('SiteNo') . '=' . $this->getFormattedValue('SiteNo')
        );
    }

    /**
     * Allocates the next site number for this customer
     * @access private
     * @param  void
     * @return integer Next Siteno
     */
    function getNextPKValue()
    {
        $this->dbeNextPK->setQueryString(
            'SELECT MAX(' . $this->getDBColumnName('SiteNo') . ') + 1 FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getDBColumnName('CustomerID') . '=' . $this->getFormattedValue('CustomerID')
        );
        if ($this->dbeNextPK->runQuery()) {
            if ($this->dbeNextPK->nextRecord()) {
                $siteNo = $this->dbeNextPK->getDBColumnValue(0);
            }
        }
        if ($siteNo == null) {
            $siteNo = '0';
        }
        $this->dbeNextPK->resetQueryString();
        return $siteNo;
    }

    /**
     * Build and return string that can be used by update() function
     * @access private
     * @return string Update SQL statement
     */
    function getUpdateString()
    {
        $colString = "";
        for ($ixCol = 0; $ixCol < $this->colCount(); $ixCol++) {
            // exclude primary key columns
            if (($this->getName($ixCol) != 'CustomerID') & ($this->getName($ixCol) != 'SiteNo')) {
                if ($colString != "") $colString = $colString . ",";
                $colString = $colString . $this->getDBColumnName($ixCol) . "='" .
                    $this->prepareForSQL($this->getValue($ixCol)) . "'";
            }
        }
        return $colString;
    }

    /**
     * Return all rows from DB
     * @access public
     * @return bool Success
     */
    function getRowsByCustomerID($activeFlag = 'Y')
    {
        $this->setMethodName("getRowsByCustomerID");
        if ($this->getValue('CustomerID') == "") {
            $this->raiseError('CustomerID not set');
        }
        $queryString =
            'SELECT ' . $this->getDBColumnNamesAsString() .
            ' FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getDBColumnName('CustomerID') . '=' . $this->getFormattedValue('CustomerID');

        if ($activeFlag == 'Y') {
            $queryString .= ' AND add_active_flag = "Y"';
        }

        $queryString .= ' ORDER BY ' . $this->getDBColumnName('SiteNo');

        $this->setQueryString($queryString);
        return (parent::getRows());
    }

    /**
     * Return row by customerid and site no
     * @access public
     * @return bool Success
     */
    function getRowByCustomerIDSiteNo()
    {
        $this->setMethodName("getRowByCustomerIDSiteNo");
        if ($this->getValue('CustomerID') == "") {
            $this->raiseError('CustomerID not set');
        }
        $this->setQueryString(
            'SELECT ' . $this->getDBColumnNamesAsString() .
            ' FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getDBColumnName('CustomerID') . '=' . $this->getFormattedValue('CustomerID') .
            ' AND ' . $this->getDBColumnName('SiteNo') . '=' . $this->getFormattedValue('SiteNo')
        );
        return (parent::getRow());
    }

    function getRowByPostcode($customerID, $postcode)
    {
        $this->setMethodName("getRowByPostcode");
        $this->setQueryString(
            'SELECT ' . $this->getDBColumnNamesAsString() .
            ' FROM ' . $this->getTableName() .
            ' WHERE ' . $this->getDBColumnName('CustomerID') . '="' . $customerID . '"' .
            ' AND ' . $this->getDBColumnName('Postcode') . '="' . $postcode . '"'
        );
        return (parent::getRow());
    }

    /**
     * Test for unique Sage Ref
     * @access public
     * @return bool Success
     */
    function uniqueSageRef($sageRef)
    {
        $this->setMethodName("uniqueSageRef");
        $this->setQueryString(
            "SELECT COUNT(*)" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('SageRef') . " = '" . $sageRef . "'"
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $count = $this->getDBColumnValue(0);
            }
        }
        if ($count == 0) {
            $ret = TRUE;
        } else {
            $ret = FALSE;
        }
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Delete Rows By CustomerID
     * @access public
     * @return bool Success
     */
    function deleteRowsByCustomerID()
    {
        $this->setMethodName("deleteRowsByCustomerID");
        if ($this->getValue('CustomerID') == '') {
            $this->raiseError('CustomerID not set');
        }
        $this->setQueryString(
            "DELETE " .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('CustomerID') . '=' . $this->getFormattedValue('CustomerID')
        );
        return (parent::runQuery());
    }

    /**
     * Delete Row By CustomerID SiteNo
     * @access public
     * @return bool Success
     */
    function deleteRow()
    {
        $this->setMethodName("deleteRow");
        if ($this->getValue('CustomerID') == '') {
            $this->raiseError('CustomerID not set');
        }
        $this->setQueryString(
            "DELETE " .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('CustomerID') . '=' . $this->getFormattedValue('CustomerID') .
            " AND " . $this->getDBColumnName('SiteNo') . '=' . $this->getFormattedValue('SiteNo')
        );
        return (parent::runQuery()); // ensures it goes to SCOTrans and deleted on UNIX box
    }
}

?>