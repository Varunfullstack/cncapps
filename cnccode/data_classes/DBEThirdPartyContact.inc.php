<?php /*
* AnswerType table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEThirdPartyContact extends DBEntity
{
    const ID = "thirdPartyContactID";
    const customerID = "customerID";
    const software = "software";
    const vendor = "vendor";
    const phone = "phone";
    const email = "email";
    const notes = "notes";

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
        $this->setTableName("third_party_contact");
        $this->addColumn(
            self::ID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self:: software,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self:: vendor,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self:: phone,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self:: email,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self:: notes,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
