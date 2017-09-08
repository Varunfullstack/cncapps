<?php /*
* Supplier payment method table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPayMethod extends DBEntity
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("paymeth");
        $this->addColumn("payMethodID", DA_ID, DA_NOT_NULL, "pay_payno");
        $this->addColumn("description", DA_STRING, DA_NOT_NULL, "pay_desc");
        $this->addColumn("cardFlag", DA_YN, DA_NOT_NULL, "pay_card");
        $this->addColumn("cardNumber", DA_STRING, DA_ALLOW_NULL, "pay_cardno");
        $this->addColumn("expiryDate", DA_DATE, DA_ALLOW_NULL, "pay_exp_date");
        $this->addColumn("userID", DA_ID, DA_ALLOW_NULL, "pay_consno");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by name match
     * @access public
     * @return bool Success
     */
    function getRowsByDescriptionMatch()
    {
        $this->setMethodName("getRowsByDescriptionMatch");
        $ret = FALSE;
        if ($this->getValue('description') == '') {
            $this->raiseError('description not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('description') . " LIKE " . $this->getFormattedLikeValue('description') .
            " ORDER BY " . $this->getDBColumnName('description')
        );
        $ret = (parent::getRows());
        return $ret;
    }
}

?>