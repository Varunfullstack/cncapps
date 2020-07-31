<?php /*
* Customer note table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStreamOneCustomers extends DBEntity
{
    const id="id" ;
    const name="name";          
    const email="email";
    const createdOn="createdOn" ;
    const country="country" ;
    const companyName="companyName";
    const city="city" ;
    const addressLine1="addressLine1" ;
    const addressLine2="addressLine2" ;
    const MsDomain="MsDomain";
    const phone1="phone1";
    const postalCode="postalCode";
    const title="title";
    const endCustomerId="endCustomerId";
    const endCustomerPO="endCustomerPO";

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
        $this->setTableName("streamonecustomers");
        $this->addColumn(self::id, DA_ID, DA_NOT_NULL, "id");
        $this->addColumn(self::addressLine1, DA_TEXT, DA_ALLOW_NULL, "addressLine1");
        $this->addColumn(self::addressLine2, DA_TEXT, DA_ALLOW_NULL, "addressLine2");
        $this->addColumn(self::city, DA_TEXT, DA_ALLOW_NULL, "city");
        $this->addColumn(self::companyName, DA_TEXT, DA_ALLOW_NULL, "companyName");
        $this->addColumn(self::country, DA_TEXT, DA_ALLOW_NULL, "country");
        $this->addColumn(self::createdOn, DA_DATETIME, DA_ALLOW_NULL, "createdOn");
        $this->addColumn(self::email, DA_TEXT, DA_ALLOW_NULL, "email");
        $this->addColumn(self::endCustomerId, DA_TEXT, DA_ALLOW_NULL, "endCustomerId");
        $this->addColumn(self::endCustomerPO, DA_TEXT, DA_ALLOW_NULL, "endCustomerPO");
        $this->addColumn(self::MsDomain, DA_TEXT, DA_ALLOW_NULL, "MsDomain");
        $this->addColumn(self::name, DA_TEXT, DA_ALLOW_NULL, "name");
        $this->addColumn(self::phone1, DA_TEXT, DA_ALLOW_NULL, "phone1");
        $this->addColumn(self::postalCode, DA_TEXT, DA_ALLOW_NULL, "postalCode");
        $this->addColumn(self::title, DA_TEXT, DA_ALLOW_NULL, "title");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>