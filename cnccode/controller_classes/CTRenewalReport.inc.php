<?php
/**
 * Daily Helpdesk Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use Signable\ApiClient;
use Signable\DocumentWithoutTemplate;
use Signable\Envelopes;
use Signable\Party;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEJContract.inc.php');
require_once($cfg['path_dbe'] . '/DBEJRenContract.inc.php');
require_once($cfg['path_dbe'] . '/DBEJRenDomain.inc.php');
require_once($cfg['path_dbe'] . '/DBEJRenBroadband.inc.php');
require_once($cfg['path_dbe'] . '/DBEJRenQuotation.inc.php');
require_once($cfg['path_dbe'] . '/DBEJRenHosting.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUExternalItem.inc.php');
require_once($cfg ['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_bu'] . '/BURenewal.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
require_once($cfg ['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg ['path_bu'] . '/BUStandardText.inc.php');

class CTRenewalReport extends CTCNC
{

    const searchFormCustomerID = 'customerID';
    public $dsActivityEngineer;
    public $dsSearchForm;
    public $page;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
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
        $this->setMenuId(307);
        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn(
            self::searchFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsSearchForm->setValue(
            self::searchFormCustomerID,
            null
        );

    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case 'runOfficeReport':
                $roles = [
                    SALES_PERMISSION
                ];
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $customerID = @$_REQUEST['customerID'];
                ignore_user_abort(true);
                session_write_close();
                set_time_limit(0);
                ob_start();
// do initial processing here
                echo json_encode(["status" => "ok"]);
                header('Connection: close');
                header('Content-Length: ' . ob_get_length());
                ob_end_flush();
                ob_flush();
                flush();
                system(" php " . BASE_DRIVE . "/htdocs/Office365LicensesExport.php -c {$customerID} > NUL");
                break;
            case 'produceReport':
                $this->page = $this->produceReport(
                    false,
                    false
                );
                break;

            case 'producePdfReport':
                $roles = [
                    SALES_PERMISSION
                ];
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $this->page = $this->produceReport(
                    false,
                    true
                );
                break;
            case 'previewPDF':

                $roles = [
                    SALES_PERMISSION
                ];
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                echo json_encode(
                    [
                        'PDFPath' => $this->generatePDFContract(
                            $this->getParam('customerID'),
                            $this->getParam('contractsIDs')
                        )
                    ]
                );
                break;
            case 'sendPDF':
                $roles = [
                    SALES_PERMISSION
                ];
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                echo json_encode(
                    [
                        'status' => $this->sendPDFContract(
                            $this->getParam('PDFPath'),
                            $this->getParam('contactID'),
                            $this->getParam('signableTemplateID'),
                            $this->getParam('customerID')
                        )
                    ]
                );
                break;
            case 'Search':
            default:
                $roles = [
                    SALES_PERMISSION
                ];
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $this->search();
                break;

        }
    }

    /**
     * @access private
     * @param bool $customerID
     * @param bool $createPdf
     * @return mixed
     * @throws Exception
     */
    function produceReport($customerID = false,
                           $createPdf = false
    )
    {
        $this->setMethodName('produceReport');

        if ($createPdf) {
            $this->setHTMLFmt(CT_HTML_FMT_PDF);
        }

        $this->setTemplateFiles(
            'RenewalReport',
            'RenewalReport.inc'
        );



        if ($customerID) {
            $calledFromSearch = true;
        } else {
            $calledFromSearch = false;
            $customerID = $this->getParam('customerID');
            $this->setPageTitle("Renewal Report");
        }

        $displayAccountsInfo = $this->hasPermissions(SALES_PERMISSION);

        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);

        $this->template->set_var(
            'customerName',
            $dbeCustomer->getValue(DBECustomer::name)
        );

        $buRenewal = new BURenewal($this);

        $items = $buRenewal->getRenewalsAndExternalItemsByCustomer(
            $customerID,
            $this,
            $displayAccountsInfo
        );

        usort(
            $items,
            function ($a,
                      $b
            ) {
                return $a['itemTypeDescription'] <=> $b['itemTypeDescription'];
            }
        );

        $lastItemTypeDescription = false;

        $this->template->set_block(
            'RenewalReport',
            'itemBlock',
            'items'
        );

        $totalCostPrice = 0;
        $totalSalePrice = 0;
        $addOfficeReportButton = false;

        foreach ($items as $item) {
            $coveredItemsString = null;

            if (count($item['coveredItems']) > 0) {
                foreach ($item['coveredItems'] as $coveredItem) {
                    $coveredItemsString .= '<br/>' . $coveredItem;
                    $this->template->set_var(
                        array(
                            'coveredItemsString' => $coveredItemsString
                        )
                    );
                }
            }
            $itemClass = 'externalItem';
            $salePrice = null;
            $costPrice = null;

            if (!is_null($item['customerItemID'])) {
                $itemClass = null;
                $salePrice = Controller::formatNumber($item['salePrice']);
                $costPrice = Controller::formatNumber($item['costPrice']);
                $totalCostPrice += $item['costPrice'];
                $totalSalePrice += $item['salePrice'];
                if ($item['itemTypeId'] == 29) {
                    $dbeItem = new DBEItem($this);
                    $dbeItem->getRow($item['itemID']);
                    if ($dbeItem->getValue(DBEItem::itemBillingCategoryID) === 4) {
                        $addOfficeReportButton = true;
                    }
                }
            }

            $itemTypeHeader = null;
            if ($item['itemTypeDescription'] != $lastItemTypeDescription) {
                $itemTypeHeader = "<tr><td colspan=\"7\"><h3>{$item['itemTypeDescription']}</h3></td></tr>";
                if ($item['itemTypeId'] == 29) {
                    $itemTypeHeader .= '<tr class="officeReport hidden" ><td colspan="7"><button  type="button" onclick="runOfficeReport(' . $customerID . ')">Run O365 Mailbox Report</button></td></tr>';
                }
            }


            $this->template->set_var(
                array(
                    'itemTypeHeader' => $itemTypeHeader
                )
            );

            $lastItemTypeDescription = $item['itemTypeDescription'];

            $buCustomer = new BUCustomer($this);

            $mainContacts = $buCustomer->getMainSupportContacts($customerID);

            $this->template->set_block(
                'RenewalReport',
                'toSignContactsBlock',
                'toSignContacts'
            );

            foreach ($mainContacts as $contact) {
                $this->template->set_var(
                    array(
                        'toSignContactID'   => $contact['contactID'],
                        'toSignContactName' => $contact['firstName'] . ' ' . $contact['lastName'],
                    )
                );
                $this->template->parse(
                    'toSignContacts',
                    'toSignContactsBlock',
                    true
                );
            }

            $BUStandardText = new BUStandardText($this);
            $standardText = new DataSet($this);
            $BUStandardText->getStandardTextByTypeID(
                BUStandardText::SignableContractsEmailType,
                $standardText
            );
            $this->template->set_block(
                'RenewalReport',
                'templateBlock',
                'templates'
            );

            while ($standardText->fetchNext()) {
                $this->template->set_var(
                    array(
                        'templateID'   => $standardText->getValue(DBEStandardText::stt_standardtextno),
                        'templateDesc' => $standardText->getValue(DBEStandardText::stt_desc)
                    )
                );
                $this->template->parse(
                    'templates',
                    'templateBlock',
                    true
                );
            }

            $checkbox = null;

            if ($item['renewalTypeID'] != 3) {
                $checkbox = '<input type="checkbox" id="' . $item['customerItemID'] . '">';
            }


            $this->template->set_var(
                array(
                    'linkURL'              => $item['linkURL'],
                    'notes'                => $item['notes'],
                    'description'          => $item['description'],
                    'itemTypeDescription'  => Controller::htmlDisplayText($item['itemTypeDescription']),
                    'expiryDate'           => Controller::htmlDisplayText($item['expiryDate']),
                    'salePrice'            => $salePrice,
                    'costPrice'            => $costPrice,
                    'customerItemID'       => $item['customerItemID'],
                    'coveredItemsString'   => $coveredItemsString,
                    'itemClass'            => $itemClass,
                    'customerID'           => $customerID,
                    'checkbox'             => $checkbox,
                    'calculatedExpiryDate' => $item['calculatedExpiryDate'],
                    'units'                => $item['units'],
                    'unitsNotEqualItems'   => count($item['coveredItems']) > 0 && count(
                        $item['coveredItems']
                    ) != $item['units'] ? 'wrong' : '',
                    'directDebit'          => $item['directDebit'] ? 'Yes' : null,
                    "showOfficeButton"     => $addOfficeReportButton ? 1 : 0,
                    "disabled"             => $displayAccountsInfo ? null : "disabled"
                )
            );

            $this->template->parse(
                'items',
                'itemBlock',
                true
            );
        }

        /*
        External Items
        */
        $addExternalItemURL =
            Controller::buildLink(
                'ExternalItem.php',
                array(
                    'action'     => 'add',
                    'customerID' => $customerID
                )
            );


        $this->template->set_var(
            array(
                'addExternalItemURL' => $addExternalItemURL
            )
        );

        if ($displayAccountsInfo) {
            $urlCreateQuote = Controller::buildLink(
                $_SERVER ['PHP_SELF'],
                array('action' => 'createQuote')
            );


            $this->template->set_var(
                array(
                    'totalSalePrice'    => Controller::formatNumber($totalSalePrice),
                    'totalCostPrice'    => Controller::formatNumber($totalCostPrice),
                    'urlCreateQuote'    => $urlCreateQuote,
                    'buttonCreateQuote' => '<input type="submit" value="Quote">'
                )
            );
        }
        if ($createPdf) {
            $this->template->parse(
                'CONTENTS',
                'RenewalReport',
                true
            );

            $this->template->parse(
                "CONTENTS",
                "page"
            );

            $output = $this->template->get("CONTENTS");

            require_once BASE_DRIVE . '/vendor/autoload.php';

            $options = new Options();
            $options->set(
                'isRemoteEnabled',
                true
            );
            $dompdf = new Dompdf($options);
            $dompdf->setBasePath(BASE_DRIVE . '/htdocs');   // so we can get the images and css

            $dompdf->loadHtml($output);

            set_time_limit(120);                           // it may take some time!

            $dompdf->setPaper(
                'a4',
                'landscape'
            );

            $dompdf->render();

            $dompdf->add_info(
                'Title',
                'Renewal Report - ' . $dbeCustomer->getValue(DBECustomer::name)
            );

            $dompdf->add_info(
                'Author',
                'CNC Ltd'
            );

            $dompdf->add_info(
                'Subject',
                'Renewal Report'
            );

            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename='downloaded.pdf'");
            echo $dompdf->output();
            exit;
        } elseif ($calledFromSearch) {
            $this->template->parse(
                'output',
                'RenewalReport',
                true
            );

            return $this->template->get_var('output');

        } else {
            $this->template->parse(
                'CONTENTS',
                'RenewalReport',
                true
            );

            $this->parsePage();
        }

        return true;
    }

    /**
     * @param $customerID
     * @param $contractsIDs
     * @return mixed|string
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws PdfReaderException
     */
    function generatePDFContract($customerID,
                                 $contractsIDs
    )
    {
        $mainPDF = new Fpdi();
        $this->addPages(
            $mainPDF,
            $contractsIDs
        );

        $pageCount = $mainPDF->setSourceFile(
            PDF_RESOURCE_DIR . '/Terms & Conditions.pdf'
        );
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pageId = $mainPDF->importPage($pageNo);
            $s = $mainPDF->getTemplatesize($pageId);
            $mainPDF->AddPage(
                $s['orientation'],
                $s
            );
            $mainPDF->useImportedPage($pageId);
        }

        $pageCount = $mainPDF->setSourceFile(PDF_RESOURCE_DIR . '/lastPage.pdf');
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pageId = $mainPDF->importPage($pageNo);
            $s = $mainPDF->getTemplatesize($pageId);
            $mainPDF->AddPage(
                $s['orientation'],
                $s
            );
            $mainPDF->useImportedPage($pageId);
        }

        $fileName = PDF_TEMP_DIR . '/' . $customerID . '-Contracts.pdf';

        $mainPDF->Output(
            'F',
            $fileName,
            true
        );

        $fileName = str_replace(
            BASE_DRIVE . '/htdocs',
            "",
            $fileName
        );

        return $fileName;
    }

    /**
     * @param Fpdi $mainPDF
     * @param $contractsIDs
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfParserException
     * @throws PdfReaderException
     * @throws PdfTypeException
     */
    function addPages(Fpdi $mainPDF,
                      $contractsIDs
    )
    {

        foreach ($contractsIDs as $contractID) {
            // Validation and setting of variables

            $buCustomerItem = new BUCustomerItem($this);
            $dsContract = new DataSet($this);
            $buCustomerItem->getCustomerItemByID(
                $contractID,
                $dsContract
            );
            $buCustomerItem->getCustomerItemsByContractID(
                $contractID,
                $dsCustomerItem
            );

            $buSite = new BUSite($this);
            $buActivity = new BUActivity($this);
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID(
                $dsContract->getValue(DBEJCustomerItem::customerID),
                $dsCustomer
            );
            $buSite->getSiteByID(
                $dsContract->getValue(DBEJCustomerItem::customerID),
                $dsContract->getValue(DBEJCustomerItem::siteNo),
                $dsSite
            );
            $customerHasServiceDeskContract = $buCustomerItem->customerHasServiceDeskContract(
                $dsContract->getValue(DBEJCustomerItem::customerID)
            );

            $buPDFSupportContract =
                new BUPDFSupportContract(
                    $this,
                    $dsContract,
                    $dsCustomerItem,
                    $dsSite,
                    $dsCustomer,
                    $buActivity,
                    $customerHasServiceDeskContract
                );

            $pdfFile = $buPDFSupportContract->generateFile(false);

            $pageCount = $mainPDF->setSourceFile($pdfFile);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pageId = $mainPDF->importPage($pageNo);
                $s = $mainPDF->getTemplatesize($pageId);
                $mainPDF->AddPage(
                    $s['orientation'],
                    $s
                );
                $mainPDF->useImportedPage($pageId);
            }
        }

    }

    private function sendPDFContract($PDFPath,
                                     $contactID,
                                     $templateID,
                                     $customerID
    )
    {
        ApiClient::setApiKey("fc2d9ba05f3f3d9f2e9de4d831e8fed9");

        $envDocs = [];

        $file = basename($PDFPath);

        $fileName = PDF_TEMP_DIR . '/' . $file;

        $dbeContact = new DBEContact($this);

        $dbeContact->getRow($contactID);

        $firstName = $dbeContact->getValue(DBEContact::firstName);
        $lastName = $dbeContact->getValue(DBEContact::lastName);
        $email = $dbeContact->getValue(DBEContact::email);
        global $server_type;
        if ($server_type !== MAIN_CONFIG_SERVER_TYPE_LIVE) {
            $email = "sales@" . CONFIG_PUBLIC_DOMAIN;
        }

        $envelopeDocument = new DocumentWithoutTemplate(
            'CNC Contracts with Terms & Conditions',
            null,
            base64_encode(file_get_contents($fileName)),
            "CNCContractsTCs.pdf"
        );

        $envDocs[] = $envelopeDocument;

        $envelopeParties = [];

        $envelopeParty = new Party(
            $firstName . ' ' . $lastName,
            $email,
            'signer1',
            'Please sign here',
            'no',
            false
        );
        $envelopeParties[] = $envelopeParty;


        $response = Envelopes::createNewWithoutTemplate(
            "Document #" . $customerID . "_" . uniqid(),
            $envDocs,
            $envelopeParties,
            null,
            false,
            null,
            0,
            0
        );


        if ($response && $response->http == 202) {

            global $twig;
            $subject = "CNC Contracts and Terms & Conditions to be signed";
            $body = $twig->render(
                '@customerFacing/RenewalContractsToBeSigned/RenewalContractsToBeSigned.html.twig',
                ["contactFirstName" => $firstName]
            );

            $buMail = new BUMail($this);
            $buMail->sendSimpleEmail($body, $subject, $email);

            $dbeCustomer = new DBECustomer($this);
            $dbeCustomer->getRow($customerID);
            $dbeCustomer->setValue(
                DBECustomer::lastContractSent,
                "Documents last sent to " . $firstName . ' ' . $lastName . " on " .
                (new DateTime())->format('d/m/Y h:i') .
                " by " .
                $this->dbeUser->getValue(DBEUser::firstName) .
                " " .
                $this->dbeUser->getValue(DBEUser::lastName)
            );
            $dbeCustomer->updateRow();

            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    function search()
    {

        $this->setMethodName('search');
        $report = null;
        if (isset ($_REQUEST ['searchForm']) == 'POST') {
            if ($this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                if (!$this->dsSearchForm->getValue(self::searchFormCustomerID)) {
                    $this->setFormErrorOn();
                } else {
                    $customerID = $this->dsSearchForm->getValue(self::searchFormCustomerID);
                    $report = $this->produceReport($customerID);
                }

            }
        }

        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'RenewalReportSearch' => 'RenewalReportSearch.inc'
            )
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );


        $this->setPageTitle('Renewal Report');
        $customerString = null;
        if ($this->dsSearchForm->getValue(self::searchFormCustomerID) != 0) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $this->dsSearchForm->getValue(self::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $this->template->set_var(
            array(
                'formError'         => $this->formError,
                'customerID'        => $this->dsSearchForm->getValue(self::searchFormCustomerID),
                'customerIDMessage' => $this->dsSearchForm->getMessage(self::searchFormCustomerID),
                'customerString'    => $customerString,
                'urlCustomerPopup'  => $urlCustomerPopup,
                'urlSubmit'         => $urlSubmit,
                'report'            => $report
            )
        );

        $this->template->parse(
            'CONTENTS',
            'RenewalReportSearch',
            true
        );

        $this->parsePage();

    } // end function displaySearchForm

    function generateEnvelope($fileName,
                              $firstName,
                              $lastName,
                              $email,
                              $customerID
    )
    {

    }

}
