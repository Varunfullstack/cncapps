<?php /**
 * Site business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBESiteNew.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerNew.inc.php");
require_once($cfg["path_bu"] . "/BUContact.inc.php");
define('BUSITE_MATCH_STR_NT_PASD', 'No match string passed');

class BUSite extends Business
{
    var $dbeSite = "";
    var $dbeJSite = "";

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeSite = new DBESite($this);
        $this->dbeJSite = new DBEJSite($this);
    }

    /**
     * Get Site rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter Integer $customerID numeric customerID
     * @parameter String $matchString String to match against or numeric siteNo
     * @parameter DataSet &$dsResults results
     * @return bool : One or more rows
     * @access public
     */
    function getSitesByDescMatch($customerID, $matchString, &$dsResults)
    {
        $this->setMethodName('getSitesByDescMatch');
        if ($matchString == '') {
            $this->raiseError(BUSITE_MATCH_STR_NT_PASD);
        }
        $ret = FALSE;
        $matchString = trim($matchString);
        if (is_numeric($matchString)) {
            $ret = ($this->getSiteByID($customerID, $matchString, $dsResults));
        }
        if (!$ret) {
            $this->dbeJSite->setValue('customerID', $customerID);
            if ($matchString{0} == '?') {  // get all contacts for supplier
                $this->dbeJSite->getRowsByColumn('customerID');
            } else {                                                // try to match
                $this->dbeJSite->getRowsByDescMatch($matchString);
            }
            $ret = ($this->getData($this->dbeJSite, $dsResults));
        }
        return $ret;
    }

    /**
     * Get site row by customerID/SiteNo
     * @parameter integer $customerID
     * @parameter integer $siteNo
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getSiteByID($customerID, $siteNo, &$dsResults)
    {
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
//		if ((integer) $siteNo==''){
//			$this->raiseError('siteNo not passed');
//		}
        $this->setMethodName('getSiteByID');
        $this->dbeJSite->setValue('customerID', $customerID);
        $this->dbeJSite->setValue('siteNo', $siteNo);
        $this->dbeJSite->getRow();
        return $this->getData($this->dbeJSite, $dsResults);
    }

    /**
     * Create a new dataset containing defaults for new site row
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function initialiseNewSite($customerID, &$dsResults)
    {
        $this->setMethodName('initialiseNewSite');
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }
        $dsResults->copyColumnsFrom($this->dbeJSite);
        $dsResults->setUpdateModeInsert();
        $dsResults->setValue('customerID', $customerID);
        $dsResults->setValue('siteNo', -9);                // means new site as zero is valid siteno
        $dsResults->setValue('maxTravelHours', -9);        // means not set because 0 is now a valid distance
        $dsResults->post();
        return TRUE;
    }

    /**
     * Update/Insert site to DB
     *    Only handles one row in dataset.
     *
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function updateSite(&$dsSite)
    {
        $this->setMethodName('updateSite');
        if ($dsSite->getValue('siteNo') == -9) { // new one
            $this->dbeSite->setValue('customerID', $dsSite->getValue('customerID'));
            $dsSite->setValue('siteNo', $this->dbeSite->getNextPKValue());
            $this->dbeSite->setUpdateModeInsert();
            $this->dbeSite->setValue('sageRef', $this->getSageRef($dsSite->getValue('customerID')));
            $insert = TRUE;
        } else {
            // get the existing row with sage ref
//			$this->getSiteByID($dsSite->getValue('customerID'), $dsSite->getValue('siteNo'), $dsSite);
            $this->dbeSite->setValue('customerID', $dsSite->getValue('customerID'));
            $this->dbeSite->setValue('siteNo', $dsSite->getValue('siteNo'));
            $this->dbeSite->setUpdateModeUpdate();
            $insert = FALSE;
        }
        $this->dbeSite->setValue('customerID', $dsSite->getValue('customerID'));
        $this->dbeSite->setValue('siteNo', $dsSite->getValue('siteNo'));
        $this->dbeSite->setValue('add1', $dsSite->getValue('add1'));
        $this->dbeSite->setValue('add2', $dsSite->getValue('add2'));
        $this->dbeSite->setValue('add3', $dsSite->getValue('add3'));
        $this->dbeSite->setValue('town', $dsSite->getValue('town'));
        $this->dbeSite->setValue('county', $dsSite->getValue('county'));
        $this->dbeSite->setValue('postcode', $dsSite->getValue('postcode'));
        $this->dbeSite->setValue('invContactID', $dsSite->getValue('invContactID'));
        $this->dbeSite->setValue('delContactID', $dsSite->getValue('delContactID'));
        $this->dbeSite->setValue('debtorCode', $dsSite->getValue('debtorCode'));
        $this->dbeSite->setValue('phone', $dsSite->getValue('phone'));
        $this->dbeSite->post();
        if ($insert) {
            //create default contact details
            $buContact = new BUContact($this);
            $dsContact = new DataSet($this);
            $buContact->initialiseNewContact('', $dsSite->getValue('customerID'), $dsSite->getValue('siteNo'), $dsContact);
            $dsContact->setUpdateModeUpdate();
            $dsContact->setValue('firstName', 'Please');
            $dsContact->setValue('lastName', 'Enter');
            $dsContact->setValue('phone', 'Please enter');
            $dsContact->post();
            $buContact->updateContact($dsContact);
            $this->dbeSite->setUpdateModeUpdate();
            $this->dbeSite->setValue('invContactID', $dsContact->getValue('contactID'));
            $this->dbeSite->setValue('delContactID', $dsContact->getValue('contactID'));
            $this->dbeSite->post();
        }
        return TRUE;
    }

    /**
     * Calculate a unique Sage Reference for new customer site
     * Based upon uppercase first two non-space characters of name plus integer starting at 1 (e.g. KA002)
     * @parameter DataSet &$source dataset
     * @parameter dbeEntity &$dbeSite site database entity
     * @return bool : Success
     * @access public
     */
    function getSageRef($customerID)
    {
        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID($customerID, $dsCustomer);
        $customerName = $dsCustomer->getValue(DBECustomer::Name);
        $shortCode = "";
        for ($ixChar = 0; $ixChar <= strlen($customerName); $ixChar++) {
            if (substr($customerName, $ixChar, 1) != " ") {
                $shortCode = $shortCode . strtoupper(substr($customerName, $ixChar, 1));
                if (strlen($shortCode) == 2) {
                    break;
                }
            }
        }
        $number = 1;
        $numberUnique = FALSE;
        $dbeSite = new DBESite($this);                // Just for sageRef check
        while (!$numberUnique) {
            $sageRef = $shortCode . str_pad($number, 3, "0", STR_PAD_LEFT);
            $numberUnique = $dbeSite->uniqueSageRef($sageRef);
            $number++;
        }
        return $sageRef;
    }
}// End of class
?>