<?php

use CNCLTD\Data\DBEItem;

global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEManufacturer.inc.php");
define('BUMANUFACTURER_NAME_STR_NT_PASD', 'No name string passed');

class BUManufacturer extends Business
{
    /** @var DBEManufacturer */
    public $dbeManufacturer;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeManufacturer = new DBEManufacturer($this);
    }

    function updateManufacturer(&$dsData)
    {
        $this->setMethodName('updateManufacturer');
        $this->updateDataAccessObject($dsData, $this->dbeManufacturer);
        return TRUE;
    }

    /**
     * Get Item rows whose names match the search string or, if the string is numeric, try to select by customerID
     * Don't include discontinued items
     * @parameter String $nameSearchString String to match against or numeric itemID
     * @parameter DataSet &$dsResults results
     * @param $matchString
     * @param $dsResults
     * @return bool : One or more rows
     * @access public
     */
    function getManufacturersByNameMatch($matchString, &$dsResults)
    {
        $this->setMethodName('getManufacturersByNameMatch');
        if ($matchString == '') {
            $this->raiseError(BUMANUFACTURER_NAME_STR_NT_PASD);
        }
        $matchString = trim($matchString);
        $ret         = FALSE;
        if (is_numeric($matchString)) {
            $ret = ($this->getManufacturerByID($matchString, $dsResults));
        }
        if (!$ret) {
            $this->dbeManufacturer->getRowsByNameMatch($matchString);
            $ret = ($this->getData($this->dbeManufacturer, $dsResults));
        }
        return $ret;
    }

    function getManufacturerByID($ID, &$dsResults)
    {
        $this->dbeManufacturer->setPKValue($ID);
        $this->dbeManufacturer->getRow();
        return ($this->getData($this->dbeManufacturer, $dsResults));
    }

    function getAll(&$dsResults)
    {
        $this->dbeManufacturer->getRows('name');
        return ($this->getData($this->dbeManufacturer, $dsResults));
    }

    function deleteManufacturer($ID)
    {
        $this->setMethodName('deleteManufacturer');
        if ($this->canDeleteManufacturer($ID)) {
            return $this->dbeManufacturer->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteManufacturer
     * Only allowed if type has no activities
     * @param $ID
     * @return bool
     */
    function canDeleteManufacturer($ID)
    {
        $dbeItem = new DBEItem($this);
        // validate no items of this manufacturer
        $dbeItem->setValue(DBEItem::manufacturerID, $ID);
        if ($dbeItem->countRowsByColumn(DBEItem::manufacturerID) < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
