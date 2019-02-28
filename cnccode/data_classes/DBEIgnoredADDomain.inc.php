<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEIgnoredADDomain extends DBEntity
{
    const ignoredADDomainID = "ignoredADDomainID";
    const domain = "domain";
    const customerID = "customerID";

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
        $this->setTableName("ignoredADDomain");
        $this->addColumn(
            self::ignoredADDomainID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::domain,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
