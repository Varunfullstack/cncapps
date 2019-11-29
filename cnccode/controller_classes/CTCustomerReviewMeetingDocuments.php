<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/09/2018
 * Time: 11:57
 */

require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_ct'] . '/CTCustomer.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerReviewMeetingDocuments.php');
require_once($cfg ['path_bu'] . '/BUStandardText.inc.php');
require_once($cfg ['path_dbe'] . '/DBECustomerReviewMeetingDocument.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerReviewMeetingDocuments extends CTCNC
{
    const FETCH_CUSTOMER_DOCUMENTS = "fetchCustomerDocuments";
    const UPLOAD_DOCUMENTS = "uploadDocuments";
    const DELETE_DOCUMENT = "deleteDocument";
    const SEND_DOCUMENTS = "sendDocuments";
    const DOWNLOAD_DOCUMENT = "downloadDocument";
    private $buCustomerReviewMeetingDocuments;

    /**
     * CTCustomerReviewMeetingDocuments constructor.
     * @param $requestMethod
     * @param array $postVars
     * @param array $getVars
     * @param array $cookieVars
     * @param array|bool|int|string $cfg
     */
    public function __construct($requestMethod,
                                array $postVars,
                                array $getVars,
                                array $cookieVars,
                                $cfg
    )
    {

        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomerReviewMeetingDocuments = new BUCustomerReviewMeetingDocuments ($this);

    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::FETCH_CUSTOMER_DOCUMENTS:

                $dbeDocuments = new DBECustomerReviewMeetingDocument($this);
                $dbeDocuments->getRowsByCustomerID($this->getParam('customerID'));

                $data = [];

                $dbeUser = new DBEUser($this);

                while ($dbeDocuments->fetchNext()) {

                    $dbeUser->getRow(
                        $dbeDocuments->getValue(
                            DBECustomerReviewMeetingDocument::uploadedBy
                        )
                    );
                    $data[] = [
                        "documentID"        => $dbeDocuments->getValue(
                            DBECustomerReviewMeetingDocument::customerReviewMeetingDocumentID
                        ),
                        "reviewMeetingDate" => self::dateYMDtoDMY(
                            $dbeDocuments->getValue(
                                DBECustomerReviewMeetingDocument::meetingDate
                            )
                        ),
                        "fileName"          => $dbeDocuments->getValue(
                            DBECustomerReviewMeetingDocument::fileName
                        ),
                        "uploadedBy"        => $dbeUser->getValue(DBEUser::username),
                        "uploadedAt"        => $dbeDocuments->getValue(
                            DBECustomerReviewMeetingDocument::uploadedAt
                        ),
                    ];
                }

                echo json_encode($data);
                break;
            case self::UPLOAD_DOCUMENTS:

                $response = [];
                try {
                    $this->uploadDocuments();
                    $response['status'] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                }
                echo json_encode($response);
                break;
            case self::DELETE_DOCUMENT:
                $response = [];
                try {
                    $this->deleteDocument();
                    $response['status'] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                }
                echo json_encode($response);
                break;
            case self::SEND_DOCUMENTS:
                $response = [];

                try {
                    $this->sendDocuments();
                    $response['status'] = "ok";
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                }
                echo json_encode($response);
                break;
            case self::DOWNLOAD_DOCUMENT:

                if (!$this->getParam('documentID')) {
                    echo 'Document ID missing';
                    http_response_code(400);
                    exit;
                }
                $dbeDocuments = new DBECustomerReviewMeetingDocument($this);
                $dbeDocuments->getRow($this->getParam('documentID'));

                header('Content-Description: File Transfer');
                header('Content-Type: ' . $dbeDocuments->getValue(DBECustomerReviewMeetingDocument::fileMIMEType));
                header(
                    'Content-Disposition: attachment; filename="' . $dbeDocuments->getValue(
                        DBECustomerReviewMeetingDocument::fileName
                    ) . '"'
                );
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($dbeDocuments->getValue(DBECustomerReviewMeetingDocument::file)));
                echo $dbeDocuments->getValue(DBECustomerReviewMeetingDocument::file);
                exit;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * @throws Exception
     */
    private function uploadDocuments()
    {
        $counter = 0;

        $buActivity = new BUActivity($this);


        if (!isset($_FILES['files']) || !count($_FILES['files']['name'])) {
            throw new Exception('At least one file must be provided');
        }

        if (!$this->getParam('customerID')) {
            throw new Exception('Customer ID is missing');
        }

        if (!$this->getParam('reviewMeetingDate')) {
            throw new Exception('Review Meeting Date is missing');
        }

        $dbeDocuments = new DBECustomerReviewMeetingDocument($this);

        foreach ($_FILES['files']['name'] as $fileName) {
            $dbeDocuments->setUpdateModeInsert();

            $dbeDocuments->setValue(
                DBECustomerReviewMeetingDocument::customerID,
                $this->getParam('customerID')
            );
            $dbeDocuments->setValue(
                DBECustomerReviewMeetingDocument::meetingDate,
                common_convertDateDMYToYMD($this->getParam('reviewMeetingDate'))
            );
            $dbeDocuments->setValue(
                DBECustomerReviewMeetingDocument::file,
                file_get_contents($_FILES['files']['tmp_name'][$counter])
            );
            $dbeDocuments->setValue(
                DBECustomerReviewMeetingDocument::uploadedBy,
                $buActivity->loggedInUserID
            );
            $dbeDocuments->setValue(
                DBECustomerReviewMeetingDocument::uploadedAt,
                (new DateTime())->format('Y-m-d H:i:s')
            );
            $dbeDocuments->setValue(
                DBECustomerReviewMeetingDocument::fileName,
                $fileName
            );
            $dbeDocuments->setValue(
                DBECustomerReviewMeetingDocument::fileMIMEType,
                $_FILES['files']['type'][$counter]
            );
            $dbeDocuments->post();
            $counter++;

        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function deleteDocument()
    {
        if (!$this->getParam('documentID')) {
            throw new Exception("Document id is missing");
        }
        $documentID = $this->getParam('documentID');

        $dbeDocuments = new DBECustomerReviewMeetingDocument($this);

        $dbeDocuments->deleteRow($documentID);

        return true;
    }

    /**
     * @throws Exception
     */
    private function sendDocuments()
    {
        if (!$this->getParam('meetingDate')) {
            throw new Exception('Meeting date is missing');
        }
        if (!$this->getParam('standardTextID')) {
            throw new Exception('Standard text ID is missing');
        }
        if (!$this->getParam('customerID')) {
            throw new Exception('Customer ID is missing');
        }

        $meetingDate = $this->getParam('meetingDate');
        $standardTextID = $this->getParam('standardTextID');
        $customerID = $this->getParam('customerID');

        $buStandardText = new BUStandardText($this);
        $dsResults = new DataSet($this);
        $buStandardText->getStandardTextByID(
            $standardTextID,
            $dsResults
        );

        $dbeContact = new DBEContact($this);
        $dbeContact->getReviewContactsByCustomerID($customerID);

        $buMail = new BUMail($this);

        $message = $dsResults->getValue(DBEStandardText::stt_text);
        $fromEmail = 'support@cnc-ltd.co.uk';
        while ($dbeContact->fetchNext()) {

            $body = str_replace(
                "[%contactFirstName%]",
                $dbeContact->getValue(DBEContact::firstName),
                $message
            );
            $body = str_replace(
                "[%reviewMeetingDate%]",
                $meetingDate,
                $body
            );

            $toEmail = $dbeContact->getValue(DBEContact::email);

            $hdrs = array(
                'From'         => $this->dbeUser->getValue(DBEUser::username) . '@cnc-ltd.co.uk',
                'To'           => $toEmail,
                'Subject'      => "CNC Review Meeting Documents",
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );
            $buMail->mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $body = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $fromEmail,
                $toEmail,
                $hdrs,
                $body
            );

        }
    }

    /**
     * @throws Exception
     */
    private function displaySearchForm()
    {
        $this->setPageTitle('Customer Review Meeting Documents');
        $this->template->setFile(
            'customerReviewMeetingDocument',
            'CustomerReviewMeetingDocuments.html'
        );

        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $fetchDataURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => self::FETCH_CUSTOMER_DOCUMENTS
            ]
        );
        $uploadFilesURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => self::UPLOAD_DOCUMENTS
            ]
        );

        $deleteDocumentURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                [
                    'action' => self::DELETE_DOCUMENT
                ]
            );

        $fetchReviewContactsDataURL =
            Controller::buildLink(
                CTCNC_PAGE_CUSTOMER,
                [
                    'action' => CTCustomer::GET_CUSTOMER_REVIEW_CONTACTS
                ]
            );

        $sendReviewEmails =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                [
                    'action' => self::SEND_DOCUMENTS
                ]
            );

        $this->template->setVar(
            [
                "urlCustomerPopup"           => $urlCustomerPopup,
                "fetchDataUrl"               => $fetchDataURL,
                "uploadFilesURL"             => $uploadFilesURL,
                "deleteDocumentURL"          => $deleteDocumentURL,
                "fetchReviewContactsDataURL" => $fetchReviewContactsDataURL,
                "sendReviewEmails"           => $sendReviewEmails
            ]
        );


        $buStandardText = new BUStandardText($this);
        $dsStandardText = new DataSet($this);
        $buStandardText->getStandardTextByTypeID(
            5,
            $dsStandardText
        );

        $this->template->setBlock(
            'customerReviewMeetingDocument',
            'standardTextBlock',
            'standardText'
        );

        $this->template->setVar(
            [
                "standardTextID"          => null,
                "standardTextDescription" => 'Please Select a Standard Text'
            ]
        );

        $this->template->parse(
            'standardText',
            'standardTextBlock',
            true
        );

        while ($dsStandardText->fetchNext()) {


            $this->template->setVar(
                [
                    "standardTextID"          => $dsStandardText->getValue(DBEStandardText::stt_standardtextno),
                    "standardTextDescription" => $dsStandardText->getValue(DBEStandardText::stt_desc)
                ]
            );

            $this->template->parse(
                'standardText',
                'standardTextBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'customerReviewMeetingDocument',
            true
        );
        $this->parsePage();

    }
}