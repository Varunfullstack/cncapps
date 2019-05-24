<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPortalDocument.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTPORTALDOCUMENT_ACT_DISPLAY_LIST', 'documentList');
define('CTPORTALDOCUMENT_ACT_ADD', 'add');
define('CTPORTALDOCUMENT_ACT_EDIT', 'edit');
define('CTPORTALDOCUMENT_ACT_DELETE', 'delete');
define('CTPORTALDOCUMENT_ACT_UPDATE', 'update');

class CTPortalDocument extends CTCNC
{
    /** @var DSForm */
    public $dsPortalDocument;
    /** @var BUPortalDocument */
    public $buPortalDocument;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "accounts"
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buPortalDocument = new BUPortalDocument($this);
        $this->dsPortalDocument = new DSForm($this);
        $this->dsPortalDocument->copyColumnsFrom($this->buPortalDocument->dbePortalDocument);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTPORTALDOCUMENT_ACT_EDIT:
            case CTPORTALDOCUMENT_ACT_ADD:
                $this->edit();
                break;
            case CTPORTALDOCUMENT_ACT_DELETE:
                $this->delete();
                break;
            case CTPORTALDOCUMENT_ACT_UPDATE:
                $this->update();
                break;
            case 'viewFile':
                $this->viewFile();
                break;
            default:
                header('Location: /');
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
        $dsPortalDocument = &$this->dsPortalDocument; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTPORTALDOCUMENT_ACT_EDIT) {
                $this->buPortalDocument->getDocumentByID($this->getParam('portalDocumentID'), $dsPortalDocument);
                $portalDocumentID = $this->getParam('portalDocumentID');
            } else {                                                                    // creating new
                $dsPortalDocument->initialise();
                $dsPortalDocument->setValue(DBEPortalDocument::portalDocumentID, null);
                $portalDocumentID = null;
            }
        } else {                                                                        // form validation error
            $dsPortalDocument->initialise();
            $dsPortalDocument->fetchNext();
            $portalDocumentID = $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTPORTALDOCUMENT_ACT_EDIT) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'           => CTPORTALDOCUMENT_ACT_DELETE,
                        'portalDocumentID' => $portalDocumentID
                    )
                );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'           => CTPORTALDOCUMENT_ACT_UPDATE,
                    'portalDocumentID' => $portalDocumentID
                )
            );
        $urlDisplayHeader =
            Controller::buildLink(
                'Header.php',
                array()
            );
        $this->setPageTitle('Edit Document');
        $this->setTemplateFiles(
            array('PortalDocumentEdit' => 'PortalDocumentEdit.inc')
        );
        $this->template->set_var(
            array(
                'portalDocumentID'              => $portalDocumentID,
                'filename'                      => Controller::htmlDisplayText(
                    $dsPortalDocument->getValue(DBEPortalDocument::filename)
                ),
                'description'                   => Controller::htmlInputText(
                    $dsPortalDocument->getValue(DBEPortalDocument::description)
                ),
                'descriptionMessage'            => Controller::htmlDisplayText(
                    $dsPortalDocument->getMessage(DBEPortalDocument::description)
                ),
                'mainContactOnlyFlagChecked'    => Controller::htmlChecked(
                    $dsPortalDocument->getValue(DBEPortalDocument::mainContactOnlyFlag)
                ),
                'mainContactOnlyFlagMessage'    => Controller::htmlDisplayText(
                    $dsPortalDocument->getMessage(DBEPortalDocument::mainContactOnlyFlag)
                ),
                'requiresAcceptanceFlagChecked' => Controller::htmlChecked(
                    $dsPortalDocument->getValue(DBEPortalDocument::requiresAcceptanceFlag)
                ),
                'requiresAcceptanceFlagMessage' => Controller::htmlDisplayText(
                    $dsPortalDocument->getMessage(DBEPortalDocument::requiresAcceptanceFlag)
                ),
                'urlUpdate'                     => $urlUpdate,
                'urlDelete'                     => $urlDelete,
                'urlDisplayHeader'              => $urlDisplayHeader,
                'txtDelete'                     => $txtDelete,
            )
        );
        $this->template->parse('CONTENTS', 'PortalDocumentEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    function viewFile()
    {
        // Validation and setting of variables
        $this->setMethodName('viewFile');
        $dsPortalDocument = new DataSet($this);
        $this->buPortalDocument->getDocumentByID(
            $this->getParam('portalDocumentID'),
            $dsPortalDocument
        );

        header('Content-type: ' . $dsPortalDocument->getValue(DBEPortalDocument::fileMimeType));
        header(
            'Content-Disposition: attachment; filename="' . $dsPortalDocument->getValue(
                DBEPortalDocument::filename
            ) . '"'
        );
        print $dsPortalDocument->getValue(DBEPortalDocument::file);

        exit;
    }

    /**
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsPortalDocument->populateFromArray($this->getParam('portalDocument')));
        /*
        Need a file when creating new
        */
        if (!$_FILES['userfile']['name'] && !$this->dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)) {
            $this->setFormErrorMessage('Please enter a file path');
        } else {
            /* uploading a file */

            if ($_FILES['userfile']['name'] && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                $this->setFormErrorMessage('Document not loaded - is it bigger than 6 MBytes?');
            }

        }

        if ($this->formError) {
            if (!$this->dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)) {
                $this->setAction(CTPORTALDOCUMENT_ACT_EDIT);
            } else {
                $this->setAction(CTPORTALDOCUMENT_ACT_ADD);
            }
            $this->edit();
            exit;
        }

        $this->buPortalDocument->updateDocument($this->dsPortalDocument, $_FILES['userfile']);

        $urlNext =
            Controller::buildLink(
                'Header.php',
                array()
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

        $this->buPortalDocument->getDocumentByID($this->getParam('portalDocumentID'), $dsPortalDocument);

        if (!$this->buPortalDocument->deleteDocument($this->getParam('portalDocumentID'))) {
            $this->displayFatalError('Cannot delete this document');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    'Header.php',
                    array(
                        'action' => CTCNC_ACT_DISP_EDIT
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

}
