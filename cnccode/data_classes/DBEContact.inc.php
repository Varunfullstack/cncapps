<?php /*
* Contact table
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
        $this->addColumn("ContactID", DA_ID, DA_NOT_NULL, "con_contno");
        $this->addColumn("SiteNo", DA_ID, DA_ALLOW_NULL, "con_siteno");
        $this->addColumn("CustomerID", DA_ID, DA_ALLOW_NULL, "con_custno");
        $this->addColumn("SupplierID", DA_ID, DA_ALLOW_NULL, "con_suppno");
        $this->addColumn("Title", DA_STRING, DA_ALLOW_NULL, "con_title");
        $this->addColumn("Position", DA_STRING, DA_ALLOW_NULL, "con_position");
        $this->addColumn("LastName", DA_STRING, DA_ALLOW_NULL, "con_last_name");
        $this->addColumn("FirstName", DA_STRING, DA_ALLOW_NULL, "con_first_name");
        $this->addColumn("Email", DA_STRING, DA_ALLOW_NULL, "con_email");
        $this->addColumn("Phone", DA_STRING, DA_ALLOW_NULL, "con_phone");
        $this->addColumn("MobilePhone", DA_STRING, DA_ALLOW_NULL, "con_mobile_phone");
        $this->addColumn("Fax", DA_STRING, DA_ALLOW_NULL, "con_fax");
        $this->addColumn("PortalPassword", DA_STRING, DA_ALLOW_NULL, "con_portal_password");
        $this->addColumn("SendMailshotFlag", DA_YN, DA_NOT_NULL, "con_mailshot");
        $this->addColumn("DiscontinuedFlag", DA_YN, DA_ALLOW_NULL, "con_discontinued");
        $this->addColumn("AccountsFlag", DA_YN, DA_NOT_NULL, "con_accounts_flag");
        $this->addColumn("StatementFlag", DA_YN, DA_NOT_NULL, "con_statement_flag");
        $this->addColumn("Mailshot1Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag1");
        $this->addColumn("Mailshot2Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag2");
        $this->addColumn("Mailshot3Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag3");
        $this->addColumn("Mailshot4Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag4");
        $this->addColumn("Mailshot5Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag5");
        $this->addColumn("Mailshot6Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag6");
        $this->addColumn("Mailshot7Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag7");
        $this->addColumn("Mailshot8Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag8");
        $this->addColumn("Mailshot9Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag9");
        $this->addColumn("Mailshot10Flag", DA_YN, DA_ALLOW_NULL, "con_mailflag10");
        $this->addColumn("mailshot10Flag")
        $this->addColumn("Notes", DA_STRING, DA_ALLOW_NULL, "con_notes");
        $this->addColumn("WorkStartedEmailFlag", DA_YN, DA_ALLOW_NULL, "con_work_started_email_flag");
        $this->addColumn("AutoCloseEmailFlag", DA_YN, DA_ALLOW_NULL, "con_auto_close_email_flag");
        $this->addColumn("FailedLoginCount", DA_INTEGER, DA_ALLOW_NULL, "con_failed_login_count");
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
}

?>