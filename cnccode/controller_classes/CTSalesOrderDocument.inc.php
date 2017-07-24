<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrderDocument.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTSALESORDERDOCUMENT_ACT_ADD', 'add');
define('CTSALESORDERDOCUMENT_ACT_EDIT', 'edit');
define('CTSALESORDERDOCUMENT_ACT_DELETE', 'delete');
define('CTSALESORDERDOCUMENT_ACT_UPDATE', 'update');

class CTSalesOrderDocument extends CTCNC
{
    var $dsSalesOrderDocument = '';
    var $buSalesOrderDocument = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buSalesOrderDocument = new BUSalesOrderDocument($this);
        $this->dsSalesOrderDocument = new DSForm($this);
        $this->dsSalesOrderDocument->copyColumnsFrom($this->buSalesOrderDocument->dbeSalesOrderDocument);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case CTSALESORDERDOCUMENT_ACT_EDIT:
            case CTSALESORDERDOCUMENT_ACT_ADD:
                $this->edit();
                break;
            case CTSALESORDERDOCUMENT_ACT_DELETE:
                $this->delete();
                break;
            case CTSALESORDERDOCUMENT_ACT_UPDATE:
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
        $dsSalesOrderDocument = &$this->dsSalesOrderDocument; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTSALESORDERDOCUMENT_ACT_EDIT) {
                $this->buSalesOrderDocument->getDocumentByID($_REQUEST['salesOrderDocumentID'], $dsSalesOrderDocument);
                $salesOrderDocumentID = $_REQUEST['salesOrderDocumentID'];
            } else {                                                                    // creating new
                $dsSalesOrderDocument->initialise();
                $dsSalesOrderDocument->setValue('salesOrderDocumentID', '0');
                $dsSalesOrderDocument->setValue('ordheadID', $_REQUEST['ordheadID']);
                $salesOrderDocumentID = '0';
            }
        } else {                                                                        // form validation error
            $dsSalesOrderDocument->initialise();
            $dsSalesOrderDocument->fetchNext();
            $salesOrderDocumentID = $dsSalesOrderDocument->getValue('salesOrderDocumentID');
        }
        if ($_REQUEST['action'] == CTSALESORDERDOCUMENT_ACT_EDIT && $this->buSalesOrderDocument->canDelete($_REQUEST['salesOrderDocumentID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDERDOCUMENT_ACT_DELETE,
                        'salesOrderDocumentID' => $salesOrderDocumentID
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
                    'action' => CTSALESORDERDOCUMENT_ACT_UPDATE,
                    'salesOrderDocumentID' => $salesOrderDocumentID
                )
            );
        $urlDisplayOrder =
            $this->buildLink(
                'SalesOrder.php',
                array(
                    'ordheadID' => $dsSalesOrderDocument->getValue('ordheadID'),
                    'action' => 'displaySalesOrder'
                )
            );

        $this->setPageTitle('Edit Document');
        $this->setTemplateFiles(
            array('SalesOrderDocumentEdit' => 'SalesOrderDocumentEdit.inc')
        );
        $this->template->set_var(
            array(
                'salesOrderDocumentID' => $salesOrderDocumentID,
                'ordheadID' => $dsSalesOrderDocument->getValue('ordheadID'),
                'filename' => Controller::htmlDisplayText($dsSalesOrderDocument->getValue('filename')),
                'description' => Controller::htmlInputText($dsSalesOrderDocument->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsSalesOrderDocument->getMessage('description')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'urlDisplayOrder' => $urlDisplayOrder,
                'txtDelete' => $txtDelete,
                'urlDisplay' => $urlDisplay
            )
        );
        $this->template->parse('CONTENTS', 'SalesOrderDocumentEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    function viewFile()
    {
        // Validation and setting of variables
        $this->setMethodName('viewFile');

        $this->buSalesOrderDocument->getDocumentByID(
            $_REQUEST['salesOrderDocumentID'],
            $dsSalesOrderDocument
        );

        header('Content-type: ' . $dsSalesOrderDocument->getValue('fileMimeType'));
        header('Content-Disposition: attachment; filename="' . $dsSalesOrderDocument->getValue('filename') . '"');
        print $dsSalesOrderDocument->getValue('file');

        exit;
    }

    function update()
    {
        $this->setMethodName('update');

        $dsSalesOrderDocument = &$this->dsSalesOrderDocument;
        $this->formError = (!$this->dsSalesOrderDocument->populateFromArray($_REQUEST['salesOrderDocument']));
        /*
        Need a file when creating new
        */
        if ($_FILES['userfile']['name'] == '' && $this->dsSalesOrderDocument->getValue('salesOrderDocumentID') == '') {
            $this->setFormErrorMessage('Please enter a file path');
        } else {
            /* uploading a file */

            if ($_FILES['userfile']['name'] != '' && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                $this->setFormErrorMessage('Document not loaded - is it bigger than 6 MBytes?');
            }

        }

        if ($this->formError) {
            if ($this->dsSalesOrderDocument->getValue('salesOrderDocumentID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTSALESORDERDOCUMENT_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTSALESORDERDOCUMENT_ACT_ADD;
            }
            $this->edit();
            exit;
        }

        $this->buSalesOrderDocument->updateDocument($this->dsSalesOrderDocument, $_FILES['userfile']);

        $urlNext =
            $this->buildLink(
                'SalesOrder.php',
                array
                (
                    'action' => 'displaySalesOrder',
                    'ordheadID' => $this->dsSalesOrderDocument->getValue('ordheadID')
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

        $this->buSalesOrderDocument->getDocumentByID($_REQUEST['salesOrderDocumentID'], $dsSalesOrderDocument);

        if (!$this->buSalesOrderDocument->deleteDocument($_REQUEST['salesOrderDocumentID'])) {
            $this->displayFatalError('Cannot delete this document');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    'SalesOrder.php',
                    array
                    (
                        'action' => 'displaySalesOrder',
                        'ordheadID' => $dsSalesOrderDocument->getValue('ordheadID')
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

}// end of class
?>