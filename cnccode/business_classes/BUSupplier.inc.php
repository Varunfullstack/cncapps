<?php /**
 * Supplier business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBESupplier.inc.php");
require_once($cfg["path_dbe"] . "/DBEJSupplier.php");
require_once($cfg["path_dbe"] . "/DBEPayMethod.inc.php");
define('BUSUPPLIER_MATCH_STR_NT_PASD', 'No match string passed');

class BUSupplier extends Business
{
    /** @var DBESupplier */
    public $dbeSupplier;
    /** @var DBEJSupplier */
    public $dbeJSupplier;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeSupplier = new DBESupplier($this);
        $this->dbeJSupplier = new DBEJSupplier($this);
    }

    /**
     * Get Supplier rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter String $nameSearchString String to match against or numeric supplierID
     * @parameter DataSet &$dsResults results
     * @param $matchString
     * @param $dsResults
     * @param string $address
     * @return bool : One or more rows
     * @access public
     */
    function getSuppliersByNameMatch($matchString, &$dsResults, $address = null)
    {
        $this->setMethodName('getSuppliersByNameMatch');
        $ret = FALSE;
        $matchString = trim($matchString);
        if (is_numeric($matchString)) {
            $ret = ($this->getSupplierByID($matchString, $dsResults));
        }
        if (!$ret) {
            $this->dbeJSupplier->getRowsByNameMatch($matchString, $address);
            $ret = ($this->getData($this->dbeJSupplier, $dsResults));
        }
        return $ret;
    }

    /**
     * Get supplier row by supplierID
     * @parameter integer $supplierID
     * @parameter DataSet &$dsResults results
     * @param $ID
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getSupplierByID($ID, &$dsResults)
    {
        $this->setMethodName('getSupplierByID');
        return ($this->getDatasetByPK($ID, $this->dbeJSupplier, $dsResults));
    }

    /**
     * Get all payment type rows
     * @parameter DataSet &$dsResults results
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getAllPayMethods(&$dsResults)
    {
        $this->setMethodName('getAllPayMethods');
        $dbePayMethod = new DBEPayMethod($this);
        $dbePayMethod->getRows('description');    // description is the sort order
        return ($this->getData($dbePayMethod, $dsResults));
    }

    /**
     * Create a new dataset containing defaults for new supplier row
     * @parameter DataSet &$dsResults results
     * @param DataSet $dsResults
     * @return bool : Success
     * @access public
     */
    function initialiseNewSupplier(&$dsResults)
    {
        $this->setMethodName('initialiseNewSupplier');
        // create/populate new dataset
        //	$dsResults = new DSForm($this);
        $dsResults->copyColumnsFrom($this->dbeJSupplier);
        $dsResults->setUpdateModeInsert();
        $dsResults->setValue(DBEJSupplier::supplierID, null);
        $dsResults->post();
        return TRUE;
    }

    /**
     * Update/Insert supplier to DB
     *    Only handles one row in dataset.
     *
     * @parameter DataSet &$dsResults results
     * @param $dsSupplier
     * @return bool : Success
     * @access public
     */
    function updateSupplier(&$dsSupplier)
    {
        $this->setMethodName('updateSupplier');
        return ($this->updateDataAccessObject($dsSupplier, $this->dbeSupplier));
    }
}
