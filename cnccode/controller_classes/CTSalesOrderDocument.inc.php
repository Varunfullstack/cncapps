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
    /** @var DSForm */
    public $dsSalesOrderDocument;
    /** @var BUSalesOrderDocument */
    public $buSalesOrderDocument;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buSalesOrderDocument = new BUSalesOrderDocument($this);
        $this->dsSalesOrderDocument = new DSForm($this);
        $this->dsSalesOrderDocument->copyColumnsFrom($this->buSalesOrderDocument->dbeSalesOrderDocument);
        $this->dsSalesOrderDocument->setPK($this->buSalesOrderDocument->dbeSalesOrderDocument->getPK());
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTSALESORDERDOCUMENT_ACT_EDIT:
            case CTSALESORDERDOCUMENT_ACT_ADD:
                $this->checkPermissions(SALES_PERMISSION);
                $this->edit();
                break;
            case CTSALESORDERDOCUMENT_ACT_DELETE:
                $this->checkPermissions(SALES_PERMISSION);
                $this->delete();
                break;
            case CTSALESORDERDOCUMENT_ACT_UPDATE:
                $this->checkPermissions(SALES_PERMISSION);
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
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsSalesOrderDocument = &$this->dsSalesOrderDocument; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTSALESORDERDOCUMENT_ACT_EDIT) {
                $this->buSalesOrderDocument->getDocumentByID(
                    $this->getParam('salesOrderDocumentID'),
                    $dsSalesOrderDocument
                );
                $salesOrderDocumentID = $this->getParam('salesOrderDocumentID');
            } else {                                                                    // creating new
                $dsSalesOrderDocument->initialise();
                $dsSalesOrderDocument->setValue(DBESalesOrderDocument::ordheadID, $this->getParam('ordheadID'));
                $salesOrderDocumentID = null;
            }
        } else {                                                                        // form validation error
            $dsSalesOrderDocument->initialise();
            $dsSalesOrderDocument->fetchNext();
            $salesOrderDocumentID = $dsSalesOrderDocument->getValue(DBESalesOrderDocument::salesOrderDocumentID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTSALESORDERDOCUMENT_ACT_EDIT) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'               => CTSALESORDERDOCUMENT_ACT_DELETE,
                    'salesOrderDocumentID' => $salesOrderDocumentID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'               => CTSALESORDERDOCUMENT_ACT_UPDATE,
                    'salesOrderDocumentID' => $salesOrderDocumentID
                )
            );
        $urlDisplayOrder =
            Controller::buildLink(
                'SalesOrder.php',
                array(
                    'ordheadID' => $dsSalesOrderDocument->getValue(DBESalesOrderDocument::ordheadID),
                    'action'    => 'displaySalesOrder'
                )
            );

        $this->setPageTitle('Edit Document');
        $this->setTemplateFiles(
            array('SalesOrderDocumentEdit' => 'SalesOrderDocumentEdit.inc')
        );
        $this->template->set_var(
            array(
                'salesOrderDocumentID' => $salesOrderDocumentID,
                'ordheadID'            => $dsSalesOrderDocument->getValue(DBESalesOrderDocument::ordheadID),
                'filename'             => Controller::htmlDisplayText(
                    $dsSalesOrderDocument->getValue(DBESalesOrderDocument::filename)
                ),
                'description'          => Controller::htmlInputText(
                    $dsSalesOrderDocument->getValue(DBESalesOrderDocument::description)
                ),
                'descriptionMessage'   => Controller::htmlDisplayText(
                    $dsSalesOrderDocument->getMessage(DBESalesOrderDocument::description)
                ),
                'createdDate'          => $dsSalesOrderDocument->getValue(
                    DBESalesOrderDocument::createdDate
                ) ? $dsSalesOrderDocument->getValue(DBESalesOrderDocument::createdDate) : (new DateTime())->format(
                    DATE_MYSQL_DATETIME
                ),
                'createdUserID'        => $dsSalesOrderDocument->getValue(
                    DBESalesOrderDocument::createdUserID
                ) ? $dsSalesOrderDocument->getValue(DBESalesOrderDocument::createdUserID) : $this->userID,
                'urlUpdate'            => $urlUpdate,
                'urlDelete'            => $urlDelete,
                'urlDisplayOrder'      => $urlDisplayOrder,
                'txtDelete'            => $txtDelete,
            )
        );
        $this->template->parse('CONTENTS', 'SalesOrderDocumentEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    function viewFile()
    {
        // Validation and setting of variables
        $this->setMethodName('viewFile');
        $dsSalesOrderDocument = new DataSet($this);
        $this->buSalesOrderDocument->getDocumentByID(
            $this->getParam('salesOrderDocumentID'),
            $dsSalesOrderDocument
        );

        header('Content-type: ' . $dsSalesOrderDocument->getValue(DBESalesOrderDocument::fileMimeType));
        header(
            'Content-Disposition: attachment; filename="' . $dsSalesOrderDocument->getValue(
                DBESalesOrderDocument::filename
            ) . '"'
        );
        print $dsSalesOrderDocument->getValue(DBESalesOrderDocument::file);

        exit;
    }

    /**
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsSalesOrderDocument->populateFromArray($this->getParam('salesOrderDocument')));

        /*
        Need a file when creating new
        */
        if (!$_FILES['userfile']['name'] && !$this->dsSalesOrderDocument->getValue(
                DBESalesOrderDocument::salesOrderDocumentID
            )) {
            $this->setFormErrorMessage('Please enter a file path');
        } else {
            /* uploading a file */
            if ($_FILES['userfile']['name'] && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                $this->setFormErrorMessage('Document not loaded - is it bigger than 6 MBytes?');
            }

        }

        if ($this->formError) {
            if (!$this->dsSalesOrderDocument->getValue(DBESalesOrderDocument::salesOrderDocumentID)) {
                $this->setAction(CTSALESORDERDOCUMENT_ACT_EDIT);
            } else {
                $this->setAction(CTSALESORDERDOCUMENT_ACT_ADD);
            }
            $this->edit();
            exit;
        }
        $this->buSalesOrderDocument->updateDocument($this->dsSalesOrderDocument, $_FILES['userfile']);

        $urlNext =
            Controller::buildLink(
                'SalesOrder.php',
                array
                (
                    'action'    => 'displaySalesOrder',
                    'ordheadID' => $this->dsSalesOrderDocument->getValue(DBESalesOrderDocument::ordheadID)
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        $dsSalesOrderDocument = new DataSet($this);
        $this->buSalesOrderDocument->getDocumentByID($this->getParam('salesOrderDocumentID'), $dsSalesOrderDocument);

        if (!$this->buSalesOrderDocument->deleteDocument($this->getParam('salesOrderDocumentID'))) {
            $this->displayFatalError('Cannot delete this document');
            exit;
        } else {
            $urlNext = Controller::buildLink(
                'SalesOrder.php',
                array
                (
                    'action'    => 'displaySalesOrder',
                    'ordheadID' => $dsSalesOrderDocument->getValue(DBESalesOrderDocument::ordheadID)
                )
            );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}
