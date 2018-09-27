<?php /*
* portal document table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . '/DBESalesOrderDocumentWithoutFile.php');

class DBESalesOrderDocument extends DBESalesOrderDocumentWithoutFile
{
    const filename = "filename";
    const file = "file";
    const fileMimeType = "fileMimeType";

    /**
     * portals constructor()
     * @access public
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(
            self::filename,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::file,
            DA_BLOB,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::fileMimeType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>