<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 11:22
 */
require_once($cfg["path_dbe"] . '/DBEPortalCustomerDocumentWithoutFile.inc.php');

class DBEPortalCustomerDocument extends DBEPortalCustomerDocumentWithoutFile
{
    /**
     * portals constructor()
     * @access public
     * @param $owner
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