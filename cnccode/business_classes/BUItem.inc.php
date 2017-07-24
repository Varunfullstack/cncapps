<?php /**
 * Item business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEItem.inc.php");
require_once($cfg["path_dbe"] . "/DBEItemType.inc.php");
require_once($cfg["path_dbe"] . "/DBEManufacturer.inc.php");
define('BUITEM_NAME_STR_NT_PASD', 'No name string passed');

class BUItem extends Business
{
    var $dbeItem = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeItem = new DBEItem($this);
    }

    /**
     * Get Item rows whose names match the search string or, if the string is numeric, try to select by customerID
     * Don't include discontinued items
     * @parameter String $nameSearchString String to match against or numeric itemID
     * @parameter DataSet &$dsResults results
     * @return bool : One or more rows
     * @access public
     */
    function getItemsByNameMatch($matchString, &$dsResults, $renewalTypeID = false)
    {
        $this->setMethodName('getItemsByNameMatch');
        if ($matchString == '') {
            $this->raiseError(BUITEM_NAME_STR_NT_PASD);
        }
        $matchString = trim($matchString);
        $ret = FALSE;
        if ($matchString[0] == '[') {                // use part no
            $this->dbeItem->setValue("partNo", substr($matchString, 1, 9));
            $this->dbeItem->getRowsByPartNoMatch($renewalTypeID);
            $ret = ($this->getData($this->dbeItem, $dsResults));
        } else {
            if (is_numeric($matchString)) {
                $ret = ($this->getItemByID($matchString, $dsResults));
            }
            if (!$ret) {
                $this->dbeItem->setValue("description", $matchString);
                $this->dbeItem->getRowsByDescriptionMatch($renewalTypeID);
                $ret = ($this->getData($this->dbeItem, $dsResults));
            }
        }
        return $ret;
    }

    /**
     * Get item row by itemID
     * @parameter integer $itemID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getItemByID($ID, &$dsResults)
    {
        $this->setMethodName('getItemByID');
        return ($this->getDatasetByPK($ID, $this->dbeItem, $dsResults));
    }

    /**
     * Get all item type rows
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getAllItemTypes(&$dsResults)
    {
        $this->setMethodName('getAllItemTypes');
        $dbeItemType = new DBEItemType($this);
        $dbeItemType->getRows('description');    // description is the sort order
        return ($this->getData($dbeItemType, $dsResults));
    }

    /**
     * Get all manufacturer rows
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getAllManufacturers(&$dsResults)
    {
        $this->setMethodName(' getAllManufacturers');
        $dbeManufacturer = new DBEManufacturer($this);
        $dbeManufacturer->getRows('name');
        return ($this->getData($dbeManufacturer, $dsResults));
    }

    /**
     * Create a new dataset containing defaults for new item row
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function initialiseNewItem(&$dsResults, $renewalTypeID = 0)
    {
        $this->setMethodName('initialiseNewItem');
        // create/populate new dataset
//		$dsResults = new DSForm($this);
        $dsResults->copyColumnsFrom($this->dbeItem);
        $dsResults->setAllowEmpty('stockcat');        // because this will be worked out upon insert/update
        $dsResults->setUpdateModeInsert();
        $dsResults->setValue('itemTypeID', CONFIG_DEF_ITEMTYPEID);
        $dsResults->setValue('itemID', 0);
        $dsResults->setValue('discontinuedFlag', 'N');
        $dsResults->setValue('serialNoFlag', 'N');
        $dsResults->setValue('servercareFlag', 'N');
        $dsResults->setValue('renewalTypeID', $renewalTypeID);
        $dsResults->post();
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
    function updateItem(&$dsItem)
    {
        $this->setMethodName('updateItem');
        // get stockcat from item type
        $dsItem->fetchNext();
        $dbeItemType = new DBEItemType($this);
        $dbeItemType->getRow($dsItem->getValue('itemTypeID')); // new optional param for PK value
        $dsItem->setUpdateModeUpdate();
        if ($dsItem->getValue('serialNoFlag') == '') {
            $dsItem->setValue('serialNoFlag', 'N');
        }
        if ($dsItem->getValue('discontinuedFlag') == '') {
            $dsItem->setValue('discontinuedFlag', 'N');
        }
        if ($dsItem->getValue('servercareFlag') == '') {
            $dsItem->setValue('servercareFlag', 'N');
        }
        $dsItem->setValue('stockcat', $dbeItemType->getValue('stockcat'));
        $dsItem->post();
        return ($this->updateDataaccessObject($dsItem, $this->dbeItem));
    }

    function discontinue(
        $discontinueItemIDArray
    )
    {
        $this->dbeItem->setRowsDiscontinued($discontinueItemIDArray);
    }

}// End of class
?>