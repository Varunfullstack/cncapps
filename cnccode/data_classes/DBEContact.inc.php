<?php /*
* Contact table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEContact extends DBCNCEntity
{
    const ContactID = "ContactID";
    const SiteNo = "SiteNo";
    const CustomerID = "CustomerID";
    const SupplierID = "SupplierID";
    const Title = "Title";
    const Position = "Position";
    const LastName = "LastName";
    const FirstName = "FirstName";
    const Email = "Email";
    const Phone = "Phone";
    const MobilePhone = "MobilePhone";
    const Fax = "Fax";
    const PortalPassword = "PortalPassword";
    const SendMailshotFlag = "SendMailshotFlag";
    const DiscontinuedFlag = "DiscontinuedFlag";
    const AccountsFlag = "AccountsFlag";
    const StatementFlag = "StatementFlag";
    const Mailshot1Flag = "Mailshot1Flag";
    const Mailshot2Flag = "Mailshot2Flag";
    const Mailshot3Flag = "Mailshot3Flag";
    const Mailshot4Flag = "Mailshot4Flag";
    const Mailshot5Flag = "Mailshot5Flag";
    const Mailshot6Flag = "Mailshot6Flag";
    const Mailshot7Flag = "Mailshot7Flag";
    const Mailshot8Flag = "Mailshot8Flag";
    const Mailshot9Flag = "Mailshot9Flag";
    const Mailshot10Flag = "Mailshot10Flag";
    const Mailshot11Flag = "Mailshot11Flag";
    const Notes = "Notes";
    const WorkStartedEmailFlag = "WorkStartedEmailFlag";
    const AutoCloseEmailFlag = "AutoCloseEmailFlag";
    const FailedLoginCount = "FailedLoginCount";

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
        $this->addColumn(self::ContactID, DA_ID, DA_NOT_NULL, "con_contno");
        $this->addColumn(self::SiteNo, DA_ID, DA_ALLOW_NULL, "con_siteno");
        $this->addColumn(self::CustomerID, DA_ID, DA_ALLOW_NULL, "con_custno");
        $this->addColumn(self::SupplierID, DA_ID, DA_ALLOW_NULL, "con_suppno");
        $this->addColumn(self::Title, DA_STRING, DA_ALLOW_NULL, "con_title");
        $this->addColumn(self::Position, DA_STRING, DA_ALLOW_NULL, "con_position");
        $this->addColumn(self::LastName, DA_STRING, DA_ALLOW_NULL, "con_last_name");
        $this->addColumn(self::FirstName, DA_STRING, DA_ALLOW_NULL, "con_first_name");
        $this->addColumn(self::Email, DA_STRING, DA_ALLOW_NULL, "con_email");
        $this->addColumn(self::Phone, DA_STRING, DA_ALLOW_NULL, "con_phone");
        $this->addColumn(self::MobilePhone, DA_STRING, DA_ALLOW_NULL, "con_mobile_phone");
        $this->addColumn(self::Fax, DA_STRING, DA_ALLOW_NULL, "con_fax");
        $this->addColumn(self::PortalPassword, DA_STRING, DA_ALLOW_NULL, "con_portal_password");
        $this->addColumn(self::SendMailshotFlag, DA_YN, DA_NOT_NULL, "con_mailshot");
        $this->addColumn(self::DiscontinuedFlag, DA_YN, DA_ALLOW_NULL, "con_discontinued");
        $this->addColumn(self::AccountsFlag, DA_YN, DA_NOT_NULL, "con_accounts_flag");
        $this->addColumn(self::StatementFlag, DA_YN, DA_NOT_NULL, "con_statement_flag");
        $this->addColumn(self::Mailshot1Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag1");
        $this->addColumn(self::Mailshot2Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag2");
        $this->addColumn(self::Mailshot3Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag3");
        $this->addColumn(self::Mailshot4Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag4");
        $this->addColumn(self::Mailshot5Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag5");
        $this->addColumn(self::Mailshot6Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag6");
        $this->addColumn(self::Mailshot7Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag7");
        $this->addColumn(self::Mailshot8Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag8");
        $this->addColumn(self::Mailshot9Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag9");
        $this->addColumn(self::Mailshot10Flag, DA_YN, DA_ALLOW_NULL, "con_mailflag10");
        $this->addColumn("Mailshot11Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag11");
        $this->addColumn(self::Notes, DA_STRING, DA_ALLOW_NULL, "con_notes");
        $this->addColumn(self::WorkStartedEmailFlag, DA_YN, DA_ALLOW_NULL, "con_work_started_email_flag");
        $this->addColumn(self::AutoCloseEmailFlag, DA_YN, DA_ALLOW_NULL, "con_auto_close_email_flag");
        $this->addColumn(self::FailedLoginCount, DA_INTEGER, DA_ALLOW_NULL, "con_failed_login_count");
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
        if ($this->getValue('CustomerID') == '') {
            $this->raiseError('CustomerID not set');
        }
        $query =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('CustomerID') . '=' . $this->getFormattedValue('CustomerID');

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
    function deleteRowsByCustomerIDSiteNo()
    {
        $this->setMethodName("deleteRowsByCustomerIDSiteNo");
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
             WHERE " . $this->getDBColumnName(self::Mailshot10Flag) . " = 'Y' ";

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