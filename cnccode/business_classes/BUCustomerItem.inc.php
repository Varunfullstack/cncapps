<?php
/**
 * Customer Item business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 *
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCustomerItem.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItemDocument.inc.php");
require_once($cfg["path_dbe"] . "/DBEJContract.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerNew.inc.php");

class BUCustomerItem extends Business
{
    var $dbeJCustomerItem = '';

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
        $dsData->addColumn('startDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('endDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('customerItemID', DA_INTEGER, DA_ALLOW_NULL);
        $dsData->addColumn('customerName', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('itemText', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('contractText', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('serialNo', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('ordheadID', DA_INTEGER, DA_ALLOW_NULL);
//		$dsData->addColumn('contractID', DA_INTEGER, DA_ALLOW_NULL);
        $dsData->addColumn('contractFlag', DA_YN, DA_ALLOW_NULL);
        $dsData->addColumn('renewalStatus', DA_STRING, DA_ALLOW_NULL);
        $dsData->setValue('startDate', '');
        $dsData->setValue('endDate', '');
        $dsData->setValue('customerID', '');
        $dsData->setValue('customerName', '');
        $dsData->setValue('itemText', '');
        $dsData->setValue('contractText', '');
        $dsData->setValue('serialNo', '');
        $dsData->setValue('ordheadID', '');
        $dsData->setValue('renewalStatus', '');
    }

    function search(&$dsSearchForm, &$dsResults, $row_limit = 1000)
    {
        $this->setMethodName('search');
        $dsSearchForm->initialise();
        $dsSearchForm->fetchNext();
        $itemText = trim($dsSearchForm->getValue('itemText'));
        $contractText = trim($dsSearchForm->getValue('contractText'));
        if ($dsSearchForm->getValue('customerItemID') != '') {
            $this->getCustomerItemByID($dsSearchForm->getValue('customerItemID'), $dsResults);
        } else {
            if ($itemText{0} == '?') {  // get all customer items if ? passed in
                $itemText = '';
            }
            $this->dbeJCustomerItem->getRowsBySearchCriteria(
                $dsSearchForm->getValue('customerID'),
                $dsSearchForm->getValue('ordheadID'),
                $dsSearchForm->getValue('startDate'),
                $dsSearchForm->getValue('endDate'),
                $itemText,
                $contractText,
                $dsSearchForm->getValue('serialNo'),
                $dsSearchForm->getValue('renewalStatus'),
                $row_limit
            );
            $this->dbeJCustomerItem->initialise();
            $dsResults = $this->dbeJCustomerItem;
        }
        return $ret;
    }

    function getCustomerItemByID($ID, &$dsResults)
    {
        $this->setMethodName('getCustomerItemByID');
        $this->dbeJCustomerItem->getRow($ID);
        $this->getData($this->dbeJCustomerItem, $dsResults);
    }

    function getContractsByCustomerID($customerID, &$dsResults)
    {
        $this->setMethodName('getContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getRowsByCustomerID($customerID);
        $this->getData($dbeJContract, $dsResults);
    }

    function getServerCareValidContractsByCustomerID($customerID, &$dsResults)
    {
        $this->setMethodName('getServerCareValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getServerCareContracts($customerID);
        $this->getData($dbeJContract, $dsResults);
    }

    function getServerWatchContractByCustomerID($customerID, &$dsResults){
        $this->setMethodName('getServerCareValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getServerWatchContracts($customerID);
        $this->getData($dbeJContract, $dsResults);
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
            $contractIDs[] = $dbeJContract->getValue('customerItemID');
        }
        return $contractIDs;
    }

    /**
     * Create a new dataset containing defaults for new item row
     * @parameter DataSet &$dsResults results
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
     * @return bool : Success
     * @access public
     */
    function update(&$dsCustomerItem, $contractIDs = false)
    {
        $this->setMethodName('update');

        $dsCustomerItem->fetchNext();

        $customerItemID = $dsCustomerItem->getValue('customerItemID');
        if ($customerItemID == '') {
            $this->raiseError('customerItemID not passed');
        }
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->getRow($customerItemID);
        $dsCustomerItem->setUpdateModeUpdate();

        /*
        If being suspended now, set suspended date and user
        */
        if (
            $dsCustomerItem->getValue('secondsiteValidationSuspendUntilDate') != '' &&
            $dbeCustomerItem->getValue('secondsiteValidationSuspendUntilDate') != $dsCustomerItem->getValue('secondsiteValidationSuspendUntilDate')
        ) {
            $dsCustomerItem->setValue('secondsiteSuspendedByUserID', $GLOBALS ['auth']->is_authenticated());
            $dsCustomerItem->setValue('secondsiteSuspendedDate', date(CONFIG_MYSQL_DATE));
        } else {
            $dsCustomerItem->setValue('secondsiteSuspendedByUserID',
                                      $dbeCustomerItem->getValue('secondsiteSuspendedByUserID'));
            $dsCustomerItem->setValue('secondsiteSuspendedDate', $dbeCustomerItem->getValue('secondsiteSuspendedDate'));

        }

        /*
        If being delayed now, set date and user
        */
        if (
            $dsCustomerItem->getValue('secondsiteImageDelayDays') != '0' &&
            $dbeCustomerItem->getValue('secondsiteImageDelayDays') != $dsCustomerItem->getValue('secondsiteImageDelayDays')
        ) {
            $dsCustomerItem->setValue('secondsiteImageDelayUserID', $GLOBALS ['auth']->is_authenticated());
            $dsCustomerItem->setValue('secondsiteImageDelayDate', date(CONFIG_MYSQL_DATE));
        } else {
            $dsCustomerItem->setValue('secondsiteImageDelayUserID',
                                      $dbeCustomerItem->getValue('secondsiteImageDelayUserID'));
            $dsCustomerItem->setValue('secondsiteImageDelayDate',
                                      $dbeCustomerItem->getValue('secondsiteImageDelayDate'));

        }
        $dsCustomerItem->post();

        // if customerID changed then get default siteno and contact
        if ($dbeCustomerItem->getValue('customerID') != $dsCustomerItem->getValue('customerID')) {
            $buCustomer = new BUCustomer($this);
            $buCustomer->getDeliverSiteByCustomerID($dsCustomerItem->getValue('customerID'), $dsSite, $dsContact);
            $dsCustomerItem->setUpdateModeUpdate();
            $dsCustomerItem->setValue('siteNo', $dsSite->getValue(DBESite::siteNo));
            $dsCustomerItem->post();
        }
        if ($success = $this->updateDataaccessObject($dsCustomerItem, $dbeCustomerItem)) {
            $dbeCustomerItem->updateContract($dbeCustomerItem->getPKValue(), $contractIDs);
        }
        return $success;
    }

    /**
     * Test whether given customer item has dependendent entities
     *
     * @parameter integer $customerItemID
     * @return bool : TRUE = has dependencies
     * @access public
     */
    function canDelete($customerItemID)
    {
        $this->setMethodName('canDelete');
        $return = TRUE;
        /*
            are there any service calls?
        */
        $dbeProblem = new DBEProblem($this);
        // calls for this item as a contract item (e.g. General Support Contract)

        return $return;
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
     * @param Integer $callID call to upload file for
     * @param String $filename
     * @param Array $userfile parameters from browser POST
     * @return bool : success
     * @access public
     */
    function uploadDocumentFile($customerItemID, $description, &$userfile)
    {
        $this->setMethodName('uploadDocumentFile');
        if ($customerItemID == '') $this->raiseError('customerItemID not passed');
        if ($description == '') $this->raiseError('description not passed');
        $dbeDocument = new DBECustomerItemDocument($this);
        $dbeDocument->setPKValue('');
        $dbeDocument->setValue('customerItemID', $customerItemID);
        $dbeDocument->setValue('file', fread(fopen($userfile['tmp_name'], 'rb'), $userfile['size']));
        $dbeDocument->setValue('description', (string)$description);
        $dbeDocument->setValue('filename', (string)$userfile['name']);
        $dbeDocument->setValue('fileLength', (int)$userfile['size']);
        $dbeDocument->setValue('createUserID', (string)$GLOBALS['auth']->is_authenticated());
        $dbeDocument->setValue('createDate', date('Y-m-d H:i:s'));
        $dbeDocument->setValue('fileMIMEType', (string)$userfile['type']);
        return ($dbeDocument->insertRow());
    }

    function getCustomerItemsByContractID($contractCustomerItemID, &$dsResults)
    {
        /*
        @todo: update for new many-to-many
        */

        $this->setMethodName('getCustomerItemsByContractID');
        $dbeJCustomerItem = new DBEJCustomerItem($this);
        $dbeJCustomerItem->getItemsByContractID($contractCustomerItemID);
        return ($this->getData($dbeJCustomerItem, $dsResults));
    }

    function customerHasValidServerCareContract($customerID)
    {
        $this->setMethodName('customerHasValidServerCareContract');
        $dbeJCustomerItem = new DBEJCustomerItem($this);
        $dbeJCustomerItem->setValue('customerID', $customerID);
        $dbeJCustomerItem->getRowsByColumn('customerID');
        $hasContract = false;
        while ($dbeJCustomerItem->fetchNext()) {

            if (
                $dbeJCustomerItem->getValue('servercareFlag') == 'Y' AND
                $dbeJCustomerItem->getValue('expiryDate') >= date(CONFIG_MYSQL_DATE)
            ) {
                $hasContract = true;
            }
        }
        return $hasContract;
    }

    function getMinResponseTime($customerID)
    {
        $minResponseHours = $this->getMinumumContractResponseHours($customerID);

        if ($minResponseHours > 0) {
            $minResponseTime = 'Response required within ' . $minResponseHours . ' hours<BR/><BR/>';
        } else {
            $minResponseTime = 'Response is on a best endeavours basis<BR/><BR/>';
        }

        return $minResponseTime;

    }

    function getMinumumContractResponseHours($customerID)
    {
        global $db; //PHPLib DB object

        $this->setMethodName('getMinumumContractResponseHours');

        $queryString =
            "
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
     */
    function getServersByCustomerID($customerID, &$dsResults)
    {
        if (!$customerID) {

            $this->raiseError('No customerID passed');

        }

        $dbeJCustomerItem = new DBEJCustomerItem($this);
        $dbeJCustomerItem->setValue('customerID', $customerID);

        $dbeJCustomerItem->getServersByCustomerID();

        return ($this->getData($dbeJCustomerItem, $dsResults));

    }

    function addContractToCustomerItems(
        $contractID,
        $customerItemIDArray
    )
    {
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->addContractToCustomerItems($contractID, $customerItemIDArray);
    }

    function removeContractFromCustomerItems(
        $contractID,
        $customerItemIDArray
    )
    {
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->removeContractFromCustomerItems($contractID, $customerItemIDArray);
    }

    function customerHasServiceDeskContract($customerID)
    {
        $this->getContractsByCustomerID($customerID, $dsContract);

        while ($dsContract->fetchNext()) {

            if ($dsContract->getValue('itemTypeID') == CONFIG_SERVICEDESK_ITEMTYPEID) {
                return true;
            }

        }
        return false;
    }

    function serverIsUnderLocalSecondsiteContract($customerItemID)
    {
        global $db; //PHPLib DB object

        $ret = false;

        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getRowsByCustomerItemID($customerItemID);

        while ($dbeJContract->fetchNext()) {
            if ($dbeJContract->getValue('itemTypeID') == CONFIG_2NDSITE_LOCAL_ITEMTYPEID) {
                $ret = true;
            }
        }// end while

        return $ret;
    }

    public function getServiceDeskValidContractsByCustomerID($customerID, &$dsResults)
    {
        $this->setMethodName('getServiceDeskValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getServiceDeskContracts($customerID);
        $this->getData($dbeJContract, $dsResults);
    }

    public function getPrepayContractByCustomerID($customerID, &$dsResults)
    {
        $this->setMethodName('getServiceDeskValidContractsByCustomerID');
        $dbeJContract = new DBEJContract($this);
        $dbeJContract->getPrePayContracts($customerID);
        $this->getData($dbeJContract, $dsResults);
    }
}// End of class
?>