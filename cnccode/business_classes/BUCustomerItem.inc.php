<?php
/**
 * Customer Item business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 *
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCustomerItem.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItemDocument.inc.php");
require_once($cfg["path_dbe"] . "/DBEJContract.inc.php");
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");

class BUCustomerItem extends Business
{

    const searchFormStartDate      = 'startDate';
    const searchFormEndDate        = 'endDate';
    const searchFormCustomerID     = 'customerID';
    const searchFormCustomerItemID = 'customerItemID';
    const searchFormCustomerName   = 'customerName';
    const searchFormItemText       = 'itemText';
    const searchFormContractText   = 'contractText';
    const searchFormSerialNo       = 'serialNo';
    const searchFormOrdheadID      = 'ordheadID';
    const searchFormContractFlag   = 'contractFlag';
    const searchFormRenewalStatus  = 'renewalStatus';

    /** @var DBEJCustomerItem */
    public $dbeJCustomerItem;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJCustomerItem = new DBEJCustomerItem($this);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::searchFormStartDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormEndDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCustomerItemID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCustomerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormItemText,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormContractText,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormSerialNo,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormOrdheadID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormContractFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormRenewalStatus,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::searchFormStartDate,
            null
        );
        $dsData->setValue(
            self::searchFormEndDate,
            null
        );
        $dsData->setValue(
            self::searchFormCustomerID,
            null
        );
        $dsData->setValue(
            self::searchFormCustomerName,
            null
        );
        $dsData->setValue(
            self::searchFormItemText,
            null
        );
        $dsData->setValue(
            self::searchFormContractText,
            null
        );
        $dsData->setValue(
            self::searchFormSerialNo,
            null
        );
        $dsData->setValue(
            self::searchFormOrdheadID,
            null
        );
        $dsData->setValue(
            self::searchFormRenewalStatus,
            null
        );
    }

    /**
     * @param DataSet $dsSearchForm
     * @param DataSet $dsResults
     * @param int $row_limit
     */
    function search(&$dsSearchForm,
                    &$dsResults,
                    $row_limit = 1000
    )
    {
        $this->setMethodName('search');
        $dsSearchForm->initialise();
        $dsSearchForm->fetchNext();
        $itemText     = trim($dsSearchForm->getValue(self::searchFormItemText));
        $contractText = trim($dsSearchForm->getValue(self::searchFormContractText));
        if ($dsSearchForm->getValue(self::searchFormCustomerItemID)) {
            $this->getCustomerItemByID(
                $dsSearchForm->getValue(self::searchFormCustomerItemID),
                $dsResults
            );
        } else {
            if ($itemText && $itemText[0] == '?') {  // get all customer items if ? passed in
                $itemText = null;
            }
            $this->dbeJCustomerItem->getRowsBySearchCriteria(
                $dsSearchForm->getValue(self::searchFormCustomerID),
                $dsSearchForm->getValue(self::searchFormOrdheadID),
                $dsSearchForm->getValue(self::searchFormStartDate),
                $dsSearchForm->getValue(self::searchFormEndDate),
                $itemText,
                $contractText,
                $dsSearchForm->getValue(self::searchFormSerialNo),
                $dsSearchForm->getValue(self::searchFormRenewalStatus),
                $row_limit
            );
            $this->dbeJCustomerItem->initialise();
            $dsResults = $this->dbeJCustomerItem;
        }
    }

    function getCustomerItemByID($ID,
                                 &$dsResults
    )
    {
        $this->setMethodName('getCustomerItemByID');
        $this->dbeJCustomerItem->getRow($ID);
        $this->getData(
            $this->dbeJCustomerItem,
            $dsResults
        );
    }

    /**
     * @param $clientID
     * @return bool
     */
    function clientHasDirectDebit($clientID)
    {
        return $this->dbeJCustomerItem->getCountCustomerDirectDebitItems($clientID) > 0;
    }

    function getServerCareValidContractsByCustomerID($customerID,
                                                     &$dsResults
    )
    {
        $this->setMethodName('getServerCareValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getServerCareContracts($customerID);
        $this->getData(
            $dbeJContract,
            $dsResults
        );
    }

    function getServerWatchContractByCustomerID($customerID,
                                                &$dsResults
    )
    {
        $this->setMethodName('getServerCareValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getServerWatchContracts($customerID);
        $this->getData(
            $dbeJContract,
            $dsResults
        );
    }

    function getContractDescriptionsByCustomerItemId($customerItemID)
    {
        $this->setMethodName('getContractsByCustomerID');
        return $this->dbeJCustomerItem->getContractDescriptionsByCustomerItemID($customerItemID);
    }

    function getContractIDsByCustomerItemID($customerItemID)
    {
        $this->setMethodName('getContractsByCustomerItemID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getRowsByCustomerItemID($customerItemID);
        $contractIDs = false;
        while ($dbeJContract->fetchNext()) {
            $contractIDs[] = $dbeJContract->getValue(DBEJContract::customerItemID);
        }
        return $contractIDs;
    }

    /**
     * Create a new dataset containing defaults for new item row
     * @parameter DataSet &$dsResults results
     * @param DataSet $dsResults
     * @return bool : Success
     * @access public
     */
    function initialiseNewCustomerItem(&$dsResults)
    {
        $this->setMethodName('initialiseNewCustomerItem');
        // create/populate new dataset
//		$dsResults = new DSForm($this);
        $dsResults->copyColumnsFrom($this->dbeJCustomerItem);
        return TRUE;
    }

    /**
     * Update/Insert item to DB
     *    Only handles one row in dataset.
     *
     * @parameter DataSet &$dsResults results
     * @param DataSet|DBECustomerItem $dsCustomerItem
     * @param bool $contractIDs
     * @return bool : Success
     * @access public
     */
    function update(&$dsCustomerItem,
                    $contractIDs = false
    )
    {
        $this->setMethodName('update');
        $dsCustomerItem->fetchNext();
        $customerItemID = $dsCustomerItem->getValue(DBECustomerItem::customerItemID);
        $dbeCustomerItem = new DBECustomerItem($this);
        if ($dsCustomerItem->getValue(DBECustomerItem::customerItemID)) {
            $dbeCustomerItem->getRow($customerItemID);
        }
        $dsCustomerItem->setUpdateModeUpdate();
        /*
        If being suspended now, set suspended date and user
        */
        if ($dsCustomerItem->getValue(
                DBECustomerItem::secondsiteValidationSuspendUntilDate
            ) && $dbeCustomerItem->getValue(
                DBECustomerItem::secondsiteValidationSuspendUntilDate
            ) != $dsCustomerItem->getValue(
                DBECustomerItem::secondsiteValidationSuspendUntilDate
            )) {
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteSuspendedByUserID,
                $GLOBALS ['auth']->is_authenticated()
            );
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteSuspendedDate,
                date(DATE_MYSQL_DATE)
            );
        } else {
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteSuspendedByUserID,
                $dbeCustomerItem->getValue(DBECustomerItem::secondsiteSuspendedByUserID)
            );
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteSuspendedDate,
                $dbeCustomerItem->getValue(DBECustomerItem::secondsiteSuspendedDate)
            );
        }
        if ($dsCustomerItem->getValue(
                DBECustomerItem::offsiteReplicationValidationSuspendedUntilDate
            ) && $dbeCustomerItem->getValue(
                DBECustomerItem::offsiteReplicationValidationSuspendedUntilDate
            ) != $dsCustomerItem->getValue(
                DBECustomerItem::offsiteReplicationValidationSuspendedUntilDate
            )) {
            $dsCustomerItem->setValue(
                DBECustomerItem::offsiteReplicationSuspendedByUserID,
                $GLOBALS ['auth']->is_authenticated()
            );
            $dsCustomerItem->setValue(
                DBECustomerItem::offsiteReplicationSuspendedDate,
                date(DATE_MYSQL_DATE)
            );
        } else {
            $dsCustomerItem->setValue(
                DBECustomerItem::offsiteReplicationSuspendedByUserID,
                $dbeCustomerItem->getValue(DBECustomerItem::offsiteReplicationSuspendedByUserID)
            );
            $dsCustomerItem->setValue(
                DBECustomerItem::offsiteReplicationSuspendedDate,
                $dbeCustomerItem->getValue(DBECustomerItem::offsiteReplicationSuspendedDate)
            );
        }
        /*
        If being delayed now, set date and user
        */
        if ($dsCustomerItem->getValue(DBECustomerItem::secondsiteImageDelayDays) != '0' && $dbeCustomerItem->getValue(
                DBECustomerItem::secondsiteImageDelayDays
            ) != $dsCustomerItem->getValue(
                DBECustomerItem::secondsiteImageDelayDays
            )) {
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteImageDelayUserID,
                $GLOBALS ['auth']->is_authenticated()
            );
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteImageDelayDate,
                date(DATE_MYSQL_DATE)
            );
        } else {
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteImageDelayUserID,
                $dbeCustomerItem->getValue(DBECustomerItem::secondsiteImageDelayUserID)
            );
            $dsCustomerItem->setValue(
                DBECustomerItem::secondsiteImageDelayDate,
                $dbeCustomerItem->getValue(DBECustomerItem::secondsiteImageDelayDate)
            );

        }
        $dsCustomerItem->post();
        // if customerID changed then get default siteno and contact
        if ($dbeCustomerItem->getValue(DBECustomerItem::customerID) != $dsCustomerItem->getValue(
                DBECustomerItem::customerID
            ) && $customerItemID != 0) {
            $buCustomer = new BUCustomer($this);
            $dsSite     = new DataSet($this);
            $buCustomer->getDeliverSiteByCustomerID(
                $dsCustomerItem->getValue(DBECustomerItem::customerID),
                $dsSite,
                $dsContact
            );
            $dsCustomerItem->setUpdateModeUpdate();
            $dsCustomerItem->setValue(
                DBECustomerItem::siteNo,
                $dsSite->getValue(DBESite::siteNo)
            );
            $dsCustomerItem->post();
        }
        if ($success = $this->updateDataAccessObject(
            $dsCustomerItem,
            $dbeCustomerItem
        )) {
            $dbeCustomerItem->updateContract(
                $dbeCustomerItem->getPKValue(),
                $contractIDs
            );
        }
        return $success;
    }

    /**
     * Test whether given customer item has dependent entities
     *
     * @parameter integer $customerItemID
     * @return bool : TRUE = has dependencies
     * @access public
     */
    function canDelete()
    {
        $this->setMethodName('canDelete');
        return true;
    }

    function deleteCustomerItem($customerItemID)
    {
        $this->setMethodName('deleteCustomerItem');
        $dbeCustomerItem = new DBECustomerItem($this);
        if (!$dbeCustomerItem->getRow($customerItemID)) {
            $this->raiseError('Customer Item ' . $customerItemID . ' not found');
        } else {
            $dbeCustomerItem->deleteRow($customerItemID);
        }
    }

    /**
     * Upload document file
     * NOTE: Only expects one document
     * @param $customerItemID
     * @param $description
     * @param $userfile
     * @return bool : success
     * @access public
     */
    function uploadDocumentFile($customerItemID,
                                $description,
                                &$userfile
    )
    {
        $this->setMethodName('uploadDocumentFile');
        if (!$customerItemID) $this->raiseError('customerItemID not passed');
        if (!$description) $this->raiseError('description not passed');
        $dbeDocument = new DBECustomerItemDocument($this);
        $dbeDocument->setPKValue(null);
        $dbeDocument->setValue(
            DBECustomerItemDocument::customerItemID,
            $customerItemID
        );
        $dbeDocument->setValue(
            DBECustomerItemDocument::file,
            fread(
                fopen(
                    $userfile['tmp_name'],
                    'rb'
                ),
                $userfile['size']
            )
        );
        $dbeDocument->setValue(
            DBECustomerItemDocument::description,
            (string)$description
        );
        $dbeDocument->setValue(
            DBECustomerItemDocument::filename,
            (string)$userfile['name']
        );
        $dbeDocument->setValue(
            DBECustomerItemDocument::fileLength,
            (int)$userfile['size']
        );
        $dbeDocument->setValue(
            DBECustomerItemDocument::createUserID,
            (string)$GLOBALS['auth']->is_authenticated()
        );
        $dbeDocument->setValue(
            DBECustomerItemDocument::createDate,
            date('Y-m-d H:i:s')
        );
        $dbeDocument->setValue(
            DBECustomerItemDocument::fileMIMEType,
            (string)$userfile['type']
        );
        return ($dbeDocument->insertRow());
    }

    function getCustomerItemsByContractID($contractCustomerItemID,
                                          &$dsResults
    )
    {
        $this->setMethodName('getCustomerItemsByContractID');
        $dbeJCustomerItem = new DBEJCustomerItem($this);
        $dbeJCustomerItem->getItemsByContractID($contractCustomerItemID);
        return ($this->getData(
            $dbeJCustomerItem,
            $dsResults
        ));
    }

    function customerHasValidServerCareContract($customerID)
    {
        $this->setMethodName('customerHasValidServerCareContract');
        $dbeJCustomerItem = new DBEJCustomerItem($this);
        $dbeJCustomerItem->setValue(
            DBEJCustomerItem::customerID,
            $customerID
        );
        $dbeJCustomerItem->getRowsByColumn(DBEJCustomerItem::customerID);
        $hasContract = false;
        while ($dbeJCustomerItem->fetchNext()) {

            if ($dbeJCustomerItem->getValue(DBEJCustomerItem::servercareFlag) == 'Y' and $dbeJCustomerItem->getValue(
                    DBEJCustomerItem::expiryDate
                ) >= date(DATE_MYSQL_DATE)) {
                $hasContract = true;
            }
        }
        return $hasContract;
    }

    function getValidServerCareContractID($customerID)
    {
        $this->setMethodName('customerHasValidServerCareContract');
        $dbeJCustomerItem = new DBEJCustomerItem($this);
        $dbeJCustomerItem->setValue(
            DBEJCustomerItem::customerID,
            $customerID
        );
        $dbeJCustomerItem->getRowsByColumn(DBEJCustomerItem::customerID);
        while ($dbeJCustomerItem->fetchNext()) {

            if ($dbeJCustomerItem->getValue(DBEJCustomerItem::servercareFlag) == 'Y' and $dbeJCustomerItem->getValue(
                    DBEJCustomerItem::expiryDate
                ) >= date(DATE_MYSQL_DATE)) {
                return $dbeJCustomerItem->getValue(DBEJCustomerItem::customerItemID);
            }
        }
        return null;
    }

    function getMinResponseTime($customerID)
    {
        $minResponseHours = $this->getMinimumContractResponseHours($customerID);
        if ($minResponseHours > 0) {
            $minResponseTime = 'Response required within ' . $minResponseHours . ' hours<BR/><BR/>';
        } else {
            $minResponseTime = 'Response is on a best endeavours basis<BR/><BR/>';
        }
        return $minResponseTime;

    }

    function getMinimumContractResponseHours($customerID)
    {
        global $db; //PHPLib DB object
        $this->setMethodName('getMinimumContractResponseHours');
        $queryString = "
				select
					min(cui_sla_response_hours)
				from
					custitem
				where
					renewalStatus = 'R'
					and custitem.cui_sla_response_hours > 0
					and cui_custno = $customerID";
        $db->query($queryString);
        $db->next_record();
        if ($db->Record[0] == 0) {
            $ret = 10;           // default 10 hours if no contract
        } else {
            $ret = $db->Record[0];
        }
        return $ret;

    }

    /**
     * Get server customer items by customerID
     *
     * @param integer $customerID
     * @param $dsResults
     * @return bool
     */
    function getServersByCustomerID($customerID,
                                    &$dsResults
    )
    {
        if (!$customerID) {

            $this->raiseError('No customerID passed');

        }
        $dbeJCustomerItem = new DBEJCustomerItem($this);
        $dbeJCustomerItem->setValue(
            DBEJCustomerItem::customerID,
            $customerID
        );
        $dbeJCustomerItem->getServersByCustomerID();
        return ($this->getData(
            $dbeJCustomerItem,
            $dsResults
        ));

    }

    function addContractToCustomerItems($contractID,
                                        $customerItemIDArray
    )
    {
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->addContractToCustomerItems(
            $contractID,
            $customerItemIDArray
        );
    }

    function removeContractFromCustomerItems($contractID,
                                             $customerItemIDArray
    )
    {
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->removeContractFromCustomerItems(
            $contractID,
            $customerItemIDArray
        );
    }

    /**
     * @param $customerID
     * @return bool
     */
    function customerHasServiceDeskContract($customerID)
    {
        $dsContract = new DataSet($this);
        $this->getContractsByCustomerID(
            $customerID,
            $dsContract,
            null
        );
        while ($dsContract->fetchNext()) {

            if ($dsContract->getValue(DBEJContract::itemTypeID) == CONFIG_SERVICEDESK_ITEMTYPEID) {
                return true;
            }

        }
        return false;
    }

    /**
     * @param $customerID
     * @param $dsResults
     * @param int|null $itemID
     */
    function getContractsByCustomerID($customerID,
                                      &$dsResults,
                                      int $itemID = null
    )
    {
        $this->setMethodName('getContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getRowsByCustomerID($customerID, $itemID);
        $this->getData(
            $dbeJContract,
            $dsResults
        );
    }

    function serverIsUnderLocalSecondsiteContract($customerItemID)
    {
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getRowsByCustomerItemID($customerItemID);
        while ($dbeJContract->fetchNext()) {
            if (in_array(
                $dbeJContract->getValue(DBEJContract::itemTypeID),
                [CONFIG_2NDSITE_LOCAL_ITEMTYPEID, CONFIG_2NDSITE_CNC_ITEMTYPEID]
            )) {
                return true;
            }
        }
        return false;
    }

    public function getServiceDeskValidContractsByCustomerID($customerID,
                                                             &$dsResults
    )
    {
        $this->setMethodName('getServiceDeskValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getServiceDeskContracts($customerID);
        $this->getData(
            $dbeJContract,
            $dsResults
        );
    }

    public function getPrepayContractByCustomerID($customerID,
                                                  &$dsResults
    )
    {
        $this->setMethodName('getServiceDeskValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getPrePayContracts($customerID);
        $this->getData(
            $dbeJContract,
            $dsResults
        );
    }
}
