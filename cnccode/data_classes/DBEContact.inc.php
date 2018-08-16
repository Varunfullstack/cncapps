<?php /*
* Contact table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEContact extends DBCNCEntity
{
    const contactID = "contactID";
    const siteNo = "siteNo";
    const customerID = "customerID";
    const supplierID = "supplierID";
    const title = "title";
    const position = "position";
    const lastName = "lastName";
    const firstName = "firstName";
    const email = "email";
    const phone = "phone";
    const mobilePhone = "mobilePhone";
    const fax = "fax";
    const portalPassword = "portalPassword";
    const sendMailshotFlag = "sendMailshotFlag";
    const discontinuedFlag = "discontinuedFlag";
    const accountsFlag = "accountsFlag";
    const mailshot2Flag = "mailshot2Flag";
    const mailshot3Flag = "mailshot3Flag";
    const mailshot4Flag = "mailshot4Flag";
    const mailshot8Flag = "mailshot8Flag";
    const mailshot9Flag = "mailshot9Flag";
    const mailshot11Flag = "mailshot11Flag";
    const notes = "notes";
    const workStartedEmailFlag = "workStartedEmailFlag";
    const autoCloseEmailFlag = "autoCloseEmailFlag";
    const failedLoginCount = "failedLoginCount";
    const othersEmailFlag = 'othersEmailMainFlag';
    const othersWorkStartedEmailFlag = "othersWorkStartedEmailFlag";
    const othersAutoCloseEmailFlag = "othersAutoCloseEmailFlag";
    const reviewUser = "reviewUser";
    const supportLevel = "supportLevel";
    const hrUser = "hrUser";


    const supportLevelMain = 'main';
    const supportLevelSupport = 'support';
    const supportLevelSupportDelegate = 'delegate';

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
        $this->addColumn(
            self::contactID,
            DA_ID,
            DA_NOT_NULL,
            "con_contno"
        );
        $this->addColumn(
            self::siteNo,
            DA_ID,
            DA_ALLOW_NULL,
            "con_siteno"
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_ALLOW_NULL,
            "con_custno"
        );
        $this->addColumn(
            self::supplierID,
            DA_ID,
            DA_ALLOW_NULL,
            "con_suppno"
        );
        $this->addColumn(
            self::title,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_title"
        );
        $this->addColumn(
            self::position,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_position"
        );
        $this->addColumn(
            self::lastName,
            DA_STRING,
            DA_NOT_NULL,
            "con_last_name"
        );
        $this->addColumn(
            self::firstName,
            DA_STRING,
            DA_NOT_NULL,
            "con_first_name"
        );
        $this->addColumn(
            self::email,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_email"
        );
        $this->addColumn(
            self::phone,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_phone"
        );
        $this->addColumn(
            self::mobilePhone,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_mobile_phone"
        );
        $this->addColumn(
            self::fax,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_fax"
        );
        $this->addColumn(
            self::portalPassword,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_portal_password"
        );
        $this->addColumn(
            self::sendMailshotFlag,
            DA_YN,
            DA_NOT_NULL,
            "con_mailshot"
        );
        $this->addColumn(
            self::discontinuedFlag,
            DA_YN,
            DA_NOT_NULL,
            "con_discontinued"
        );
        $this->addColumn(
            self::accountsFlag,
            DA_YN,
            DA_NOT_NULL,
            "con_accounts_flag"
        );
        $this->addColumn(
            self::mailshot2Flag,
            DA_YN,
            DA_NOT_NULL,
            "con_mailflag2"
        );
        $this->addColumn(
            self::mailshot3Flag,
            DA_YN,
            DA_NOT_NULL,
            "con_mailflag3"
        );
        $this->addColumn(
            self::mailshot4Flag,
            DA_YN,
            DA_NOT_NULL,
            "con_mailflag4"
        );
        $this->addColumn(
            self::mailshot8Flag,
            DA_YN,
            DA_NOT_NULL,
            "con_mailflag8"
        );
        $this->addColumn(
            self::mailshot9Flag,
            DA_YN,
            DA_NOT_NULL,
            "con_mailflag9"
        );
        $this->addColumn(
            self::mailshot11Flag,
            DA_YN,
            DA_NOT_NULL,
            "con_mailflag11"
        );
        $this->addColumn(
            self::notes,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_notes"
        );
        $this->addColumn(
            self::workStartedEmailFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_work_started_email_flag"
        );
        $this->addColumn(
            self::autoCloseEmailFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_auto_close_email_flag"
        );
        $this->addColumn(
            self::failedLoginCount,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "con_failed_login_count"
        );
        $this->addColumn(
            self::othersEmailFlag,
            DA_YN_FLAG,
            DA_NOT_NULL,
            "othersEmailFlag"
        );
        $this->addColumn(
            self::othersWorkStartedEmailFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "othersWorkStartedEmailFlag"
        );
        $this->addColumn(
            self::othersAutoCloseEmailFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "othersAutoCloseEmailFlag"
        );
        $this->addColumn(
            self::reviewUser,
            DA_YN,
            DA_ALLOW_NULL,
            "reviewUser"
        );
        $this->addColumn(
            self::supportLevel,
            DA_STRING,
            DA_ALLOW_NULL,
            'supportLevel'
        );

        $this->addColumn(
            self::hrUser,
            DA_YN,
            DA_NOT_NULL,
            "hrUser"
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Return Rows By CustomerID
     * @access public
     * @param $customerID
     * @param bool $includeInactive
     * @param bool $supportOnly
     * @return bool Success
     */
    function getRowsByCustomerID($customerID,
                                 $includeInactive = false,
                                 $supportOnly = false
    )
    {
        $this->setValue(
            self::customerID,
            $customerID
        );
        $query =
            "SELECT " . $this->getDBColumnNamesAsString() .
            ", case when supportLevel = 'main' then 0
              when supportLevel = 'support' then 1
              else 2
              end as orderSupport " .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(self::customerID);

        if (!$includeInactive) {
            $query .=
                " AND (
					con_mailshot = 'Y' OR
					con_mailflag2 = 'Y' OR
					con_mailflag3 = 'Y' OR
					con_mailflag4 = 'Y' OR
					con_mailflag8 = 'Y' OR
					con_mailflag9 = 'Y' OR
					supportLevel is not null
					)
					";
        }

        if ($supportOnly) {
            $query .= " AND " . $this->getDBColumnName(
                    self::supportLevel
                ) . " = " . self::supportLevelSupport;    // only nominated support contacts
        }


        $query .= " ORDER BY con_siteno, orderSupport, con_first_name, con_last_name";
        $this->setQueryString($query);

        return (parent::getRows());

    }

    /**
     * Delete Rows By CustomerID
     * @access public
     * @return bool Success
     */
    function deleteRowsByCustomerID()
    {
        //log the rows that are going to be deleted

        global $db;
        $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();

        $query = "insert into contactauditlog select
                              'delete'                  as action,
                              current_timestamp         as createdAt,
                              $currentLoggedInUserID    as userId,
                              null                      as contactId,
                              contact.*
                            from contact
                            WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(
                self::customerID
            );

        $db->query($query);

        $this->setMethodName("deleteRowsByCustomerID");
        if ($this->getValue(self::customerID) == '') {
            $this->raiseError('CustomerID not set');
        }
        $this->setQueryString(
            "DELETE " .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(self::customerID)
        );
        return (parent::runQuery());
    }

    /**
     * Delete Row By CustomerID SiteNo
     * @access public
     * @return bool Success
     */
    function deleteRowsByCustomerIDSiteNo()
    {
        global $db;
        $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();

        $query = "insert into contactauditlog select
                              'delete'                  as action,
                              current_timestamp         as createdAt,
                              $currentLoggedInUserID    as userId,
                              null                      as contactId,
                              contact.*
                            from contact
                            WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(
                self::customerID
            ) .
            " AND " . $this->getDBColumnName(self::siteNo) . '=' . $this->getFormattedValue(self::siteNo);

        $db->query($query);
        $this->setMethodName("deleteRowsByCustomerIDSiteNo");
        if ($this->getValue(self::customerID) == '') {
            $this->raiseError('CustomerID not set');
        }
        $this->setQueryString(
            "DELETE " .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(self::customerID) .
            " AND " . $this->getDBColumnName(self::siteNo) . '=' . $this->getFormattedValue(self::siteNo)
        );
        return (parent::runQuery()); // ensures it goes to SCOTrans and deleted on UNIX box
    }

    /**
     * Return Rows By CustomerID And SiteNo
     * @access public
     * @param $customerID
     * @param $siteNo
     * @param bool $supportOnly
     * @return bool Success
     */
    function getRowsByCustomerIDSiteNo($customerID,
                                       $siteNo,
                                       $supportOnly = false
    )
    {
        $this->setMethodName("getRowsByCustomerIDSiteNo");

        $this->setValue(
            self::customerID,
            $customerID
        );
        $this->setValue(
            self::siteNo,
            $siteNo
        );

        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(self::customerID) .
            " AND " . $this->getDBColumnName(self::discontinuedFlag) . " <> 'Y'" .
            " AND " . $this->getDBColumnName(self::siteNo) . '=' . $this->getFormattedValue(self::siteNo);

        if ($supportOnly) {
            $sql .= " AND " . $this->getDBColumnName(self::supportLevel) . " = '" . self::supportLevelMain . "'";
        }
        $sql .=
            " ORDER BY " . $this->getDBColumnName(self::lastName);

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
        if ($this->getValue(self::supplierID) == '') {
            $this->raiseError('supplierID not set');
        }
        if ($match == '') {
            $this->raiseError('$match not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE (" . $this->getDBColumnName(self::lastName) . " LIKE '%" . mysqli_real_escape_string(
                $this->db->link_id(),
                $match
            ) . "%'" .
            " OR " . $this->getDBColumnName(self::firstName) . " LIKE '%" . mysqli_real_escape_string(
                $this->db->link_id(),
                $match
            ) . "%')" .
            " AND " . $this->getDBColumnName(self::discontinuedFlag) . " <> 'Y'" .
            " AND " . $this->getDBColumnName(self::supplierID) . " = " . $this->getFormattedValue(self::supplierID) .
            " ORDER BY " . $this->getDBColumnName(self::lastName) . "," . $this->getDBColumnName(self::firstName)

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
    function getCustomerRowsByNameMatch($customerId,
                                        $match
    )
    {
        $this->setMethodName("getCustomerRowsByNameMatch");
        $this->setValue(
            self::customerID,
            $customerId
        );
        if ($match == '') {
            $this->raiseError('$match not set');
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE (" . $this->getDBColumnName(self::lastName) . " LIKE '%" . mysqli_real_escape_string(
                $this->db->link_id(),
                $match
            ) . "%'" .
            " OR " . $this->getDBColumnName(self::firstName) . " LIKE '%" . mysqli_real_escape_string(
                $this->db->link_id(),
                $match
            ) . "%')" .
            " AND " . $this->getDBColumnName(self::discontinuedFlag) . " <> 'Y'" .
            " AND " . $this->getDBColumnName(self::customerID) . " = " . $this->getFormattedValue(self::customerID);

        if ($this->getValue(self::siteNo) != '') {
            $queryString .=
                " AND " . $this->getDBColumnName(self::siteNo) . " = " . $this->getFormattedValue(self::siteNo);
        }
        $queryString .=
            " AND (
        con_mailshot = 'Y' OR
        con_mailflag2 = 'Y' OR
        con_mailflag3 = 'Y' OR
        con_mailflag4 = 'Y' Or
        con_mailflag8 = 'Y' OR
        con_mailflag9 = 'Y' OR
        con_mailflag11 = 'Y'
        )
        ";

        $queryString .=
            " ORDER BY " . $this->getDBColumnName(self::lastName) . "," . $this->getDBColumnName(self::firstName);
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * all rows for given supplier
     */
    function getSupplierRows()
    {
        if ($this->getValue(self::supplierID) == '') {
            $this->raiseError('supplierID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::discontinuedFlag) . " <> 'Y'" .
            " AND " . $this->getDBColumnName(self::supplierID) . " = " . $this->getFormattedValue(self::supplierID) .
            " ORDER BY " . $this->getDBColumnName(self::lastName) . "," . $this->getDBColumnName(self::firstName)

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
            " AND " . $this->getDBColumnName(self::customerID) . " = " . $customerID
        );
        return (parent::getRows());
    }

    function getMainSupportRowsByCustomerID($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::supportLevel) . " = '" . self::supportLevelMain . "'" .
            " AND " . $this->getDBColumnName(self::customerID) . " = " . $customerID;
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getSupportRows($customerID = false)
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::supportLevel) .
            " is no null AND (SELECT cus_prospect = 'N' FROM customer WHERE con_custno = cus_custno )";

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
        AND cus_prospect = 'N'"
        );

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
            " AND " . $this->getDBColumnName(self::customerID) . " = " . $customerID

        );
        return (parent::getRows());
    }

    function getMainContacts($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::supportLevel) . " = '" . self::supportLevelMain . "'" .
            " AND " . $this->getDBColumnName(self::customerID) . " = " . $customerID

        );
        return (parent::getRows());
    }

    /**
     * @param null $leadStatusID
     * @return DBEContact $this
     */
    function getMainContactsByLeadStatus($leadStatusID = null)
    {
        $sqlQuery =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " left join customer on con_custno = cus_custno 
             WHERE " . $this->getDBColumnName(self::supportLevel) . " = '" . self::supportLevelMain . "'";

        if ($leadStatusID) {
            $sqlQuery .= " and customer_lead_status_id = $leadStatusID";
        } else {
            $sqlQuery .= " and customer_lead_status_id is not null and customer_lead_status_id <> 0";
        }
        $this->setQueryString($sqlQuery);

        $this->getRows();
        return $this;
    }


    function insertRow()
    {
        $inserted = parent::insertRow();

        $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();
        global $db;

        if ($inserted) {
            $query = "insert into contactauditlog select
                              'insert'                  as action,
                              current_timestamp         as createdAt,
                              $currentLoggedInUserID    as userId,
                              null                      as contactId,
                              contact.*
                            from contact
                            where con_contno = " . $this->getFormattedValue(
                    self::contactID
                );

            $db->query($query);
        }

        return $inserted;
    }

    function updateRow()
    {
        global $db;
        // pull the data before it's updated
        $readQuery = "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . " where con_contno = " . $this->getFormattedValue(
                self::contactID
            );
        $result = $db->query($readQuery);

        $readRow = $result->fetch_assoc();

        $stringColumns = $this->getDBColumnNamesAsString();

        $columns = explode(
            ',',
            $stringColumns
        );


        $counter = 0;
        $hasChanged = false;

        while (!$hasChanged && count($columns) > $counter) {
            if ($this->getValue($counter) != $readRow[$columns[$counter]]) {
                $hasChanged = true;
            }
            $counter++;
        }

        $updated = parent::updateRow();
        $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();


        if ($updated && $hasChanged) {
            $query = "insert into contactauditlog select
                              'update'                  as action,
                              current_timestamp         as createdAt,
                              $currentLoggedInUserID as userId,
                              null                      as contactId,
                              contact.*
                            from contact
                            where con_contno = " . $this->getFormattedValue(
                    self::contactID
                );

            $db->query($query);
        }

        return $updated;
    }

    public function deleteRow($pkValue = null)
    {
        global $db;
        $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();

        $query = "insert into contactauditlog select
                              'delete'                  as action,
                              current_timestamp         as createdAt,
                              $currentLoggedInUserID as userId,
                              null                      as contactId,
                              contact.*
                            from contact
                            where con_contno = " . $this->getFormattedValue(
                self::contactID
            );

        $db->query($query);

        return parent::deleteRow($pkValue);
    }
}

?>