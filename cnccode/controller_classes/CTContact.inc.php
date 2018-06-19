<?php /**
 * Contact controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define('CTCONTACT_MSG_CONTACTID_NOT_PASSED', 'ContactID not passed');
define('CTCONTACT_MSG_SUPPLIERID_NOT_PASSED', 'SupplierID not passed');
define('CTCONTACT_MSG_CONTACT_ARRAY_NOT_PASSED', 'Contact array not passed');
define('CTCONTACT_MSG_NONE_FND', 'No contacts found');
define('CTCONTACT_MSG_CONTACT_NOT_FND', 'Contact not found');
// Actions
define('CTCONTACT_ACT_CONTACT_INSERT', 'insertContact');
define('CTCONTACT_ACT_CONTACT_UPDATE', 'updateContact');
// Page text
define('CTCONTACT_TXT_NEW_CONTACT', 'Create Contact');
define('CTCONTACT_TXT_UPDATE_CONTACT', 'Update Contact');

class CTContact extends CTCNC
{
    /**
     * Dataset for contact record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsContact = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
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
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($_REQUEST['action']) {
            case CTCNC_ACT_CONTACT_ADD:
            case CTCNC_ACT_CONTACT_EDIT:
                $this->checkPermissions(array(PHPLIB_PERM_MAINTENANCE, PHPLIB_PERM_SALES));
                $this->contactForm();
                break;
            case CTCONTACT_ACT_CONTACT_INSERT:
            case CTCONTACT_ACT_CONTACT_UPDATE:
                $this->checkPermissions(array(PHPLIB_PERM_MAINTENANCE, PHPLIB_PERM_SALES));
                $this->contactUpdate();
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
        if (isset($_REQUEST['parentIDField'])) {
            $_SESSION['contactParentIDField'] = $_REQUEST['parentIDField'];
        }
        if (isset($_REQUEST['parentDescField'])) {
            $_SESSION['contactParentDescField'] = $_REQUEST['parentDescField'];
        }
    }

    /**
     * Display the popup selector form
     * @access private
     */
    function displayContactSelectPopup()
    {
        $this->setMethodName('displayContactSelectPopup');
        if (($_REQUEST['supplierID'] == '') AND ($_REQUEST['customerID'] == '')) {
            $this->raiseError('supplierID or customerID not passed');
        }
        /*				for contact lookup from calls
                if (($_REQUEST['customerID']!='') AND ($_REQUEST['siteNo']=='')){
                    $this->raiseError('siteNo not passed');
                }
        */
        $urlCreate = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'     => CTCNC_ACT_CONTACT_ADD,
                'supplierID' => $_REQUEST['supplierID'],
                'customerID' => $_REQUEST['customerID'],
                'siteNo'     => $_REQUEST['siteNo'],
                'htmlFmt'    => CT_HTML_FMT_POPUP
            )
        );
        if ($_REQUEST['contactName']{0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        if ($_REQUEST['supplierID'] != '') {
            $this->buContact->getSupplierContactsByNameMatch(
                $_REQUEST['supplierID'],
                $_REQUEST['contactName'],
                $this->dsContact
            );
        } else {
            $this->buContact->getCustomerContactsByNameMatch(
                $_REQUEST['customerID'],
                $_REQUEST['siteNo'],
                $_REQUEST['contactName'],
                $this->dsContact
            );
        }
        if ($this->dsContact->rowCount() == 1) {
            $this->setTemplateFiles('ContactSelect', 'ContactSelectOne.inc');
        }
        if ($this->dsContact->rowCount() == 0) {
            $this->template->set_var('contactName', $_REQUEST['contactName']);
            $this->setTemplateFiles('ContactSelect', 'ContactSelectNone.inc');
        }
        if ($this->dsContact->rowCount() > 1) {
            $this->setTemplateFiles('ContactSelect', 'ContactSelectPopup.inc');
        }
        $this->template->set_var(
            array(
                'urlContactCreate' => $urlCreate
            )
        );
// Parameters
        $this->setPageTitle('Contact Selection');
        if ($this->dsContact->rowCount() > 0) {
            $this->template->set_block('ContactSelect', 'contactBlock', 'contacts');
            while ($this->dsContact->fetchNext()) {
                $name = $this->dsContact->getValue("firstName") . ' ' . $this->dsContact->getValue("lastName");
                $this->template->set_var(
                    array(
                        'contactName' => Controller::htmlDisplayText(($name)),
                        'submitName'  => addslashes($name), //so dblquotes don't mess javascript up
                        'contactID'   => $this->dsContact->getValue("contactID")
                    )
                );
                $this->template->parse('contacts', 'contactBlock', true);
            }
        }
        $this->template->set_var(
            array(
                'parentIDField'   => $_SESSION['contactParentIDField'],
                'parentDescField' => $_SESSION['contactParentDescField']
            )
        );
        $this->template->parse('CONTENTS', 'ContactSelect', true);
        $this->parsePage();
    }

    /**
     * Add/Edit Contact
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function contactForm()
    {
        $this->setMethodName('contactForm');
        // initialisation stuff
        if ($_REQUEST['action'] == CTCNC_ACT_CONTACT_ADD) {
            $urlSubmit = $this->contactFormPrepareAdd();
        } else {
            $urlSubmit = $this->contactFormPrepareEdit();
        }
        // template
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $this->setTemplateFiles('ContactEdit', 'ContactEdit.inc');
        $this->template->set_var(
            array(
                'contactID'                   => $this->dsContact->getValue('contactID'),
                'supplierID'                  => $this->dsContact->getValue('supplierID'),
                'customerID'                  => $this->dsContact->getValue('customerID'),
                'siteNo'                      => $this->dsContact->getValue('siteNo'),
                'firstName'                   => Controller::htmlInputText($this->dsContact->getValue('firstName')),
                'firstNameMessage'            => Controller::htmlDisplayText($this->dsContact->getMessage('firstName')),
                'lastName'                    => Controller::htmlInputText($this->dsContact->getValue('lastName')),
                'lastNameMessage'             => Controller::htmlDisplayText($this->dsContact->getMessage('lastName')),
                'position'                    => Controller::htmlInputText($this->dsContact->getValue('position')),
                'positionMessage'             => Controller::htmlDisplayText($this->dsContact->getMessage('position')),
                'title'                       => Controller::htmlInputText($this->dsContact->getValue('title')),
                'titleMessage'                => Controller::htmlDisplayText($this->dsContact->getMessage('title')),
                'email'                       => Controller::htmlInputText($this->dsContact->getValue('email')),
                'emailMessage'                => Controller::htmlDisplayText($this->dsContact->getMessage('email')),
                'portalPassword'              => Controller::htmlInputText($this->dsContact->getValue('portalPassword')),
                'portalPasswordMessage'       => Controller::htmlDisplayText($this->dsContact->getMessage('portalPassword')),
                'failedLoginCount'            => Controller::htmlDisplayText($this->dsContact->getValue('failedLoginCount')),
                'failedLoginCountMessage'     => Controller::htmlDisplayText($this->dsContact->getMessage('failedLoginCount')),
                'notes'                       => Controller::htmlInputText($this->dsContact->getValue('notes')),
                'notesMessage'                => Controller::htmlDisplayText($this->dsContact->getMessage('notes')),
                'phone'                       => Controller::htmlDisplayText($this->dsContact->getValue('phone')),
                'phoneMessage'                => Controller::htmlDisplayText($this->dsContact->getMessage('phone')),
                'mobilePhone'                 => Controller::htmlDisplayText($this->dsContact->getValue('mobilePhone')),
                'mobilePhoneMessage'          => Controller::htmlDisplayText($this->dsContact->getMessage('mobilePhone')),
                'fax'                         => Controller::htmlDisplayText($this->dsContact->getValue('fax')),
                'faxMessage'                  => Controller::htmlDisplayText($this->dsContact->getMessage('fax')),
                'workStartedEmailFlagChecked' => Controller::htmlChecked($this->dsContact->getValue('workStartedEmailFlag')),
                'autoCloseEmailFlagChecked'   => Controller::htmlChecked($this->dsContact->getValue('autoCloseEmailFlag')),
                'accountsFlagChecked'         => Controller::htmlChecked($this->dsContact->getValue('accountsFlag')),
                'sendMailshotFlagChecked'     => Controller::htmlChecked($this->dsContact->getValue('sendMailshotFlag')),
                'discontinuedFlagChecked'     => Controller::htmlChecked($this->dsContact->getValue('discontinuedFlag')),
                'mailshot2FlagDesc'           => Controller::htmlDisplayText($dsHeader->getValue('mailshot2FlagDesc')),
                'mailshot3FlagDesc'           => Controller::htmlDisplayText($dsHeader->getValue('mailshot3FlagDesc')),
                'mailshot4FlagDesc'           => Controller::htmlDisplayText($dsHeader->getValue('mailshot4FlagDesc')),
                'mailshot8FlagDesc'           => Controller::htmlDisplayText($dsHeader->getValue('mailshot8FlagDesc')),
                'mailshot9FlagDesc'           => Controller::htmlDisplayText($dsHeader->getValue('mailshot9FlagDesc')),
                'mailshot11FlagDesc'          => Controller::htmlDisplayText($dsHeader->getValue('mailshot11FlagDesc')),
                'mailshot2FlagChecked'        => Controller::htmlChecked($this->dsContact->getValue('mailshot2Flag')),
                'mailshot3FlagChecked'        => Controller::htmlChecked($this->dsContact->getValue('mailshot3Flag')),
                'mailshot4FlagChecked'        => Controller::htmlChecked($this->dsContact->getValue('mailshot4Flag')),
                'mailshot8FlagChecked'        => Controller::htmlChecked($this->dsContact->getValue('mailshot8Flag')),
                'mailshot9FlagChecked'        => Controller::htmlChecked($this->dsContact->getValue('mailshot9Flag')),
                'mailshot11FlagChecked'       => Controller::htmlChecked($this->dsContact->getValue('mailshot11Flag')),
                'urlSubmit'                   => $urlSubmit,
                //				'urlCancel' => $urlCancel
            )
        );
        $this->template->parse('CONTENTS', 'ContactEdit', true);
        $this->parsePage();
    }

    /**
     * Prepare for add
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function contactFormPrepareAdd()
    {
        // If form error then preserve values in $this->dsContact else initialise new
        $this->setPageTitle(CTCONTACT_TXT_NEW_CONTACT);
        if (!$this->getFormError()) {
            if (($_REQUEST['supplierID'] == '') AND ($_REQUEST['customerID'] == '')) {
                $this->raiseError('supplierID or customerID not passed');
            }
            if (($_REQUEST['customerID'] != '') AND ($_REQUEST['siteNo'] == '')) {
                $this->raiseError('siteNo not passed');
            }
            $this->buContact->initialiseNewContact($_REQUEST['supplierID'],
                                                   $_REQUEST['customerID'],
                                                   $_REQUEST['siteNo'],
                                                   $this->dsContact);
        }
        return (
        $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTCONTACT_ACT_CONTACT_INSERT,
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
    function contactFormPrepareEdit()
    {
        $this->setPageTitle(CTCONTACT_TXT_UPDATE_CONTACT);
        // if updating and not a form error then validate passed id and get row from DB
        if (!$this->getFormError()) {
            if (empty($_REQUEST['contactID'])) {
                $this->displayFatalError(CTCONTACT_MSG_CONTACTID_NOT_PASSED);
            }
            if (!$this->buContact->getContactByID($_REQUEST['contactID'], $this->dsContact)) {
                $this->displayFatalError(CTCONTACT_MSG_CONTACT_NOT_FND);
            }
        }
        return (
        $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTCONTACT_ACT_CONTACT_UPDATE,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        )
        );
    }

    function parsePayMethodSelector($payMethodID)
    {
        $this->buContact->getAllPayMethods($dsPayMethod);
        $this->template->set_block('ContactEdit', 'payMethodBlock', 'payMethods');
        while ($dsPayMethod->fetchNext()) {
            $this->template->set_var(
                array(
                    'payMethodDescription' => $dsPayMethod->getValue('description'),
                    'payMethodID'          => $dsPayMethod->getValue('payMethodID'),
                    'payMethodSelected'    => ($payMethodID == $dsPayMethod->getValue('payMethodID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse('payMethods', 'payMethodBlock', true);
        }
    }

    /**
     * Update contact record
     * @access private
     */
    function contactUpdate()
    {
        $this->setMethodName('contactUpdate');
        if (!isset($_REQUEST['contact'])) {
            $this->displayFatalError(CTCONTACT_MSG_CONTACT_ARRAY_NOT_PASSED);
            return;
        }
        $this->buContact->initialiseUpdateContact($this->dsContact);
        if (!$this->dsContact->populateFromArray($_REQUEST['contact'])) {
            $this->setFormErrorOn();
            if ($_REQUEST['action'] == CTCONTACT_ACT_CONTACT_INSERT) {
                $_REQUEST['action'] = CTCNC_ACT_CONTACT_ADD;
            } else {
                $_REQUEST['action'] = CTCNC_ACT_CONTACT_EDIT;
            }
            $_REQUEST['contactID'] = $this->dsContact->getValue('contactID');
            $this->contactForm();
            exit;
        }
        $this->buContact->updateContact($this->dsContact);
        // this forces update of contactID back through Javascript to parent HTML window
        $urlNext = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'      => CTCNC_ACT_DISP_CONTACT_POPUP,
                'supplierID'  => $this->dsContact->getValue('supplierID'),
                'customerID'  => $this->dsContact->getValue('customerID'),
                'siteNo'      => $this->dsContact->getValue('siteNo'),
                'contactName' => $this->dsContact->getPKValue(),
                'htmlFmt'     => CT_HTML_FMT_POPUP
            )
        );
        header('Location: ' . $urlNext);
    }
}// end of class
?>