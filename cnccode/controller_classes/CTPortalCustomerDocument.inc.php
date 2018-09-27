<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTPORTALCUSTOMERDOCUMENT_ACT_DISPLAY_LIST', 'documentList');
define('CTPORTALCUSTOMERDOCUMENT_ACT_ADD', 'add');
define('CTPORTALCUSTOMERDOCUMENT_ACT_EDIT', 'edit');
define('CTPORTALCUSTOMERDOCUMENT_ACT_DELETE', 'delete');
define('CTPORTALCUSTOMERDOCUMENT_ACT_UPDATE', 'update');

class CTPortalCustomerDocument extends CTCNC
{
    var $dsPortalCustomerDocument = '';
    var $buPortalCustomerDocument = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            'sales'
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buPortalCustomerDocument = new BUPortalCustomerDocument($this);
        $this->dsPortalCustomerDocument = new DSForm($this);
        $this->dsPortalCustomerDocument->copyColumnsFrom($this->buPortalCustomerDocument->dbePortalCustomerDocument);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case CTPORTALCUSTOMERDOCUMENT_ACT_EDIT:
            case CTPORTALCUSTOMERDOCUMENT_ACT_ADD:
                $this->edit();
                break;
            case CTPORTALCUSTOMERDOCUMENT_ACT_DELETE:
                $this->delete();
                break;
            case CTPORTALCUSTOMERDOCUMENT_ACT_UPDATE:
                $this->update();
                break;
            case 'viewFile':
                $this->viewFile();
                break;
        }
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsPortalCustomerDocument = &$this->dsPortalCustomerDocument; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTPORTALCUSTOMERDOCUMENT_ACT_EDIT) {
                $this->buPortalCustomerDocument->getDocumentByID($_REQUEST['portalCustomerDocumentID'],
                                                                 $dsPortalCustomerDocument);
                $portalCustomerDocumentID = $_REQUEST['portalCustomerDocumentID'];
            } else {                                                                    // creating new
                $dsPortalCustomerDocument->initialise();
                $dsPortalCustomerDocument->setValue('portalCustomerDocumentID', '0');
                $dsPortalCustomerDocument->setValue('customerID', $_REQUEST['customerID']);
                $portalCustomerDocumentID = '0';
            }
        } else {                                                                        // form validation error
            $dsPortalCustomerDocument->initialise();
            $dsPortalCustomerDocument->fetchNext();
            $portalCustomerDocumentID = $dsPortalCustomerDocument->getValue('portalCustomerDocumentID');
        }
        if ($_REQUEST['action'] == CTPORTALCUSTOMERDOCUMENT_ACT_EDIT && $this->buPortalCustomerDocument->canDelete($_REQUEST['portalCustomerDocumentID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTPORTALCUSTOMERDOCUMENT_ACT_DELETE,
                        'portalCustomerDocumentID' => $portalCustomerDocumentID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPORTALCUSTOMERDOCUMENT_ACT_UPDATE,
                    'portalCustomerDocumentID' => $portalCustomerDocumentID
                )
            );
        $urlDisplayCustomer =
            $this->buildLink(
                'Customer.php',
                array(
                    'customerID' => $this->dsPortalCustomerDocument->getValue('customerID'),
                    'action' => CTCNC_ACT_DISP_EDIT
                )
            );
        $this->setPageTitle('Edit Document');
        $this->setTemplateFiles(
            array('PortalCustomerDocumentEdit' => 'PortalCustomerDocumentEdit.inc')
        );
        $this->template->set_var(
            array(
                'customerID' => $dsPortalCustomerDocument->getValue('customerID'),
                'portalCustomerDocumentID' => $portalCustomerDocumentID,
                'filename' => Controller::htmlDisplayText($dsPortalCustomerDocument->getValue('filename')),
                'description' => Controller::htmlInputText($dsPortalCustomerDocument->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsPortalCustomerDocument->getMessage('description')),
                'leaversFormFlagChecked' => Controller::htmlChecked($dsPortalCustomerDocument->getValue('leaversFormFlag')),
                'leaversFormFlagMessage' => Controller::htmlDisplayText($dsPortalCustomerDocument->getMessage('leaversFormFlag')),
                'startersFormFlagChecked' => Controller::htmlChecked($dsPortalCustomerDocument->getValue('startersFormFlag')),
                'startersFormFlagMessage' => Controller::htmlDisplayText($dsPortalCustomerDocument->getMessage('startersFormFlag')),
                'mainContactOnlyFlagChecked' => Controller::htmlChecked($dsPortalCustomerDocument->getValue('mainContactOnlyFlag')),
                'mainContactOnlyFlagMessage' => Controller::htmlDisplayText($dsPortalCustomerDocument->getMessage('mainContactOnlyFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayCustomer' => $urlDisplayCustomer
            )
        );
        $this->template->parse('CONTENTS', 'PortalCustomerDocumentEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    function viewFile()
    {
        // Validation and setting of variables
        $this->setMethodName('viewFile');

        $this->buPortalCustomerDocument->getDocumentByID(
            $_REQUEST['portalCustomerDocumentID'],
            $dsPortalCustomerDocument
        );

        header('Content-type: ' . $dsPortalCustomerDocument->getValue('fileMimeType'));
        header('Content-Disposition: inline; filename="' . $dsPortalCustomerDocument->getValue('filename') . '"');
        print $dsPortalCustomerDocument->getValue('file');

        exit;
    }

    private function return_bytes($val)
    {
        $val = trim($val);

        $last = strtolower($val[strlen($val) - 1]);
        $val = substr($val, 0, -1); // necessary since PHP 7.1; otherwise optional

        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    function update()
    {
        $this->setMethodName('update');

        $this->formError = (!$this->dsPortalCustomerDocument->populateFromArray($_REQUEST['portalCustomerDocument']));
        /*
        Need a file when creating new
        */

        if ($_FILES['userfile']['name'] == '' && $this->dsPortalCustomerDocument->getValue('portalCustomerDocumentID') == '') {
            $this->setFormErrorMessage('Please enter a file path');
        } else {
            /* uploading a file */

            if ($_FILES['userfile']['name'] != '' && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                $this->setFormErrorMessage('Document not loaded - is it bigger than ' .
                                           $this->return_bytes(ini_get('upload_max_filesize')) / 1024 / 1024 . '
                                            MBytes ?');
            }

        }

        if ($this->formError) {
            if ($this->dsPortalCustomerDocument->getValue('portalCustomerDocumentID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTPORTALCUSTOMERDOCUMENT_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTPORTALCUSTOMERDOCUMENT_ACT_ADD;
            }
            $this->edit();
            exit;
        }

        $this->buPortalCustomerDocument->updateDocument($this->dsPortalCustomerDocument, $_FILES['userfile']);

        $urlNext =
            $this->buildLink(
                'Customer.php',
                array(
                    'customerID' => $this->dsPortalCustomerDocument->getValue('customerID'),
                    'action' => CTCNC_ACT_DISP_EDIT
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');

        $this->buPortalCustomerDocument->getDocumentByID($_REQUEST['portalCustomerDocumentID'],
                                                         $dsPortalCustomerDocument);

        if (!$this->buPortalCustomerDocument->deleteDocument($_REQUEST['portalCustomerDocumentID'])) {
            $this->displayFatalError('Cannot delete this document');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    'Customer.php',
                    array(
                        'customerID' => $dsPortalCustomerDocument->getValue('customerID'),
                        'action' => CTCNC_ACT_DISP_EDIT
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

}// end of class
?>