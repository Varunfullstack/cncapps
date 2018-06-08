<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEStandardText.inc.php");

class BUStandardText extends Business
{
    public $dbeStandardText;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeStandardText = new DBEStandardText($this);
    }

    function updateStandardText(&$dsData)
    {
        $this->setMethodName('updateStandardText');
        $this->updateDataaccessObject($dsData, $this->dbeStandardText);
        return TRUE;
    }

    function getStandardTextByID($ID, &$dsResults)
    {
        $this->dbeStandardText->setPKValue($ID);
        $this->dbeStandardText->getRow();
        return ($this->getData($this->dbeStandardText, $dsResults));
    }

    function getAllTypes(&$dsResults)
    {
        $this->dbeStandardText->getRows();
        return ($this->getData($this->dbeStandardText, $dsResults));
    }

    function deleteStandardText($ID)
    {
        $this->setMethodName('deleteStandardText');
        return $this->dbeStandardText->deleteRow($ID);
    }
}// End of class
?>