<?php /*
* Quotation table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESignableEnvelope extends DBEntity
{
    const id = "id";
    const status = "status";
    const downloadLink = "downloadLink";
    const processingClass = "processingClass";
    const processingArguments = "processingArguments";
    const createdAt = "createdAt";
    const updatedAt = "updatedAt";

    /**
     * calls constructor()
     * @access public
     * @param $owner
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("signableEnvelope");
        $this->addColumn(self::id, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::status, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::downloadLink, DA_STRING, DA_ALLOW_NULL);
        $this->addColumn(self::processingClass, DA_STRING, DA_ALLOW_NULL);
        $this->addColumn(self::processingArguments, DA_STRING, DA_ALLOW_NULL);
        $this->addColumn(self::createdAt, DA_DATETIME, DA_ALLOW_NULL);
        $this->addColumn(self::updatedAt, DA_DATETIME, DA_ALLOW_NULL);
        $this->setAddColumnsOff();
        $this->setPK(0, false);
    }
}
