<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

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
    const CONST_PORTAL_DOCUMENTS="documents";
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
            case self::CONST_PORTAL_DOCUMENTS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getDocuuments(),JSON_NUMERIC_CHECK);
                        break;                    
                    case 'POST':
                            echo  json_encode($this->updateDocument(),JSON_NUMERIC_CHECK);
                        break;
                     case 'DELETE':
                         echo  json_encode($this->delete(),JSON_NUMERIC_CHECK);
                         break;
                    default:
                        # code...
                        break;
                }
                exit;         
            case 'viewFile':
                $this->viewFile();
                break;
            default:
                header('Location: /');
        }
    }

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
            return $this->fail('Cannot delete this document');            
        } 
        return $this->success();
    }
    //----------new code
    function getDocuuments(){
        $data=[];
        if ($this->getAction() != 'add') {
            $buPortalDocument = new BUPortalDocument($this);
            $dsPortalDocument = new DataSet($this);
            $buPortalDocument->getDocuments($dsPortalDocument);
            
            while ($dsPortalDocument->fetchNext()) {

                $urlEditDocument   = Controller::buildLink(
                    'PortalDocument.php',
                    array(
                        'action'           => 'edit',
                        'portalDocumentID' => $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)
                    )
                );
                $urlViewFile       = Controller::buildLink(
                    'PortalDocument.php',
                    array(
                        'action'           => 'viewFile',
                        'portalDocumentID' => $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)
                    )
                );
                $urlDeleteDocument = Controller::buildLink(
                    'PortalDocument.php',
                    array(
                        'action'           => 'delete',
                        'portalDocumentID' => $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)
                    )
                );
                $data []=
                    array(
                        'portalDocumentID'       => $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID),
                        'description'            => $dsPortalDocument->getValue(DBEPortalDocument::description),
                        'filename'               => $dsPortalDocument->getValue(DBEPortalDocument::filename),
                        'mainContactOnlyFlag'    => $dsPortalDocument->getValue(DBEPortalDocument::mainContactOnlyFlag),
                        'requiresAcceptanceFlag' => $dsPortalDocument->getValue(
                            DBEPortalDocument::requiresAcceptanceFlag
                        ),
                        'createDate'             => $dsPortalDocument->getValue(DBEPortalDocument::createdDate),
                        'urlViewFile'            => $urlViewFile,
                        'urlEditDocument'        => $urlEditDocument,
                        'urlDeleteDocument'      => $urlDeleteDocument,
                        
                    );
            }
        }
        return $this->success($data);
    }
    function updateDocument(){
        $data=json_decode($_REQUEST["data"],true);                
        $this->formError = (!$this->dsPortalDocument->populateFromArray(['data'=>$data]));
        if ($this->formError) {
            return $this->fail(APIException::badRequest,'Missing data');
        }
        /*
        Need a file when creating new
        */
        if (isset($_FILES['userfile'])&&!$_FILES['userfile']['name'] && !$this->dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)) {
            return $this->fail(APIException::badRequest,'Please enter a file path');
        } else {
            /* uploading a file */
            if (isset($_FILES['userfile'])&&$_FILES['userfile']['name'] && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {                
                return $this->fail(APIException::badRequest,'Document not loaded - is it bigger than 6 MBytes?');
            }
        }
       
        $this->buPortalDocument->updateDocument($this->dsPortalDocument, $_FILES['userfile']??null);
        return $this->success();    
       
    }
}
