<?php /*
* Supplier payment terms table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPaymentTerms extends DBEntity
{

    const paymentTermsID = "paymentTermsID";
    const description = "description";
    const days = "days";
    const generateInvoiceFlag = "generateInvoiceFlag";
    const automaticInvoiceFlag = "automaticInvoiceFlag";

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
        $this->setTableName("paymentterms");
        $this->addColumn(
            self::paymentTermsID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::days,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::generateInvoiceFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::automaticInvoiceFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>