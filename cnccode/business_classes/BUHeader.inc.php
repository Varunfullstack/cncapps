<?php /**
 * System Header business class
 * NOTE: Uses new lower case naming convention
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBEJHeader.php");

class BUHeader extends Business
{
    public $dbeHeader;
    public $dbeJHeader;

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeHeader = new DBEHeader($this);
        $this->dbeJHeader = new DBEJHeader($this);
    }

    /**
     * Get customer rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @param DataSet $dsResults
     * @return bool : One or more rows
     * @access public
     */
    function getHeader(&$dsResults)
    {
        $this->setMethodName('getHeader');
        $this->dbeJHeader->getRow();
        return ($this->getData($this->dbeJHeader, $dsResults));
    }

    function updateHeader(&$dsData)
    {
        $this->setMethodName('updateHeader');
        $this->updateDataaccessObject($dsData, $this->dbeHeader);
        return TRUE;
    }

    function updateHelpDesk($data)
    {

        $this->setMethodName('updateHelpDesk');
        $this->dbeHeader->getRow(1);
        $this->dbeHeader->setValue('helpDeskProblems', $data['helpDeskProblems']);
        $this->dbeHeader->updateRow();
        return TRUE;
    }

    function clearActivityProblemField()
    {

        $this->setMethodName('clearActivityProblemField');
        $this->dbeHeader->getRow(1);
        $this->dbeHeader->setValue('helpDeskProblems', '');
        $this->dbeHeader->updateRow();
        return TRUE;
    }
}// End of class
?>