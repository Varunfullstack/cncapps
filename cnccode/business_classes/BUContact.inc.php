<?php /**
 * Contact business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBEContact.inc.php");
define(
    'BUCONTACT_MATCH_STR_NT_PASD',
    'No match string passed'
);

class BUContact extends Business
{
    const EmailClass = 'EmailCLass';

    /** @var DBEContact */
    public $dbeContact;
    /**
     * @var DBEContact
     */
    private $dsContact;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeContact = new DBEContact($this);
    }

    /**
     * @param $supportLevelValue
     * @param Template $template
     * @param string $selected
     * @param string $value
     * @param string $description
     * @param string $parent
     * @param string $block
     */
    public static function supportLevelDropDown($supportLevelValue,
                                                $template,
                                                $selected = 'supportLevelSelected',
                                                $value = 'supportLevelValue',
                                                $description = 'supportLevelDescription',
                                                $parent = 'selectSupportLevel',
                                                $block = 'supportLevelBlock'
    )
    {
        // Site selection
        $supportLevels = [
            ["value" => null, "description" => "None"],
            ["value" => DBEContact::supportLevelMain, "description" => "Main"],
            ["value" => DBEContact::supportLevelSupervisor, "description" => "Supervisor"],
            ["value" => DBEContact::supportLevelSupport, "description" => "Support"],
            ["value" => DBEContact::supportLevelDelegate, "description" => "Delegate"],
            ["value" => DBEContact::supportLevelFurlough, 'description' => 'Furlough']
        ];
        foreach ($supportLevels as $supportLevel) {
            $supportLevelSelected = ($supportLevelValue == $supportLevel['value']) ? CT_SELECTED : null;
            $template->set_var(
                [
                    $selected    => $supportLevelSelected,
                    $value       => $supportLevel['value'],
                    $description => $supportLevel['description']
                ]
            );
            $template->parse(
                $parent,
                $block,
                true
            );
        }
    }

    /**
     * Get general support contact statement row by customerID
     * @parameter integer $contactID
     * @parameter DataSet &$dsResults results
     * @param $ID
     * @param DataSet $dsResults
     * @return bool : Success
     * @access public
     */
    function getContactByID($ID,
                            &$dsResults
    )
    {
        $this->setMethodName('getContactByID');
        return ($this->getDatasetByPK(
            $ID,
            $this->dbeContact,
            $dsResults
        ));
    }

    /**
     * Get Support Contact rows at all customers
     * @param DataSet $dsResults
     * @param bool $customerID
     * @return bool : One or more rows
     * @access public
     */
    function getSupportContacts(&$dsResults,
                                $customerID = false
    )
    {
        $this->setMethodName('getSupportContacts');
        $this->dbeContact->getSupportRows($customerID);
        $ret = ($this->getData(
            $this->dbeContact,
            $dsResults
        ));
        $dsResults->columnSort(
            DBEContact::lastName,
            DBEContact::firstName
        );
        return $ret;
    }

    /**
     * @param DataSet $dsResults
     * @param $customerID
     * @return bool
     */
    function getAuthorisingContacts(&$dsResults,
                                    $customerID
    )
    {
        $this->setMethodName('getSupportContacts');
        $this->dbeContact->getAuthorisingRows($customerID);
        $ret = ($this->getData(
            $this->dbeContact,
            $dsResults
        ));
        $dsResults->columnSort(
            DBEContact::lastName,
            DBEContact::firstName
        );
        return $ret;
    }

    /**
     * Get Contact rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter customerID
     * @parameter siteNo
     * @parameter String $nameSearchString String to match against or numeric contactID
     * @parameter DataSet &$dsResults results
     * @param $customerID
     * @param $matchString
     * @param DataSet $dsResults
     * @param string $siteNo
     * @return bool : One or more rows
     * @access public
     */
    function getCustomerContactsByNameMatch($customerID,
                                            $matchString,
                                            &$dsResults,
                                            $siteNo = null
    )
    {
        $this->setMethodName('getCustomerContactsByNameMatch');
        if (!$customerID) {
            $this->raiseError('customerID not passed');
        }
        $matchString = trim($matchString);
        $ret         = FALSE;
        if (is_numeric($matchString)) {
            $ret = ($this->getContactByID(
                $matchString,
                $dsResults
            ));
            if ($ret) {
                if ($dsResults->getValue(DBEContact::customerID) != $customerID) {
                    $ret = false;
                }
            }
        }
        if (!$ret) {

            if (isset($matchString[0]) && $matchString{0} == '?') {  // get all contacts for customer/site
                if ($siteNo == '') {
                    $this->dbeContact->getRowsByCustomerID($customerID);
                } else {
                    $this->dbeContact->getRowsByCustomerIDSiteNo(
                        $customerID,
                        $siteNo
                    );
                }
            } else {                                                // try to match
                $this->dbeContact->getSupportContactRowsByNameMatch(
                    $customerID,
                    $matchString
                );
            }
            $ret = ($this->getData(
                $this->dbeContact,
                $dsResults
            ));
            $dsResults->columnSort(
                DBEContact::lastName,
                DBEContact::firstName
            );
        }
        return $ret;
    }

    /**
     * Get contact row by customerID
     * @param $customerID
     * @param DataSet $dsResults
     */
    function getGSCContactByCustomerID($customerID,
                                       &$dsResults
    )
    {
        $this->setMethodName('getContactByCustomerID');
        $this->dbeContact->getGSCRowsByCustomerID($customerID);
        $this->getData(
            $this->dbeContact,
            $dsResults
        );
    }

    /**
     * Create a new dataset containing defaults for new contact row
     * @parameter integer $supplierID for whom to create contact (optional)
     * @parameter integer $customerID for whom to create contact
     * @parameter DataSet &$dsResults results
     * @param $supplierID
     * @param $customerID
     * @param $siteNo
     * @param $dsResults DataSet
     * @return bool : Success
     * @access public
     */
    function initialiseNewContact($supplierID,
                                  $customerID,
                                  $siteNo,
                                  &$dsResults
    )
    {
        $this->setMethodName('initialiseNewContact');
        // create/populate new dataset
        if (!$supplierID && !$customerID) {
            $this->raiseError('a supplierID or customerID must be passed');
        }
        if ($customerID && $siteNo == '') {
            $this->raiseError('default siteNo must be passed');
        }
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $dsResults->copyColumnsFrom($this->dbeContact);
        $dsResults->setNull(DBEContact::contactID, DA_ALLOW_NULL);
        $dsResults->setUpdateModeInsert();
        $dsResults->setValue(
            DBEContact::contactID,
            null
        );
        $dsResults->setValue(
            DBEContact::siteNo,
            $siteNo
        );
        $dsResults->setValue(
            DBEContact::customerID,
            $customerID
        );
        $dsResults->setValue(
            DBEContact::discontinuedFlag,
            'N'
        );
        $dsResults->setValue(
            DBEContact::notes,
            null
        );
        $dsResults->setValue(
            DBEContact::mailshot2Flag,
            $dsHeader->getValue(DBEHeader::mailshot2FlagDef)
        );
        $dsResults->setValue(
            DBEContact::mailshot3Flag,
            $dsHeader->getValue(DBEHeader::mailshot3FlagDef)
        );
        $dsResults->setValue(
            DBEContact::mailshot4Flag,
            $dsHeader->getValue(DBEHeader::mailshot4FlagDef)
        );
        $dsResults->setValue(
            DBEContact::mailshot8Flag,
            $dsHeader->getValue(DBEHeader::mailshot8FlagDef)
        );
        $dsResults->setValue(
            DBEContact::mailshot9Flag,
            $dsHeader->getValue(DBEHeader::mailshot9FlagDef)
        );
        $dsResults->setValue(
            DBEContact::mailshot11Flag,
            $dsHeader->getValue(DBEHeader::mailshot11FlagDef)
        );
        $dsResults->post();
        return TRUE;
    }

    /**
     * Create a new dataset containing defaults contact mailshot flags
     * this is done because HTML posts do not return values for unchecked
     * checkbox fields.
     * @parameter integer $supplierID for whom to create contact
     * @parameter DataSet &$dsResults results
     * @param $dsResults DataSet
     * @return bool : Success
     * @access public
     */
    function initialiseUpdateContact(&$dsResults)
    {
        $this->setMethodName('initialiseUpdateContact');
        // create/populate new dataset
        $dsResults->copyColumnsFrom($this->dbeContact);
        $dsResults->setValue(
            DBEContact::discontinuedFlag,
            'N'
        );
        $dsResults->setValue(
            DBEContact::mailshot2Flag,
            'N'
        );
        $dsResults->setValue(
            DBEContact::mailshot3Flag,
            'N'
        );
        $dsResults->setValue(
            DBEContact::mailshot4Flag,
            'N'
        );
        $dsResults->setValue(
            DBEContact::mailshot8Flag,
            'N'
        );
        $dsResults->setValue(
            DBEContact::mailshot9Flag,
            'N'
        );
        return TRUE;
    }

    /**
     * Update/Insert contact to DB
     *    Only handles one row in dataset.
     *
     * @parameter DataSet &$dsResults results
     * @param $dsContact
     * @return bool : Success
     * @access public
     */
    function updateContact(&$dsContact)
    {
        $this->setMethodName('updateContact');
        return ($this->updateDataAccessObject(
            $dsContact,
            $this->dbeContact
        ));
    }

    public function validateContact(DataSet &$dsContact)
    {
        /**
         *
         * + First Name Required
         * + Last Name Required
         * + Title Required
         * + Email optional, unique
         * + Password at least 8 characters, at least 3 of 4 charsets ([a-z], [A-Z], [0-9], special characters)
         * + Accounts at least one per customer, ignore if referred
         * + Statement at least one, at most one, per customer, ignore if referred
         * + Main at least one per customer, ignore if referred
         * + Review at least one per customer, ignore if referred
         * + topUp at least one per customer if prepay contract, ignore if referred
         * + Reports at least one per customer, ignore if referred
         */
        if (empty($dsContact->getValue(DBEContact::firstName))) {
            $dsContact->setMessage(
                DBEContact::firstName,
                'First Name is required'
            );
        }
        if (empty($dsContact->getValue(DBEContact::lastName))) {
            $dsContact->setMessage(
                DBEContact::lastName,
                'Last Name is required'
            );
        }
        if (empty($dsContact->getValue(DBEContact::title))) {
            $dsContact->setMessage(
                DBEContact::title,
                'Title is required'
            );
        }
        if (!empty($dsContact->getValue(DBEContact::email))) {

            $buCustomer = new BUCustomer($this);
            if ($buCustomer->duplicatedEmail(
                $dsContact->getValue(DBEContact::email),
                $dsContact->getValue(DBEContact::contactID) ? $dsContact->getValue(DBEContact::contactID) : null
            )) {
                $this->dsContact->setValue(
                    self::EmailClass,
                    CTCUSTOMER_CLS_FORM_ERROR
                );
            }


        }

    }


    public function getTodayLeaverContacts(&$dsResults)
    {
        $this->setMethodName('getContactByCustomerID');
        $this->dbeContact->getTodayLeavers();
        $this->getData(
            $this->dbeContact,
            $dsResults
        );
    }

    public function getContactsWithPendingFurloughActionForToday(&$dsResults)
    {
        $this->setMethodName('getContactByCustomerID');
        $this->dbeContact->getContactsWithPendingFurloughActionForToday();
        $this->getData(
            $this->dbeContact,
            $dsResults
        );
    }

    public function getSpecialAttentionContacts(&$dsResults)
    {
        $this->dbeContact->getSpecialAttentionCustomers();
        return $this->getData(
            $this->dbeContact,
            $dsResults
        );
    }

    public function getReviewContacts($customerID,
                                      DataSet $dsResults
    )
    {
        $this->dbeContact->getReviewContacts($customerID);
        return $this->getData(
            $this->dbeContact,
            $dsResults
        );
    }
}
