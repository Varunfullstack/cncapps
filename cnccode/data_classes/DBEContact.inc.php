<?php /*
* Contact table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEContact extends DBCNCEntity
{
    const FURLOUGH_ACTION_TO_FURLOUGH   = 1;
    const FURLOUGH_ACTION_TO_UNFURLOUGH = 2;


    const contactID        = "contactID";
    const siteNo           = "siteNo";
    const customerID       = "customerID";
    const title            = "title";
    const position         = "position";
    const lastName         = "lastName";
    const firstName        = "firstName";
    const email            = "email";
    const phone            = "phone";
    const mobilePhone      = "mobilePhone";
    const fax              = "fax";
    const portalPassword   = "portalPassword";
    const mailshot         = "mailshot";
    const mailshot2Flag    = "mailshot2Flag";
    const mailshot3Flag    = "mailshot3Flag";
    const mailshot8Flag    = "mailshot8Flag";
    const mailshot9Flag    = "mailshot9Flag";
    const mailshot11Flag   = "mailshot11Flag";
    const notes            = "notes";
    const failedLoginCount = "failedLoginCount";
    const reviewUser       = "reviewUser";
    const hrUser           = "hrUser";

    const supportLevel = "supportLevel";

    const supportLevelMain       = 'main';
    const supportLevelSupervisor = 'supervisor';
    const supportLevelSupport    = 'support';
    const supportLevelDelegate   = 'delegate';
    const supportLevelFurlough   = 'furlough';

    const initialLoggingEmail = 'initialLoggingEmail';

    const othersInitialLoggingEmailFlag = 'othersInitialLoggingEmailFlag';
    const othersWorkUpdatesEmailFlag    = 'othersWorkUpdatesEmailFlag';
    const othersFixedEmailFlag          = 'othersFixedEmailFlag';

    const pendingLeaverFlag           = 'pendingLeaverFlag';
    const pendingLeaverDate           = 'pendingLeaverDate';
    const specialAttentionContactFlag = "specialAttentionContactFlag";
    const linkedInURL                 = "linkedInURL";
    const active                      = "active";
    const pendingFurloughAction       = 'pendingFurloughAction';
    const pendingFurloughActionDate   = 'pendingFurloughActionDate';
    const pendingFurloughActionLevel  = 'pendingFurloughActionLevel';

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
            DA_PHONE,
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
            self::mailshot,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
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
            self::failedLoginCount,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "con_failed_login_count"
        );
        $this->addColumn(
            self::reviewUser,
            DA_YN,
            DA_ALLOW_NULL,
            "reviewUser"
        );
        $this->addColumn(
            self::supportLevel,
            DA_SUPPORT_LEVEL,
            DA_ALLOW_NULL,
            'supportLevel'
        );
        $this->addColumn(
            self::hrUser,
            DA_YN,
            DA_NOT_NULL,
            "hrUser"
        );
        $this->addColumn(
            self::initialLoggingEmail,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::othersInitialLoggingEmailFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::othersWorkUpdatesEmailFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::othersFixedEmailFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::pendingLeaverFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::pendingLeaverDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::specialAttentionContactFlag,
            DA_YN_FLAG,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::linkedInURL,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::active,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            1
        );
        $this->addColumn(
            self::pendingFurloughAction,
            DA_INTEGER,
            DA_ALLOW_NULL,
        );
        $this->addColumn(
            self::pendingFurloughActionDate,
            DA_DATE,
            DA_ALLOW_NULL,
        );
        $this->addColumn(
            self::pendingFurloughActionLevel,
            DA_STRING,
            DA_ALLOW_NULL,
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
        $query = "SELECT " . $this->getDBColumnNamesAsString() . ", case when supportLevel = 'main' then 0
              when supportLevel = 'supervisor' then 1
              when supportLevel = 'support' then 2
              else 3
              end as orderSupport " . " FROM " . $this->getTableName() . " WHERE " . $this->getDBColumnName(
                self::customerID
            ) . '=' . $this->getFormattedValue(self::customerID);
        if (!$includeInactive) {
            $query .= " AND `active` ";
        }
        if ($supportOnly) {
            $query .= " AND " . $this->getDBColumnName(
                    self::supportLevel
                ) . " is not null and " . $this->getDBColumnName(
                    self::supportLevel
                ) . ' <> "" and supportLevel <> "furlough"';
        }
        $query .= " ORDER BY con_siteno, orderSupport, con_first_name, con_last_name";
        $this->setQueryString($query);
        return (parent::getRows());

    }

    function unfurlough()
    {
        $this->setValue(self::supportLevel, $this->getValue(self::pendingFurloughActionLevel));
        $this->setValue(self::pendingFurloughAction, null);
        $this->setValue(self::pendingFurloughActionDate, null);
        $this->setValue(self::pendingFurloughActionLevel, null);
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
        $query                 = "insert into contactauditlog select
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
    function deleteRowsByCustomerIDSiteNo()
    {
        global $db;
        $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();
        $query                 = "insert into contactauditlog select
                              'delete'                  as action,
                              current_timestamp         as createdAt,
                              $currentLoggedInUserID    as userId,
                              null                      as contactId,
                              contact.*
                            from contact
                            WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(
                self::customerID
            ) . " AND " . $this->getDBColumnName(self::siteNo) . '=' . $this->getFormattedValue(self::siteNo);
        $db->query($query);
        $this->setMethodName("deleteRowsByCustomerIDSiteNo");
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
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(self::customerID) . '=' . $this->getFormattedValue(
                self::customerID
            ) . " AND active AND " . $this->getDBColumnName(self::siteNo) . '=' . $this->getFormattedValue(
                self::siteNo
            );
        if ($supportOnly) {
            $sql .= " AND " . $this->getDBColumnName(self::supportLevel) . " = '" . self::supportLevelMain . "'";
        }
        $sql .= " ORDER BY " . $this->getDBColumnName(self::lastName);
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getSupportContactRowsByNameMatch($customerId, $match)
    {
        $this->setMethodName("getCustomerRowsByNameMatch");
        $this->setValue(
            self::customerID,
            $customerId
        );
        $escapedMatch = mysqli_real_escape_string(
            $this->db->link_id(),
            $match
        );
        $queryString  = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE (
        {$this->getDBColumnName(self::lastName)} LIKE '%{$escapedMatch}%' OR {$this->getDBColumnName(self::firstName)} LIKE '%{$escapedMatch}%'
    )  AND {$this->getDBColumnName(self::customerID)} = {$this->getFormattedValue(self::customerID)} 
                          AND {$this->getDBColumnName(self::supportLevel)} in('support' or 'main') 
                          and {$this->getDBColumnName(self::active)}
         ORDER BY {$this->getDBColumnName(self::lastName)},{$this->getDBColumnName(self::firstName)}";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Get customer/site rows by name match
     * Excludes discontinued rows
     * @access public
     * @param $customerId
     * @param $match
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
        $escapedMatch = mysqli_real_escape_string(
            $this->db->link_id(),
            $match
        );
        $queryString  = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE 
               (
                  {$this->getDBColumnName(self::lastName)} LIKE '%{$escapedMatch}%' OR
                  {$this->getDBColumnName(self::firstName)} LIKE '%{$escapedMatch}%'
               ) 
               AND
               {$this->getDBColumnName(self::customerID)} = {$this->getFormattedValue(self::customerID)}";
        if ($this->getValue(self::siteNo) != '') {
            $queryString .= " AND {$this->getDBColumnName(self::siteNo)} = {$this->getFormattedValue(
                    self::siteNo
                )}";
        }
        $queryString .= " AND {$this->getDBColumnName(self::active)}  ORDER BY {$this->getDBColumnName(self::lastName)},{$this->getDBColumnName(self::firstName)}";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Get customer/site rows by name match
     * Excludes discontinued rows
     * @access public
     * @param $customerId
     * @param $match
     * @return bool Success
     */
    function getCustomerRowsByContactFullNameMatch($customerId,
                                                   $match
    )
    {
        $this->setMethodName("getCustomerRowsByNameMatch");
        $this->setValue(
            self::customerID,
            $customerId
        );
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE concat(".$this->getDBColumnName(self::firstName).",' ', ".$this->getDBColumnName(self::lastName).") like '%" . mysqli_real_escape_string(
                $this->db->link_id(),
                $match
            ) . "%' AND " . $this->getDBColumnName(
                self::active
            ) . "  AND " . $this->getDBColumnName(self::customerID) . " = " . $this->getFormattedValue(
                self::customerID
            );
        if ($this->getValue(self::siteNo) != '') {
            $queryString .= " AND " . $this->getDBColumnName(self::siteNo) . " = " . $this->getFormattedValue(
                    self::siteNo
                );
        }
        $queryString .= " AND `active` ";
        $queryString .= " ORDER BY " . $this->getDBColumnName(self::lastName) . "," . $this->getDBColumnName(
                self::firstName
            );
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /* contact to send gsc statements to */
    function getGSCRowsByCustomerID($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                CONFIG_HEADER_GSC_STATEMENT_FLAG
            ) . " = 'Y'" . " AND active and " . $this->getDBColumnName(self::customerID) . " = " . $customerID
        );
        return (parent::getRows());
    }

    function getMainSupportRowsByCustomerID($customerID,
                                            $includeSupervisors = false
    )
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(self::customerID) . " = " . $customerID;
        if (!$includeSupervisors) {
            $sql .= " AND " . $this->getDBColumnName(self::supportLevel) . " = '" . self::supportLevelMain . "'";
        } else {
            $sql .= " AND " . $this->getDBColumnName(
                    self::supportLevel
                ) . " in ('" . self::supportLevelMain . "','" . self::supportLevelSupervisor . "')";
        }
        $sql .= " and active ";
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getAuthorisingRows($customerID)
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::supportLevel
            ) . " in ('main', 'supervisor') and active  AND con_custno = " . $customerID;
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getSupportRows($customerID = false)
    {
        $dbeCustomer = new DBECustomer($this);
        $sql         = "SELECT {$this->getDBColumnNamesAsString()}
FROM {$this->getTableName()}
         left join {$dbeCustomer->getTableName()} on {$this->getDBColumnName(DBEContact::customerID)} = {$dbeCustomer->getDBColumnName(DBECustomer::customerID)}
WHERE {$this->getDBColumnName(self::supportLevel)} is not null
  and {$this->getDBColumnName(self::supportLevel)} <> ''
  AND {$dbeCustomer->getDBColumnName(DBECustomer::becameCustomerDate)} is not null
  and {$dbeCustomer->getDBColumnName(DBECustomer::droppedCustomerDate)} is null and active ";
        if ($customerID) {
            $sql .= " AND {$this->getDBColumnName(self::customerID)} = " . $customerID;
        }
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getInvoiceContactsByCustomerID($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                CONFIG_HEADER_INVOICE_CONTACT
            ) . " = 'Y'" . " AND active and " . $this->getDBColumnName(self::customerID) . " = " . $customerID
        );
        return (parent::getRows());
    }

    function getMainContacts($customerID)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::supportLevel
            ) . " = '" . self::supportLevelMain . "'" . " AND active and " . $this->getDBColumnName(
                self::customerID
            ) . " = " . $customerID
        );
        return (parent::getRows());
    }

    /**
     * @param null $leadStatusID
     * @return DBEContact $this
     */
    function getContactsByLeadStatus($leadStatusID = null,$customerID=null)
    {
        $sqlQuery = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " left join customer on con_custno = cus_custno WHERE active and ";
        if ($leadStatusID) {
            $sqlQuery .= " leadStatusId = $leadStatusID";
        } else {
            $sqlQuery .= " leadStatusId is not null and leadStatusId <> 0";
        }
        if ($customerID) {
            $sqlQuery .= " and cus_custno = $customerID";
        }  
        $this->setQueryString($sqlQuery);
        $this->getRows();
        return $this;
    }


    function insertRow()
    {
        $inserted              = parent::insertRow();
        $currentLoggedInUserID = USER_SYSTEM;
        if (isset($GLOBALS['auth'])) {
            $currentLoggedInUserID = $GLOBALS['auth']->is_authenticated();
        }
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
        $readQuery     = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " where con_contno = " . $this->getFormattedValue(
                self::contactID
            );
        $result        = $db->query($readQuery);
        $readRow       = $result->fetch_assoc();
        $stringColumns = $this->getDBColumnNamesAsString();
        $columns       = explode(
            ',',
            $stringColumns
        );
        $counter       = 0;
        $hasChanged    = false;
        while (!$hasChanged && count($columns) > $counter) {
            if ($this->getValue($counter) != $readRow[$columns[$counter]]) {
                $hasChanged = true;
            }
            $counter++;
        }
        $updated = parent::updateRow();
        if (isset($GLOBALS['auth'])) {
            $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();
        } else {
            $currentLoggedInUserID = USER_SYSTEM;
        }
        if ($updated && $hasChanged) {
            $query = "insert into contactauditlog select
                              'update'                  as action,
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
        return $updated;
    }

    public function deleteRow($pkValue = null)
    {
        global $db;
        if (isset($GLOBALS['auth'])) {
            $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();
        } else {
            $currentLoggedInUserID = USER_SYSTEM;
        }
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


    public function getContactsWithPendingFurloughActionForToday()
    {
        $sqlQuery = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " left join customer on con_custno = cus_custno 
             WHERE " . $this->getDBColumnName(
                self::pendingFurloughAction
            ) . " is not null  and active and " . $this->getDBColumnName(
                self::pendingFurloughActionDate
            ) . " <= curdate() ";
        $this->setQueryString($sqlQuery);
        $this->getRows();
        return $this;
    }

    public function getTodayLeavers()
    {
        $sqlQuery = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " left join customer on con_custno = cus_custno 
             WHERE " . $this->getDBColumnName(
                self::pendingLeaverFlag
            ) . " = 'Y' and active and " . $this->getDBColumnName(self::pendingLeaverDate) . " <= curdate() ";
        $this->setQueryString($sqlQuery);
        $this->getRows();
        return $this;
    }

    public function getReviewContactsByCustomerID($customerID)
    {
        $sqlQuery = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::reviewUser
            ) . " = 'Y' and active and " . $this->getDBColumnName(self::customerID) . " = " . $customerID;
        $this->setQueryString($sqlQuery);
        $this->getRows();
        return $this;
    }

    public function getSpecialAttentionCustomers()
    {
        $this->setMethodName("getSpecialAttentionContacts");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " where specialAttentionContactFlag = 'Y' and active
      ORDER BY con_custno, con_contno";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    public function getRowsByDomain($prefix)
    {
        $this->setMethodName("getSpecialAttentionContacts");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " where  active and con_email like '%@$prefix' ORDER BY con_custno, con_contno";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    public function getReviewContacts($customerID)
    {
        $this->setMethodName("getReviewContacts");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " where active and " . $this->getDBColumnName(
                DBEContact::reviewUser
            ) . " = 'Y' and " . $this->getDBColumnName(
                DBEContact::customerID
            ) . " = $customerID
      ORDER BY con_custno, con_contno";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;

    }

    public function validateUniqueEmail($email, $contactID)
    {
        $query      = "select count(con_contno) as count from contact where con_email = ? and con_contno <> ? and active";
        $parameters = [
            [
                'type'  => 's',
                'value' => $email
            ],
            [
                'type'  => 'd',
                'value' => $contactID
            ],
        ];
        $result     = $this->db->preparedQuery($query, $parameters);
        $data       = $result->fetch_assoc();
        if ($data['count'] > 0) {
            return false;
        }
        return true;
    }

    public function getOthersWorkUpdateRowsByCustomerID($customerID)
    {
        $this->setMethodName("getSpecialAttentionContacts");
        $queryString = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} where {$this->getDBColumnName(self::othersWorkUpdatesEmailFlag)} = 'Y' and active and {$this->getDBColumnName(DBEContact::customerID)}  = $customerID ORDER BY con_custno, con_contno";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }
}
