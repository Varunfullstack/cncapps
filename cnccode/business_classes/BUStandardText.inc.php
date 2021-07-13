<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\StandardTextNotFoundException;

require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEStandardText.inc.php");

class BUStandardText extends Business
{
    const SignableContractsEmailType = 4;

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
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeStandardText
        );
        return TRUE;
    }

    /**
     * @throws StandardTextNotFoundException
     */
    function getStandardTextByID($ID,
                                 &$dsResults
    ): bool
    {
        $this->dbeStandardText->setPKValue($ID);
        if (!$this->dbeStandardText->getRow()) {
            throw new StandardTextNotFoundException($ID);
        }
        return ($this->getData(
            $this->dbeStandardText,
            $dsResults
        ));
    }

    function getAllTypes(&$dsResults)
    {
        $this->dbeStandardText->getRows();
        return ($this->getData(
            $this->dbeStandardText,
            $dsResults
        ));
    }

    function deleteStandardText($ID)
    {
        $this->setMethodName('deleteStandardText');
        return $this->dbeStandardText->deleteRow($ID);
    }

    public function getStandardTextByTypeID(int $standardTextTypeID,
                                            &$dsResults
    )
    {
        $this->dbeStandardText->getRowsByTypeID($standardTextTypeID);
        return ($this->getData(
            $this->dbeStandardText,
            $dsResults
        ));
    }
}// End of class
?>