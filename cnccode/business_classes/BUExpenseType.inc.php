<?php /**
 * Call expense type business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEExpenseType.inc.php");
require_once($cfg["path_dbe"] . "/DBEExpense.inc.php");

class BUExpenseType extends Business
{
    public $dbeExpenseType;
    public $dbeExpense;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeExpenseType = new DBEExpenseType($this);
        $this->dbeExpense = new DBEExpense($this);                // join to item table
    }

    function updateExpenseType(&$dsData)
    {
        $this->setMethodName('updateExpenseType');
        $this->updateDataAccessObject($dsData, $this->dbeExpenseType);
        return TRUE;
    }

    function getExpenseTypeByID($ID, &$dsResults)
    {
        $this->dbeExpenseType->setPKValue($ID);
        $this->dbeExpenseType->getRow();
        return ($this->getData($this->dbeExpenseType, $dsResults));
    }

    function getAllTypes(&$dsResults)
    {
        $this->dbeExpenseType->getRows();
        return ($this->getData($this->dbeExpenseType, $dsResults));
    }

    function deleteExpenseType($ID)
    {
        $this->setMethodName('deleteExpenseType');
        if ($this->canDeleteExpenseType($ID)) {
            return $this->dbeExpenseType->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteExpenseType
     * Only allowed if type has no activities
     * @param $ID
     * @return bool
     */
    function canDeleteExpenseType($ID)
    {
        $dbeExpense = new DBEExpense($this);
        // validate no activities of this type
        $dbeExpense->setValue(DBEExpense::expenseTypeID, $ID);
        return $dbeExpense->countRowsByColumn(DBEExpense::expenseTypeID) < 1;
    }

    public function getExpenseTypesAllowedForActivityTypeID($activityTypeID)
    {
        global $db;
        $result = $db->preparedQuery(
            'select expenseTypeID   from expenseTypeActivityAvailability where activityTypeID = ?',
            [["type" => "i", "value" => $activityTypeID]]
        );

        $selectedActivitiesArray = $result->fetch_all(MYSQLI_ASSOC);
        return array_map(
            function ($activityArray) { return $activityArray['expenseTypeID']; },
            $selectedActivitiesArray
        );
    }
}
