<?php /**
 * Supplier controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUSupplier.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define(
    'CTSUPPLIER_MSG_SUPPLIERID_NOT_PASSED',
    'SupplierID not passed'
);
define(
    'CTSUPPLIER_MSG_SUPPLIER_ARRAY_NOT_PASSED',
    'Supplier array not passed'
);
define(
    'CTSUPPLIER_MSG_NONE_FND',
    'No suppliers found'
);
define(
    'CTSUPPLIER_MSG_SUPPLIER_NOT_FND',
    'Supplier not found'
);
// Actions
define(
    'CTSUPPLIER_ACT_SUPPLIER_INSERT',
    'insertSupplier'
);
define(
    'CTSUPPLIER_ACT_SUPPLIER_UPDATE',
    'updateSupplier'
);
define(
    'CTSUPPLIER_ACT_SUPPLIER_SEARCH_FORM',
    'searchForm'
);
// Page text
define(
    'CTSUPPLIER_TXT_NEW_SUPPLIER',
    'Create Supplier'
);
define(
    'CTSUPPLIER_TXT_UPDATE_SUPPLIER',
    'Update Supplier'
);

class CTSupplier extends CTCNC
{
    /**
     * Dataset for supplier record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsSupplier;
    public $buSupplier;

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
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buSupplier = new BUSupplier($this);
        $this->dsSupplier = new DSForm($this);    // new specialised dataset with form message support
        $this->dsSupplier->copyColumnsFrom($this->buSupplier->dbeJSupplier);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($_REQUEST['action']) {
            case CTCNC_ACT_SUPPLIER_ADD:
            case CTCNC_ACT_SUPPLIER_EDIT:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->supplierForm();
                break;
            case CTSUPPLIER_ACT_SUPPLIER_INSERT:
            case CTSUPPLIER_ACT_SUPPLIER_UPDATE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->supplierUpdate();
                break;
            case CTCNC_ACT_SEARCH:
                $this->search();
                break;
            case CTCNC_ACT_DISP_SUPPLIER_POPUP:
                $this->displaySupplierSelectPopup();
                break;
            case CTSUPPLIER_ACT_SUPPLIER_SEARCH_FORM:
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
            $_SESSION['supplierParentIDField'] = $_REQUEST['parentIDField'];
        } else {
            unset($_SESSION['supplierParentIDField']);
        }
        if (isset($_REQUEST['parentDescField'])) {
            $_SESSION['supplierParentDescField'] = $_REQUEST['parentDescField'];
        } else {
            unset($_SESSION['supplierParentDescField']);
        }
    }

    /**
     * Display the popup selector form
     * @access private
     */
    function displaySupplierSelectPopup()
    {
        $this->setMethodName('displaySupplierSelectPopup');

        // A single slash means create new supplier
        if ($_REQUEST['supplierName']{0} == '/') {
            $urlCreate = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'  => CTCNC_ACT_SUPPLIER_ADD,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
            header('Location: ' . $urlCreate);
            exit;
        }

        $this->buSupplier->getSuppliersByNameMatch(
            $_REQUEST['supplierName'],
            $this->dsSupplier
        );
        if ($this->dsSupplier->rowCount() == 1) {
            $this->setTemplateFiles(
                'SupplierSelect',
                'SupplierSelectOne.inc'
            );
            $this->template->set_var(
                array(
                    'submitName' => addslashes($this->dsSupplier->getValue("name")),
                    'supplierID' => $this->dsSupplier->getValue("supplierID")
                )
            );
        } else {
            if ($this->dsSupplier->rowCount() == 0) {
                $this->template->set_var(
                    'supplierName',
                    $_REQUEST['supplierName']
                );
                $this->setTemplateFiles(
                    'SupplierSelect',
                    'SupplierSelectNone.inc'
                );
            }
            if ($this->dsSupplier->rowCount() > 1) {
                $this->setTemplateFiles(
                    'SupplierSelect',
                    'SupplierSelectPopup.inc'
                );
            }
            $this->template->set_var(
                array(
                    'urlSupplierCreate' => $urlCreate
                )
            );
            // Parameters
            $this->setPageTitle('Supplier Selection');
            if ($this->dsSupplier->rowCount() > 0) {
                $this->template->set_block(
                    'SupplierSelect',
                    'supplierBlock',
                    'suppliers'
                );
                while ($this->dsSupplier->fetchNext()) {
                    $this->template->set_var(
                        array(
                            'supplierName' => Controller::htmlDisplayText(($this->dsSupplier->getValue("name"))),
                            'submitName'   => Controller::htmlDisplayText($this->dsSupplier->getValue("name")),
                            'supplierID'   => $this->dsSupplier->getValue("supplierID")
                        )
                    );
                    $this->template->parse(
                        'suppliers',
                        'supplierBlock',
                        true
                    );
                }
            }
        } // not ($dsSupplier->rowCount()==1)
        $this->template->set_var(
            array(
                'parentIDField'   => $_SESSION['supplierParentIDField'],
                'parentDescField' => $_SESSION['supplierParentDescField']
            )
        );
        $this->template->parse(
            'CONTENTS',
            'SupplierSelect',
            true
        );
        $this->parsePage();
    }

    /**
     * Display the search form
     * @access private
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            'SupplierSearch',
            'SupplierSearch.inc'
        );
        $this->setPageTitle("Supplier");
        // clear these vars so that context of edit will NOT be assumed to be a pop-up
        unset($_SESSION['supplierParentIDField']);
        unset($_SESSION['supplierParentDescField']);
        $submitURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $createURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SUPPLIER_ADD)
        );
        $supplierPopup = Controller::buildLink(
            CTCNC_PAGE_SUPPLIER,
            array(
                'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->template->set_var(
            array(
                'supplierString'        => $_REQUEST['supplierString'],
                'address'               => $_REQUEST['address'],
                'supplierStringMessage' => $GLOBALS['supplierStringMessage'],
                'submitURL'             => $submitURL,
                'createURL'             => $createURL,
                'urlSupplierPopup'      => $supplierPopup
            )
        );
        if (is_object($this->dsSupplier)) {
            $this->template->set_block(
                'SupplierSearch',
                'supplierBlock',
                'suppliers'
            );
            while ($this->dsSupplier->fetchNext()) {
                $supplierURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTCNC_ACT_SUPPLIER_EDIT,
                            'supplierID' => $this->dsSupplier->getValue("supplierID")
                        )
                    );

                $this->template->set_var(
                    array(
                        'supplierName' => $this->dsSupplier->getValue("name"),
                        'supplierURL'  => $supplierURL
                    )
                );
                $this->template->parse(
                    'suppliers',
                    'supplierBlock',
                    true
                );
            }
        }
        $this->template->parse(
            'CONTENTS',
            'SupplierSearch',
            true
        );
        $this->parsePage();
    }

    /**
     * Search for suppliers using supplierName
     * @access private
     */
    function search()
    {
        $this->setMethodName('search');
// Parameter validation
        if ($_REQUEST['supplierString'] == '' && $_REQUEST['address'] == '') {
            $GLOBALS['supplierStringMessage'] = 'You must specify some parameters';
            $this->displaySearchForm();
            exit;
        } else {
            if (!$this->buSupplier->getSuppliersByNameMatch(
                $_REQUEST['supplierString'],
                $this->dsSupplier,
                $_REQUEST['address']                        // on the end to ensure existing code works OK
            )
            ) {
                $GLOBALS['supplierStringMessage'] = CTSUPPLIER_MSG_NONE_FND;
            }

        }

        if ($this->dsSupplier->rowCount() == 1) {
            $this->dsSupplier->fetchNext();
            $_REQUEST['supplierID'] = $this->dsSupplier->getValue("supplierID");
            $_REQUEST['action'] = CTCNC_ACT_SUPPLIER_EDIT;
            $this->supplierForm();
        } else {
            $this->displaySearchForm();

        }
    }

    /**
     * Add/Edit Supplier
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function supplierForm()
    {
        $this->setMethodName('supplierForm');
        // initialisation stuff
        if ($_REQUEST['action'] == CTCNC_ACT_SUPPLIER_ADD) {
            $urlSubmit = $this->supplierFormPrepareAdd();
        } else {
            $urlSubmit = $this->supplierFormPrepareEdit();
        }

        // template
        $this->setTemplateFiles(
            array(
                'SupplierEdit'        => 'SupplierEdit.inc',
                'SupplierEditContact' => 'SupplierEditContact.inc',
                'SupplierEditJS'      => 'SupplierEditJS.inc'
            )
        );
        // If editing a supplier then the contact field will exist
        if ($_REQUEST['action'] == CTCNC_ACT_SUPPLIER_EDIT) {
            $urlContactPopup =
                Controller::buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'     => CTCNC_ACT_CONTACT_POPUP,
                        'supplierID' => $this->dsSupplier->getValue('supplierID'),
                        'htmlFmt'    => CT_HTML_FMT_POPUP
                    )
                );
            $urlContactEdit =
                Controller::buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'  => CTCNC_ACT_CONTACT_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'contactName'     => Controller::htmlDisplayText($this->dsSupplier->getValue('contactName')),
                    'contactID'       => $this->dsSupplier->getValue('contactID'),
                    'urlContactEdit'  => $urlContactEdit,
                    'urlContactPopup' => $urlContactPopup
                )
            );
        }
        $this->template->set_var(
            array(
                'supplierID'          => $this->dsSupplier->getValue(DBESupplier::supplierID),
                'name'                => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::name)),
                'nameMessage'         => Controller::htmlDisplayText($this->dsSupplier->getMessage(DBESupplier::name)),
                'add1'                => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::add1)),
                'add1Message'         => Controller::htmlDisplayText($this->dsSupplier->getMessage(DBESupplier::add1)),
                'add2'                => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::add2)),
                'add2Message'         => Controller::htmlDisplayText($this->dsSupplier->getMessage(DBESupplier::add2)),
                'town'                => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::town)),
                'townMessage'         => Controller::htmlDisplayText($this->dsSupplier->getMessage(DBESupplier::town)),
                'county'              => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::county)),
                'countyMessage'       => Controller::htmlDisplayText(
                    $this->dsSupplier->getMessage(DBESupplier::county)
                ),
                'postcode'            => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::postcode)),
                'postcodeMessage'     => Controller::htmlDisplayText(
                    $this->dsSupplier->getMessage(DBESupplier::postcode)
                ),
                'phone'               => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::phone)),
                'phoneMessage'        => Controller::htmlDisplayText($this->dsSupplier->getMessage(DBESupplier::phone)),
                'fax'                 => Controller::htmlInputText($this->dsSupplier->getValue(DBESupplier::fax)),
                'faxMessage'          => Controller::htmlDisplayText($this->dsSupplier->getMessage(DBESupplier::fax)),
                'webSiteURL'          => Controller::htmlInputText(
                    $this->dsSupplier->getValue(DBESupplier::webSiteURL)
                ),
                'creditLimit'         => Controller::htmlInputText(
                    $this->dsSupplier->getValue(DBESupplier::creditLimit)
                ),
                'creditLimitMessage'  => Controller::htmlDisplayText(
                    $this->dsSupplier->getMessage(DBESupplier::creditLimit)
                ),
                'cncAccountNo'        => Controller::htmlInputText(
                    $this->dsSupplier->getValue(DBESupplier::cncAccountNo)
                ),
                'cncAccountNoMessage' => Controller::htmlDisplayText(
                    $this->dsSupplier->getMessage(DBESupplier::creditLimit)
                ),
                'urlSubmit'           => $urlSubmit
            )
        );
        $this->parsePayMethodSelector($this->dsSupplier->getValue(DBESupplier::payMethodID));
        $this->template->parse(
            'supplierEditJS',
            'SupplierEditJS',
            true
        );
        if ($_REQUEST['action'] == CTCNC_ACT_SUPPLIER_EDIT) {
            $this->template->parse(
                'supplierEditContact',
                'SupplierEditContact',
                true
            );
        }
        $this->template->parse(
            'CONTENTS',
            'SupplierEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * Prepare for add
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function supplierFormPrepareAdd()
    {
        // If form error then preserve values in $this->dsSupplier else initialise new
        $this->setPageTitle(CTSUPPLIER_TXT_NEW_SUPPLIER);
        if (!$this->getFormError()) {
            $this->buSupplier->initialiseNewSupplier($this->dsSupplier);
        }
        return (
        Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTSUPPLIER_ACT_SUPPLIER_INSERT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        )
        );
    }

    /**
     * Prepare for edit
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function supplierFormPrepareEdit()
    {
        $this->setPageTitle(CTSUPPLIER_TXT_UPDATE_SUPPLIER);
        // if updating and not a form error then validate passed id and get row from DB
        if (!$this->getFormError()) {
            if (empty($_REQUEST['supplierID'])) {
                $this->displayFatalError(CTSUPPLIER_MSG_SUPPLIERID_NOT_PASSED);
            }
            if (!$this->buSupplier->getSupplierByID(
                $_REQUEST['supplierID'],
                $this->dsSupplier
            )) {
                $this->displayFatalError(CTSUPPLIER_MSG_SUPPLIER_NOT_FND);
            }
        }
        return (
        Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTSUPPLIER_ACT_SUPPLIER_UPDATE,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        )
        );
    }

    function parsePayMethodSelector($payMethodID)
    {
        $this->buSupplier->getAllPayMethods($dsPayMethod);
        $this->template->set_block(
            'SupplierEdit',
            'payMethodBlock',
            'payMethods'
        );
        while ($dsPayMethod->fetchNext()) {
            $this->template->set_var(
                array(
                    'payMethodDescription' => $dsPayMethod->getValue(DBEPayMethod::description),
                    'payMethodID'          => $dsPayMethod->getValue(DBEPayMethod::payMethodID),
                    'payMethodSelected'    => ($payMethodID == $dsPayMethod->getValue(
                            DBEPayMethod::payMethodID
                        )) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'payMethods',
                'payMethodBlock',
                true
            );
        }
    }

    /**
     * Update supplier record
     * @access private
     */
    function supplierUpdate()
    {
        $this->setMethodName('supplierUpdate');
        if (!isset($_REQUEST['supplier'])) {
            $this->displayFatalError(CTSUPPLIER_MSG_SUPPLIER_ARRAY_NOT_PASSED);
            return;
        }
        if (!$this->dsSupplier->populateFromArray($_REQUEST['supplier'])) {
            $this->setFormErrorOn();
            if ($_REQUEST['action'] == CTSUPPLIER_ACT_SUPPLIER_INSERT) {
                $_REQUEST['action'] = CTCNC_ACT_SUPPLIER_ADD;
            } else {
                $_REQUEST['action'] = CTCNC_ACT_SUPPLIER_EDIT;
            }
            $_REQUEST['supplierID'] = $this->dsSupplier->getValue('supplierID');
            $this->supplierForm();
            exit;
        }
        $this->buSupplier->updateSupplier($this->dsSupplier);
        // force entry of a contact if none exists

        if ($this->dsSupplier->getValue('contactID') == 0) {
            $this->setFormErrorMessage('Please create a contact or select an existing contact');
            $_REQUEST['action'] = CTCNC_ACT_SUPPLIER_EDIT;
            $this->supplierForm();
            exit;
        } else {
//             if there is a parent (popup) this forces update of supplierID back through Javascript to parent HTML window
            if (isset($_SESSION['supplierParentDescField'])) {
                $urlNext = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'       => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                        'supplierName' => $this->dsSupplier->getPKValue(),
                        'htmlFmt'      => CT_HTML_FMT_POPUP
                    )
                );
            } else {

                $urlNext = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array()
                );
            }
            header('Location: ' . $urlNext);
        }
    }
}// end of class
?>