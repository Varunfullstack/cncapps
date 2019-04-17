<?php /**
 * Domain renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_dbe"] . "/DBEThirdPartyContact.inc.php");

class BUThirdPartyContact extends Business
{
    const searchFormCustomerID = 'customerID';
    /** @var DBEThirdPartyContact */
    private $dbeThirdPartyContact;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeThirdPartyContact = new DBEThirdPartyContact ($this);
    }

    function updateThirdPartyContact(&$dsData)
    {
        $this->setMethodName('updateThirdPartyContact');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeThirdPartyContact
        );

        return TRUE;
    }

    function getThirdPartyContactByID($ID,
                                      &$dsResults
    )
    {
        $this->dbeThirdPartyContact->getRow($ID);
        return ($this->getData(
            $this->dbeThirdPartyContact,
            $dsResults
        ));
    }

    function getRowsByCustomerID($customerID,
                                 &$dsResults
    )
    {
        $this->dbeThirdPartyContact->setValue(
            DBEThirdPartyContact::customerID,
            $customerID
        );
        $this->dbeThirdPartyContact->getRowsByColumn(
            DBEThirdPartyContact::customerID,
            DBEThirdPartyContact::software
        );
        return ($this->getData(
            $this->dbeThirdPartyContact,
            $dsResults
        ));
    }

    function delete($thirdPartyContactID)
    {
        $this->dbeThirdPartyContact->deleteRow($thirdPartyContactID);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::searchFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::searchFormCustomerID,
            null
        );
    }

}
