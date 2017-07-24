<?php
/**
 * Customer Item controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerItemDocument.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define('CTCUSTOMERITEM_MSG_CUSTOMER_ITEM_NOT_FND', 'Customer Item not found');
define('CTCUSTOMERITEM_MSG_customerItemID_NOT_PASSED', 'customerItemID not passed');
// Actions
define('CTCUSTOMERITEM_ACT_DISP_SEARCH', 'dispSearch');
define('CTCUSTOMERITEM_ACT_DISPLAY', 'displayCI');
define('CTCUSTOMERITEM_ACT_DELETE', 'deleteCI');
define('CTCUSTOMERITEM_ACT_UPDATE', 'updateCI');
define('CTCUSTOMERITEM_ACT_INSERT', 'insertCI');
define('CTCUSTOMERITEM_ACT_ADD', 'addCI');
define('CTCUSTOMERITEM_ACT_EDIT', 'editCI');
define('CTCUSTOMERITEM_ACT_UPLOAD_DOCUMENT', 'uploadDocument');
define('CTCUSTOMERITEM_ACT_VIEW_DOCUMENT', 'viewDocument');
define('CTCUSTOMERITEM_ACT_GET_DOCUMENT', 'getDocument'); // get stream
define('CTCUSTOMERITEM_ACT_DELETE_DOCUMENT', 'deleteDocument');
define('CTCUSTOMERITEM_ACT_PRINT_CONTRACT', 'printContract');

class CTCustomerItem extends CTCNC
{
    var $dsCustomerItem = '';
    var $buCustomerItem = '';

    var $contractIDs;

    var $dsSearchForm = '';
    var $renewalStatusArray = array(
        "D" => "Declined",
        "R" => "Renewed"
    );
    var $secondsiteImageDelayDays = array(
        "0" => "0 - Check for image time-stamped last night at 7pm",
        "1" => "1",
        "2" => "2",
        "3" => "3"
    );

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buCustomerItem = new BUCustomerItem($this);
        $this->dsSearchForm = new DSForm($this);
        $this->dsCustomerItem = new DSForm($this);
        $this->dsCustomerItem->addColumn('siteDesc', DA_ALLOW_NULL, DA_STRING);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_TECHNICAL);
        $this->setParentFormFields();
        switch ($_REQUEST['action']) {
            case CTCNC_ACT_SEARCH:

                if ($_REQUEST['Search'] == 'Add Contract') {
                    $this->applyContractUpdates('add');
                } elseif ($_REQUEST['Search'] == 'Remove Contract') {
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

            case CTCUSTOMERITEM_ACT_EDIT:
                $this->edit();
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
            case CTCUSTOMERITEM_ACT_GET_DOCUMENT:
                $this->getDocument();
                break;
            case CTCUSTOMERITEM_ACT_DELETE_DOCUMENT:
                $this->deleteDocument();
                break;
            case CTCUSTOMERITEM_ACT_PRINT_CONTRACT:
                $this->printContract();
                break;
            case 'displayContractItemList':
                $this->displayContractItemList();
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
        if (isset($_REQUEST['parentIDField'])) {
            $_SESSION['parentIDField'] = $_REQUEST['parentIDField'];
        }
        if (isset($_REQUEST['parentWarrantyIDField'])) {
            $_SESSION['parentWarrantyIDField'] = $_REQUEST['parentWarrantyIDField'];
        }
        if (isset($_REQUEST['parentDescField'])) {
            $_SESSION['parentDescField'] = $_REQUEST['parentDescField'];
        }
    }

    /**
     * Run search based upon passed parameters
     * Display search form with results
     * @access private
     */
    function search()
    {
        $this->setMethodName('search');
        $this->buCustomerItem->initialiseSearchForm($this->dsSearchForm);
        if (!$this->dsSearchForm->populateFromArray($_REQUEST['customerItem'])) {
            $this->setFormErrorOn();
            $this->displaySearchForm(); //redisplay with errors
            exit;
        }

        if ($_REQUEST['CSV']) {

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
        if ($_REQUEST['CSV'] != '') {
            $this->generateCSV();
        } else {
            $this->displaySearchForm();
        }
    }

    /**
     * Display the results of order search
     * @access private
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $this->setTemplateFiles(
            array(
                'CustomerItemSearch' => 'CustomerItemSearch.inc',
                'CustomerItemSearchContractSelector' => 'CustomerItemSearchContractSelector.inc'
            )
        );

// Parameters
        $this->setPageTitle("Customer Items");
        $urlSubmit = $this->buildLink($_SERVER['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));
        $urlCreate = $this->buildLink($_SERVER['PHP_SELF'], array('action' => CTCUSTOMERITEM_ACT_ADD));
        $customerPopupURL =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

// search parameter section
        if ($dsSearchForm->rowCount() == 0) {
            $this->buCustomerItem->initialiseSearchForm($dsSearchForm);
        }
        if ($dsSearchForm->getValue('customerID') != '') {
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue('name');
        }
        $this->template->set_var(
            array(
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerString' => $customerString,
                'customerItemID' => Controller::htmlDisplayText($dsSearchForm->getValue('customerItemID')),
                'ordheadID' => Controller::htmlDisplayText($dsSearchForm->getValue('ordheadID')),
                'ordheadIDMessage' => Controller::htmlDisplayText($dsSearchForm->getMessage('ordheadID')),
                'serialNo' => Controller::htmlDisplayText($dsSearchForm->getValue('serialNo')),
                'itemText' => Controller::htmlDisplayText($dsSearchForm->getValue('itemText')),
                'contractText' => Controller::htmlDisplayText($dsSearchForm->getValue('contractText')),
                'urlCreate' => $urlCreateCustomerItem,
                'customerPopupURL' => $customerPopupURL,
                'urlSubmit' => $urlSubmit
            )
        );
        $this->template->set_block('CustomerItemSearch', 'renewalStatusBlock', 'renewalStatuss');
        $this->parseRenewalSelector($dsSearchForm->getValue('renewalStatus'));
        // Lines section
        if ($this->dsCustomerItem->rowCount() > 0) {
            $this->dsCustomerItem->initialise();
            $this->template->set_block('CustomerItemSearch', 'itemBlock', 'items');
            $customerNameCol = $this->dsCustomerItem->columnExists('customerName');
            $siteCol = $this->dsCustomerItem->columnExists('siteDescription');
            $customerNameCol = $this->dsCustomerItem->columnExists('customerName');
            $serialNoCol = $this->dsCustomerItem->columnExists('serialNo');
            $itemDescriptionCol = $this->dsCustomerItem->columnExists('itemDescription');
            $customerItemIDCol = $this->dsCustomerItem->columnExists('customerItemID');
            $contractDescriptionCol = $this->dsCustomerItem->columnExists('contractDescription');
            $serverNameCol = $this->dsCustomerItem->columnExists('serverName');


            if (
            $dsSearchForm->getValue('customerID')
            ) {

                $this->parseContractSelector(
                    $dsSearchForm->getValue('customerID'),
                    'CustomerItemSearchContractSelector',
                    false
                );

            }

            while ($this->dsCustomerItem->fetchNext()) {

                $customerItemID = $this->dsCustomerItem->getValue($customerItemIDCol);

                $urlItem = $this->getContractUrl($this->dsCustomerItem);

                if (
                $dsSearchForm->getValue('customerID')
                ) {
                    $checkBox =
                        '<input type="checkbox" id="salesOrder" name="customerItemIDs[' . $customerItemID . ']" value="' . $customerItemID . '" />';
                } else {
                    $checkBox = '';
                }

                $contracts = $this->buCustomerItem->getContractDescriptionsByCustomerItemId($customerItemID);

                $this->template->set_var(
                    array(
                        'listCustomerName' => Controller::htmlDisplayText($this->dsCustomerItem->getValue($customerNameCol)),
                        'listSiteName' => Controller::htmlDisplayText($this->dsCustomerItem->getValue($siteCol)),
                        'listItemDescription' => Controller::htmlDisplayText($this->dsCustomerItem->getValue($itemDescriptionCol)),
                        'urlItem' => $urlItem,
                        'listSerialNo' => Controller::htmlDisplayText($this->dsCustomerItem->getValue($serialNoCol)),
                        'listContractDescription' => $contracts,
                        'listServerName' => Controller::htmlDisplayText($this->dsCustomerItem->getValue($serverNameCol)),
                        'checkBox' => $checkBox
                    )
                );
                $this->template->parse('items', 'itemBlock', true);
            }
        }

        if (
        $dsSearchForm->getValue('customerID')
        ) {
            $this->template->parse(
                'customerItemSearchContractSelector',
                'CustomerItemSearchContractSelector',
                true
            );

        }
        $this->template->parse('CONTENTS', 'CustomerItemSearch', true);
        $this->parsePage();
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

            $contracts =
                $this->buCustomerItem->getContractDescriptionsByCustomerItemId(
                    $this->dsCustomerItem->getValue('customerItemID')
                );

            echo
                '"' . $this->getExcelValue($this->dsCustomerItem->getValue('customerName')) . '",' .
                '"' . $this->getExcelValue($this->dsCustomerItem->getValue('siteDescription')) . '",' .
                '"' . $this->getExcelValue($this->dsCustomerItem->getValue('itemDescription')) . '",' .
                '"' . $this->getExcelValue($this->dsCustomerItem->getValue('serialNo')) . '",' .
                '"' . $this->getExcelValue($contracts) . '",' .
                '"' . $this->getExcelValue($this->dsCustomerItem->getValue('serverName')) . '"' . "\n";
        }
        $this->pageClose();
        exit;
    }

    function getExcelValue($value)
    {

        $value = str_replace(',', '', $value);
        $value = str_replace("\r\n", " ", $value);      // remove carrage returns
        $value = str_replace("\"", "", $value);        // and double quotes

        return $value;
    }

    function getContractUrl($dsCustomerItem)
    {
        switch ($dsCustomerItem->getValue('renewalTypeID')) {
            case CONFIG_BROADBAND_RENEWAL_TYPE_ID:
                $urlItem =
                    $this->buildLink(
                        'RenBroadband.php',
                        array(
                            'action' => 'edit',
                            'ID' => $dsCustomerItem->getValue('customerItemID')
                        )
                    );
                break;
            case CONFIG_CONTRACT_RENEWAL_TYPE_ID:
                $urlItem =
                    $this->buildLink(
                        'RenContract.php',
                        array(
                            'action' => 'edit',
                            'ID' => $dsCustomerItem->getValue('customerItemID')
                        )
                    );
                break;
            case CONFIG_QUOTATION_RENEWAL_TYPE_ID:
                $urlItem =
                    $this->buildLink(
                        'RenQuotation.php',
                        array(
                            'action' => 'edit',
                            'ID' => $dsCustomerItem->getValue('customerItemID')
                        )
                    );
                break;
            case CONFIG_DOMAIN_RENEWAL_TYPE_ID:
                $urlItem =
                    $this->buildLink(
                        'RenDomain.php',
                        array(
                            'action' => 'edit',
                            'ID' => $dsCustomerItem->getValue('customerItemID')
                        )
                    );
                break;
            case CONFIG_HOSTING_RENEWAL_TYPE_ID:
                $urlItem =
                    $this->buildLink(
                        'RenHosting.php',
                        array(
                            'action' => 'edit',
                            'ID' => $dsCustomerItem->getValue('customerItemID')
                        )
                    );
                break;
            default:
                $urlItem =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCUSTOMERITEM_ACT_DISPLAY,
                            'customerItemID' => $dsCustomerItem->getValue('customerItemID')
                        )
                    );
                break;
        } // end switch
        return $urlItem;
    }

    /**
     * Display the renewal status drop-down selector
     *
     * @access private
     */
    function parseRenewalSelector($renewalStatus)
    {
        foreach ($this->renewalStatusArray as $key => $value) {
            $renewalStatusSelected = ($renewalStatus == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'renewalStatusSelected' => $renewalStatusSelected,
                    'renewalStatus' => $key,
                    'renewalStatusDescription' => $value
                )
            );
            $this->template->parse('renewalStatuss', 'renewalStatusBlock', true);
        }
    }

    /**
     * Display the second site delay days drop-down selector
     *
     * @access private
     */
    function parseSecondsiteImageDelayDaysSelector($delayDays)
    {
        foreach ($this->secondsiteImageDelayDays as $key => $value) {

            $delayDaysSelected = ($delayDays == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'delayDaysSelected' => $delayDaysSelected,
                    'delayDaysValue' => $key,
                    'delayDaysDescription' => $value
                )
            );
            $this->template->parse('secondsiteImageDelayDays', 'secondsiteImageDelayDaysBlock', true);
        }
    }

    /**
     * Display the popup selector form
     */
    function displayItemSelectPopup()
    {
        $this->setMethodName('displayItemSelectPopup');
        // this may be required in a number of situations
        $this->buCustomerItem->initialiseSearchForm($dsSearch);
        $dsSearch->setValue('itemText', $_REQUEST['itemDescription']);
        $dsSearch->setValue('customerID', $_REQUEST['customerID']);
        $this->buCustomerItem->search($dsSearch, $dsCustomerItem);
        if ($dsCustomerItem->rowCount() == 0) {
            $dsSearch->setValue('itemText', '');
            $dsSearch->setValue('serialNo', $_REQUEST['itemDescription']);
        }
        $this->buCustomerItem->search($dsSearch, $dsCustomerItem);
        $this->template->set_var(
            array(
                'parentIDField' => $_SESSION['parentIDField'],
                'parentWarrantyIDField' => $_SESSION['parentWarrantyIDField'],
                'parentDescField' => $_SESSION['parentDescField']
            )
        );
        if ($dsCustomerItem->rowCount() == 1) {
            $this->setTemplateFiles('CustomerItemSelect', 'CustomerItemSelectOne.inc');
            // This template runs a javascript function NOT inside HTML and so must use stripslashes()
            $dsCustomerItem->fetchNext();
            $this->template->set_var(
                array(
                    'itemDescription' => addslashes($dsCustomerItem->getValue("itemDescription")), // for javascript
                    'warrantyID' => $dsCustomerItem->getValue("warrantyID"),
                    'customerItemID' => $dsCustomerItem->getValue("customerItemID")
                )
            );
        } else {
            if ($dsCustomerItem->rowCount() == 0) {
                $this->template->set_var(
                    array(
                        'itemDescription' => $_REQUEST['itemDescription'],
                    )
                );
                $this->setTemplateFiles('CustomerItemSelect', 'CustomerItemSelectNone.inc');
            }
            if ($dsCustomerItem->rowCount() > 1) {
                $this->setTemplateFiles('CustomerItemSelect', 'CustomerItemSelectPopup.inc');
            }
            // Parameters
            $this->setPageTitle('Customer Item Selection');
            if ($dsCustomerItem->rowCount() > 0) {
                $this->template->set_block('CustomerItemSelect', 'itemBlock', 'items');
                while ($dsCustomerItem->fetchNext()) {
                    $this->template->set_var(
                        array(
                            'itemDescription' => Controller::htmlDisplayText($dsCustomerItem->getValue("itemDescription")),
                            'serialNo' => Controller::htmlDisplayText($dsCustomerItem->getValue("serialNo")),
                            'purchaseDate' => Controller::dateYMDtoDMY($dsCustomerItem->getValue("sOrderDate")),
                            'submitDescription' => Controller::htmlInputText(addslashes($dsCustomerItem->getValue("itemDescription"))),
                            'customerItemID' => $dsCustomerItem->getValue("customerItemID"),
                            'warrantyID' => $dsCustomerItem->getValue("warrantyID")
                        )
                    );
                    $this->template->parse('items', 'itemBlock', true);
                }
            }
        } // not ($dsItem->rowCount()==1)
        $this->template->parse('CONTENTS', 'CustomerItemSelect', true);
        $this->parsePage();
    }
    /**
     * Display the popup list of items under given contract (for CTActivity)
     *
     * @access private
     */
    /**
     * Display the results of order search
     * @access private
     */
    function displayRenewalContract()
    {
        $buCustomerItem = &$this->buCustomerItem;
        $buCustomerItem->getCustomerItemByID($_REQUEST['customerItemID'], $dsCustomerItem);
        $url = $this->getContractUrl($dsCustomerItem);
        header('Location: ' . $url);
        exit;
    }

    function display()
    {
        $this->setMethodName('display');
        $this->setTemplateFiles('CustomerItemDisplay', 'CustomerItemDisplay.inc');
// Parameters
        $this->setPageTitle("Customer Item");
        $dsCustomerItem = &$this->dsCustomerItem; // local refs
        $buCustomerItem = &$this->buCustomerItem;

        if ($_REQUEST['action'] != CTCUSTOMERITEM_ACT_ADD) {
            if (!$this->getFormError()) {
                $buCustomerItem->getCustomerItemByID($_REQUEST['customerItemID'], $dsCustomerItem);
                /*
            Get list of contracts this item is attached to
            */
                $this->contractIDs = $buCustomerItem->getContractIDsByCustomerItemID($_REQUEST['customerItemID']);
            } else {
                $dsCustomerItem->initialise(); // form error so already have a dataset
            }
            $dsCustomerItem->fetchNext();
            $customerItemID = $dsCustomerItem->getValue('customerItemID');
        } else {
            if (!$_REQUEST['customerID']) {
                $this->raiseError('CustomerID not passed');
            } else {
                $buCustomerItem->initialiseNewCustomerItem($dsCustomerItem);
                $dsCustomerItem->setValue('customerID', $_REQUEST['customerID']);
            }
        }

        if ($_REQUEST['action'] == CTCUSTOMERITEM_ACT_ADD) {
            $urlSubmit =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCUSTOMERITEM_ACT_INSERT
                    )
                );
        } else {
            $urlSubmit =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCUSTOMERITEM_ACT_UPDATE
                    )
                );
        }
        $urlContractPopup =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMERITEM_ACT_CONTRACT_POPUP
                )
            );
        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_DISP_ITEM_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_ITEM_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlCustomerPopup =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $urlPrintContract =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMERITEM_ACT_PRINT_CONTRACT,
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
        if ($_REQUEST['action'] != CTCUSTOMERITEM_ACT_ADD) {
            if ($buCustomerItem->canDelete($customerItemID)) {
                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCUSTOMERITEM_ACT_DELETE,
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
        $urlSiteEdit =
            $this->buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action' => CTCNC_ACT_SITE_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlSitePopup =
            $this->buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action' => CTCNC_ACT_SITE_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        if ($this->teamLevelIs(2)) {
            $readonly = '';
            $disabled = '';
        } else {
            $disabled = CTCNC_HTML_DISABLED;
            $readonly = CTCNC_HTML_READONLY;
        }

        if ($dsCustomerItem->getValue('secondsiteLocationPath')) {
            /*
            validate 2nd site location path
            */
            if (!file_exists($dsCustomerItem->getValue('secondsiteLocationPath'))) {
                $secondSiteLocationPathValidationText = 'Location is not available';
            } else {
                $secondSiteLocationPathValidationText = '';
            }
        }

        /* Default to enable 2ndSite fields */
        $secondsiteReadonly = '';
        $secondsiteDisabled = '';

        if (
        $buCustomerItem->serverIsUnderLocalSecondsiteContract($dsCustomerItem->getValue('customerItemID'))
        ) {
            $secondsiteLocalExcludeFlagShow = 1;
            /*
            if secondsiteLocalExcludeFlag is set to exclude this server from checks then disable all secondsite input fields
            */
            if ($dsCustomerItem->getValue('secondsiteLocalExcludeFlag') == 'Y') {

                $secondsiteReadonly = CTCNC_HTML_READONLY;

                $secondsiteDisabled = CTCNC_HTML_DISABLED;
            }
        } else {
            $secondsiteLocalExcludeFlagShow = 0;
        }

        $buUser = new BUUser($this);

        if (
            $dsCustomerItem->getValue('secondsiteValidationSuspendUntilDate') &&
            $dsCustomerItem->getValue('secondsiteValidationSuspendUntilDate') != '0000-00-00' &&
            $dsCustomerItem->getValue('secondsiteSuspendedByUserID')
        ) {
            $buUser->getUserByID($dsCustomerItem->getValue('secondsiteSuspendedByUserID'), $dsUser);

            $suspendedByText =
                $dsUser->getValue('name') . ' on ' .
                Controller::dateYMDtoDMY($dsCustomerItem->getValue('secondsiteSuspendedDate'));
        } else {
            $suspendedByText = '';
        }

        if (
            $dsCustomerItem->getValue('secondsiteImageDelayDays') > '0' &&
            $dsCustomerItem->getValue('secondsiteImageDelayUserID')
        ) {
            $buUser->getUserByID($dsCustomerItem->getValue('secondsiteImageDelayUserID'), $dsUser);

            $imageDelayByText =
                $dsUser->getValue('name') . ' on ' .
                Controller::dateYMDtoDMY($dsCustomerItem->getValue('secondsiteImageDelayDate'));
        } else {
            $imageDelayByText = '';
        }

        $this->template->set_var(
            array(
                'urlSubmit' => $urlSubmit,
                'urlContractPopup' => $urlContractPopup,
                'urlItemPopup' => $urlItemPopup,
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlItemEdit' => $urlItemEdit,
                'customerItemID' => $dsCustomerItem->getValue('customerItemID'),
                'siteNo' => $dsCustomerItem->getValue('siteNo'),
                'siteDesc' => Controller::htmlDisplayText($dsCustomerItem->getValue('siteDescription')),
                'serverName' => Controller::htmlDisplayText($dsCustomerItem->getValue('serverName')),
                'urlSiteEdit' => $urlSiteEdit,
                'urlSitePopup' => $urlSitePopup,
                'customerID' => $dsCustomerItem->getValue('customerID'),
                'customerName' => Controller::htmlDisplayText($dsCustomerItem->getValue('customerName')),
                'itemID' => $dsCustomerItem->getValue('itemID'),
                'itemDescription' => Controller::htmlDisplayText($dsCustomerItem->getValue('itemDescription')),
                'partNo' => Controller::htmlDisplayText($dsCustomerItem->getValue('partNo')),
                'serialNo' => Controller::htmlDisplayText($dsCustomerItem->getValue('serialNo')),
                'ordheadID' => Controller::htmlDisplayText($dsCustomerItem->getValue('ordheadID')),
                'ordheadIDMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('ordheadID')),
                'porheadID' => Controller::htmlDisplayText($dsCustomerItem->getValue('porheadID')),
                'porheadIDMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('porheadID')),
                'curUnitSale' => Controller::htmlDisplayText($dsCustomerItem->getValue('curUnitSale')),
                'curUnitSaleMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('curUnitSale')),
                'curUnitCost' => $dsCustomerItem->getValue('curUnitCost'),
                'curUnitCostMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('curUnitCost')),
                'sOrderDate' => Controller::dateYMDtoDMY($dsCustomerItem->getValue('sOrderDate')),
                'sOrderDateMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('sOrderDate')),
                'expiryDate' => Controller::dateYMDtoDMY($dsCustomerItem->getValue('expiryDate')),
                'expiryDateMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('expiryDate')),
                'curGSCBalance' => Controller::htmlDisplayText($dsCustomerItem->getValue('curGSCBalance')),
                'curGSCBalanceMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('curGSCBalance')),
                'customerItemNotes' => Controller::htmlTextArea($dsCustomerItem->getValue('customerItemNotes')),
                'internalNotes' => Controller::htmlTextArea($dsCustomerItem->getValue('internalNotes')),
                'slaResponseHours' => $dsCustomerItem->getValue('slaResponseHours'),
                'despatchDate' => Controller::dateYMDtoDMY($dsCustomerItem->getValue('despatchDate')),
                'despatchDateMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('despatchDate')),

                'secondsiteLocationPath' => $dsCustomerItem->getValue('secondsiteLocationPath'),
                'secondsiteLocationPathMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('secondsiteLocationPath')),
                'secondsiteValidationSuspendUntilDate' => Controller::dateYMDtoDMY($dsCustomerItem->getValue('secondsiteValidationSuspendUntilDate')),
                'secondsiteValidationSuspendUntilDateMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('secondsiteValidationSuspendUntilDate')),

                'suspendedByText' => $suspendedByText,

                'imageDelayByText' => $imageDelayByText,

                'secondsiteImageDelayDays' => Controller::htmlDisplayText($dsCustomerItem->getValue('secondsiteImageDelayDays')),
                'secondsiteImageDelayDaysMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('secondsiteImageDelayDays')),
                'secondSiteLocationPathValidationText' => $secondSiteLocationPathValidationText,

                'secondsiteLocalExcludeFlagShow' => $secondsiteLocalExcludeFlagShow,

                'secondsiteLocalExcludeFlagChecked' => Controller::htmlChecked($dsCustomerItem->getValue('secondsiteLocalExcludeFlag')),

                'secondsiteDisabled' => $secondsiteDisabled,

                'secondsiteReadonly' => $secondsiteReadonly,
            )
        );

        $this->template->set_block('CustomerItemDisplay', 'secondsiteImageDelayDaysBlock', 'secondsiteImageDelayDays');
        $this->parseSecondsiteImageDelayDaysSelector($dsCustomerItem->getValue('secondsiteImageDelayDays'));


        $this->parseWarrantySelector($dsCustomerItem->getValue('warrantyID'));
        $this->parseContractSelector(
            $this->dsCustomerItem->getValue('customerID'),
            'CustomerItemDisplay',
            $this->contractIDs
        );
        $this->template->set_block('CustomerItemDisplay', 'renewalStatusBlock', 'renewalStatuss');
        $this->parseRenewalSelector($dsCustomerItem->getValue('renewalStatus'));


        /*
        2nd Site Images
        */
        if ($_REQUEST['action'] != CTCUSTOMERITEM_ACT_ADD) {

            $this->template->set_block('CustomerItemDisplay', 'secondsiteImageBlock', 'secondsiteImages');

            $addSecondsiteImageURL =
                $this->buildLink(
                    'SecondSite.php',
                    array(
                        'action' => 'add',
                        'customerItemID' => $dsCustomerItem->getValue('customerItemID')
                    )
                );

            $this->template->set_var(
                array(
                    'addSecondsiteImageText' => 'Add 2nd Site Image',
                    'addSecondsiteImageUrl' => $addSecondsiteImageURL
                )
            );

            $BUSecondsite = new BUSecondsite($this);
            $BUSecondsite->getSecondsiteImagesByCustomerItemID($dsCustomerItem->getValue('customerItemID'), $dsSecondsiteImage);

            while ($dsSecondsiteImage->fetchNext()) {

                $deleteSecondsiteImageLink =
                    $this->buildLink('SecondSite.php',
                        array(
                            'action' => 'delete',
                            'secondsiteImageID' => $dsSecondsiteImage->getValue('secondsiteImageID')
                        )
                    );
                $deleteSecondsiteImageText = 'delete';

                $editSecondsiteImageLink =
                    $this->buildLink('SecondSite.php',
                        array(
                            'action' => 'edit',
                            'secondsiteImageID' => $dsSecondsiteImage->getValue('secondsiteImageID')
                        )
                    );

                if ($dsSecondsiteImage->getValue('status') && $dsSecondsiteImage->getValue('imageTime') > 0) {

                    $imageTime = strftime("%d/%m/%Y %H:%M:%S", strtotime($dsSecondsiteImage->getValue('imageTime')));

                    $imageAgeDays = number_format((time() - strtotime($dsSecondsiteImage->getValue('imageTime'))) / 86400, 0);
                } else {
                    $imageTime = '';
                    $imageAgeDays = '';
                }

                $this->template->set_var(
                    array(
                        'secondsiteImageID' => $dsSecondsiteImage->getValue('secondsiteImageID'),
                        'imageName' => $dsSecondsiteImage->getValue('imageName'),
                        'status' => $dsSecondsiteImage->getValue('status'),
                        'imageTime' => $imageTime,
                        'imageAgeDays' => $imageAgeDays,
                        'editSecondsiteImageLink' => $editSecondsiteImageLink,
                        'deleteSecondsiteImageLink' => $deleteSecondsiteImageLink,
                        'deleteSecondsiteImageText' => $deleteSecondsiteImageText
                    )
                );

                $this->template->parse('secondsiteImages', 'secondsiteImageBlock', true);

            }
        }

        /*
        end 2nd Site Images
        */

        /*
         Documents section
        */
        if ($_REQUEST['action'] != CTCUSTOMERITEM_ACT_ADD) {
            $this->template->set_block('CustomerItemDisplay', 'documentBlock', 'documents');

            $urlUploadFile =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCUSTOMERITEM_ACT_UPLOAD_DOCUMENT,
                        'customerItemID' => $customerItemID
                    )
                );
            $txtUploadFile = '[upload]';

            $this->template->set_var(
                array(
                    'uploadDescription' => $_REQUEST['uploadDescription'],
                    'userfile' => $_FILES['userfile']['name'],
                    'txtUploadFile' => $txtUploadFile,
                    'urlUploadFile' => $urlUploadFile
                )
            );

            $dbeJCustomerItemDocument = new DBEJCustomerItemDocument($this);
            $dbeJCustomerItemDocument->setValue('customerItemID', $customerItemID);
            $dbeJCustomerItemDocument->getRowsByColumn('customerItemID');
            while ($dbeJCustomerItemDocument->fetchNext()) {
                $urlViewFile =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCUSTOMERITEM_ACT_VIEW_DOCUMENT,
                            'customerItemDocumentID' => $dbeJCustomerItemDocument->getValue('customerItemDocumentID')
                        )
                    );
                $urlDeleteFile =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCUSTOMERITEM_ACT_DELETE_DOCUMENT,
                            'customerItemDocumentID' => $dbeJCustomerItemDocument->getValue('customerItemDocumentID')
                        )
                    );
                $this->template->set_var(
                    array(
                        'description' => $dbeJCustomerItemDocument->getValue("description"),
                        'filename' => $dbeJCustomerItemDocument->getValue("filename"),
                        'createUserName' => $dbeJCustomerItemDocument->getValue("createUserName"),
                        'createDate' => $dbeJCustomerItemDocument->getValue("createDate"),
                        'urlViewFile' => $urlViewFile,
                        'urlDeleteFile' => $urlDeleteFile,
                        'txtDeleteFile' => '[delete]'
                    )
                );
                $this->template->parse('documents', 'documentBlock', true);
            }
        }// if ($_REQUEST['action'] != CTACTIVITY_ACT_CREATE_CALL)
        /*
        End documents section
        */


        $this->template->parse('CONTENTS', 'CustomerItemDisplay', true);
        $this->parsePage();
    } // end display()

    /**
     * Edit/Add Activity
     * @access private
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
            $customerItemID = $dsCustomerItem->getValue('customerItemID');
        }


        $urlSubmit =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMERITEM_ACT_INSERT
                )
            );


        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_DISP_ITEM_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_ITEM_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlCustomerPopup =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlSiteEdit =
            $this->buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action' => CTCNC_ACT_SITE_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlSitePopup =
            $this->buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action' => CTCNC_ACT_SITE_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $this->setPageTitle('Add Customer Item');
        $this->setTemplateFiles(
            array('CustomerItemAdd' => 'CustomerItemAdd.inc')
        );
        $this->template->set_var(
            array(

                'urlSubmit' => $urlSubmit,
                'urlItemPopup' => $urlItemPopup,
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlItemEdit' => $urlItemEdit,
                'customerItemID' => $dsCustomerItem->getValue('customerItemID'),
                'siteNo' => $dsCustomerItem->getValue('siteNo'),
                'siteDesc' => Controller::htmlDisplayText($dsCustomerItem->getValue('siteDescription')),
                'urlSiteEdit' => $urlSiteEdit,
                'urlSitePopup' => $urlSitePopup,
                'customerID' => $dsCustomerItem->getValue('customerID'),
                'customerName' => Controller::htmlDisplayText($dsCustomerItem->getValue('customerName')),
                'itemID' => $dsCustomerItem->getValue('itemID'),
                'itemDescription' => Controller::htmlDisplayText($dsCustomerItem->getValue('itemDescription')),
                'descriptionMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('itemID')),
                'customerNameMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('customerID')),
                'siteDescMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('siteNo')),
                'serialNoMessage' => Controller::htmlDisplayText($dsCustomerItem->getMessage('serialNo')),
            )
        );
        $this->template->parse('CONTENTS', 'CustomerItemAdd', true);
        $this->parsePage();
    }// end function addCustomerItem()

    /**
     * Redirect to display
     * @access private
     */
    function redirectToDisplay($customerItemID)
    {
        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'customerItemID' => $customerItemID,
                    'action' => CTCUSTOMERITEM_ACT_DISPLAY
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    function parseWarrantySelector($warrantyID)
    {
        global $cfg;
        // Manufacturer selector
        require_once($GLOBALS['cfg']['path_dbe'] . '/DBEWarranty.inc.php');
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows();
        $this->template->set_block('CustomerItemDisplay', 'warrantyBlock', 'warranties');
        while ($dbeWarranty->fetchNext()) {
            $this->template->set_var(
                array(
                    'warrantyDescription' => $dbeWarranty->getValue('description'),
                    'warrantyID' => $dbeWarranty->getValue('warrantyID'),
                    'warrantySelected' => ($warrantyID == $dbeWarranty->getValue('warrantyID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse('warranties', 'warrantyBlock', true);
        } // while ($dbeWarranty->fetchNext()
    }

    /**
     * put your comment there...
     *
     * @param mixed $dsCustomerItemContract Dataset of contracts this item is attached to
     * @param mixed $customerID
     * @param mixed $templateName
     */
    function parseContractSelector($customerID, $templateName, $contractIDs = false)
    {

        $this->buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract
        );

        $this->template->set_block($templateName, 'contractBlock', 'contracts');

        while ($dsContract->fetchNext()) {

            if ($contractIDs && count($contractIDs) > 0) {

                if (in_array($dsContract->getValue('customerItemID'), $contractIDs)) {
                    $selected = CT_CHECKED;
                } else {
                    $selected = '';
                }

            }

            $this->template->set_var(
                array(
                    'contractDescription' => $dsContract->getValue('itemDescription'),
                    'contractID' => $dsContract->getValue('customerItemID'),
                    'contractSelected' => $selected
                )
            );
            $this->template->parse('contracts', 'contractBlock', true);
        } // while
    }

    /**
     * Display the renewal status drop-down selector
     *
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $this->buCustomerItem->initialiseNewCustomerItem($this->dsCustomerItem);
        /*
        contractID array is the contracts
        */
        $this->contractIDs = $_REQUEST['contractID']; /* ?? */

        if (!$this->dsCustomerItem->populateFromArray($_REQUEST['customerItem'])) {
            $this->setFormErrorOn();
            if ($_REQUEST['action'] == CTCUSTOMERITEM_ACT_INSERT) {

                $this->add();
            } else {
                $_REQUEST['action'] = CTCUSTOMERITEM_ACT_EDIT;
            }


            $_REQUEST['customerItemID'] = $this->dsCustomerItem->getValue('customerItemID');

            $this->display();
            exit;
        }

        $this->buCustomerItem->update($this->dsCustomerItem, $this->contractIDs);

        $this->dsCustomerItem->initialise();
        // this forces update of itemID back through Javascript to parent HTML window
        $urlNext = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCUSTOMERITEM_ACT_DISPLAY,
                'customerItemID' => $this->dsCustomerItem->getPKValue()
            )
        );
        header('Location: ' . $urlNext);
    }

    function delete()
    {
        $this->setMethodName('delete');
        if ($this->buCustomerItem->canDelete($_REQUEST['customerItemID'])) {
            $this->buCustomerItem->deleteCustomerItem($_REQUEST['customerItemID']);
            $urlNext = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMERITEM_ACT_SEARCH
                )
            );
            header('Location: ' . $urlNext);
        } else {
            $this->displayError('Can not delete customer item, dependencies exist');
        }
    }

    /**
     * Generate page required to embed file
     * this is done because simply calling documentView() with PDF files causes
     * IE to call documentView a second time! this is a known problem. The workaround
     * is to produce a page with and EMBED tag that makes a call back to the server.
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function viewDocument()
    {
        // Validation and setting of variables
        $this->setMethodName('viewDocument');
        $dbeCustomerItemDocument = new DBECustomerItemDocument($this);
        if (!$dbeCustomerItemDocument->getRow($_REQUEST['customerItemDocumentID'])) {
            $this->displayFatalError('Acrivity file not found.');
        }
        if ($dbeCustomerItemDocument->getValue('fileMIMEType') != 'application/pdf') {
            $this->getFile();
        }
        $urlFile =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITY_ACT_GET_DOCUMENT,
                    'customerItemDocumentID' => $_REQUEST['customerItemDocumentID']
                )
            );
        // build embed code
        echo
            '<HTML>' .
            '<HEAD>' .
            '</HEAD>' .
            '<BODY leftMargin=0 topMargin=0 scroll=no>' .
            '<EMBED src="' . $getFile .
            '" width="100%" height="100%" type=' . $dbeCustomerItemDocument->getValue('fileMIMEType') .
            ' fullscreen="yes">' .
            '</body>' .
            '</html>';
        exit;
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
        if (!$dbeCustomerItemDocument->getRow($_REQUEST['customerItemDocumentID'])) {
            $this->displayFatalError('File not found.');
        }
        header('Pragma: ');
        header('Cache-Control: ');
        header('Content-type: ' . $dbeCustomerItemDocument->getValue('fileMIMEType'));
        header('Content-Length: ' . $dbeCustomerItemDocument->getValue('fileLength'));
        header('Content-Disposition: inline; filename="' . $dbeCustomerItemDocument->getValue('filename') . '"');
        print $dbeCustomerItemDocument->getValue('file');
        exit;
    }

    /**
     * Upload new document from local disk
     * @access private
     */
    function uploadDocument()
    {
        // validate
        if ($_REQUEST['uploadDescription'] == '') {
            $this->setFormErrorMessage('Please enter a description');
        }
        if ($_FILES['userfile']['name'] == '') {
            $this->setFormErrorMessage('Please enter a file path');
        }
        if (!is_uploaded_file($_FILES['userfile']['tmp_name'])) {                    // Possible hack?
            $this->setFormErrorMessage('Document not loaded - is it bigger that 6 MBytes?');
        }
        if ($this->formError) {
            $this->buCustomerItem->getCustomerItemByID($_REQUEST['customerItemID'], $this->dsCustomerItem);
            $this->display();
            exit;
        }
        $this->buCustomerItem->uploadDocumentFile(
            $_REQUEST['customerItemID'],
            $_REQUEST['uploadDescription'],
            $_FILES['userfile']
        );
        $this->redirectToDisplay($_REQUEST['customerItemID']);
    }

    /**
     * Generate page required to embed file
     * this is done because simply calling documentView() with PDF files causes
     * IE to call documentView a second time! this is a known problem. The workaround
     * is to produce a page with and EMBED tag that makes a call back to the server.
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteDocument()
    {
        // Validation and setting of variables
        $this->setMethodName('deleteDocument');
        $dbeCustomerItemDocument = new DBECustomerItemDocument($this);
        if (!$dbeCustomerItemDocument->getRow($_REQUEST['customerItemDocumentID'])) {
            $this->displayFatalError('Document not found.');
        }
        $customerItemID = $dbeCustomerItemDocument->getValue('customerItemID');
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
        $buCustomerItem->getCustomerItemByID($_REQUEST['customerItemID'], $dsContract);
        $buCustomerItem->getCustomerItemsByContractID($_REQUEST['customerItemID'], $dsCustomerItem);
        $buSite = new BUSite($this);
        $buActivity = new BUActivity($this);
        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID($dsContract->getValue('customerID'), $dsCustomer);
        $buSite->getSiteByID($dsContract->getValue('customerID'), $dsContract->getValue('siteNo'), $dsSite);
        $customerHasServiceDeskContract = $buCustomerItem->customerHasServiceDeskContract($dsContract->getValue('customerID'));

        $buPDFSupportContract =
            new BUPDFSupportContract(
                $this,
                $dsContract,
                $dsCustomerItem,
                $dsSite,
                $dsCustomer,
                $buActivity,
                $customerHasServiceDeskContract
            );

        $pdfFile = $buPDFSupportContract->generateFile();
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

    function applyContractUpdates($action)
    {
        $this->setMethodName('applyContractUpdates');

        if (isset($_REQUEST['customerItemIDs'])) {

            if ($action == 'add') {
                $this->buCustomerItem->addContractToCustomerItems(
                    $_REQUEST['contractID'],
                    $_REQUEST['customerItemIDs']
                );
            } else {
                $this->buCustomerItem->removeContractFromCustomerItems(
                    $_REQUEST['contractID'],
                    $_REQUEST['customerItemIDs']
                );

            }

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

}// end of class
?>