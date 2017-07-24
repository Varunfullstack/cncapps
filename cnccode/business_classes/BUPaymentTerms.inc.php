<?php /**
 * Call payment terms business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEPaymentTerms.inc.php");
require_once($cfg["path_dbe"] . "/DBEInvhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdhead.inc.php");

class BUPaymentTerms extends Business
{
    var $dbePaymentTerms = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePaymentTerms = new DBEPaymentTerms($this);
    }

    function updatePaymentTerms(&$dsData)
    {
        $this->setMethodName('updatePaymentTerms');
        $this->updateDataaccessObject($dsData, $this->dbePaymentTerms);
        return TRUE;
    }

    function getPaymentTermsByID($ID, &$dsResults)
    {
        $this->dbePaymentTerms->setPKValue($ID);
        $this->dbePaymentTerms->getRow();
        return ($this->getData($this->dbePaymentTerms, $dsResults));
    }

    function getAllTerms(&$dsResults)
    {
        $this->dbePaymentTerms->getRows();
        return ($this->getData($this->dbePaymentTerms, $dsResults));
    }

    function deletePaymentTerms($ID)
    {
        $this->setMethodName('deletePaymentTerms');
        if ($this->canDeletePaymentTerms($ID)) {
            return $this->dbePaymentTerms->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeletePaymentTerms
     * Only allowed if type has no activities
     */
    function canDeletePaymentTerms($ID)
    {
        $dbeInvhead = new DBEInvhead($this);
        $dbeOrdhead = new DBEOrdhead($this);
        // validate no activities of this type
        $dbeInvhead->setValue('paymentTermsID', $ID);
        $dbeOrdhead->setValue('paymentTermsID', $ID);
        if ($dbeInvhead->countRowsByColumn('paymentTermsID') < 1 && $dbeOrdhead->countRowsByColumn('paymentTermsID') < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}// End of class
?>