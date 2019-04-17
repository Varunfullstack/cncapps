<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBERootCause.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");

class BURootCause extends Business
{
    var $dbeRootCause = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRootCause = new DBERootCause($this);
    }

    function updateRootCause(&$dsData)
    {
        $this->setMethodName('updateRootCause');
        $this->updateDataAccessObject($dsData, $this->dbeRootCause);
        return TRUE;
    }

    function getRootCauseByID($ID, &$dsResults)
    {
        $this->dbeRootCause->setPKValue($ID);
        $this->dbeRootCause->getRow();
        return ($this->getData($this->dbeRootCause, $dsResults));
    }

    function getAll(&$dsResults)
    {
        $this->dbeRootCause->getRows('description');

        return ($this->getData($this->dbeRootCause, $dsResults));
    }

    function deleteRootCause($ID)
    {
        $this->setMethodName('deleteRootCause');
        if ($this->canDelete($ID)) {
            return $this->dbeRootCause->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteRootCause
     * Only allowed if this further actionhas no future action rows at the moment
     * @param $ID
     * @return bool
     */
    function canDelete($ID)
    {
        $dbeProblem = new DBEProblem($this);
        // validate no activities of this type
        $dbeProblem->setValue('rootCauseID', $ID);
        if ($dbeProblem->countRowsByColumn('rootCauseID') < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}