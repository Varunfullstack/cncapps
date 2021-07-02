<?php /** @noinspection PhpMissingBreakStatementInspection */
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
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case "documents":
                switch($this->requestMethod){
                    // case 'GET':
                    //     echo  json_encode($this->getItemTypes(),JSON_NUMERIC_CHECK);
                    //     break;
                    // case 'POST':
                    //     echo  json_encode($this->addItemType(),JSON_NUMERIC_CHECK);
                    //     break;
                    case 'PUT':
                        echo  json_encode($this->update(),JSON_NUMERIC_CHECK);
                        exit;
                    // case 'DELETE':
                    //     echo  json_encode($this->deleteItemType(),JSON_NUMERIC_CHECK);
                    //     break;
                    default:
                        # code...
                        break;
                }
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
        $dsPortalCustomerDocument = &$this->dsPortalCustomerDocument; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTPORTALCUSTOMERDOCUMENT_ACT_EDIT) {
                $this->buPortalCustomerDocument->getDocumentByID(
                    $this->getParam('portalCustomerDocumentID'),
                    $dsPortalCustomerDocument
                );
                $portalCustomerDocumentID = $this->getParam('portalCustomerDocumentID');
            } else {                                                                    // creating new
                $dsPortalCustomerDocument->initialise();
                $dsPortalCustomerDocument->setValue(DBEPortalCustomerDocument::portalCustomerDocumentID, null);
                $dsPortalCustomerDocument->setValue(
                    DBEPortalCustomerDocument::customerID,
                    $this->getParam('customerID')
                );
                $portalCustomerDocumentID = null;
            }
        } else {                                                                        // form validation error
            $dsPortalCustomerDocument->initialise();
            $dsPortalCustomerDocument->fetchNext();
            $portalCustomerDocumentID = $dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::portalCustomerDocumentID
            );
        }
        if ($this->getAction() == CTPORTALCUSTOMERDOCUMENT_ACT_EDIT) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'                   => CTPORTALCUSTOMERDOCUMENT_ACT_DELETE,
                        'portalCustomerDocumentID' => $portalCustomerDocumentID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }


        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'                   => CTPORTALCUSTOMERDOCUMENT_ACT_UPDATE,
                    'portalCustomerDocumentID' => $portalCustomerDocumentID
                )
            );
        $urlDisplayCustomer =
            Controller::buildLink(
                'Customer . php',
                array(
                    'customerID' => $this->dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::customerID),
                    'action'     => CTCNC_ACT_DISP_EDIT
                )
            );
        $this->setPageTitle('Edit Document');
        $this->setTemplateFiles(
            array('PortalCustomerDocumentEdit' => 'PortalCustomerDocumentEdit.inc')
        );

        $createdDateString = (new DateTime())->format(DATE_MYSQL_DATETIME);

        if ($dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::createdDate
            ) && $dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::createdDate
            ) != '0000-00-00 00:00:00') {
            $createdDateString = $dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::createdDate
            );
        }


        $this->template->set_var(
            array(
                'customerID'                 => $dsPortalCustomerDocument->getValue(
                    DBEPortalCustomerDocument::customerID
                ),
                'portalCustomerDocumentID'   => $portalCustomerDocumentID,
                'filename'                   => Controller::htmlDisplayText(
                    $dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::filename)
                ),
                'description'                => Controller::htmlInputText(
                    $dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::description)
                ),
                'descriptionMessage'         => Controller::htmlDisplayText(
                    $dsPortalCustomerDocument->getMessage(DBEPortalCustomerDocument::description)
                ),
                'customerContractChecked'    => $dsPortalCustomerDocument->getValue(
                    DBEPortalCustomerDocument::customerContract
                ) ? 'checked' : null,
                'customerContractMessage'    => Controller::htmlDisplayText(
                    $dsPortalCustomerDocument->getMessage(DBEPortalCustomerDocument::customerContract)
                ),
                'mainContactOnlyFlagChecked' => Controller::htmlChecked(
                    $dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::mainContactOnlyFlag)
                ),
                'mainContactOnlyFlagMessage' => Controller::htmlDisplayText(
                    $dsPortalCustomerDocument->getMessage(DBEPortalCustomerDocument::mainContactOnlyFlag)
                ),
                'createdDate'                => $createdDateString,
                'urlUpdate'                  => $urlUpdate,
                'urlDelete'                  => $urlDelete,
                'txtDelete'                  => $txtDelete,
                'urlDisplayCustomer'         => $urlDisplayCustomer
            )
        );
        $this->template->parse('CONTENTS', 'PortalCustomerDocumentEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

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
        $dsPortalCustomerDocument = new DataSet($this);
        $this->buPortalCustomerDocument->getDocumentByID(
            $this->getParam('portalCustomerDocumentID'),
            $dsPortalCustomerDocument
        );

        if (!$this->buPortalCustomerDocument->deleteDocument($this->getParam('portalCustomerDocumentID'))) {
            $this->displayFatalError('Cannot delete this document');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    'Customer.php',
                    array(
                        'customerID' => $dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::customerID),
                        'action'     => CTCNC_ACT_DISP_EDIT
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    /**
     * @throws Exception
     */
    function update()
    {
        $body=$this->getBody();
        $dbePortalDocument=new DBEPortalCustomerDocument($this);
        $dbePortalDocument->getRow($body->id);
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::description,$body->description);
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::customerContract,$body->customerContract);
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::mainContactOnlyFlag,$body->mainContactOnlyFlag?"Y":"N");        
        return $this->success($dbePortalDocument->updateRow());
        // $this->setMethodName('update');
        // $this->formError = !$this->dsPortalCustomerDocument->populateFromArray(
        //     $this->getParam('portalCustomerDocument')
        // );
        /*
        Need a file when creating new
        */

        if ($_FILES['userfile']['name'] == '' && !$this->dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::portalCustomerDocumentID
            )) {
            $this->setFormErrorMessage('Please enter a file path');
        } else {
            /* uploading a file */
            if (!$this->dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::createdDate)) {
                $this->dsPortalCustomerDocument->setValue(
                    DBEPortalCustomerDocument::createdDate,
                    (new DateTime())->format(DATE_MYSQL_DATETIME)
                );
            }

            if ($_FILES['userfile']['name'] != '' && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                $this->setFormErrorMessage(
                    'Document not loaded - is it bigger than ' .
                    $this->return_bytes(ini_get('upload_max_filesize')) / 1024 / 1024 . '
                                            MBytes ? '
                );
            }

        }

        if ($this->formError) {
            if ($this->dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::portalCustomerDocumentID
            )) {                    // attempt to insert
                $this->setAction(CTPORTALCUSTOMERDOCUMENT_ACT_EDIT);
            } else {
                $this->setAction(CTPORTALCUSTOMERDOCUMENT_ACT_ADD);
            }
            $this->edit();
            exit;
        }

        $this->buPortalCustomerDocument->updateDocument($this->dsPortalCustomerDocument, $_FILES['userfile']);

        $urlNext =
            Controller::buildLink(
                'Customer.php',
                array(
                    'customerID' => $this->dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::customerID),
                    'action'     => CTCNC_ACT_DISP_EDIT
                )
            );
        header('Location: ' . $urlNext);
        // $this->setMethodName('update');
        // $this->formError = !$this->dsPortalCustomerDocument->populateFromArray(
        //     $this->getParam('portalCustomerDocument')
        // );
        // /*
        // Need a file when creating new
        // */

        // if ($_FILES['userfile']['name'] == '' && !$this->dsPortalCustomerDocument->getValue(
        //         DBEPortalCustomerDocument::portalCustomerDocumentID
        //     )) {
        //     $this->setFormErrorMessage('Please enter a file path');
        // } else {
        //     /* uploading a file */
        //     if (!$this->dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::createdDate)) {
        //         $this->dsPortalCustomerDocument->setValue(
        //             DBEPortalCustomerDocument::createdDate,
        //             (new DateTime())->format(DATE_MYSQL_DATETIME)
        //         );
        //     }

        //     if ($_FILES['userfile']['name'] != '' && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
        //         $this->setFormErrorMessage(
        //             'Document not loaded - is it bigger than ' .
        //             $this->return_bytes(ini_get('upload_max_filesize')) / 1024 / 1024 . '
        //                                     MBytes ? '
        //         );
        //     }

        // }

        // if ($this->formError) {
        //     if ($this->dsPortalCustomerDocument->getValue(
        //         DBEPortalCustomerDocument::portalCustomerDocumentID
        //     )) {                    // attempt to insert
        //         $this->setAction(CTPORTALCUSTOMERDOCUMENT_ACT_EDIT);
        //     } else {
        //         $this->setAction(CTPORTALCUSTOMERDOCUMENT_ACT_ADD);
        //     }
        //     $this->edit();
        //     exit;
        // }

        // $this->buPortalCustomerDocument->updateDocument($this->dsPortalCustomerDocument, $_FILES['userfile']);

        // $urlNext =
        //     Controller::buildLink(
        //         'Customer.php',
        //         array(
        //             'customerID' => $this->dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::customerID),
        //             'action'     => CTCNC_ACT_DISP_EDIT
        //         )
        //     );
        // header('Location: ' . $urlNext);
    }

    /**
     * @param $val
     * @return bool|int|string
     */
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

    function viewFile()
    {
        // Validation and setting of variables
        $this->setMethodName('viewFile');
        $dsPortalCustomerDocument = new DataSet($this);
        $this->buPortalCustomerDocument->getDocumentByID(
            $this->getParam('portalCustomerDocumentID'),
            $dsPortalCustomerDocument
        );

        header('Content-Type: ' . $dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::fileMimeType));
        header(
            'Content-Disposition: inline; filename = "' . $dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::filename
            ) . '"'
        );
        header(
            'custom: filename = "' . $dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::filename
            ) . '"'
        );
        print $dsPortalCustomerDocument->getValue(DBEPortalCustomerDocument::file);
        exit;
    }

}