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
    const statementFlag = "statementFlag";
    const mailshot1Flag = "mailshot1Flag";
    const mailshot2Flag = "mailshot2Flag";
    const mailshot3Flag = "mailshot3Flag";
    const mailshot4Flag = "mailshot4Flag";
    const mailshot5Flag = "mailshot5Flag";
    const mailshot6Flag = "mailshot6Flag";
    const mailshot7Flag = "mailshot7Flag";
    const mailshot8Flag = "mailshot8Flag";
    const mailshot9Flag = "mailshot9Flag";
    const mailshot10Flag = "mailshot10Flag";
    const mailshot11Flag = "mailshot11Flag";
    const notes = "notes";
    const workStartedEmailFlag = "workStartedEmailFlag";
    const autoCloseEmailFlag = "autoCloseEmailFlag";
    const failedLoginCount = "failedLoginCount";

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
            DA_ALLOW_NULL,
            "con_last_name"
        );
        $this->addColumn(
            self::firstName,
            DA_STRING,
            DA_ALLOW_NULL,
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
            DA_ALLOW_NULL,
            "con_discontinued"
        );
        $this->addColumn(
            self::accountsFlag,
            DA_YN,
            DA_NOT_NULL,
            "con_accounts_flag"
        );
        $this->addColumn(
            self::statementFlag,
            DA_YN,
            DA_NOT_NULL,
            "con_statement_flag"
        );
        $this->addColumn(
            self::mailshot1Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag1"
        );
        $this->addColumn(
            self::mailshot2Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag2"
        );
        $this->addColumn(
            self::mailshot3Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag3"
        );
        $this->addColumn(
            self::mailshot4Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag4"
        );
        $this->addColumn(
            self::mailshot5Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag5"
        );
        $this->addColumn(
            self::mailshot6Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag6"
        );
        $this->addColumn(
            self::mailshot7Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag7"
        );
        $this->addColumn(
            self::mailshot8Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag8"
        );
        $this->addColumn(
            self::mailshot9Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag9"
        );
        $this->addColumn(
            self::mailshot10Flag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_mailflag10"
        );
        $this->addColumn(
            "Mailshot11Flag",
            DA_YN,
            DA_ALLOW_NULL,
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
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Return Rows By CustomerID
     * @access public
     * @return bool Success
     */
    function getRowsByCustomerID($includeInactive = false)
    {
        $this->setMethodName("getRowsByCustomerID");
        if ($this->getValue(self::customerID) == '') {
            $this->raiseError('CustomerID not set');
        }
        $query =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(self::customerID);

        if (!$includeInactive) {

            $query .=
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
        }

        $query .= " ORDER BY con_mailflag10 DESC, con_first_name, con_last_name";

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
     * @param null $leadStatusID
     * @return DBEContact $this
     */
    function getMainContactsByLeadStatus($leadStatusID = null)
    {
        $sqlQuery =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " left join customer on con_custno = cus_custno 
             WHERE " . $this->getDBColumnName(self::mailshot10Flag) . " = 'Y' ";

        if ($leadStatusID) {
            $sqlQuery .= " and customer_lead_status_id = $leadStatusID";
        } else {
            $sqlQuery .= " and customer_lead_status_id is not null and customer_lead_status_id <> 0";
        }
        $this->setQueryString($sqlQuery);

        $this->getRows();
        return $this;
    }
}

?>