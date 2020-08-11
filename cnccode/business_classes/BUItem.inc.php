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
define(
    'BUITEM_NAME_STR_NT_PASD',
    'No name string passed'
);

class BUItem extends Business
{
    /** @var DBEItem */
    public $dbeItem;

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
     * @param $matchString
     * @param $dsResults
     * @param bool $renewalTypeID
     * @return bool : One or more rows
     * @access public
     */
    function getItemsByNameMatch($matchString,
                                 &$dsResults,
                                 $renewalTypeID = false
    )
    {
        $this->setMethodName('getItemsByNameMatch');
        if ($matchString == '') {
            $this->raiseError(BUITEM_NAME_STR_NT_PASD);
        }
        $matchString = trim($matchString);
        $ret = FALSE;
        if ($matchString[0] == '[') {                // use part no
            $this->dbeItem->setValue(
                DBEItem::partNo,
                substr(
                    $matchString,
                    1,
                    9
                )
            );
            $this->dbeItem->getRowsByPartNoMatch($renewalTypeID);
            $ret = ($this->getData(
                $this->dbeItem,
                $dsResults
            ));
        } else {
            if (is_numeric($matchString)) {
                $ret = ($this->getItemByID(
                    $matchString,
                    $dsResults
                ));
            }
            if (!$ret) {
                $this->dbeItem->getRowsByDescriptionOrPartNoSearch($matchString, $renewalTypeID);
                $ret = ($this->getData(
                    $this->dbeItem,
                    $dsResults
                ));
            }
        }
        return $ret;
    }

    /**
     * @param $ID
     * @param DataSet $dsResults
     * @return bool
     */
    function getItemByID($ID,
                         &$dsResults
    )
    {
        $this->setMethodName('getItemByID');
        return ($this->getDatasetByPK(
            $ID,
            $this->dbeItem,
            $dsResults
        ));
    }

    /**
     * Get all item type rows
     * @parameter DataSet &$dsResults results
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getAllItemTypes(&$dsResults)
    {
        $this->setMethodName('getAllItemTypes');
        $dbeItemType = new DBEItemType($this);
        $dbeItemType->getRows('description');    // description is the sort order
        return ($this->getData(
            $dbeItemType,
            $dsResults
        ));
    }

    /**
     * Get all manufacturer rows
     * @parameter DataSet &$dsResults results
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getAllManufacturers(&$dsResults)
    {
        $this->setMethodName(' getAllManufacturers');
        $dbeManufacturer = new DBEManufacturer($this);
        $dbeManufacturer->getRows('name');
        return ($this->getData(
            $dbeManufacturer,
            $dsResults
        ));
    }

    /**
     * Create a new dataset containing defaults for new item row
     * @param DataSet $newDBEItem
     * @param int $renewalTypeID
     * @return bool : Success
     * @access public
     */
    function initialiseNewItem(&$newDBEItem,
                               $renewalTypeID = 0
    )
    {
        $this->setMethodName('initialiseNewItem');
        // create/populate new dataset
//		$dsResults = new DSForm($this);
        $newDBEItem->copyColumnsFrom($this->dbeItem);
        $newDBEItem->setAllowEmpty('stockcat');        // because this will be worked out upon insert/update
        $newDBEItem->setUpdateModeInsert();
        $newDBEItem->setValue(
            DBEItem::itemTypeID,
            CONFIG_DEF_ITEMTYPEID
        );
        $newDBEItem->setValue(
            DBEItem::itemID,
            null
        );
        $newDBEItem->setValue(
            DBEItem::discontinuedFlag,
            'N'
        );
        $newDBEItem->setValue(
            DBEItem::serialNoFlag,
            'N'
        );
        $newDBEItem->setValue(
            DBEItem::servercareFlag,
            'N'
        );
        $newDBEItem->setValue(
            DBEItem::renewalTypeID,
            $renewalTypeID
        );

        $newDBEItem->post();
        return TRUE;
    }

    /**
     * Update/Insert item to DB
     *    Only handles one row in dataset.
     *
     * @param DataSet &$dsItem results
     * @return bool : Success
     * @access public
     */
    function updateItem(&$dsItem)
    {
        $this->setMethodName('updateItem');
        // get stockcat from item type
        $dsItem->fetchNext();
        $dbeItemType = new DBEItemType($this);
        $dbeItemType->getRow($dsItem->getValue(DBEItem::itemTypeID)); // new optional param for PK value
        $dsItem->setUpdateModeUpdate();
        if ($dsItem->getValue(DBEItem::serialNoFlag) == '') {
            $dsItem->setValue(
                DBEItem::serialNoFlag,
                'N'
            );
        }
        if ($dsItem->getValue(DBEItem::discontinuedFlag) == '') {
            $dsItem->setValue(
                DBEItem::discontinuedFlag,
                'N'
            );
        }
        if ($dsItem->getValue(DBEItem::servercareFlag) == '') {
            $dsItem->setValue(
                DBEItem::servercareFlag,
                'N'
            );
        }
        $dsItem->setValue(
            DBEItem::stockcat,
            $dbeItemType->getValue(DBEItemType::stockcat)
        );
        $dsItem->post();
        return ($this->updateDataAccessObject(
            $dsItem,
            $this->dbeItem
        ));
    }

    function discontinue($discontinueItemIDArray)
    {
        $this->dbeItem->setRowsDiscontinued($discontinueItemIDArray);
    }

}