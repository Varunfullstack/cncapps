<?php
/**
 * Customer Item controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;

global $cfg;
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerItemDocument.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEWarranty.inc.php');
// Messages
define(
    'CTCUSTOMERITEM_MSG_CUSTOMER_ITEM_NOT_FND',
    'Customer Item not found'
);
define(
    'CTCUSTOMERITEM_MSG_customerItemID_NOT_PASSED',
    'customerItemID not passed'
);
// Actions
define(
    'CTCUSTOMERITEM_ACT_DISP_SEARCH',
    'dispSearch'
);
define(
    'CTCUSTOMERITEM_ACT_DISPLAY',
    'displayCI'
);
define(
    'CTCUSTOMERITEM_ACT_DELETE',
    'deleteCI'
);
define(
    'CTCUSTOMERITEM_ACT_UPDATE',
    'updateCI'
);
define(
    'CTCUSTOMERITEM_ACT_INSERT',
    'insertCI'
);
define(
    'CTCUSTOMERITEM_ACT_ADD',
    'addCI'
);
define(
    'CTCUSTOMERITEM_ACT_EDIT',
    'editCI'
);
define(
    'CTCUSTOMERITEM_ACT_UPLOAD_DOCUMENT',
    'uploadDocument'
);
define(
    'CTCUSTOMERITEM_ACT_VIEW_DOCUMENT',
    'viewDocument'
);
define(
    'CTCUSTOMERITEM_ACT_GET_DOCUMENT',
    'getDocument'
); // get stream
define(
    'CTCUSTOMERITEM_ACT_DELETE_DOCUMENT',
    'deleteDocument'
);
define(
    'CTCUSTOMERITEM_ACT_PRINT_CONTRACT',
    'printContract'
);

class CTCustomerItem extends CTCNC
{
    public $dsCustomerItem;
    public $buCustomerItem;

    public $contractIDs;

    public $dsSearchForm;
    public $renewalStatusArray       = array(
        "D" => "Declined",
        "R" => "Renewed"
    );
    public $secondsiteImageDelayDays = array(
        "0" => "0 - Check for image time-stamped last night at 7pm",
        "1" => "1",
        "2" => "2",
        "3" => "3"
    );

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            TECHNICAL_PERMISSION,
            SALES_PERMISSION
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsSearchForm   = new DSForm($this);
        $this->dsCustomerItem = new DSForm($this);
        $this->template->setVar("menuId", 106);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($this->getAction()) {
            case CTCNC_ACT_SEARCH:
                if ($this->getParam('Search') == 'Add Contract') {
                    $this->applyContractUpdates('add');
                } elseif ($this->getParam('Search') == 'Remove Contract') {
                    $this->applyContractUpdates('remove');
                }
                $this->search();
                break;
            case CTCUSTOMERITEM_ACT_DISP_SEARCH:
                $this->displaySearchForm();
                break;
            case CTCUSTOMERITEM_ACT_DISPLAY:
                $this->display();
                break;
            case 'displayRenewalContract':
                $this->displayRenewalContract();
                break;
            case CTCUSTOMERITEM_ACT_ADD:
                $this->add();
                break;
            case CTCUSTOMERITEM_ACT_INSERT:
            case CTCUSTOMERITEM_ACT_UPDATE:
                $this->update();
                break;
            case CTCUSTOMERITEM_ACT_DELETE:
                $this->delete();
                break;
            case CTCNC_ACT_CUSTOMERITEM_POPUP:
                $this->displayItemSelectPopup();
                break;
            case CTCUSTOMERITEM_ACT_UPLOAD_DOCUMENT:
                $this->uploadDocument();
                break;
            case CTCUSTOMERITEM_ACT_VIEW_DOCUMENT:
                $this->viewDocument();
                break;
            case CTCUSTOMERITEM_ACT_DELETE_DOCUMENT:
                $this->deleteDocument();
                break;
            case CTCUSTOMERITEM_ACT_PRINT_CONTRACT:
                $this->printContract();
                break;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function setParentFormFields()
    {
        if ($this->getParam('parentIDField')) {
            $this->setSessionParam('parentIDField', $this->getParam('parentIDField'));
        }
        if ($this->getParam('parentWarrantyIDField')) {
            $this->setSessionParam('parentWarrantyIDField', $this->getParam('parentWarrantyIDField'));
        }
        if ($this->getParam('parentDescField')) {
            $this->setSessionParam('parentDescField', $this->getParam('parentDescField'));
        }
    }

    function applyContractUpdates($action)
    {
        $this->setMethodName('applyContractUpdates');
        if ($this->getParam('customerItemIDs')) {

            if ($action == 'add') {
                $this->buCustomerItem->addContractToCustomerItems(
                    $this->getParam('contractID'),
                    $this->getParam('customerItemIDs')
                );
            } else {
                $this->buCustomerItem->removeContractFromCustomerItems(
                    $this->getParam('contractID'),
                    $this->getParam('customerItemIDs')
                );

            }

        }
    }

    /**
     * Run search based upon passed parameters
     * Display search form with results
     * @access private
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        $this->buCustomerItem->initialiseSearchForm($this->dsSearchForm);
        if (!$this->dsSearchForm->populateFromArray($this->getParam('customerItem'))) {
            $this->setFormErrorOn();
            $this->displaySearchForm(); //redisplay with errors
            exit;
        }
        if ($this->getParam('CSV')) {

            $this->buCustomerItem->search(
                $this->dsSearchForm,
                $this->dsCustomerItem,
                0                                // no row count limit
            );
        } else {
            $this->buCustomerItem->search(
                $this->dsSearchForm,
                $this->dsCustomerItem,
                3000                            // row count limit
            );
        }
        if ($this->getParam('CSV')) {
            $this->generateCSV();
        } else {
            $this->displaySearchForm();
        }
    }

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $this->setTemplateFiles(
            array(
                'CustomerItemSearch'                 => 'CustomerItemSearch.inc',
                'CustomerItemSearchContractSelector' => 'CustomerItemSearchContractSelector.inc'
            )
        );
        $this->template->setVar('javaScript', "<link rel='stylesheet' href='components/shared/ToolTip.css'>");
// Parameters
        $this->setPageTitle("Customer Items");
        $urlSubmit        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $customerPopupURL = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
// search parameter section
        if ($dsSearchForm->rowCount() == 0) {
            $this->buCustomerItem->initialiseSearchForm($dsSearchForm);
        }
        $customerString = null;
        if ($dsSearchForm->getValue(DBECustomerItem::customerID)) {
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(DBECustomerItem::customerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $this->template->set_var(
            array(
                'customerID'       => $dsSearchForm->getValue(DBECustomerItem::customerID),
                'customerString'   => $customerString,
                'customerItemID'   => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(DBECustomerItem::customerItemID)
                ),
                'ordheadID'        => Controller::htmlDisplayText($dsSearchForm->getValue(DBECustomerItem::ordheadID)),
                'ordheadIDMessage' => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage(BUCustomerItem::searchFormOrdheadID)
                ),
                'serialNo'         => Controller::htmlDisplayText($dsSearchForm->getValue(DBECustomerItem::serialNo)),
                'itemText'         => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUCustomerItem::searchFormItemText)
                ),
                'contractText'     => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUCustomerItem::searchFormContractText)
                ),
                'customerPopupURL' => $customerPopupURL,
                'urlSubmit'        => $urlSubmit
            )
        );
        $this->template->set_block(
            'CustomerItemSearch',
            'renewalStatusBlock',
            'renewalStatus'
        );
        $this->parseRenewalSelector($dsSearchForm->getValue(BUCustomerItem::searchFormRenewalStatus));
        // Lines section
        if ($this->dsCustomerItem->rowCount() > 0) {
            $this->dsCustomerItem->initialise();
            $this->template->set_block(
                'CustomerItemSearch',
                'itemBlock',
                'items'
            );
            $siteCol            = $this->dsCustomerItem->columnExists(DBEJCustomerItem::siteDescription);
            $customerNameCol    = $this->dsCustomerItem->columnExists(DBEJCustomerItem::customerName);
            $serialNoCol        = $this->dsCustomerItem->columnExists(DBECustomerItem::serialNo);
            $itemDescriptionCol = $this->dsCustomerItem->columnExists(DBEJCustomerItem::itemDescription);
            $customerItemIDCol  = $this->dsCustomerItem->columnExists(DBECustomerItem::customerItemID);
            $serverNameCol      = $this->dsCustomerItem->columnExists(DBECustomerItem::serverName);
            if ($dsSearchForm->getValue(BUCustomerItem::searchFormCustomerID)) {
                $this->parseContractSelector(
                    $dsSearchForm->getValue(BUCustomerItem::searchFormCustomerID),
                    'CustomerItemSearchContractSelector'
                );
            }
            while ($this->dsCustomerItem->fetchNext()) {

                $customerItemID = $this->dsCustomerItem->getValue($customerItemIDCol);
                $urlItem        = $this->getContractUrl($this->dsCustomerItem);
                $checkBox       = null;
                if ($dsSearchForm->getValue(BUCustomerItem::searchFormCustomerID)) {
                    $checkBox = '<input type="checkbox" id="salesOrder" name="customerItemIDs[' . $customerItemID . ']" value="' . $customerItemID . '" />';
                }
                $contracts = $this->buCustomerItem->getContractDescriptionsByCustomerItemId($customerItemID);
                $this->template->set_var(
                    array(
                        'listCustomerName'        => Controller::htmlDisplayText(
                            $this->dsCustomerItem->getValue($customerNameCol)
                        ),
                        'listSiteName'            => Controller::htmlDisplayText(
                            $this->dsCustomerItem->getValue($siteCol)
                        ),
                        'listItemDescription'     => Controller::htmlDisplayText(
                            $this->dsCustomerItem->getValue($itemDescriptionCol)
                        ),
                        'urlItem'                 => $urlItem,
                        'listSerialNo'            => Controller::htmlDisplayText(
                            $this->dsCustomerItem->getValue($serialNoCol)
                        ),
                        'listContractDescription' => $contracts,
                        'listServerName'          => Controller::htmlDisplayText(
                            $this->dsCustomerItem->getValue($serverNameCol)
                        ),
                        'checkBox'                => $checkBox
                    )
                );
                $this->template->parse(
                    'items',
                    'itemBlock',
                    true
                );
            }
        }
        if ($dsSearchForm->getValue(BUCustomerItem::searchFormCustomerID)) {
            $this->template->parse(
                'customerItemSearchContractSelector',
                'CustomerItemSearchContractSelector',
                true
            );

        }
        $this->template->parse(
            'CONTENTS',
            'CustomerItemSearch',
            true
        );
        $this->parsePage();
    }

    /**
     * Display the renewal status drop-down selector
     *
     * @access private
     * @param $renewalStatus
     */
    function parseRenewalSelector($renewalStatus)
    {
        foreach ($this->renewalStatusArray as $key => $value) {
            $renewalStatusSelected = ($renewalStatus == $key) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'renewalStatusSelected'    => $renewalStatusSelected,
                    'renewalStatus'            => $key,
                    'renewalStatusDescription' => $value
                )
            );
            $this->template->parse(
                'renewalStatus',
                'renewalStatusBlock',
                true
            );
        }
    }

    /**
     * put your comment there...
     *
     * @param mixed $customerID
     * @param mixed $templateName
     * @param array $contractIDs
     */
    function parseContractSelector($customerID,
                                   $templateName,
                                   $contractIDs = []
    )
    {
        $dsContract = new DataSet($this);
        $this->buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract,
            null
        );
        $this->template->set_block(
            $templateName,
            'contractBlock',
            'contracts'
        );
        while ($dsContract->fetchNext()) {
            $selected = null;
            if ($contractIDs && count($contractIDs) > 0) {

                if (in_array(
                    $dsContract->getValue(DBEJCustomerItem::customerItemID),
                    $contractIDs
                )) {
                    $selected = CT_CHECKED;
                }
            }
            $this->template->set_var(
                array(
                    'contractDescription' => $dsContract->getValue(DBEJCustomerItem::itemDescription),
                    'contractID'          => $dsContract->getValue(DBEJCustomerItem::customerItemID),
                    'contractSelected'    => $selected
                )
            );
            $this->template->parse(
                'contracts',
                'contractBlock',
                true
            );
        } // while
    }

    /**
     * @param DataSet $dsCustomerItem
     * @return mixed|string
     * @throws Exception
     */
    function getContractUrl($dsCustomerItem)
    {
        switch ($dsCustomerItem->getValue(DBEJCustomerItem::renewalTypeID)) {
            case CONFIG_BROADBAND_RENEWAL_TYPE_ID:
                $urlItem = Controller::buildLink(
                    'RenBroadband.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                    )
                );
                break;
            case CONFIG_CONTRACT_RENEWAL_TYPE_ID:
                $urlItem = Controller::buildLink(
                    'RenContract.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                    )
                );
                break;
            case CONFIG_QUOTATION_RENEWAL_TYPE_ID:
                $urlItem = Controller::buildLink(
                    'RenQuotation.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                    )
                );
                break;
            case CONFIG_DOMAIN_RENEWAL_TYPE_ID:
                $urlItem = Controller::buildLink(
                    'RenDomain.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                    )
                );
                break;
            case CONFIG_HOSTING_RENEWAL_TYPE_ID:
                $urlItem = Controller::buildLink(
                    'RenHosting.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                    )
                );
                break;
            default:
                $urlItem = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTCUSTOMERITEM_ACT_DISPLAY,
                        'customerItemID' => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                    )
                );
                break;
        } // end switch
        return $urlItem;
    }

    /**
     * User search results to download a CSV file
     *
     * @access private
     */
    function generateCSV()
    {
        $fileName = 'CUSTOMER_ITEMS.CSV';
        Header('Content-type: text/plain');
        Header('Content-Disposition: attachment; filename=' . $fileName);
        echo 'Customer,Site,Description,SerialNo,Contracts,ServerName' . "\n";
        while ($this->dsCustomerItem->fetchNext()) {

            $contracts = $this->buCustomerItem->getContractDescriptionsByCustomerItemId(
                $this->dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
            );
            echo '"' . $this->getExcelValue(
                    $this->dsCustomerItem->getValue(DBEJCustomerItem::customerName)
                ) . '",' . '"' . $this->getExcelValue(
                    $this->dsCustomerItem->getValue(DBEJCustomerItem::siteDescription)
                ) . '",' . '"' . $this->getExcelValue(
                    $this->dsCustomerItem->getValue(DBEJCustomerItem::itemDescription)
                ) . '",' . '"' . $this->getExcelValue(
                    $this->dsCustomerItem->getValue(DBEJCustomerItem::serialNo)
                ) . '",' . '"' . $this->getExcelValue($contracts) . '",' . '"' . $this->getExcelValue(
                    $this->dsCustomerItem->getValue(DBEJCustomerItem::serverName)
                ) . '"' . "\n";
        }
        $this->pageClose();
        exit;
    }

    function getExcelValue($value)
    {

        $value = str_replace(
            ',',
            '',
            $value
        );
        $value = str_replace(
            "\r\n",
            " ",
            $value
        );      // remove carriage returns
        $value = str_replace(
            "\"",
            "",
            $value
        );        // and double quotes
        return $value;
    }
    /**
     * Display the popup list of items under given contract (for CTActivity)
     *
     * @access private
     */
    /**
     * @throws Exception
     */
    function display()
    {
        $this->setMethodName('display');
        $this->setTemplateFiles(
            'CustomerItemDisplay',
            'CustomerItemDisplay.inc'
        );
        $this->setPageTitle("Customer Item");
        $dsCustomerItem = &$this->dsCustomerItem; // local refs
        $buCustomerItem = &$this->buCustomerItem;
        $customerItemID = null;
        if ($this->getAction() != CTCUSTOMERITEM_ACT_ADD) {
            if (!$this->getFormError()) {
                $buCustomerItem->getCustomerItemByID(
                    $this->getParam('customerItemID'),
                    $dsCustomerItem
                );
                /*
            Get list of contracts this item is attached to
            */
                $this->contractIDs = $buCustomerItem->getContractIDsByCustomerItemID($this->getParam('customerItemID'));
            } else {
                $dsCustomerItem->initialise(); // form error so already have a dataset
            }
            $dsCustomerItem->fetchNext();
            $customerItemID = $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID);
        } else {
            if (!$this->getParam('customerID')) {
                $this->raiseError('CustomerID not passed');
            } else {
                $buCustomerItem->initialiseNewCustomerItem($dsCustomerItem);
                $dsCustomerItem->setValue(
                    DBEJCustomerItem::customerID,
                    $this->getParam('customerID')
                );
            }
        }
        if ($this->getAction() == CTCUSTOMERITEM_ACT_ADD) {
            $urlSubmit = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMERITEM_ACT_INSERT
                )
            );
        } else {
            $urlSubmit = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMERITEM_ACT_UPDATE
                )
            );
        }
        $urlContractPopup = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            []
        );
        $urlItemPopup     = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_DISP_ITEM_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlItemEdit      = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_ITEM_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlPrintContract = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => CTCUSTOMERITEM_ACT_PRINT_CONTRACT,
                'customerItemID' => $customerItemID
            )
        );
        $this->template->set_var(
            array(
                'txtPrintContract' => 'Print Contract',
                'urlPrintContract' => $urlPrintContract
            )
        );
        // Display delete link if no dependencies
        if ($this->getAction() != CTCUSTOMERITEM_ACT_ADD) {
            if ($buCustomerItem->canDelete()) {
                $urlDelete = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTCUSTOMERITEM_ACT_DELETE,
                        'customerItemID' => $customerItemID
                    )
                );
                $this->template->set_var(
                    array(
                        'txtDelete' => 'Delete',
                        'urlDelete' => $urlDelete
                    )
                );
            }
        }
        $urlSiteEdit                          = Controller::buildLink(
            CTCNC_PAGE_SITE,
            array(
                'action'  => CTCNC_ACT_SITE_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlSitePopup                         = Controller::buildLink(
            CTCNC_PAGE_SITE,
            array(
                'action'  => CTCNC_ACT_SITE_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $secondSiteLocationPathValidationText = null;
        if ($dsCustomerItem->getValue(DBEJCustomerItem::secondsiteLocationPath)) {
            /*
            validate Offsite backup location path
            */
            if (!file_exists($dsCustomerItem->getValue(DBEJCustomerItem::secondsiteLocationPath))) {
                $secondSiteLocationPathValidationText = 'Location is not available';
            }
        }
        $secondSiteReplicationPathValidationText = null;
        if ($dsCustomerItem->getValue(DBECustomerItem::secondSiteReplicationPath)) {
            if (!file_exists($dsCustomerItem->getValue(DBECustomerItem::secondSiteReplicationPath))) {
                $secondSiteReplicationPathValidationText = 'Location is not available';
            }
        }
        /* Default to enable 2ndSite fields */
        $secondsiteReadonly                   = null;
        $secondsiteDisabled                   = null;
        $secondsiteReplicationReadonly        = null;
        $secondsiteReplicationDisabled        = null;
        $secondsiteLocalExcludeFlagShow       = 0;
        $secondSiteReplicationExcludeFlagShow = 0;
        if ($buCustomerItem->serverIsUnderLocalSecondsiteContract(
            $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
        )) {
            $secondsiteLocalExcludeFlagShow       = 1;
            $secondSiteReplicationExcludeFlagShow = 1;
            /*
            if secondsiteLocalExcludeFlag is set to exclude this server from checks then disable all secondsite input fields
            */
            if ($dsCustomerItem->getValue(DBEJCustomerItem::secondsiteLocalExcludeFlag) == 'Y') {
                $secondsiteReadonly = CTCNC_HTML_READONLY;
                $secondsiteDisabled = CTCNC_HTML_DISABLED;
            }
            if ($dsCustomerItem->getValue(DBECustomerItem::secondSiteReplicationExcludeFlag) == 'Y') {
                $secondsiteReplicationReadonly = CTCNC_HTML_READONLY;
                $secondsiteReplicationDisabled = CTCNC_HTML_DISABLED;
            }
        }
        $buUser                       = new BUUser($this);
        $offsiteBackupSuspendedByText = null;
        if ($dsCustomerItem->getValue(DBEJCustomerItem::secondsiteSuspendedDate) && $dsCustomerItem->getValue(
                DBEJCustomerItem::secondsiteSuspendedByUserID
            )) {
            $dsUser = new DataSet($this);
            $buUser->getUserByID(
                $dsCustomerItem->getValue(DBEJCustomerItem::secondsiteSuspendedByUserID),
                $dsUser
            );
            $offsiteBackupSuspendedByText = $dsUser->getValue(DBEUser::name) . ' on ' . Controller::dateYMDtoDMY(
                    $dsCustomerItem->getValue(DBEJCustomerItem::secondsiteSuspendedDate)
                );
        }
        $offsiteReplicationSuspendedByText = null;
        if ($dsCustomerItem->getValue(DBEJCustomerItem::offsiteReplicationSuspendedDate) && $dsCustomerItem->getValue(
                DBEJCustomerItem::offsiteReplicationSuspendedByUserID
            )) {
            $dsUser = new DataSet($this);
            $buUser->getUserByID(
                $dsCustomerItem->getValue(DBEJCustomerItem::offsiteReplicationSuspendedByUserID),
                $dsUser
            );
            $offsiteReplicationSuspendedByText = $dsUser->getValue(DBEUser::name) . ' on ' . Controller::dateYMDtoDMY(
                    $dsCustomerItem->getValue(DBEJCustomerItem::offsiteReplicationSuspendedDate)
                );
        }
        $imageDelayByText = null;
        if ($dsCustomerItem->getValue(DBEJCustomerItem::secondsiteImageDelayDays) > '0' && $dsCustomerItem->getValue(
                DBEJCustomerItem::secondsiteImageDelayUserID
            )) {
            $buUser->getUserByID(
                $dsCustomerItem->getValue(DBEJCustomerItem::secondsiteImageDelayUserID),
                $dsUser
            );
            $imageDelayByText = $dsUser->getValue(DBEUser::name) . ' on ' . Controller::dateYMDtoDMY(
                    $dsCustomerItem->getValue(DBEJCustomerItem::secondsiteImageDelayDate)
                );
        }
        $pdoDB     = DBConnect::instance()->getDB();
        $statement = $pdoDB->prepare(
            'select checkedAt,sizeInGb from OBRSServerStorage where serverCustomerItemId = :id order by checkedAt desc limit 1'
        );
        $statement->execute(["id" => $dsCustomerItem->getValue(DBECustomerItem::customerItemID)]);
        $serverUsedSpaceData = $statement->fetch();
        $serverUsedSpaceText = "";
        if ($serverUsedSpaceData) {
            $date                = DateTimeImmutable::createFromFormat(
                DATE_MYSQL_DATETIME,
                $serverUsedSpaceData['checkedAt']
            );
            $serverUsedSpaceText = "{$serverUsedSpaceData['sizeInGb']} GB last checked on {$date->format(DATE_CNC_DATE_TIME_FORMAT)}";
        }
        $this->template->set_var(
            array(
                'urlSubmit'                                             => $urlSubmit,
                'urlContractPopup'                                      => $urlContractPopup,
                'urlItemPopup'                                          => $urlItemPopup,
                'urlCustomerPopup'                                      => $urlCustomerPopup,
                'urlItemEdit'                                           => $urlItemEdit,
                'customerItemID'                                        => $dsCustomerItem->getValue(
                    DBEJCustomerItem::customerItemID
                ),
                'siteNo'                                                => $dsCustomerItem->getValue(
                    DBEJCustomerItem::siteNo
                ),
                'siteDesc'                                              => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::siteDescription)
                ),
                'serverName'                                            => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::serverName)
                ),
                'urlSiteEdit'                                           => $urlSiteEdit,
                'urlSitePopup'                                          => $urlSitePopup,
                'customerID'                                            => $dsCustomerItem->getValue(
                    DBEJCustomerItem::customerID
                ),
                'customerName'                                          => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::customerName)
                ),
                'itemID'                                                => $dsCustomerItem->getValue(
                    DBEJCustomerItem::itemID
                ),
                'itemDescription'                                       => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::itemDescription)
                ),
                'partNo'                                                => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::partNo)
                ),
                'serialNo'                                              => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::serialNo)
                ),
                'ordheadID'                                             => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::ordheadID)
                ),
                'ordheadIDMessage'                                      => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::ordheadID)
                ),
                'serverUsedSpace'                                       => $serverUsedSpaceText,
                'porheadID'                                             => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::porheadID)
                ),
                'porheadIDMessage'                                      => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::porheadID)
                ),
                'curUnitSale'                                           => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::curUnitSale)
                ),
                'curUnitSaleMessage'                                    => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::curUnitSale)
                ),
                'curUnitCost'                                           => $dsCustomerItem->getValue(
                    DBEJCustomerItem::curUnitCost
                ),
                'curUnitCostMessage'                                    => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::curUnitCost)
                ),
                'sOrderDate'                                            => $dsCustomerItem->getValue(
                    DBEJCustomerItem::sOrderDate
                ),
                'sOrderDateMessage'                                     => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::sOrderDate)
                ),
                'expiryDate'                                            => $dsCustomerItem->getValue(
                    DBEJCustomerItem::expiryDate
                ),
                'expiryDateMessage'                                     => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::expiryDate)
                ),
                'curGSCBalance'                                         => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::curGSCBalance)
                ),
                'curGSCBalanceMessage'                                  => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::curGSCBalance)
                ),
                'customerItemNotes'                                     => Controller::htmlTextArea(
                    $dsCustomerItem->getValue(DBEJCustomerItem::customerItemNotes)
                ),
                'internalNotes'                                         => Controller::htmlTextArea(
                    $dsCustomerItem->getValue(DBEJCustomerItem::internalNotes)
                ),
                'slaResponseHours'                                      => $dsCustomerItem->getValue(
                    DBEJCustomerItem::slaResponseHours
                ),
                'despatchDate'                                          => $dsCustomerItem->getValue(
                    DBEJCustomerItem::despatchDate
                ),
                'despatchDateMessage'                                   => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::despatchDate)
                ),
                'secondsiteLocationPath'                                => $dsCustomerItem->getValue(
                    DBEJCustomerItem::secondsiteLocationPath
                ),
                'secondsiteLocationPathMessage'                         => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::secondsiteLocationPath)
                ),
                'secondSiteReplicationPath'                             => $dsCustomerItem->getValue(
                    DBECustomerItem::secondSiteReplicationPath
                ),
                'secondSiteReplicationPathMessage'                      => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::secondSiteReplicationPath)
                ),
                'secondsiteValidationSuspendUntilDate'                  => $dsCustomerItem->getValue(
                    DBEJCustomerItem::secondsiteValidationSuspendUntilDate
                ),
                'secondsiteValidationSuspendUntilDateMessage'           => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::secondsiteValidationSuspendUntilDate)
                ),
                'offsiteReplicationValidationSuspendedUntilDate'        => $dsCustomerItem->getValue(
                    DBEJCustomerItem::offsiteReplicationValidationSuspendedUntilDate
                ),
                'offsiteReplicationValidationSuspendedUntilDateMessage' => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::offsiteReplicationValidationSuspendedUntilDate)
                ),
                'offsiteBackupSuspendedByText'                          => $offsiteBackupSuspendedByText,
                'offsiteReplicationSuspendedByText'                     => $offsiteReplicationSuspendedByText,
                'imageDelayByText'                                      => $imageDelayByText,
                'secondsiteImageDelayDays'                              => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::secondsiteImageDelayDays)
                ),
                'secondsiteImageDelayDaysMessage'                       => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::secondsiteImageDelayDays)
                ),
                'offsiteBackupDelayDisable'                             => ($this->dbeUser->getValue(
                        DBEUser::offsiteBackupAdditionalPermissionsFlag
                    ) == 'Y') ? 'true' : 'false',
                'bypassCWAAgentCheckChecked'                            => $dsCustomerItem->getValue(
                    DBECustomerItem::bypassCWAAgentCheck
                ) ? "checked" : '',
                'secondSiteLocationPathValidationText'                  => $secondSiteLocationPathValidationText,
                'secondSiteReplicationPathValidationText'               => $secondSiteReplicationPathValidationText,
                'secondsiteLocalExcludeFlagShow'                        => $secondsiteLocalExcludeFlagShow,
                'secondsiteLocalExcludeFlagChecked'                     => Controller::htmlChecked(
                    $dsCustomerItem->getValue(DBEJCustomerItem::secondsiteLocalExcludeFlag)
                ),
                'secondsiteDisabled'                                    => $secondsiteDisabled,
                'secondsiteReadonly'                                    => $secondsiteReadonly,
                "secondSiteReplicationExcludeFlagChecked"               => Controller::htmlChecked(
                    $dsCustomerItem->getValue(DBECustomerItem::secondSiteReplicationExcludeFlag)
                ),
                "secondSiteReplicationExcludeFlagShow"                  => $secondSiteReplicationExcludeFlagShow,
                "secondsiteReplicationReadonly"                         => $secondsiteReplicationReadonly,
                "secondsiteReplicationDisabled"                         => $secondsiteReplicationDisabled,
            )
        );
        $this->template->set_block(
            'CustomerItemDisplay',
            'secondsiteImageDelayDaysBlock',
            'secondsiteImageDelayDays'
        );
        $this->parseSecondsiteImageDelayDaysSelector(
            $dsCustomerItem->getValue(DBEJCustomerItem::secondsiteImageDelayDays)
        );
        $this->parseWarrantySelector($dsCustomerItem->getValue(DBEJCustomerItem::warrantyID));
        $this->parseContractSelector(
            $this->dsCustomerItem->getValue(DBEJCustomerItem::customerID),
            'CustomerItemDisplay',
            $this->contractIDs
        );
        $this->template->set_block(
            'CustomerItemDisplay',
            'renewalStatusBlock',
            'renewalStatus'
        );
        $this->parseRenewalSelector($dsCustomerItem->getValue(DBEJCustomerItem::renewalStatus));
        /*
        Offsite Backup Images
        */
        if ($this->getAction() != CTCUSTOMERITEM_ACT_ADD) {

            $this->template->set_block(
                'CustomerItemDisplay',
                'secondsiteImageBlock',
                'secondsiteImages'
            );
            $addSecondsiteImageURL = Controller::buildLink(
                'OffsiteBackupStatus.php',
                array(
                    'action'         => 'add',
                    'customerItemID' => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID)
                )
            );
            $this->template->set_var(
                array(
                    'addSecondsiteImageText' => 'Add Offsite Backup Image',
                    'addSecondsiteImageUrl'  => $addSecondsiteImageURL
                )
            );
            $BUSecondsite      = new BUSecondsite($this);
            $dsSecondsiteImage = new DataSet($this);
            $BUSecondsite->getSecondsiteImagesByCustomerItemID(
                $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID),
                $dsSecondsiteImage
            );
            while ($dsSecondsiteImage->fetchNext()) {

                $deleteSecondsiteImageLink = Controller::buildLink(
                    'OffsiteBackupStatus.php',
                    array(
                        'action'            => 'delete',
                        'secondsiteImageID' => $dsSecondsiteImage->getValue(DBESecondsiteImage::secondsiteImageID)
                    )
                );
                $deleteSecondsiteImageText = 'delete';
                $editSecondsiteImageLink   = Controller::buildLink(
                    'OffsiteBackupStatus.php',
                    array(
                        'action'            => 'edit',
                        'secondsiteImageID' => $dsSecondsiteImage->getValue(DBESecondsiteImage::secondsiteImageID)
                    )
                );
                $imageTime                 = null;
                $imageAgeDays              = null;
                if ($dsSecondsiteImage->getValue(DBESecondsiteImage::status) && $dsSecondsiteImage->getValue(
                        DBESecondsiteImage::imageTime
                    ) > 0) {

                    $imageTime    = strftime(
                        "%d/%m/%Y %H:%M:%S",
                        strtotime($dsSecondsiteImage->getValue(DBESecondsiteImage::imageTime))
                    );
                    $imageAgeDays = number_format(
                        (time() - strtotime($dsSecondsiteImage->getValue(DBESecondsiteImage::imageTime))) / 86400,
                        0
                    );
                }
                $replicationImageAgeDays = null;
                if ($dsSecondsiteImage->getValue(DBESecondsiteImage::replicationStatus) && $dsSecondsiteImage->getValue(
                        DBESecondsiteImage::replicationImageTime
                    ) > 0) {
                    $replicationImageAgeDays = number_format(
                        (time() - strtotime(
                                $dsSecondsiteImage->getValue(DBESecondsiteImage::replicationImageTime)
                            )) / 86400,
                        0
                    );
                }
                $this->template->set_var(
                    array(
                        'secondsiteImageID'         => $dsSecondsiteImage->getValue(
                            DBESecondsiteImage::secondsiteImageID
                        ),
                        'imageName'                 => $dsSecondsiteImage->getValue(DBESecondsiteImage::imageName),
                        'status'                    => $dsSecondsiteImage->getValue(DBESecondsiteImage::status),
                        'imageTime'                 => $imageTime,
                        'imageAgeDays'              => $imageAgeDays,
                        'editSecondsiteImageLink'   => $editSecondsiteImageLink,
                        'deleteSecondsiteImageLink' => $deleteSecondsiteImageLink,
                        'deleteSecondsiteImageText' => $deleteSecondsiteImageText,
                        'replicationImageAgeDays'   => $replicationImageAgeDays
                    )
                );
                $this->template->parse(
                    'secondsiteImages',
                    'secondsiteImageBlock',
                    true
                );

            }
        }
        /*
        end Offsite Backup Images
        */
        /*
         Documents section
        */
        if ($this->getAction() != CTCUSTOMERITEM_ACT_ADD) {
            $this->template->set_block(
                'CustomerItemDisplay',
                'documentBlock',
                'documents'
            );
            $urlUploadFile = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTCUSTOMERITEM_ACT_UPLOAD_DOCUMENT,
                    'customerItemID' => $customerItemID
                )
            );
            $txtUploadFile = '[upload]';
            $this->template->set_var(
                array(
                    'uploadDescription' => $this->getParam('uploadDescription'),
                    'userfile'          => isset($_FILES['userfile']) ? $_FILES['userfile']['name'] : null,
                    'txtUploadFile'     => $txtUploadFile,
                    'urlUploadFile'     => $urlUploadFile
                )
            );
            $dbeJCustomerItemDocument = new DBEJCustomerItemDocument($this);
            $dbeJCustomerItemDocument->setValue(DBEJCustomerItem::customerItemID, $customerItemID);
            $dbeJCustomerItemDocument->getRowsByColumn(DBEJCustomerItem::customerItemID);
            while ($dbeJCustomerItemDocument->fetchNext()) {
                $urlViewFile   = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'                 => CTCUSTOMERITEM_ACT_VIEW_DOCUMENT,
                        'customerItemDocumentID' => $dbeJCustomerItemDocument->getValue(
                            DBEJCustomerItemDocument::customerItemDocumentID
                        )
                    )
                );
                $urlDeleteFile = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'                 => CTCUSTOMERITEM_ACT_DELETE_DOCUMENT,
                        'customerItemDocumentID' => $dbeJCustomerItemDocument->getValue(
                            DBEJCustomerItemDocument::customerItemDocumentID
                        )
                    )
                );
                $this->template->set_var(
                    array(
                        'description'    => $dbeJCustomerItemDocument->getValue(DBEJCustomerItemDocument::description),
                        'filename'       => $dbeJCustomerItemDocument->getValue(DBEJCustomerItemDocument::filename),
                        'createUserName' => $dbeJCustomerItemDocument->getValue(
                            DBEJCustomerItemDocument::createUserName
                        ),
                        'createDate'     => $dbeJCustomerItemDocument->getValue(DBEJCustomerItemDocument::createDate),
                        'urlViewFile'    => $urlViewFile,
                        'urlDeleteFile'  => $urlDeleteFile,
                        'txtDeleteFile'  => '[delete]'
                    )
                );
                $this->template->parse(
                    'documents',
                    'documentBlock',
                    true
                );
            }
        }// if ($this->getAction() != CTACTIVITY_ACT_CREATE_CALL)
        /*
        End documents section
        */
        $this->template->parse(
            'CONTENTS',
            'CustomerItemDisplay',
            true
        );
        $this->parsePage();
    }

    /**
     * Display the second site delay days drop-down selector
     *
     * @access private
     * @param $delayDays
     */
    function parseSecondsiteImageDelayDaysSelector($delayDays)
    {
        foreach ($this->secondsiteImageDelayDays as $key => $value) {

            $delayDaysSelected = ($delayDays == $key) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'delayDaysSelected'    => $delayDaysSelected,
                    'delayDaysValue'       => $key,
                    'delayDaysDescription' => $value
                )
            );
            $this->template->parse(
                'secondsiteImageDelayDays',
                'secondsiteImageDelayDaysBlock',
                true
            );
        }
    } // end display()

    function parseWarrantySelector($warrantyID)
    {
        // Manufacturer selector
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows();
        $this->template->set_block(
            'CustomerItemDisplay',
            'warrantyBlock',
            'warranties'
        );
        while ($dbeWarranty->fetchNext()) {
            $this->template->set_var(
                array(
                    'warrantyDescription' => $dbeWarranty->getValue(DBEWarranty::description),
                    'warrantyID'          => $dbeWarranty->getValue(DBEWarranty::warrantyID),
                    'warrantySelected'    => ($warrantyID == $dbeWarranty->getValue(
                            DBEWarranty::warrantyID
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'warranties',
                'warrantyBlock',
                true
            );
        } // while ($dbeWarranty->fetchNext()
    }// end function addCustomerItem()

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function displayRenewalContract()
    {
        $buCustomerItem = &$this->buCustomerItem;
        $buCustomerItem->getCustomerItemByID(
            $this->getParam('customerItemID'),
            $dsCustomerItem
        );
        $url = $this->getContractUrl($dsCustomerItem);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Edit/Add Activity
     * @access private
     * @throws Exception
     */
    function add()
    {
        $this->setMethodName('add');
        $dsCustomerItem = &$this->dsCustomerItem; // ref to class var
        if (!$this->getFormError()) {
            $this->buCustomerItem->initialiseNewCustomerItem($dsCustomerItem);
        } else {                                                                        // form validation error
            $dsCustomerItem->initialise();
            $dsCustomerItem->fetchNext();
        }
        $urlSubmit        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCUSTOMERITEM_ACT_INSERT
            )
        );
        $urlItemPopup     = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_DISP_ITEM_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlItemEdit      = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_ITEM_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlSiteEdit      = Controller::buildLink(
            CTCNC_PAGE_SITE,
            array(
                'action'  => CTCNC_ACT_SITE_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlSitePopup     = Controller::buildLink(
            CTCNC_PAGE_SITE,
            array(
                'action'  => CTCNC_ACT_SITE_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->setPageTitle('Add Customer Item');
        $this->setTemplateFiles(
            array('CustomerItemAdd' => 'CustomerItemAdd.inc')
        );
        $this->loadReactScript('ItemListTypeAheadRenderer.js');
        $this->template->set_var(
            array(
                'urlSubmit'           => $urlSubmit,
                'urlItemPopup'        => $urlItemPopup,
                'urlCustomerPopup'    => $urlCustomerPopup,
                'urlItemEdit'         => $urlItemEdit,
                'customerItemID'      => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID),
                'siteNo'              => $dsCustomerItem->getValue(DBEJCustomerItem::siteNo),
                'siteDesc'            => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::siteDescription)
                ),
                'urlSiteEdit'         => $urlSiteEdit,
                'urlSitePopup'        => $urlSitePopup,
                'customerID'          => $dsCustomerItem->getValue(DBEJCustomerItem::customerID),
                'customerName'        => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::customerName)
                ),
                'itemID'              => $dsCustomerItem->getValue(DBEJCustomerItem::itemID),
                'itemDescription'     => Controller::htmlDisplayText(
                    $dsCustomerItem->getValue(DBEJCustomerItem::itemDescription)
                ),
                'descriptionMessage'  => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::itemID)
                ),
                'customerNameMessage' => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::customerID)
                ),
                'siteDescMessage'     => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::siteNo)
                ),
                'serialNoMessage'     => Controller::htmlDisplayText(
                    $dsCustomerItem->getMessage(DBECustomerItem::serialNo)
                ),
            )
        );
        $this->template->parse(
            'CONTENTS',
            'CustomerItemAdd',
            true
        );
        $this->parsePage();
    }

    /**
     * Display the renewal status drop-down selector
     *
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->buCustomerItem->initialiseNewCustomerItem($this->dsCustomerItem);
        /*
        contractID array is the contracts
        */
        $this->contractIDs = $this->getParam('contractID'); /* ?? */
        if (!$this->dsCustomerItem->populateFromArray($this->getParam('customerItem'))) {
            $this->setFormErrorOn();
            if ($this->getAction() == CTCUSTOMERITEM_ACT_INSERT) {
                $this->add();
            } else {
                $this->setAction(CTCUSTOMERITEM_ACT_EDIT);
            }
            $this->setParam('customerItemID', $this->dsCustomerItem->getValue(DBECustomerItem::customerItemID));
            $this->display();
            exit;
        }
        $this->buCustomerItem->update(
            $this->dsCustomerItem,
            $this->contractIDs
        );
        $this->dsCustomerItem->initialise();
        // this forces update of itemID back through Javascript to parent HTML window
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => CTCUSTOMERITEM_ACT_DISPLAY,
                'customerItemID' => $this->dsCustomerItem->getPKValue()
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if ($this->buCustomerItem->canDelete()) {
            $this->buCustomerItem->deleteCustomerItem($this->getParam('customerItemID'));
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                []
            );
            header('Location: ' . $urlNext);
        } else {
            throw new Exception('Can not delete customer item, dependencies exist');
        }
    }

    /**
     * Display the popup selector form
     * @throws Exception
     */
    function displayItemSelectPopup()
    {
        $this->setMethodName('displayItemSelectPopup');
        // this may be required in a number of situations
        $dsSearch = new DataSet($this);
        $this->buCustomerItem->initialiseSearchForm($dsSearch);
        $dsSearch->setValue(
            BUCustomerItem::searchFormItemText,
            $this->getParam('itemDescription')
        );
        $dsSearch->setValue(
            BUCustomerItem::searchFormCustomerID,
            $this->getParam('customerID')
        );
        $dsCustomerItem = new DataSet($this);
        $this->buCustomerItem->search(
            $dsSearch,
            $dsCustomerItem
        );
        if ($dsCustomerItem->rowCount() == 0) {
            $dsSearch->setValue(
                BUCustomerItem::searchFormItemText,
                null
            );
            $dsSearch->setValue(
                BUCustomerItem::searchFormSerialNo,
                $this->getParam('itemDescription')
            );
        }
        $this->buCustomerItem->search(
            $dsSearch,
            $dsCustomerItem
        );
        $this->template->set_var(
            array(
                'parentIDField'         => $_SESSION['parentIDField'],
                'parentWarrantyIDField' => $_SESSION['parentWarrantyIDField'],
                'parentDescField'       => $_SESSION['parentDescField']
            )
        );
        if ($dsCustomerItem->rowCount() == 1) {
            $this->setTemplateFiles(
                'CustomerItemSelect',
                'CustomerItemSelectOne.inc'
            );
            // This template runs a javascript function NOT inside HTML and so must use stripslashes()
            $dsCustomerItem->fetchNext();
            $this->template->set_var(
                array(
                    'itemDescription'  => addslashes($dsCustomerItem->getValue(DBEJCustomerItem::itemDescription)),
                    // for javascript
                    'warrantyID'       => $dsCustomerItem->getValue(DBEJCustomerItem::warrantyID),
                    'customerItemID'   => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID),
                    'allowDirectDebit' => $dsCustomerItem->getValue(
                        DBEItem::allowDirectDebit
                    ) === 'Y' ? 'true' : 'false'
                )
            );
        } else {
            if ($dsCustomerItem->rowCount() == 0) {
                $this->template->set_var(
                    array(
                        'itemDescription' => $this->getParam('itemDescription'),
                    )
                );
                $this->setTemplateFiles(
                    'CustomerItemSelect',
                    'CustomerItemSelectNone.inc'
                );
            }
            if ($dsCustomerItem->rowCount() > 1) {
                $this->setTemplateFiles(
                    'CustomerItemSelect',
                    'CustomerItemSelectPopup.inc'
                );
            }
            // Parameters
            $this->setPageTitle('Customer Item Selection');
            if ($dsCustomerItem->rowCount() > 0) {
                $this->template->set_block(
                    'CustomerItemSelect',
                    'itemBlock',
                    'items'
                );
                while ($dsCustomerItem->fetchNext()) {
                    $this->template->set_var(
                        array(
                            'itemDescription'   => Controller::htmlDisplayText(
                                $dsCustomerItem->getValue(DBEJCustomerItem::itemDescription)
                            ),
                            'serialNo'          => Controller::htmlDisplayText(
                                $dsCustomerItem->getValue(DBEJCustomerItem::serialNo)
                            ),
                            'purchaseDate'      => Controller::dateYMDtoDMY(
                                $dsCustomerItem->getValue(DBEJCustomerItem::sOrderDate)
                            ),
                            'submitDescription' => Controller::htmlInputText(
                                addslashes($dsCustomerItem->getValue(DBEJCustomerItem::itemDescription))
                            ),
                            'customerItemID'    => $dsCustomerItem->getValue(DBEJCustomerItem::customerItemID),
                            'warrantyID'        => $dsCustomerItem->getValue(DBEJCustomerItem::warrantyID),
                            'allowDirectDebit'  => $dsCustomerItem->getValue(
                                DBEItem::allowDirectDebit
                            ) === 'Y' ? 'true' : 'false'
                        )
                    );
                    $this->template->parse(
                        'items',
                        'itemBlock',
                        true
                    );
                }
            }
        } // not ($dsItem->rowCount()==1)
        $this->template->parse(
            'CONTENTS',
            'CustomerItemSelect',
            true
        );
        $this->parsePage();
    }

    /**
     * Upload new document from local disk
     * @access private
     * @throws Exception
     */
    function uploadDocument()
    {
        // validate
        if (!$this->getParam('uploadDescription')) {
            $this->setFormErrorMessage('Please enter a description');
        }
        if (!$_FILES['userfile']['name']) {
            $this->setFormErrorMessage('Please enter a file path');
        }
        if (!is_uploaded_file($_FILES['userfile']['tmp_name'])) {                    // Possible hack?
            $this->setFormErrorMessage('Document not loaded - is it bigger that 6 MBytes?');
        }
        if ($this->formError) {
            $this->buCustomerItem->getCustomerItemByID(
                $this->getParam('customerItemID'),
                $this->dsCustomerItem
            );
            $this->display();
            exit;
        }
        $this->buCustomerItem->uploadDocumentFile(
            $this->getParam('customerItemID'),
            $this->getParam('uploadDescription'),
            $_FILES['userfile']
        );
        $this->redirectToDisplay($this->getParam('customerItemID'));
    }

    /**
     * Redirect to display
     * @access private
     * @param $customerItemID
     * @throws Exception
     */
    function redirectToDisplay($customerItemID)
    {
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'customerItemID' => $customerItemID,
                'action'         => CTCUSTOMERITEM_ACT_DISPLAY
            )
        );
        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Generate page required to embed file
     * this is done because simply calling documentView() with PDF files causes
     * IE to call documentView a second time! this is a known problem. The workaround
     * is to produce a page with and EMBED tag that makes a call back to the server.
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function viewDocument()
    {
        // Validation and setting of variables
        $this->setMethodName('viewDocument');
        $dbeCustomerItemDocument = new DBECustomerItemDocument($this);
        if (!$dbeCustomerItemDocument->getRow($this->getParam('customerItemDocumentID'))) {
            $this->displayFatalError('Activity file not found.');
        }
        if ($dbeCustomerItemDocument->getValue(DBEJCustomerItemDocument::fileMIMEType) != 'application/pdf') {
            return $this->getFile();
        }
        return null;
    }

    /**
     * echo given document to client
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function getFile()
    {
        // Validation and setting of variables
        $this->setMethodName('getFile');
        $dbeCustomerItemDocument = new DBECustomerItemDocument($this);
        if (!$dbeCustomerItemDocument->getRow($this->getParam('customerItemDocumentID'))) {
            $this->displayFatalError('File not found.');
        }
        header('Pragma: ');
        header('Cache-Control: ');
        header('Content-type: ' . $dbeCustomerItemDocument->getValue(DBEJCustomerItemDocument::fileMIMEType));
        header('Content-Length: ' . $dbeCustomerItemDocument->getValue(DBEJCustomerItemDocument::fileLength));
        header(
            'Content-Disposition: inline; filename="' . $dbeCustomerItemDocument->getValue(
                DBEJCustomerItemDocument::filename
            ) . '"'
        );
        print $dbeCustomerItemDocument->getValue(DBEJCustomerItemDocument::file);
        exit;
    }

    /**
     * Generate page required to embed file
     * this is done because simply calling documentView() with PDF files causes
     * IE to call documentView a second time! this is a known problem. The workaround
     * is to produce a page with and EMBED tag that makes a call back to the server.
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function deleteDocument()
    {
        // Validation and setting of variables
        $this->setMethodName('deleteDocument');
        $dbeCustomerItemDocument = new DBECustomerItemDocument($this);
        if (!$dbeCustomerItemDocument->getRow($this->getParam('customerItemDocumentID'))) {
            $this->displayFatalError('Document not found.');
        }
        $customerItemID = $dbeCustomerItemDocument->getValue(DBEJCustomerItemDocument::customerItemID);
        $dbeCustomerItemDocument->deleteRow();
        $this->redirectToDisplay($customerItemID);
    }

    /**
     * Print Support Contract
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function printContract()
    {

        // Validation and setting of variables
        $this->setMethodName('printContract');
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract     = new DataSet($this);
        $buCustomerItem->getCustomerItemByID(
            $this->getParam('customerItemID'),
            $dsContract
        );
        $buCustomerItem->getCustomerItemsByContractID(
            $this->getParam('customerItemID'),
            $dsCustomerItem
        );
        $buSite     = new BUSite($this);
        $buActivity = new BUActivity($this);
        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID(
            $dsContract->getValue(DBEJCustomerItem::customerID),
            $dsCustomer
        );
        $buSite->getSiteByID(
            $dsContract->getValue(DBEJCustomerItem::customerID),
            $dsContract->getValue(DBEJCustomerItem::siteNo),
            $dsSite
        );
        $customerHasServiceDeskContract = $buCustomerItem->customerHasServiceDeskContract(
            $dsContract->getValue(DBEJCustomerItem::customerID)
        );
        $buPDFSupportContract           = new BUPDFSupportContract(
            $this, $dsContract, $dsCustomerItem, $dsSite, $dsCustomer, $buActivity, $customerHasServiceDeskContract
        );
        $pdfFile                        = $buPDFSupportContract->generateFile();
        if ($pdfFile != FALSE) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=contract.pdf;');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($pdfFile));
            readfile($pdfFile);
            unlink($pdfFile);
            exit();
        }
    }

    /**
     * Display section of second site images
     *
     * @param mixed $customerItemID
     */
    function secondSiteImages($customerItemID)
    {


    }

}
