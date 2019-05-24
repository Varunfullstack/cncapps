<?php /*
* Supplier payment method table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPayMethod extends DBEntity
{
    const payMethodID = "payMethodID";
    const description = "description";
    const cardFlag = "cardFlag";
    const cardNumber = "cardNumber";
    const expiryDate = "expiryDate";
    const userID = "userID";

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("paymeth");
        $this->addColumn(
            self::payMethodID,
            DA_ID,
            DA_NOT_NULL,
            "pay_payno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "pay_desc"
        );
        $this->addColumn(
            self::cardFlag,
            DA_YN,
            DA_NOT_NULL,
            "pay_card"
        );
        $this->addColumn(
            self::cardNumber,
            DA_STRING,
            DA_ALLOW_NULL,
            "pay_cardno"
        );
        $this->addColumn(
            self::expiryDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "pay_exp_date"
        );
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_ALLOW_NULL,
            "pay_consno"
        );
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
        if (!$this->getValue(self::description)) {
            $this->raiseError('description not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::description) . " LIKE " . $this->getFormattedLikeValue(
                self::description
            ) .
            " ORDER BY " . $this->getDBColumnName(self::description)
        );
        return parent::getRows();
    }
}

?>