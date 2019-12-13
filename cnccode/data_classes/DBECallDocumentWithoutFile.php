<?php /*
* Call document table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallDocumentWithoutFile extends DBEntity
{
    const callDocumentID = "callDocumentID";
    const problemID = "problemID";
    const callActivityID = "callActivityID";
    const description = "description";
    const filename = "filename";
    const fileLength = "fileLength";
    const fileMIMEType = "fileMIMEType";
    const createDate = "createDate";
    const createUserID = "createUserID";

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
        $this->setTableName("calldocument");
        $this->addColumn(
            self::callDocumentID,
            DA_ID,
            DA_NOT_NULL
        );                // following move to activity-based system
        $this->addColumn(
            self::problemID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::callActivityID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::filename,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::fileLength,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::fileMIMEType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createDate,
            DA_DATE,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createUserID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}
