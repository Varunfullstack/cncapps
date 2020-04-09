<?php /*
* portal document table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPortalCustomerDocumentWithoutFile extends DBEntity
{
    const portalCustomerDocumentID = "portalCustomerDocumentID";
    const customerID = "customerID";
    const description = "description";
    const customerContract = "customerContract";
    const mainContactOnlyFlag = "mainContactOnlyFlag";
    const createdDate = "createdDate";
    const createdUserID = "createdUserID";

    /**
     * portals constructor()
     * @access public
     * @param void
     * @return void
     * @throws Exception
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("portal_customer_document");
        $this->addColumn(self::portalCustomerDocumentID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::customerID, DA_ID, DA_ALLOW_NULL);
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::customerContract, DA_BOOLEAN, DA_NOT_NULL, null, 0);
        $this->addColumn(self::mainContactOnlyFlag, DA_YN, DA_NOT_NULL);
        $this->addColumn(
            self::createdDate,
            DA_DATETIME,
            DA_NOT_NULL,
            null,
            (new DateTime())->format(DATE_MYSQL_DATETIME)
        );
        $this->addColumn(self::createdUserID, DA_ID, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    public function hasContractDocumentByCustomerId($customerID)
    {
        $queryString = 'select count(*) > 0 from ' . $this->getTableName() . " where " . $this->getDBColumnName(
                self::customerContract
            ) . " = 1 and " . $this->getDBColumnName(self::customerID) . " = $customerID";
        $result = $this->db->query($queryString);
        return +$result->fetch_row()[0];
    }
}

?>