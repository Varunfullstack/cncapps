<?php
/**
 * Prospect import controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
// Messages
define('CTPROSPECT_IMPORT_MSG_FILE_NOT_FND', 'File Not Found');
// Actions
define('CTPROSPECT_IMPORT_ACT_SELECT', 'select');
define('CTPROSPECT_IMPORT_ACT_GENERATE', 'generate');

class CTProspectImport extends CTCNC
{
    var $fileNames = '';        // array of file names created

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
        $roles = [
            "sales",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomer = new BUCustomer($this);
        $this->buSite = new BUSite($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
        switch ($_REQUEST['action']) {
            case CTPROSPECT_IMPORT_ACT_GENERATE:
                $this->generate();
                break;
            case CTPROSPECT_IMPORT_ACT_SELECT:
                $this->select();
                break;
            default:
                $this->select();
                break;
        }
    }

    /**
     * Display search form
     * @access private
     */
    function select()
    {
        $this->setMethodName('select');
        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTPROSPECT_IMPORT_ACT_GENERATE
            )
        );
        $this->setPageTitle('Prospect Import');
        $this->setTemplateFiles('ProspectImport', 'ProspectImport.inc');
        $this->template->set_var(
            array(
                'urlSubmit' => $urlSubmit
            )
        );
        // display results
        $this->template->parse('CONTENTS', 'ProspectImport', true);
        $this->parsePage();
    }

    function generate()
    {
        $this->setMethodName('generate');
        if ($_FILES['prospectFile']['name'] == '') {                // empty file name
            $this->setFormErrorMessage('You must specify a file to load');
            $this->select();
            exit();
        }
        if (!is_uploaded_file($_FILES['prospectFile']['tmp_name'])) {    // Possible hack?
            $this->setFormErrorMessage('Document Not Loaded');
            $this->select();
            exit();
        }
        if ($_FILES['prospectFile']['size'] == 0) {                    // something else wrong
            $this->setFormErrorMessage('Document Not Loaded');
            $this->select();
            exit();
        }
        $dbeCustomer = new DBECustomer($this);
        $dbeSite = new DBESite($this);
        $dbeContact = new DBEContact($this);

        $importDataset = new Dataset($this);
        $importDataset->loadFromCSVFile($_FILES['prospectFile']['tmp_name']);
        // validate fields
        $dsCustomer = new Dataset($this);
        $dsCustomer->copyColumnsFrom($this->buCustomer->dbeCustomer);
        $dsSite = new Dataset($this);
        $dsSite->copyColumnsFrom($this->buCustomer->dbeSite);
        $dsContact = new Dataset($this);
        $dsContact->copyColumnsFrom($this->buCustomer->dbeContact);
        $dsCustomer->setAddColumnsOff();
        $dsCustomer->replicate($importDataset);
        $errorMessage = '';
        while ($dsCustomer->fetchNext()) {
            $dsCustomer->setUpdateModeUpdate();
            $dsCustomer->setValue(DBECustomer::MailshotFlag, 'Y');
            $dsCustomer->setValue(DBECustomer::CreateDate, date('Y-m-d'));
            $dsCustomer->setValue(DBECustomer::ReferredFlag, 'Y');
            $dsCustomer->setValue(DBECustomer::PCXFlag, 'N');
            $dsCustomer->setValue(DBECustomer::CustomerTypeID, '47');
            $dsCustomer->setValue(DBECustomer::ProspectFlag, 'Y');
            $errorMessage .= $this->validateNotNull($dsCustomer);
            // ensure name doesn't exist on DB
            $dbeCustomer->setValue(DBECustomer::Name, $dsCustomer->getValue(DBECustomer::Name));
            $dbeCustomer->getRowsByColumn(DBECustomer::Name);
            if ($dbeCustomer->fetchNext()) {
                $errorMessage .=
                    'Duplicate customer name on line ' .
                    ($dsCustomer->ixCurrentRow + 2) .
                    ', value: ' . $dsCustomer->getValue(DBECustomer::Name) . '<BR>';
            }
            $dsCustomer->post();
        }

        $dsSite->setAddColumnsOff();
        $dsSite->replicate($importDataset);
        while ($dsSite->fetchNext()) {
            // ensure postcode doesn't exist on DB
            $dbeSite->setValue(DBESite::Postcode, $dsSite->getValue(DBESite::Postcode));
            $dbeSite->getRowsByColumn(DBESite::Postcode);
            if ($dbeSite->fetchNext()) {
                $errorMessage .=
                    'Duplicate postcode on line ' .
                    ($dsSite->ixCurrentRow + 2) .
                    ', value: ' . $dsSite->getValue(DBESite::Postcode) . '<BR>';
            }
            $errorMessage .= $this->validateNotNull($dsSite);
        }

        $dsContact->setAddColumnsOff();
        $dsContact->replicate($importDataset);
        $columns = $dsContact->colCount();
        while ($dsContact->fetchNext()) {
            $dsContact->setUpdateModeUpdate();
            $dsContact->setValue(DBEContact::SendMailshotFlag, 'Y');
            $dsContact->setValue('discontinuedFlag', 'N');
            $dsContact->setValue('mailshot1Flag', 'Y');                    // CNC address book
            $dsContact->setValue('mailshot2Flag', 'N');
            $dsContact->setValue('mailshot3Flag', 'Y');                    // newsletter
            $dsContact->setValue('mailshot4Flag', 'N');
            $dsContact->setValue('mailshot5Flag', 'N');
            $dsContact->setValue('mailshot6Flag', 'N');
            $dsContact->setValue('mailshot7Flag', 'N');
            $dsContact->setValue('mailshot8Flag', 'N');
            $dsContact->setValue('mailshot9Flag', 'N');
            $dsContact->setValue('mailshot10Flag', 'N');
            $dsContact->post();
            $errorMessage .= $this->validateNotNull($dsContact);
        }
        if ($errorMessage != '') {
            $errorMessage = 'File not imported, the following errors were found:<BR/><BR/>' . $errorMessage;
            $this->setFormErrorMessage($errorMessage);
            $this->select();
            exit();
        }

        /* second pass - insert to DB */
        $dsCustomer->initialise();
        $dsSite->initialise();
        $dsContact->initialise();
        while ($dsCustomer->fetchNext()) {
            $dsSite->fetchNext();
            $dsContact->fetchNext();

            // insert customer row
            $this->copyValues($dsCustomer, $dbeCustomer);
            $dbeCustomer->insertRow();

            // insert site row
            $this->copyValues($dsSite, $dbeSite);
            $dbeSite->setValue(DBESite::CustomerID, $dbeCustomer->getValue(DBECustomer::CustomerID));
            $dbeSite->insertRow();

            // go back to customer and update invoice and delivery site numbers
            $dbeCustomer->getRow();
            $dbeCustomer->setValue(DBECustomer::DeliverSiteNo, $dbeSite->getValue(DBESite::SiteNo));
            $dbeCustomer->setValue(DBECustomer::InvoiceSiteNo, $dbeSite->getValue(DBESite::SiteNo));
            $dbeCustomer->updateRow();

            // Insert contact row
            $this->copyValues($dsContact, $dbeContact);
            $dbeContact->setValue('customerID', $dbeCustomer->getValue(DBECustomer::CustomerID));
            $dbeContact->setValue('siteNo', $dbeSite->getValue(DBESite::SiteNo));
            $dbeContact->setValue('phone', '');
            $dbeContact->insertRow();

            // go back to site and update default contacts
            $dbeSite->getRow();
            $dbeSite->setValue(DBESite::SageRef, $this->buSite->getSageRef($dbeCustomer->getValue(DBECustomer::CustomerID)));
            $dbeSite->setValue(DBESite::DelContactID, $dbeContact->getValue('contactID'));
            $dbeSite->setValue(DBESite::InvContactID, $dbeContact->getValue('contactID'));
            $dbeSite->updateRow();
        }

        $this->setFormErrorMessage('Import Completed');
        $this->select();
        exit();
    }

    function validateNotNull(&$dataset)
    {
        $errorMessage = '';
        $columns = $dataset->colCount();
        for ($ix = 0; $ix < $columns; $ix++) {
            if ($dataset->getValue($ix) == '' && $dataset->getNull($ix) == DA_NOT_NULL) {
                $errorMessage .=
                    'Empty value on line ' . ($dataset->currentRowNo() + 2) . ', field ' . $dataset->getName($ix) . '<BR>';;
            }
        }
        return $errorMessage;
    }

    function copyValues(&$dataset, &$dbObject)
    {
        $columns = $dataset->colCount();
        for ($ix = 0; $ix < $columns; $ix++) {
            $dbObject->setValueNoCheckByColumnNumber($ix, $dataset->getValue($ix));
        }
    }
}// end of class
?>