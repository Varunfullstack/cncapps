<?php
/** @noinspection PhpMissingBreakStatementInspection */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPortalCustomerDocument.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');


class CTPortalCustomerDocument extends CTCNC
{
    var $dsPortalCustomerDocument = '';
    var $buPortalCustomerDocument = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        // $roles = [
        //     'sales'
        // ];
        // if (!self::hasPermissions($roles)) {
        //     Header("Location: /NotAllowed.php");
        //     exit;
        // }
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
        // Actions
        switch ($this->getAction()) {
            case "documents":
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getPortalDocuments(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo json_encode($this->addDocument(), JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo json_encode($this->update(), JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo json_encode($this->delete(), JSON_NUMERIC_CHECK);
                        break;
                }
                exit;
            case "uploadDocument":
                echo json_encode($this->uploadDocument(), JSON_NUMERIC_CHECK);
                exit;
            case 'viewFile':
                $this->viewFile();
                break;
            default:
                header('Location: /');
        }
    }

    /**
     * @param $customerID
     * @return array
     * @throws Exception
     */
    function getPortalDocuments()
    {
        $customerID      = $_REQUEST["customerID"];
        $portalDocuments = new DBEPortalCustomerDocumentWithoutFile($customerID);
        $portalDocuments->setValue(DBEPortalCustomerDocumentWithoutFile::customerID, $customerID);
        $portalDocuments->getRowsByColumn(
            DBEPortalCustomerDocument::customerID,
            DBEPortalCustomerDocumentWithoutFile::description
        );
        $documents = [];
        while ($portalDocuments->fetchNext()) {
            $documents[] = [
                'id'                  => $portalDocuments->getValue(
                    DBEPortalCustomerDocument::portalCustomerDocumentID
                ),
                'description'         => $portalDocuments->getValue(
                    DBEPortalCustomerDocumentWithoutFile::description
                ),
                'filename'            => $portalDocuments->getValue(
                    DBEPortalCustomerDocumentWithoutFile::filename
                ),
                'customerContract'    => $portalDocuments->getValue(
                    DBEPortalCustomerDocumentWithoutFile::customerContract
                ),
                'mainContactOnlyFlag' => $portalDocuments->getValue(
                        DBEPortalCustomerDocument::mainContactOnlyFlag
                    ) === 'Y',
            ];
        }
        return $this->success($documents);
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Mustafa Taha
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
            return $this->fail(APIException::badRequest, 'Cannot delete this document');
        } else {
            return $this->success();
        }
    }

    /**
     * @throws Exception
     */
    function addDocument()
    {
        $body              = $this->getBody();
        $dbePortalDocument = new DBEPortalCustomerDocument($this);
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::description, $body->description);
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::customerID, $body->customerID);
        $dbePortalDocument->setValue(
            DBEPortalCustomerDocument::createdDate,
            (new DateTime())->format(DATE_MYSQL_DATETIME)
        );
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::customerContract, $body->customerContract);
        $dbePortalDocument->setValue(
            DBEPortalCustomerDocument::mainContactOnlyFlag,
            $body->mainContactOnlyFlag ? "Y" : "N"
        );
        $dbePortalDocument->insertRow();
        return $this->success(["documentID" => $dbePortalDocument->getPKValue()]);
    }

    /**
     * @throws Exception
     */
    function update()
    {
        $body              = $this->getBody();
        $dbePortalDocument = new DBEPortalCustomerDocument($this);
        $dbePortalDocument->getRow($body->id);
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::description, $body->description);
        $dbePortalDocument->setValue(DBEPortalCustomerDocument::customerContract, $body->customerContract);
        $dbePortalDocument->setValue(
            DBEPortalCustomerDocument::mainContactOnlyFlag,
            $body->mainContactOnlyFlag ? "Y" : "N"
        );
        return $this->success($dbePortalDocument->updateRow());
    }

    function uploadDocument()
    {

        if ($_FILES['userfile']['name'] == '' && !$this->dsPortalCustomerDocument->getValue(
                DBEPortalCustomerDocument::portalCustomerDocumentID
            )) {
            return $this->fail(APIException::badRequest, "Please select file");
        } else {
            $dbePortalDocument = new DBEPortalCustomerDocument($this);
            $dbePortalDocument->getRow($_REQUEST["documentID"]);
            if (!$dbePortalDocument->rowCount) return $this->fail(APIException::notFound);
            if (!$dbePortalDocument->getValue(DBEPortalCustomerDocument::createdDate)) {
                $dbePortalDocument->setValue(
                    DBEPortalCustomerDocument::createdDate,
                    (new DateTime())->format(DATE_MYSQL_DATETIME)
                );
            }
            $userfile = $_FILES['userfile'];
            //return $this->success(["userfile"=>$userfile]);
            if ($_FILES['userfile']['name'] != '' && !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                return $this->fail(
                    APIException::badRequest,
                    'Document not loaded - is it bigger than ' . $this->return_bytes(
                        ini_get('upload_max_filesize')
                    ) / 1024 / 1024 . '
                                    MBytes ? '
                );
            }
            $dbePortalDocument->setValue(
                DBEPortalCustomerDocument::file,
                fread(fopen($userfile['tmp_name'], 'rb'), $userfile['size'])
            );
            $dbePortalDocument->setValue(
                DBEPortalCustomerDocument::filename,
                $userfile['name']
            );
            $dbePortalDocument->setValue(
                DBEPortalCustomerDocument::fileMimeType,
                (string)$userfile['type']
            );
            $dbePortalDocument->updateRow();
        }
        //$this->buPortalCustomerDocument->updateDocument($dbePortalDocument, $_FILES['userfile']);
        return $this->success();
    }

    /**
     * @param $val
     * @return bool|int|string
     */
    private function return_bytes($val)
    {
        $val  = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val  = substr($val, 0, -1); // necessary since PHP 7.1; otherwise optional
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
