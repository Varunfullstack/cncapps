<?php /*
* New contact table class
* used lower case 1st letter and names in line with order table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEContact extends DBCNCEntity
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
        $this->setTableName("Contact");
        $this->addColumn("contactID", DA_ID, DA_NOT_NULL, "con_contno");
        $this->addColumn("siteNo", DA_ID, DA_ALLOW_NULL, "con_siteno");
        $this->addColumn("customerID", DA_ID, DA_ALLOW_NULL, "con_custno");
        $this->addColumn("supplierID", DA_ID, DA_ALLOW_NULL, "con_suppno");
        $this->addColumn("title", DA_STRING, DA_ALLOW_NULL, "con_title");
        $this->addColumn("lastName", DA_STRING, DA_NOT_NULL, "con_last_name");
        $this->addColumn("position", DA_STRING, DA_ALLOW_NULL, "con_position");
        $this->addColumn("firstName", DA_STRING, DA_NOT_NULL, "con_first_name");
        $this->addColumn("email", DA_STRING, DA_ALLOW_NULL, "con_email");
        $this->addColumn("phone", DA_STRING, DA_ALLOW_NULL, "con_phone");
        $this->addColumn("mobilePhone", DA_STRING, DA_ALLOW_NULL, "con_mobile_phone");
        $this->addColumn("fax", DA_STRING, DA_ALLOW_NULL, "con_fax");
        $this->addColumn("portalPassword", DA_STRING, DA_ALLOW_NULL, "con_portal_password");
        $this->addColumn("sendMailshotFlag", DA_YN, DA_NOT_NULL, "con_mailshot");
        $this->addColumn("discontinuedFlag", DA_YN, DA_NOT_NULL, "con_discontinued");
        $this->addColumn("accountsFlag", DA_YN, DA_NOT_NULL, "con_accounts_flag");
        $this->addColumn("statementFlag", DA_YN, DA_NOT_NULL, "con_statement_flag");
        $this->addColumn("mailshot1Flag", DA_YN, DA_NOT_NULL, "con_mailflag1");
        $this->addColumn("mailshot2Flag", DA_YN, DA_NOT_NULL, "con_mailflag2");
        $this->addColumn("mailshot3Flag", DA_YN, DA_NOT_NULL, "con_mailflag3");
        $this->addColumn("mailshot4Flag", DA_YN, DA_NOT_NULL, "con_mailflag4");
        $this->addColumn("mailshot5Flag", DA_YN, DA_NOT_NULL, "con_mailflag5");
        $this->addColumn("mailshot6Flag", DA_YN, DA_NOT_NULL, "con_mailflag6");
        $this->addColumn("mailshot7Flag", DA_YN, DA_NOT_NULL, "con_mailflag7");
        $this->addColumn("mailshot8Flag", DA_YN, DA_NOT_NULL, "con_mailflag8");
        $this->addColumn("mailshot9Flag", DA_YN, DA_NOT_NULL, "con_mailflag9");
        $this->addColumn("mailshot10Flag", DA_YN, DA_NOT_NULL, "con_mailflag10");
        $this->addColumn("notes", DA_STRING, DA_ALLOW_NULL, "con_notes");
        $this->addColumn("workStartedEmailFlag", DA_YN, DA_ALLOW_NULL, "con_work_started_email_flag");
        $this->addColumn("autoCloseEmailFlag", DA_YN, DA_ALLOW_NULL, "con_auto_close_email_flag");
        $this->addColumn("failedLoginCount", DA_INTEGER, DA_ALLOW_NULL, "con_failed_login_count");

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Return Rows By CustomerID And SiteNo
     * @access public
     * @return bool Success
     */
    function getRowsByCustomerID($supportOnly = true)
    {
        $this->setMethodName("getRowsByCustomerID");
        if ($this->getValue('customerID') == '') {
            $this->raiseError('customerID not set');
        }
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('customerID') . '=' . $this->getFormattedValue('customerID');

        $sql .=
            " AND (
        con_mailshot = 'Y' OR
        con_mailflag1 = 'Y' OR
        con_mailflag2 = 'Y' OR
        con_mailflag3 = 'Y' OR
        con_mailflag4 = 'Y' OR
        con_mailflag5 = 'Y' OR
        con_mailflag6 = 'Y' OR
        con_mailflag7 = 'Y' OR
        con_mailflag8 = 'Y' OR
        con_mailflag9 = 'Y' OR
        con_mailflag10 = 'Y'
        )
        ";

        if ($supportOnly) {
            $sql .= " AND " . $this->getDBColumnName('mailshot5Flag') . " = 'Y'";    // only nominated support contacts
        }

        $sql .= " ORDER BY con_siteno, con_mailflag10 DESC, con_last_name, con_first_name";

        $this->setQueryString($sql);

        return (parent::getRows());
    }

    /**
     * Return Rows By CustomerID And SiteNo
     * @access public
     * @return bool Success
     */
    function getRowsByCustomerIDSiteNo($supportOnly = false)
    {
        $this->setMethodName("getRowsByCustomerIDSiteNo");
        if ($this->getValue('customerID') == '') {
            $this->raiseError('customerID not set');
        }
        /*
                if ($this->getValue('siteNo')==''){
                    $this->raiseError('siteNo not set');
                }
        */
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('customerID') . '=' . $this->getFormattedValue('customerID') .
            " AND " . $this->getDBColumnName('discontinuedFlag') . " <> 'Y'" .
            " AND " . $this->getDBColumnName('siteNo') . '=' . $this->getFormattedValue('siteNo');

        if ($supportOnly) {
            $sql .= " AND " . $this->getDBColumnName('mailshot5Flag') . " = 'Y'";        // only nominated support contacts
        }

        $sql .=
            " ORDER BY " . $this->getDBColumnName('lastName');

        $this->setQueryString($sql);

        return (parent::getRows());
    }

    /**
     * Get rows by name match
     * Excludes discontinued rows
     * @access public
     * @return bool Success
     */
    function getSupplierContactRowsByNameMatch($match)
    {
        $this->setMethodName("getSupplierContactRowsByNameMatch");
        $ret = FALSE;
        if ($this->getValue('supplierID') == '') {
            $this->raiseError('supplierID not set');
        }
        if ($match == '') {
            $this->raiseError('$match not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE (" . $this->getDBColumnName('lastName') . " LIKE '%" . mysqli_real_escape_string($this->db->link_id(), $match) . "%'" .
            " OR " . $this->getDBColumnName('firstName') . " LIKE '%" . mysqli_real_escape_string($this->db->link_id(), $match) . "%')" .
            " AND " . $this->getDBColumnName('discontinuedFlag') . " <> 'Y'" .
            " AND " . $this->getDBColumnName('supplierID') . " = " . $this->getFormattedValue('supplierID') .
            " ORDER BY " . $this->getDBColumnName('lastName') . "," . $this->getDBColumnName('firstName')

        );
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Get customer/site rows by name match
     * Excludes discontinued rows
     * @access public
     * @return bool Success
     */
    function getCustomerRowsByNameMatch($match)
    {
        $this->setMethodName("getCustomerRowsByNameMatch");
        $ret = FALSE;
        if ($this->getValue('customerID') == '') {
            $this->raiseError('customerID not set');
        }
        /*
                if ($this->getValue('siteNo')==''){
                    $this->raiseError('siteNo not set');
                }
        */
        if ($match == '') {
            $this->raiseError('$match not set');
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE (" . $this->getDBColumnName('lastName') . " LIKE '%" . mysqli_real_escape_string($this->db->link_id(), $match) . "%'" .
            " OR " . $this->getDBColumnName('firstName') . " LIKE '%" . mysqli_real_escape_string($this->db->link_id(), $match) . "%')" .
            " AND " . $this->getDBColumnName('discontinuedFlag') . " <> 'Y'" .
            " AND " . $this->getDBColumnName('customerID') . " = " . $this->getFormattedValue('customerID');

        if ($this->getValue('siteNo') != '') {
            $queryString .=
                " AND " . $this->getDBColumnName('siteNo') . " = " . $this->getFormattedValue('siteNo');
        }
        $queryString .=
            " AND (
        con_mailshot = 'Y' OR
        con_mailflag1 = 'Y' OR
        con_mailflag2 = 'Y' OR
        con_mailflag3 = 'Y' OR
        con_mailflag4 = 'Y' OR
        con_mailflag5 = 'Y' OR
        con_mailflag6 = 'Y' OR
        con_mailflag7 = 'Y' OR
        con_mailflag8 = 'Y' OR
        con_mailflag9 = 'Y' OR
        con_mailflag10 = 'Y'
        )
        ";

        $queryString .=
            " ORDER BY " . $this->getDBColumnName('lastName') . "," . $this->getDBColumnName('firstName');
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * all rows for given supplier
     */
    function getSupplierRows()
    {
        if ($this->getValue('supplierID') == '') {
            $this->raiseError('supplierID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('discontinuedFlag') . " <> 'Y'" .
            " AND " . $this->getDBColumnName('supplierID') . " = " . $this->getFormattedValue('supplierID') .
            " ORDER BY " . $this->getDBColumnName('lastName') . "," . $this->getDBColumnName('firstName')

        );
        return (parent::getRows());
    }

    /* contact to send gsc statements to */
    function getGSCRowsByCustomerID($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(CONFIG_HEADER_GSC_STATEMENT_FLAG) . " = 'Y'" .
            " AND " . $this->getDBColumnName('customerID') . " = " . $customerID
        );
        return (parent::getRows());
    }

    function getMainSupportRowsByCustomerID($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(CONFIG_HEADER_MAIN_CONTACT_FLAG) . " = 'Y'" .
            " AND " . $this->getDBColumnName('customerID') . " = " . $customerID
        );
        return (parent::getRows());
    }

    function getSupportRows($customerID = false)
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(CONFIG_HEADER_SUPPORT_CONTACT_FLAG) . " = 'Y'
        AND (SELECT cus_prospect = 'N' FROM customer WHERE con_custno = cus_custno )";

        if ($customerID) {
            $sql .= " AND con_custno = " . $customerID;
        }
        $this->setQueryString($sql);

        return (parent::getRows());
    }

    function getTechnicalMailshotRows()
    {
        $this->setQueryString(
            "SELECT
        contact.*
      FROM contact
          JOIN customer ON cus_custno = con_custno
      WHERE cus_mailshot = 'Y'
        AND " . $this->getDBColumnName(CONFIG_HEADER_TECHNICAL_MAILSHOT_CONTACT_FLAG) . " = 'Y'
        AND " . $this->getDBColumnName(CONFIG_HEADER_SUPPORT_CONTACT_FLAG) . " = 'Y'
        AND cus_prospect = 'N'");

        return (parent::getRows());
    }

    function getInvoiceContactsByCustomerID($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(CONFIG_HEADER_INVOICE_CONTACT) . " = 'Y'" .
            " AND " . $this->getDBColumnName('customerID') . " = " . $customerID

        );
        return (parent::getRows());
    }
}

?>