<?php /*
* portal document table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . '/DBESalesOrderDocumentWithoutFile.php');

class DBESalesOrderDocument extends DBESalesOrderDocumentWithoutFile
{
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
        $this->addColumn("filename", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("file", DA_BLOB, DA_ALLOW_NULL);
        $this->addColumn("fileMimeType", DA_STRING, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>