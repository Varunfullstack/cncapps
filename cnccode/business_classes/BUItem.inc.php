<?php /**
 * Item business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEItem.inc.php");
require_once($cfg["path_dbe"] . "/DBEItemType.inc.php");
require_once($cfg["path_dbe"] . "/DBEManufacturer.inc.php");


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

    function updateItem(&$dsItem, DBEUser $currentUser)
    {
        $this->setMethodName('updateItem');
        $dsItem->fetchNext();
        $dbeItemType = new DBEItemType($this);
        $dbeItemType->getRow($dsItem->getValue(DBEItem::itemTypeID));
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
            DBEItem::updatedBy,
            $currentUser->getFullName()
        );
        $dsItem->setValue(
            DBEItem::updatedAt,
            (new DateTimeImmutable())->format(DATE_MYSQL_DATETIME)
        );
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
}