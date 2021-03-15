<?php /**
 * Contact controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define(
    'CTCONTACT_MSG_CONTACTID_NOT_PASSED',
    'ContactID not passed'
);
define(
    'CTCONTACT_MSG_SUPPLIERID_NOT_PASSED',
    'SupplierID not passed'
);
define(
    'CTCONTACT_MSG_CONTACT_ARRAY_NOT_PASSED',
    'Contact array not passed'
);
define(
    'CTCONTACT_MSG_NONE_FND',
    'No contacts found'
);
define(
    'CTCONTACT_MSG_CONTACT_NOT_FND',
    'Contact not found'
);
// Actions
define(
    'CTCONTACT_ACT_CONTACT_INSERT',
    'insertContact'
);
define(
    'CTCONTACT_ACT_CONTACT_UPDATE',
    'updateContact'
);
// Page text
define(
    'CTCONTACT_TXT_NEW_CONTACT',
    'Create Contact'
);
define(
    'CTCONTACT_TXT_UPDATE_CONTACT',
    'Update Contact'
);
define(
    'CTCNC_ACT_DISP_CONTACT_POPUP',
    'CTCNC_ACT_DISP_CONTACT_POPUP'
);

class CTContact extends CTCNC
{
    /**
     * Dataset for contact record storage.
     *
     * @var     DSForm
     * @access  private
     */
    public $dsContact;

    /** @var BUContact */
    private $buContact;

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
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buContact = new BUContact($this);
        $this->dsContact = new DSForm($this);    // new specialised dataset with form message support
        $this->dsContact->copyColumnsFrom($this->buContact->dbeContact);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($this->getAction()) {
            case CTCNC_ACT_CONTACT_ADD:
            case CTCNC_ACT_CONTACT_EDIT:
                $this->checkPermissions(array(MAINTENANCE_PERMISSION, SALES_PERMISSION));
                $this->contactForm();
                break;
            case CTCONTACT_ACT_CONTACT_INSERT:
            case CTCONTACT_ACT_CONTACT_UPDATE:
                $this->checkPermissions(array(MAINTENANCE_PERMISSION, SALES_PERMISSION));
                $this->contactUpdate();
                break;
            case 'search':
                $itemsPerPage = 20;
                $page         = 1;
                $term         = '';
                if (isset($_REQUEST['term'])) {
                    $term = $_REQUEST['term'];
                }
                if (!isset($_REQUEST['customerId'])) {
                    throw new Exception('Customer ID is required');
                }
                $customerId = $_REQUEST['customerId'];
                $dsResult   = new DataSet($this);
                $this->buContact->getCustomerContactsByNameMatch($customerId, $term, $dsResult);
                $sites = [];
                while ($dsResult->fetchNext()) {

                    $sites[] = [
                        "id"    => $dsResult->getValue(DBEContact::contactID),
                        "label" => $dsResult->getValue(DBEContact::firstName) . ' ' . $dsResult->getValue(
                                DBEContact::lastName
                            ),
                        "value" => $dsResult->getValue(DBEContact::firstName) . ' ' . $dsResult->getValue(
                                DBEContact::lastName
                            ),
                    ];

                }
                echo json_encode($sites);
                break;
            case 'validation':
                $this->validateContacts();
                break;
            case CTCNC_ACT_DISP_CONTACT_POPUP:
            default:
                $this->displayContactSelectPopup();
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
            $this->setSessionParam('contactParentIDField', $this->getParam('parentIDField'));
        }
        if ($this->getParam('parentDescField')) {
            $this->setSessionParam('contactParentDescField', $this->getParam('parentDescField'));
        }
    }

    /**
     * Add/Edit Contact
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function contactForm()
    {
        $this->setMethodName('contactForm');
        // initialisation stuff
        if ($this->getAction() == CTCNC_ACT_CONTACT_ADD) {
            $urlSubmit = $this->contactFormPrepareAdd();
        } else {
            $urlSubmit = $this->contactFormPrepareEdit();
        }
        // template
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $this->setTemplateFiles(
            'ContactEdit',
            'ContactEdit.inc'
        );
        $this->template->set_block(
            'CustomerEdit',
            'selectSupportLevel',
            null
        );
        $this->template->set_block(
            'ContactEdit',
            'supportLevelBlock',
            'selectSupportLevel'
        );
        $buContact = new BUContact($this);
        $buContact->supportLevelDropDown(
            $this->dsContact->getValue(DBEContact::supportLevel),
            $this->template
        );
        $buCustomer = new BUCustomer($this);
        $this->template->set_var(
            array(
                'contactID'                            => $this->dsContact->getValue(DBEContact::contactID),
                'customerID'                           => $this->dsContact->getValue(DBEContact::customerID),
                'siteNo'                               => $this->dsContact->getValue(DBEContact::siteNo),
                'firstName'                            => Controller::htmlInputText(
                    $this->dsContact->getValue(DBEContact::firstName)
                ),
                'linkedInURL'                          => $this->dsContact->getValue(DBEContact::linkedInURL),
                'firstNameMessage'                     => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::firstName)
                ),
                'lastName'                             => Controller::htmlInputText(
                    $this->dsContact->getValue(DBEContact::lastName)
                ),
                'lastNameMessage'                      => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::lastName)
                ),
                'position'                             => Controller::htmlInputText(
                    $this->dsContact->getValue(DBEContact::position)
                ),
                'positionMessage'                      => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::position)
                ),
                'title'                                => Controller::htmlInputText(
                    $this->dsContact->getValue(DBEContact::title)
                ),
                'titleMessage'                         => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::title)
                ),
                'email'                                => Controller::htmlInputText(
                    $this->dsContact->getValue(DBEContact::email)
                ),
                'emailMessage'                         => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::email)
                ),
                'portalPasswordButtonClass'            => $this->dsContact->getValue(
                    DBEContact::portalPassword
                ) ? 'lockedIcon' : 'unlockedIcon',
                'failedLoginCount'                     => Controller::htmlDisplayText(
                    $this->dsContact->getValue(DBEContact::failedLoginCount)
                ),
                'failedLoginCountMessage'              => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::failedLoginCount)
                ),
                'notes'                                => Controller::htmlInputText(
                    $this->dsContact->getValue(DBEContact::notes)
                ),
                'notesMessage'                         => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::notes)
                ),
                'phone'                                => Controller::htmlDisplayText(
                    $this->dsContact->getValue(DBEContact::phone)
                ),
                'phoneMessage'                         => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::phone)
                ),
                'mobilePhone'                          => Controller::htmlDisplayText(
                    $this->dsContact->getValue(DBEContact::mobilePhone)
                ),
                'mobilePhoneMessage'                   => Controller::htmlDisplayText(
                    $this->dsContact->getMessage(DBEContact::mobilePhone)
                ),
                'accountsFlagChecked'                  => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::accountsFlag)
                ),
                'sendMailshotFlagChecked'              => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::sendMailshotFlag)
                ),
                'discontinuedFlagChecked'              => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::discontinuedFlag)
                ),
                'reviewUserFlagChecked'                => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::reviewUser)
                ),
                'initialLoggingEmailFlagChecked'       => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::initialLoggingEmailFlag)
                ),
                'workStartedEmailFlagChecked'          => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::workStartedEmailFlag)
                ),
                'workUpdatesEmailFlagChecked'          => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::workUpdatesEmailFlag)
                ),
                'fixedEmailFlagChecked'                => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::fixedEmailFlag)
                ),
                'pendingClosureEmailFlagChecked'       => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::pendingClosureEmailFlag)
                ),
                'closureEmailFlagChecked'              => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::closureEmailFlag)
                ),
                'othersInitialLoggingEmailFlagChecked' => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::othersInitialLoggingEmailFlag)
                ),
                'othersWorkStartedEmailFlagChecked'    => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::othersWorkStartedEmailFlag)
                ),
                'othersWorkUpdatesEmailFlagChecked'    => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::othersWorkUpdatesEmailFlag)
                ),
                'othersFixedEmailFlagChecked'          => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::othersFixedEmailFlag)
                ),
                'othersPendingClosureEmailFlagChecked' => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::othersPendingClosureEmailFlag)
                ),
                'othersClosureEmailFlagChecked'        => $this->getChecked(
                    $this->dsContact->getValue(DBEContact::othersClosureEmailFlag)
                ),
                'hrUserFlagChecked'                    => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::hrUser)
                ),
                'topUpValidation'                      => $buCustomer->hasPrepayContract(
                    DBEContact::customerID
                ) ? 'data-validation="atLeastOne"' : null,
                'mailshot2FlagDesc'                    => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot2FlagDesc)
                ),
                'mailshot3FlagDesc'                    => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot3FlagDesc)
                ),
                'mailshot4FlagDesc'                    => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot4FlagDesc)
                ),
                'mailshot8FlagDesc'                    => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot8FlagDesc)
                ),
                'mailshot9FlagDesc'                    => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot9FlagDesc)
                ),
                'mailshot11FlagDesc'                   => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::mailshot11FlagDesc)
                ),
                'mailshot2FlagChecked'                 => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::mailshot2Flag)
                ),
                'mailshot3FlagChecked'                 => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::mailshot3Flag)
                ),
                'mailshot4FlagChecked'                 => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::mailshot4Flag)
                ),
                'mailshot8FlagChecked'                 => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::mailshot8Flag)
                ),
                'mailshot9FlagChecked'                 => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::mailshot9Flag)
                ),
                'mailshot11FlagChecked'                => Controller::htmlChecked(
                    $this->dsContact->getValue(DBEContact::mailshot11Flag)
                ),
                'pendingLeaverFlagChecked'             => ($this->dsContact->getValue(
                        DBEContact::pendingLeaverFlag
                    ) == 'Y') ? CT_CHECKED : null,
                'pendingLeaverDate'                    => $this->dsContact->getValue(DBEContact::pendingLeaverDate),
                'urlSubmit'                            => $urlSubmit,
                //				'urlCancel' => $urlCancel
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ContactEdit',
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
    function contactFormPrepareAdd()
    {
        // If form error then preserve values in $this->dsContact else initialise new
        $this->setPageTitle(CTCONTACT_TXT_NEW_CONTACT);
        if (!$this->getFormError()) {
            if (!$this->getParam('supplierID') && !$this->getParam('customerID')) {
                $this->raiseError('supplierID or customerID not passed');
            }
            if ($this->getParam('customerID') && $this->getParam('siteNo') == "") {
                $this->raiseError('siteNo not passed');
            }
            $this->buContact->initialiseNewContact(
                $this->getParam('supplierID'),
                $this->getParam('customerID'),
                $this->getParam('siteNo'),
                $this->dsContact
            );
        }
        return Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTCONTACT_ACT_CONTACT_INSERT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
    }

    /**
     * Prepare for edit
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function contactFormPrepareEdit()
    {
        $this->setPageTitle(CTCONTACT_TXT_UPDATE_CONTACT);
        // if updating and not a form error then validate passed id and get row from DB
        if (!$this->getFormError()) {
            if (empty($this->getParam('contactID'))) {
                $this->displayFatalError(CTCONTACT_MSG_CONTACTID_NOT_PASSED);
            }
            if (!$this->buContact->getContactByID(
                $this->getParam('contactID'),
                $this->dsContact
            )) {
                $this->displayFatalError(CTCONTACT_MSG_CONTACT_NOT_FND);
            }
        }
        return Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTCONTACT_ACT_CONTACT_UPDATE,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
    }

    /**
     * Update contact record
     * @access private
     * @throws Exception
     */
    function contactUpdate()
    {
        $this->setMethodName('contactUpdate');
        if (!$this->getParam('contact')) {
            $this->displayFatalError(CTCONTACT_MSG_CONTACT_ARRAY_NOT_PASSED);
            return;
        }
        $this->buContact->initialiseUpdateContact($this->dsContact);
        if (!$this->dsContact->populateFromArray($this->getParam('contact'))) {
            $this->setFormErrorOn();
            if ($this->getAction() == CTCONTACT_ACT_CONTACT_INSERT) {
                $this->setAction(CTCNC_ACT_CONTACT_ADD);
            } else {
                $this->setAction(CTCNC_ACT_CONTACT_EDIT);
            }
            $this->setParam('contactID', $this->dsContact->getValue(DBEContact::contactID));
            $this->contactForm();
            exit;
        }
        $this->buContact->validateContact($this->dsContact);
        $this->buContact->updateContact($this->dsContact);
        // this forces update of contactID back through Javascript to parent HTML window
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'      => CTCNC_ACT_DISP_CONTACT_POPUP,
                'customerID'  => $this->dsContact->getValue(DBEContact::customerID),
                'siteNo'      => $this->dsContact->getValue(DBEContact::siteNo),
                'contactName' => $this->dsContact->getPKValue(),
                'htmlFmt'     => CT_HTML_FMT_POPUP
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displayContactSelectPopup()
    {
        $this->setMethodName('displayContactSelectPopup');
        if (!$this->getParam('supplierID') && !$this->getParam('customerID')) {
            $this->raiseError('supplierID or customerID not passed');
        }
        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'     => CTCNC_ACT_CONTACT_ADD,
                'supplierID' => $this->getParam('supplierID'),
                'customerID' => $this->getParam('customerID'),
                'siteNo'     => $this->getParam('siteNo'),
                'htmlFmt'    => CT_HTML_FMT_POPUP
            )
        );
        if ($this->getParam('contactName'){0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        $this->buContact->getCustomerContactsByNameMatch(
            $this->getParam('customerID'),
            $this->getParam('contactName'),
            $this->dsContact,
            $this->getParam('siteNo')
        );
        if ($this->dsContact->rowCount() == 1) {
            $this->setTemplateFiles(
                'ContactSelect',
                'ContactSelectOne.inc'
            );
        }
        if ($this->dsContact->rowCount() == 0) {
            $this->template->set_var(
                'contactName',
                $this->getParam('contactName')
            );
            $this->setTemplateFiles(
                'ContactSelect',
                'ContactSelectNone.inc'
            );
        }
        if ($this->dsContact->rowCount() > 1) {
            $this->setTemplateFiles(
                'ContactSelect',
                'ContactSelectPopup.inc'
            );
        }
        $this->template->set_var(
            array(
                'urlContactCreate' => $urlCreate
            )
        );
// Parameters
        $this->setPageTitle('Contact Selection');
        if ($this->dsContact->rowCount() > 0) {
            $this->template->set_block(
                'ContactSelect',
                'contactBlock',
                'contacts'
            );
            while ($this->dsContact->fetchNext()) {
                $name = $this->dsContact->getValue(DBEContact::firstName) . ' ' . $this->dsContact->getValue(
                        DBEContact::lastName
                    );
                $this->template->set_var(
                    array(
                        'contactName' => Controller::htmlDisplayText(($name)),
                        'submitName'  => addslashes($name), //so double quotes don't mess javascript up
                        'contactID'   => $this->dsContact->getValue(DBEContact::contactID)
                    )
                );
                $this->template->parse(
                    'contacts',
                    'contactBlock',
                    true
                );
            }
        }
        $this->template->set_var(
            array(
                'parentIDField'   => $_SESSION['contactParentIDField'],
                'parentDescField' => $_SESSION['contactParentDescField']
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ContactSelect',
            true
        );
        $this->parsePage();
    }

}
