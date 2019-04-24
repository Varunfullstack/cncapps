<?php /**
 * Site controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define('CTSITE_MSG_CUSTOMERID_NOT_PASSED', 'CustomerID not passed');
define('CTSITE_MSG_SITENO_NOT_PASSED', 'Siteno not passed');
define('CTSITE_MSG_SITE_ARRAY_NOT_PASSED', 'Site array not passed');
define('CTSITE_MSG_NONE_FND', 'No sites found');
define('CTSITE_MSG_SITE_NOT_FND', 'Site not found');
// Actions
define('CTSITE_ACT_SITE_INSERT', 'insertSite');
define('CTSITE_ACT_SITE_UPDATE', 'updateSite');
// Page text
define('CTSITE_TXT_NEW_SITE', 'Create Site');
define('CTSITE_TXT_UPDATE_SITE', 'Update Site');

class CTSite extends CTCNC
{
    /** @var BUSite */
    private $buSite;
    /** @var DSForm */
    public $dsSite;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "sales"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buSite = new BUSite($this);
        $this->dsSite = new DSForm($this);    // new specialised dataset with form message support
        $this->dsSite->copyColumnsFrom($this->buSite->dbeJSite);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($_REQUEST['action']) {
            case CTCNC_ACT_SITE_ADD:
            case CTCNC_ACT_SITE_EDIT:
                $this->siteForm();
                break;
            case CTSITE_ACT_SITE_INSERT:
            case CTSITE_ACT_SITE_UPDATE:
                $this->siteUpdate();
                break;
            case CTCNC_ACT_SITE_POPUP:
                $this->displaySiteSelectPopup();
                break;
            default:
                $this->raiseError('Action not supported');
        }
    }

    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function setParentFormFields()
    {
        if (isset($_REQUEST['parentIDField'])) {
            $_SESSION['siteParentIDField'] = $_REQUEST['parentIDField'];
        }
        if (isset($_REQUEST['parentDescField'])) {
            $_SESSION['siteParentDescField'] = $_REQUEST['parentDescField'];
        }
    }

    /**
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displaySiteSelectPopup()
    {
        $this->setMethodName('displaySiteSelectPopup');
        if (!$_REQUEST['customerID']) {
            $this->raiseError('customerID not passed');
        }
        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'     => CTCNC_ACT_SITE_ADD,
                'customerID' => $_REQUEST['customerID'],
                'htmlFmt'    => CT_HTML_FMT_POPUP
            )
        );
        if ($_REQUEST['siteDesc']{0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        $this->buSite->getSitesByDescMatch($_REQUEST['customerID'], $_REQUEST['siteDesc'], $this->dsSite);
        if ($this->dsSite->rowCount() == 1) {
            $this->setTemplateFiles('SiteSelect', 'SiteSelectOne.inc');
        }
        if ($this->dsSite->rowCount() == 0) {
            $this->template->set_var('siteDesc', $_REQUEST['siteDesc']);
            $this->setTemplateFiles('SiteSelect', 'SiteSelectNone.inc');
        }
        if ($this->dsSite->rowCount() > 1) {
            $this->setTemplateFiles('SiteSelect', 'SiteSelectPopup.inc');
        }
        $this->template->set_var(
            array(
                'urlSiteCreate' => $urlCreate
            )
        );
// Parameters
        $this->setPageTitle('Site Selection');
        if ($this->dsSite->rowCount() > 0) {
            $this->template->set_block('SiteSelect', 'siteBlock', 'sites');
            while ($this->dsSite->fetchNext()) {
                $siteDesc = $this->dsSite->getValue(DBESite::add1) . ' ' .
                    $this->dsSite->getValue(DBESite::town) . ' ' . $this->dsSite->getValue(DBESite::postcode);
                $this->template->set_var(
                    array(
                        'siteDesc'        => Controller::htmlDisplayText(($siteDesc)),
                        'submitName'      => addslashes($siteDesc), //so double quotes don't mess javascript up
                        'siteNo'          => $this->dsSite->getValue(DBESite::siteNo),
                        // this is so the popup knows which field on the parent to update
                        'parentIDField'   => $_SESSION['siteParentIDField'],
                        'parentDescField' => $_SESSION['siteParentDescField']
                    )
                );
                $this->template->parse('sites', 'siteBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'SiteSelect', true);
        $this->parsePage();
    }

    /**
     * Add/Edit Site
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function siteForm()
    {
        $this->setMethodName('siteForm');
        // initialisation stuff
        $edit = false;
        if ($_REQUEST['action'] == CTCNC_ACT_SITE_ADD) {
            $urlSubmit = $this->siteFormPrepareAdd();
        } else {
            $urlSubmit = $this->siteFormPrepareEdit();
            $edit = TRUE;
        }
        // template
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $this->setTemplateFiles(
            array(
                'SiteEdit'        => 'SiteEdit.inc',
                'SiteEditJS'      => 'SiteEditJS.inc',
                'SiteEditContact' => 'SiteEditContact.inc' // only parsed if editing
            )
        );
        $urlContactEdit =
            Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
                    'action'     => CTCNC_ACT_CONTACT_EDIT,
                    'siteNo'     => $this->dsSite->getValue(DBESite::siteNo),
                    'customerID' => $this->dsSite->getValue(DBESite::customerID),
                    'htmlFmt'    => CT_HTML_FMT_POPUP
                )
            );
        $urlContactPopup =
            Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
                    'action'     => CTCNC_ACT_CONTACT_POPUP,
                    'siteNo'     => $this->dsSite->getValue(DBESite::siteNo),
                    'customerID' => $this->dsSite->getValue(DBESite::customerID),
                    'htmlFmt'    => CT_HTML_FMT_POPUP
                )
            );
        $this->template->set_var(
            array(
                'siteNo'            => $this->dsSite->getValue(DBESite::siteNo),
                'customerID'        => $this->dsSite->getValue(DBESite::customerID),
                'add1'              => Controller::htmlInputText($this->dsSite->getValue(DBESite::add1)),
                'add1Message'       => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::add1)),
                'add2'              => Controller::htmlInputText($this->dsSite->getValue(DBESite::add2)),
                'add2Message'       => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::add2)),
                'add3'              => Controller::htmlInputText($this->dsSite->getValue(DBESite::add3)),
                'add3Message'       => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::add3)),
                'town'              => Controller::htmlInputText($this->dsSite->getValue(DBESite::town)),
                'townMessage'       => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::town)),
                'county'            => Controller::htmlInputText($this->dsSite->getValue(DBESite::county)),
                'countyMessage'     => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::county)),
                'postcode'          => Controller::htmlInputText($this->dsSite->getValue(DBESite::postcode)),
                'postcodeMessage'   => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::postcode)),
                'phone'             => Controller::htmlInputText($this->dsSite->getValue(DBESite::phone)),
                'phoneMessage'      => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::phone)),
                'debtorCode'        => Controller::htmlInputText($this->dsSite->getValue(DBESite::debtorCode)),
                'debtorCodeMessage' => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::debtorCode)),
                'sageRef'           => Controller::htmlInputText($this->dsSite->getValue(DBESite::sageRef)),
                'sageRefMessage'    => Controller::htmlDisplayText($this->dsSite->getMessage(DBEJSite::sageRef)),
                'invContactID'      => $this->dsSite->getValue(DBEJSite::invoiceContactID),
                'invContactName'    => $this->dsSite->getValue(DBEJSite::invContactName),
                'delContactName'    => $this->dsSite->getValue(DBEJSite::delContactName),
                'delContactID'      => $this->dsSite->getValue(DBESite::deliverContactID),
                'urlContactPopup'   => $urlContactPopup,
                'urlContactEdit'    => $urlContactEdit,
                'urlSubmit'         => $urlSubmit
            )
        );
        if ($edit) {
            $this->template->parse('siteEditContact', 'SiteEditContact', true);
            $this->template->parse('siteEditJS', 'SiteEditJS', true);
        }
        $this->template->parse('CONTENTS', 'SiteEdit', true);
        $this->parsePage();
    }

    /**
     * Prepare for add
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function siteFormPrepareAdd()
    {
        // If form error then preserve values in $this->dsSite else initialise new
        $this->setPageTitle(CTSITE_TXT_NEW_SITE);
        if (!$this->getFormError()) {
            if (!isset($_REQUEST['customerID'])) {
                $this->displayFatalError(CTSITE_MSG_CUSTOMERID_NOT_PASSED);
            }
            $this->buSite->initialiseNewSite($_REQUEST['customerID'], $this->dsSite);
        }
        return (
        Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTSITE_ACT_SITE_INSERT,
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
     * @throws Exception
     */
    function siteFormPrepareEdit()
    {
        $this->setPageTitle(CTSITE_TXT_UPDATE_SITE);
        // if updating and not a form error then validate passed id and get row from DB
        if (!$this->getFormError()) {
            if (!isset($_REQUEST['customerID'])) {
                $this->displayFatalError(CTSITE_MSG_CUSTOMERID_NOT_PASSED);
            }
            if (!$this->buSite->getSiteByID($_REQUEST['customerID'], $_REQUEST['siteNo'], $this->dsSite)) {
                $this->displayFatalError(CTSITE_MSG_SITE_NOT_FND);
            }
        }
        return (
        Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => CTSITE_ACT_SITE_UPDATE,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        )
        );
    }

    /**
     * Update site record
     * @access private
     * @throws Exception
     */
    function siteUpdate()
    {
        $this->setMethodName('siteUpdate');
        if (!isset($_REQUEST['site'])) {
            $this->displayFatalError(CTSITE_MSG_SITE_ARRAY_NOT_PASSED);
            return;
        }
//		$this->buSite->initialiseNewSite($this->dsSite);
        if (!$this->dsSite->populateFromArray($_REQUEST['site'])) {
            $this->setFormErrorOn();
            switch ($_REQUEST['action']) {
                case CTSITE_ACT_SITE_INSERT:
                    $_REQUEST['action'] = CTCNC_ACT_SITE_ADD;
                    break;
                case CTSITE_ACT_SITE_UPDATE:
                    $_REQUEST['action'] = CTCNC_ACT_SITE_EDIT;
                    break;
            }
            $_REQUEST['customerID'] = $this->dsSite->getValue(DBESite::customerID);
            $_REQUEST['siteNo'] = $this->dsSite->getValue(DBESite::siteNo);
            $this->siteForm();
            exit;
        } else {                // Validation OK so update
            $this->buSite->updateSite($this->dsSite);
            // this forces update of contactID back through Javascript to parent HTML window
            $_REQUEST['customerID'] = $this->dsSite->getValue(DBESite::customerID);
            $_REQUEST['siteDesc'] = $this->dsSite->getValue(DBESite::siteNo);
            $this->displaySiteSelectPopup();
        }
    }
}
