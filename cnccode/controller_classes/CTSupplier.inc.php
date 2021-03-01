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
    const GET_SUPPLIERS = "getSuppliers";
    /**
     * Dataset for supplier record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var    $dsSupplier;
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
        $roles = MAINTENANCE_PERMISSION;
        $this->setMenuId(810);
        $this->buSupplier = new BUSupplier($this);
        $this->dsSupplier = new DSForm($this);    // new specialised dataset with form message support
        $this->dsSupplier->copyColumnsFrom($this->buSupplier->dbeJSupplier);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($this->getAction()) {
            case CTCNC_ACT_SUPPLIER_ADD:
            case CTCNC_ACT_SUPPLIER_EDIT:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->supplierForm();
                break;
            case CTSUPPLIER_ACT_SUPPLIER_INSERT:
            case CTSUPPLIER_ACT_SUPPLIER_UPDATE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->supplierUpdate();
                break;
            case CTCNC_ACT_DISP_SUPPLIER_POPUP:
                $this->displaySupplierSelectPopup();
                break;
            case self::GET_SUPPLIERS:
                $this->getSuppliersController();
                exit;
            case CTSUPPLIER_ACT_SUPPLIER_SEARCH_FORM:
            default:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
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
            $this->setSessionParam('supplierParentIDField', $this->getParam('parentIDField'));
        } else {
            unset($_SESSION['supplierParentIDField']);
        }
        if ($this->getParam('parentDescField')) {
            $this->setSessionParam('supplierParentDescField', $this->getParam('parentDescField'));
        } else {
            unset($_SESSION['supplierParentDescField']);
        }
    }

    /**
     * Add/Edit Supplier
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function supplierForm()
    {
        $this->setMethodName('supplierForm');
        // initialisation stuff
        if ($this->getAction() == CTCNC_ACT_SUPPLIER_ADD) {
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
        if ($this->getAction() == CTCNC_ACT_SUPPLIER_EDIT) {
            $urlContactPopup = Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
                    'action'     => CTCNC_ACT_CONTACT_POPUP,
                    'supplierID' => $this->dsSupplier->getValue(DBEJSupplier::supplierID),
                    'htmlFmt'    => CT_HTML_FMT_POPUP
                )
            );
            $urlContactEdit  = Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
                    'action'  => CTCNC_ACT_CONTACT_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
            $this->template->set_var(
                array(
                    'contactName'     => Controller::htmlDisplayText(
                        $this->dsSupplier->getValue(DBEJSupplier::contactName)
                    ),
                    'contactID'       => $this->dsSupplier->getValue(DBEJSupplier::contactID),
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
                'cncAccountNo'        => Controller::htmlInputText(
                    $this->dsSupplier->getValue(DBESupplier::cncAccountNo)
                ),
                'cncAccountNoMessage' => Controller::htmlDisplayText(
                    $this->dsSupplier->getMessage(DBESupplier::cncAccountNo)
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
        if ($this->getAction() == CTCNC_ACT_SUPPLIER_EDIT) {
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
     * @throws Exception
     */
    function supplierFormPrepareAdd()
    {
        // If form error then preserve values in $this->dsSupplier else initialise new
        $this->setPageTitle(CTSUPPLIER_TXT_NEW_SUPPLIER);
        if (!$this->getFormError()) {
            $this->buSupplier->initialiseNewSupplier($this->dsSupplier);
        }
        return (Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTSUPPLIER_ACT_SUPPLIER_INSERT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        ));
    }

    /**
     * Prepare for edit
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function supplierFormPrepareEdit()
    {
        $this->setPageTitle(CTSUPPLIER_TXT_UPDATE_SUPPLIER);
        // if updating and not a form error then validate passed id and get row from DB
        if (!$this->getFormError()) {
            if (empty($this->getParam('supplierID'))) {
                $this->displayFatalError(CTSUPPLIER_MSG_SUPPLIERID_NOT_PASSED);
            }
            if (!$this->buSupplier->getSupplierByID(
                $this->getParam('supplierID'),
                $this->dsSupplier
            )) {
                $this->displayFatalError(CTSUPPLIER_MSG_SUPPLIER_NOT_FND);
            }
        }
        return (Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTSUPPLIER_ACT_SUPPLIER_UPDATE,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        ));
    }

    function parsePayMethodSelector($payMethodID)
    {
        $dsPayMethod = new DataSet($this);
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
                        )) ? CT_SELECTED : null
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
     * @throws Exception
     */
    function supplierUpdate()
    {
        $this->setMethodName('supplierUpdate');
        if (!$this->getParam('supplier')) {
            $this->displayFatalError(CTSUPPLIER_MSG_SUPPLIER_ARRAY_NOT_PASSED);
            return;
        }
        if (!$this->dsSupplier->populateFromArray($this->getParam('supplier'))) {
            $this->setFormErrorOn();
            if ($this->getAction() == CTSUPPLIER_ACT_SUPPLIER_INSERT) {
                $this->setAction(CTCNC_ACT_SUPPLIER_ADD);
            } else {
                $this->setAction(CTCNC_ACT_SUPPLIER_EDIT);
            }
            $this->setParam('supplierID', $this->dsSupplier->getValue(DBEJSupplier::supplierID));
            $this->supplierForm();
            exit;
        }
        $this->buSupplier->updateSupplier($this->dsSupplier);
        // force entry of a contact if none exists
        if ($this->dsSupplier->getValue(DBEJSupplier::contactID) == 0) {
            $this->setFormErrorMessage('Please create a contact or select an existing contact');
            $this->setAction(CTCNC_ACT_SUPPLIER_EDIT);
            $this->supplierForm();
            exit;
        } else {

            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
            if ($this->getSessionParam('supplierParentDescField')) {
                $urlNext = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'       => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                        'supplierName' => $this->dsSupplier->getPKValue(),
                        'htmlFmt'      => CT_HTML_FMT_POPUP
                    )
                );
            }
            header('Location: ' . $urlNext);
        }
    }

    /**
     * Display the search form
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setContainerTemplate();
        $this->setPageTitle("Supplier");
        $this->loadReactScript('SupplierComponent.js');
        $this->loadReactCSS('SupplierComponent.css');
        $this->template->setVar('CONTENTS', '<div id="reactMainActivity"></div>');
        $this->parsePage();
    }

    /**
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displaySupplierSelectPopup()
    {
        $this->setMethodName('displaySupplierSelectPopup');
        // A single slash means create new supplier
        $urlCreate = null;
        if ($this->getParam('supplierName'){0} == '/') {
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
            $this->getParam('supplierName'),
            $this->dsSupplier
        );
        if ($this->dsSupplier->rowCount() == 1) {
            $this->setTemplateFiles(
                'SupplierSelect',
                'SupplierSelectOne.inc'
            );
            $this->template->set_var(
                array(
                    'submitName' => addslashes($this->dsSupplier->getValue(DBEJSupplier::name)),
                    'supplierID' => $this->dsSupplier->getValue(DBEJSupplier::supplierID)
                )
            );
        } else {
            if ($this->dsSupplier->rowCount() == 0) {
                $this->template->set_var(
                    'supplierName',
                    $this->getParam('supplierName')
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
                            'supplierName' => Controller::htmlDisplayText(
                                ($this->dsSupplier->getValue(DBEJSupplier::name))
                            ),
                            'submitName'   => Controller::htmlDisplayText(
                                $this->dsSupplier->getValue(DBEJSupplier::name)
                            ),
                            'supplierID'   => $this->dsSupplier->getValue(DBEJSupplier::supplierID)
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

    private function getSuppliersController()
    {
        $repo = new \CNCLTD\Exceptions\infra\MySQLSupplierRepository();
        echo json_encode(["status" => "ok", "data" => $repo->getAllSuppliers()]);
    }
}