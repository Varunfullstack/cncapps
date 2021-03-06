<?php /**
 * Site business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBESite.inc.php");
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg["path_bu"] . "/BUContact.inc.php");
require_once($cfg['path_dbe'] . "/DBEJSite.php");
define('BUSITE_MATCH_STR_NT_PASD', 'No match string passed');

class BUSite extends Business
{
    /** @var DBESite */
    public $dbeSite;
    /** @var DBEJSite */
    public $dbeJSite;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeSite = new DBESite($this);
        $this->dbeJSite = new DBEJSite($this);
    }

    /**
     * Get Site rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @param Integer $customerID numeric customerID
     * @param String $matchString String to match against or numeric siteNo
     * @param DataSet &$dsResults results
     * @return bool : One or more rows
     * @access public
     */
    function getSitesByDescMatch($customerID, $matchString, &$dsResults)
    {
        $this->setMethodName('getSitesByDescMatch');
        $ret = FALSE;
        $matchString = trim($matchString);
        if (is_numeric($matchString)) {
            $ret = ($this->getSiteByID($customerID, $matchString, $dsResults));
        }
        if (!$ret) {
            $this->dbeJSite->setValue(DBESite::customerID, $customerID);
            if (isset($matchString[0]) && $matchString{0} == '?') {  // get all contacts for supplier
                $this->dbeJSite->getRowsByColumn(DBESite::customerID);
            } else {                                                // try to match
                $this->dbeJSite->getRowsByDescMatch($matchString);
            }
            $ret = ($this->getData($this->dbeJSite, $dsResults));
        }
        return $ret;
    }

    /**
     * Get site row by customerID/SiteNo
     * @param integer $customerID
     * @param integer $siteNo
     * @param DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getSiteByID($customerID, $siteNo, &$dsResults)
    {
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }

        $this->setMethodName('getSiteByID');
        $this->dbeJSite->setValue(DBESite::customerID, $customerID);
        $this->dbeJSite->setValue(DBESite::siteNo, $siteNo);
        $this->dbeJSite->getRow();
        return $this->getData($this->dbeJSite, $dsResults);
    }

    /**
     * Create a new dataset containing defaults for new site row
     * @param $customerID
     * @param DataSet $dsResults
     * @return bool : Success
     * @access public
     */
    function initialiseNewSite($customerID, &$dsResults)
    {
        $this->setMethodName('initialiseNewSite');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        $dsResults->copyColumnsFrom($this->dbeJSite);
        $dsResults->setUpdateModeInsert();
        $dsResults->setValue(DBESite::customerID, $customerID);
        $dsResults->setValue(DBESite::siteNo, -9);                // means new site as zero is valid siteno
        $dsResults->setValue(DBESite::maxTravelHours, -9);        // means not set because 0 is now a valid distance
        $dsResults->post();
        return TRUE;
    }

    /**
     * Update/Insert site to DB
     *    Only handles one row in dataset.
     *
     * @param DataSet $dsSite
     * @return bool : Success
     * @access public
     */
    function updateSite(&$dsSite)
    {
        $this->setMethodName('updateSite');
        if ($dsSite->getValue(DBESite::siteNo) == -9) { // new one
            $this->dbeSite->setValue(DBESite::customerID, $dsSite->getValue(DBESite::customerID));
            $dsSite->setValue(DBESite::siteNo, $this->dbeSite->getNextPKValue());
            $this->dbeSite->setUpdateModeInsert();
            $this->dbeSite->setValue(DBESite::sageRef, $this->getSageRef($dsSite->getValue(DBESite::customerID)));
            $insert = TRUE;
        } else {
            // get the existing row with sage ref
            $this->dbeSite->setValue(DBESite::customerID, $dsSite->getValue(DBESite::customerID));
            $this->dbeSite->setValue(DBESite::siteNo, $dsSite->getValue(DBESite::siteNo));
            $this->dbeSite->setUpdateModeUpdate();
            $insert = FALSE;
        }
        $this->dbeSite->setValue(DBESite::customerID, $dsSite->getValue(DBESite::customerID));
        $this->dbeSite->setValue(DBESite::siteNo, $dsSite->getValue(DBESite::siteNo));
        $this->dbeSite->setValue(DBESite::add1, $dsSite->getValue(DBESite::add1));
        $this->dbeSite->setValue(DBESite::add2, $dsSite->getValue(DBESite::add2));
        $this->dbeSite->setValue(DBESite::add3, $dsSite->getValue(DBESite::add3));
        $this->dbeSite->setValue(DBESite::town, $dsSite->getValue(DBESite::town));
        $this->dbeSite->setValue(DBESite::county, $dsSite->getValue(DBESite::county));
        $this->dbeSite->setValue(DBESite::postcode, $dsSite->getValue(DBESite::postcode));
        $this->dbeSite->setValue(DBESite::invoiceContactID, $dsSite->getValue(DBESite::invoiceContactID));
        $this->dbeSite->setValue(DBESite::deliverContactID, $dsSite->getValue(DBESite::deliverContactID));
        $this->dbeSite->setValue(DBESite::debtorCode, $dsSite->getValue(DBESite::debtorCode));
        $this->dbeSite->setValue(DBESite::phone, $dsSite->getValue(DBESite::phone));
        $this->dbeSite->post();
        if ($insert) {
            //create default contact details
            $buContact = new BUContact($this);
            $dsContact = new DataSet($this);
            $buContact->initialiseNewContact(
                null,
                $dsSite->getValue(DBESite::customerID),
                $dsSite->getValue(DBESite::siteNo),
                $dsContact
            );
            $dsContact->setUpdateModeUpdate();
            $dsContact->setValue(DBEContact::firstName, 'Please');
            $dsContact->setValue(DBEContact::lastName, 'Enter');
            $dsContact->setValue(DBEContact::phone, 'Please enter');
            $dsContact->post();
            $buContact->updateContact($dsContact);
            $this->dbeSite->setUpdateModeUpdate();
            $this->dbeSite->setValue(DBESite::invoiceContactID, $dsContact->getValue(DBEContact::contactID));
            $this->dbeSite->setValue(DBESite::deliverContactID, $dsContact->getValue(DBEContact::contactID));
            $this->dbeSite->post();
        }
        return TRUE;
    }

    /**
     * Calculate a unique Sage Reference for new customer site
     * Based upon uppercase first two non-space characters of name plus integer starting at 1 (e.g. KA002)
     * @param integer $customerID
     * @return bool : Success
     * @access public
     */
    function getSageRef($customerID)
    {
        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID($customerID, $dsCustomer);
        $customerName = $dsCustomer->getValue(DBECustomer::name);
        $shortCode = null;
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
        $dbeSite = new DBESite($this);
        $sageRef = null;
        while (!$numberUnique) {
            $sageRef = $shortCode . str_pad($number, 3, "0", STR_PAD_LEFT);
            $numberUnique = $dbeSite->uniqueSageRef($sageRef);
            $number++;
        }
        return $sageRef;
    }

    public function getActiveSitesByCustomer(int $customerID, DataSet $dsResults)
    {
        $this->dbeJSite->setValue(DBESite::customerID, $customerID);
        $this->dbeJSite->getRowsByColumn(DBESite::customerID);
        return $this->getData($this->dbeJSite, $dsResults);
    }
}
