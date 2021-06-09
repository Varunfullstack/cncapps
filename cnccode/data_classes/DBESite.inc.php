<?php /*
* Site table
* NOTE: There are all sorts of workarounds for the fact that there is not a single
* primary key column. The primary key is composite customerID and siteNo
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBESite extends DBCNCEntity
{

    const customerID       = "customerID";
    const siteNo           = "siteNo";
    const add1             = "add1";
    const add2             = "add2";
    const add3             = "add3";
    const town             = "town";
    const county           = "county";
    const postcode         = "postcode";
    const invoiceContactID = "invoiceContactID";
    const deliverContactID = "deliverContactID";
    const debtorCode       = "debtorCode";
    const sageRef          = "sageRef";
    const phone            = "phone";
    const maxTravelHours   = "maxTravelHours";
    const activeFlag       = "activeFlag";
    const nonUKFlag        = "nonUKFlag";
    const what3Words       = "what3Words";

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
        $this->setTableName("Address");
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL,
            "add_custno"
        );
        $this->addColumn(
            self::siteNo,
            DA_ID,
            DA_ALLOW_NULL,
            "add_siteno"
        );
        $this->addColumn(
            self::add1,
            DA_STRING,
            DA_NOT_NULL,
            "add_add1"
        );
        $this->addColumn(
            self::add2,
            DA_STRING,
            DA_ALLOW_NULL,
            "add_add2"
        );
        $this->addColumn(
            self::add3,
            DA_STRING,
            DA_ALLOW_NULL,
            "add_add3"
        );
        $this->addColumn(
            self::town,
            DA_STRING,
            DA_NOT_NULL,
            "add_town"
        );
        $this->addColumn(
            self::county,
            DA_STRING,
            DA_ALLOW_NULL,
            "add_county"
        );
        $this->addColumn(
            self::postcode,
            DA_STRING,
            DA_NOT_NULL,
            "add_postcode"
        );
        $this->addColumn(
            self::invoiceContactID,
            DA_ID,
            DA_ALLOW_NULL,
            "add_inv_contno"
        );
        $this->addColumn(
            self::deliverContactID,
            DA_ID,
            DA_ALLOW_NULL,
            "add_del_contno"
        );
        $this->addColumn(
            self::debtorCode,
            DA_STRING,
            DA_ALLOW_NULL,
            "add_debtor_code"
        );
        $this->addColumn(
            self::sageRef,
            DA_STRING,
            DA_ALLOW_NULL,
            "add_sage_ref"
        );
        $this->addColumn(
            self::phone,
            DA_STRING,
            DA_ALLOW_NULL,
            "add_phone"
        );
        $this->addColumn(
            self::maxTravelHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "add_max_travel_hours"
        );
        $this->addColumn(
            self::activeFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "add_active_flag"
        );
        $this->addColumn(
            self::nonUKFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "add_non_uk_flag"
        );
        $this->addColumn(
            self::what3Words,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->setPK(1);        // NOTE: This is not really the PK, just the second element
        $this->setAddColumnsOff();
        $this->setNewRowValue(
            -9
        );        // This allows for fact that first siteNo is zero. Used in DataAccess->replicate()
    }

    /**
     * It is a workaround for the lack of a single PK column.
     * @access public
     * @return bool Success
     * function getPKValue(){
     * return $this->getValue(self::SiteNo);
     * }
     * Get string to be used as WHERE statement for update/get/delete statements.
     * @access public
     * @return string Where clause for update statements
     */
    function getPKWhere()
    {
        return ($this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(
                self::customerID
            ) . ' AND ' . $this->getDBColumnName(self::siteNo) . '=' . $this->getFormattedValue(self::siteNo));
    }

    /**
     * Allocates the next site number for this customer
     * @access private
     * @param void
     * @return integer Next Siteno
     */
    function getNextPKValue()
    {
        $this->dbeNextPK->setQueryString(
            'SELECT MAX(' . $this->getDBColumnName(self::siteNo) . ') + 1 FROM ' . $this->getTableName(
            ) . ' WHERE ' . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(self::customerID)
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
            if (($this->getName($ixCol) != self::customerID) && ($this->getName($ixCol) != self::siteNo)) {
                if ($colString != "") $colString = $colString . ",";
                $colString = $colString . $this->getDBColumnName($ixCol) . "=" . $this->prepareForSQL($ixCol);
            }
        }
        return $colString;
    }

    /**
     * Return all rows from DB
     * @access public
     * @param bool $activeFlag
     * @return bool Success
     */
    function getRowsByCustomerID($activeFlag = true)
    {

        $this->setMethodName("getRowsByCustomerID");
        if ($this->getValue(self::customerID) == "") {
            $this->raiseError('CustomerID not set');
        }
        $queryString = 'SELECT ' . $this->getDBColumnNamesAsString() . ' FROM ' . $this->getTableName(
            ) . ' WHERE ' . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(self::customerID);
        if ($activeFlag) {
            $queryString .= ' AND add_active_flag = "Y"';
        }
        $queryString .= ' ORDER BY ' . $this->getDBColumnName(self::siteNo);
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
        if ($this->getValue(self::customerID) == "") {
            $this->raiseError('CustomerID not set');
        }
        $quey = 'SELECT ' . $this->getDBColumnNamesAsString() . ' FROM ' . $this->getTableName(
            ) . ' WHERE ' . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(
                self::customerID
            ) . ' AND ' . $this->getDBColumnName(self::siteNo) . '=' . $this->getFormattedValue(self::siteNo);
        $this->setQueryString($quey);
        return (parent::getRow());
    }

    function getRowByPostcode($customerID,
                              $postcode
    )
    {
        $this->setMethodName("getRowByPostcode");
        $this->setQueryString(
            'SELECT ' . $this->getDBColumnNamesAsString() . ' FROM ' . $this->getTableName(
            ) . ' WHERE ' . $this->getDBColumnName(
                self::customerID
            ) . '="' . $customerID . '"' . ' AND ' . $this->getDBColumnName(self::postcode) . '="' . $postcode . '"'
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
            "SELECT COUNT(*)" . " FROM " . $this->getTableName() . " WHERE " . $this->getDBColumnName(
                self::sageRef
            ) . " = '" . $sageRef . "'"
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
        if ($this->getValue(self::customerID) == '') {
            $this->raiseError('CustomerID not set');
        }
        $this->setQueryString(
            "DELETE " . " FROM " . $this->getTableName() . " WHERE " . $this->getDBColumnName(
                self::customerID
            ) . '=' . $this->getFormattedValue(self::customerID)
        );
        return (parent::runQuery());
    }

    /**
     * Delete Row By CustomerID SiteNo
     * @access public
     * @return bool Success
     */
    function deleteRow($pkValue = null): bool
    {
        $this->setMethodName("deleteRow");
        if ($this->getValue(self::customerID) == '') {
            $this->raiseError('CustomerID not set');
        }
        $this->setQueryString(
            "DELETE " . " FROM " . $this->getTableName() . " WHERE " . $this->getDBColumnName(
                self::customerID
            ) . '=' . $this->getFormattedValue(self::customerID) . " AND " . $this->getDBColumnName(
                self::siteNo
            ) . '=' . $this->getFormattedValue(self::siteNo)
        );
        return (parent::runQuery()); // ensures it goes to SCOTrans and deleted on UNIX box
    }
}

?>