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
    /** @var DSForm */
    public $dsSite;
    /** @var BUSite */
    private $buSite;

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
        switch ($this->getAction()) {
            case CTCNC_ACT_SITE_ADD:
            case CTCNC_ACT_SITE_EDIT:
                $this->siteForm();
                break;
            case CTSITE_ACT_SITE_INSERT:
            case CTSITE_ACT_SITE_UPDATE:
                $this->siteUpdate();
                break;
            case 'search':
                $itemsPerPage = 20;
                $page = 1;
                $term = '';
                if (isset($_REQUEST['term'])) {
                    $term = $_REQUEST['term'];
                }

                if (!isset($_REQUEST['customerId'])) {
                    throw new Exception('Customer ID is required');
                }

                $customerId = $_REQUEST['customerId'];

                $dsResult = new DataSet($this);
                $this->buSite->getSitesByDescMatch($customerId, $term, $dsResult);
                $sites = [];
                while ($dsResult->fetchNext()) {

                    $sites[] = [
                        "id"    => $dsResult->getValue(DBESite::siteNo),
                        "label" => $dsResult->getValue(DBESite::add1) . ' ' . $dsResult->getValue(
                                DBESite::town
                            ) . ' ' . $dsResult->getValue(DBESite::postcode),
                        "value" =>$dsResult->getValue(DBESite::add1) . ' ' . $dsResult->getValue(
                                DBESite::town
                            ) . ' ' . $dsResult->getValue(DBESite::postcode),
                    ];

                }
                echo json_encode($sites);
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
        if ($this->getParam('parentIDField')) {
            $this->setSessionParam('siteParentIDField', $this->getParam('parentIDField'));
        }
        if ($this->getParam('parentDescField')) {
            $this->setSessionParam('siteParentDescField', $this->getParam('parentDescField'));
        }
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
        if ($this->getAction() == CTCNC_ACT_SITE_ADD) {
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
            if (!$this->getParam('customerID')) {
                $this->displayFatalError(CTSITE_MSG_CUSTOMERID_NOT_PASSED);
            }
            $this->buSite->initialiseNewSite($this->getParam('customerID'), $this->dsSite);
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
            if (!$this->getParam('customerID')) {
                $this->displayFatalError(CTSITE_MSG_CUSTOMERID_NOT_PASSED);
            }
            if (!$this->buSite->getSiteByID($this->getParam('customerID'), $this->getParam('siteNo'), $this->dsSite)) {
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
        if (!$this->getParam('site')) {
            $this->displayFatalError(CTSITE_MSG_SITE_ARRAY_NOT_PASSED);
            return;
        }
//		$this->buSite->initialiseNewSite($this->dsSite);
        if (!$this->dsSite->populateFromArray($this->getParam('site'))) {
            $this->setFormErrorOn();
            switch ($this->getAction()) {
                case CTSITE_ACT_SITE_INSERT:
                    $this->setAction(CTCNC_ACT_SITE_ADD);
                    break;
                case CTSITE_ACT_SITE_UPDATE:
                    $this->setAction(CTCNC_ACT_SITE_EDIT);
                    break;
            }
            $this->setParam('customerID', $this->dsSite->getValue(DBESite::customerID));
            $this->setParam('siteNo', $this->dsSite->getValue(DBESite::siteNo));
            $this->siteForm();
            exit;
        } else {                // Validation OK so update
            $this->buSite->updateSite($this->dsSite);
            // this forces update of contactID back through Javascript to parent HTML window
            $this->setParam('customerID', $this->dsSite->getValue(DBESite::customerID));
            $this->setParam('siteDesc', $this->dsSite->getValue(DBESite::siteNo));
            $this->displaySiteSelectPopup();
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
        if (!$this->getParam('customerID')) {
            $this->raiseError('customerID not passed');
        }
        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'     => CTCNC_ACT_SITE_ADD,
                'customerID' => $this->getParam('customerID'),
                'htmlFmt'    => CT_HTML_FMT_POPUP
            )
        );
        if ($this->getParam('siteDesc'){0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        $this->buSite->getSitesByDescMatch($this->getParam('customerID'), $this->getParam('siteDesc'), $this->dsSite);
        if ($this->dsSite->rowCount() == 1) {
            $this->setTemplateFiles('SiteSelect', 'SiteSelectOne.inc');
        }
        if ($this->dsSite->rowCount() == 0) {
            $this->template->set_var('siteDesc', $this->getParam('siteDesc'));
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
}
