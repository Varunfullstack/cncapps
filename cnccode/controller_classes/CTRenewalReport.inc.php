<?php
/**
 * Daily Helpdesk Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use Signable\ApiClient;
use Signable\DocumentWithoutTemplate;
use Signable\Envelopes;
use Signable\Party;

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

    var $dsActivtyEngineer = '';
    var $dsSearchForm = '';
    var $page = '';

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
        $roles = [
            "sales",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn(
            'customerID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsSearchForm->setValue(
            'customerID',
            ''
        );

    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            case 'produceReport':
                $this->page = $this->produceReport(
                    false,
                    false
                );
                break;

            case 'producePdfReport':
                $this->page = $this->produceReport(
                    false,
                    true
                );
                break;
            case 'previewPDF':


                echo json_encode(
                    [
                        'PDFPath' => $this->generatePDFContract(
                            $_REQUEST['customerID'],
                            $_REQUEST['contractsIDs']
                        )
                    ]
                );
                break;
            case 'sendPDF':

                echo json_encode(
                    [
                        'status' => $this->sendPDFContract(
                            $_REQUEST['PDFPath'],
                            $_REQUEST['contactID'],
                            $_REQUEST['signableTemplateID'],
                            $_REQUEST['customerID']
                        )
                    ]
                );
                break;
            case 'Search':
            default:
                $this->search();
                break;

        }
    }

    function generatePDFContract($customerID,
                                 $contractsIDs
    )
    {
        $mainPDF = new \setasign\Fpdi\Fpdi();
        $this->addPages(
            $mainPDF,
            $contractsIDs
        );

        $pageCount = $mainPDF->setSourceFile(
            PDF_RESOURCE_DIR . '/Terms & Conditions April 2018 branded.pdf'
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

    function runIt($customerID,
                   $firstName,
                   $lastName,
                   $emailAddress,
                   $sendToSignable = false
    )
    {

        $buCustomerItem = new BUCustomerItem($this);

// Start contracts
        $dbeJRenContract = new DBEJRenContract($this);
        $dbeJRenContract->getRowsByCustomerID($customerID);
        // broadband
        $dbeJRenBroadband = new DBEJRenBroadband($this);
        $dbeJRenBroadband->getRowsByCustomerID($customerID);
// Hosting
        $dbeJRenHosting = new DBEJRenHosting($this);
        $dbeJRenHosting->getRowsByCustomerID($customerID);

        $contracts = array_merge(
            [],
            $this->extractValidContracts($dbeJRenContract),
            $this->extractValidContracts($dbeJRenBroadband),
            $this->extractValidContracts($dbeJRenHosting)
        );


        uasort(
            $contracts,
            function ($a,
                      $b
            ) {
                if (strcmp(
                        $a['itemTypeDescription'],
                        $b['itemTypeDescription']
                    ) === 0) {
                    return strcmp(
                        $a['itemDescription'],
                        $b['itemDescription']
                    );
                }
                return strcmp(
                    $a['itemTypeDescription'],
                    $b['itemTypeDescription']
                );
            }
        );


        if ($sendToSignable) {

            $buMail = new BUMail($this);

            $toEmail = $emailAddress;

            $hdrs = array(
                'From'         => 'support@cnc-ltd.co.uk',
                'To'           => $toEmail,
                'Subject'      => "General Data Protection Regulations - Action Required",
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            // add name to top of email
            $thisBody = "<div style='font-family: Arial, sans-serif; font-size: 10pt'>
<p>Dear $firstName,</p>
<p>Following on with our recent communication regarding compliance with the new General Data Protection Regulations that are coming into force on the 25th May 2018, CNC have made some changes to our terms of conditions.</p>
<p>
We are therefore re-issuing new contract schedules and terms and conditions to all customers that must be signed and in place ready for this new legislation.
</p>
<p>
It is important that you or someone with the authority within your company to sign the attached documents does so before the above date to allow us to continue to provide the key services to your organisation.  We’ve now provided this using an e-sign option to make this process as simple as possible.
</p>
<p>
These new terms and conditions include a specific section in relation to data protection and reflect the current CNC product and service offerings as well as general changes in the market place since our last issue in 2014.
</p>
 <p>
If you have any questions then please do not hesitate to contact us.
</p>
<p>
Many thanks. 
</p>
</div>
";

            $buMail->mime->setHTMLBody($thisBody);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $thisBody = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->send(
                $emailAddress,
                $hdrs,
                $thisBody
            );

            $this->generateEnvelope(
                $fileName,
                $firstName,
                $lastName,
                $emailAddress,
                $customerID
            );
        }

        ?>
        <div>
            First Name: <?= $firstName ?>
        </div>
        <div>
            Last Name: <?= $lastName ?>
        </div>
        <div>
            Email: <?= $emailAddress ?>
        </div>

        <a href="<?= $fileName ?>">Link</a>
        <?php
    }


    function addPages(\setasign\Fpdi\Fpdi $mainPDF,
                      $contractsIDs
    )
    {

        foreach ($contractsIDs as $contractID) {
            // Validation and setting of variables

            $buCustomerItem = new BUCustomerItem($this);
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
                $dsContract->getValue('customerID'),
                $dsCustomer
            );
            $buSite->getSiteByID(
                $dsContract->getValue('customerID'),
                $dsContract->getValue('siteNo'),
                $dsSite
            );
            $customerHasServiceDeskContract = $buCustomerItem->customerHasServiceDeskContract(
                $dsContract->getValue('customerID')
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


    function generateEnvelope($fileName,
                              $firstName,
                              $lastName,
                              $email,
                              $customerID
    )
    {

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
            $email = "sales@cnc-ltd.co.uk";
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
            $buMail = new BUMail($this);

            $hdrs = array(
                'From'         => 'support@cnc-ltd.co.uk',
                'To'           => $email,
                'Subject'      => "CNC Contracts and Terms & Conditions to be signed",
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buStandardText = new BUStandardText($this);
            $dsResults = new DataSet($this);
            $buStandardText->getStandardTextByID(
                $templateID,
                $dsResults
            );

            $body = $dsResults->getValue("stt_text");

            $body = str_replace(
                "[%contactFirstName%]",
                $firstName,
                $body
            );
            $body = str_replace(
                "[%contactLastName%]",
                $lastName,
                $body
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

            $buMail->send(
                $email,
                $hdrs,
                $body
            );

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

    function search()
    {

        $this->setMethodName('search');

        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {


            } else {
                if (!$this->dsSearchForm->getValue('customerID')) {

                    $this->setFormErrorOn();

                } else {
                    $customerID = $this->dsSearchForm->getValue('customerID');
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

        $urlSubmit = $this->buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );


        $this->setPageTitle('Renewal Report');

        if ($this->dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $this->dsSearchForm->getValue('customerID'),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $urlCustomerPopup = $this->buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $this->template->set_var(
            array(
                'formError'         => $this->formError,
                'customerID'        => $this->dsSearchForm->getValue('customerID'),
                'customerIDMessage' => $this->dsSearchForm->getMessage('customerID'),
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

    /**
     * @access private
     * @param bool $customerID
     * @param bool $createPdf
     * @return mixed
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
            $customerID = $_REQUEST['customerID'];
            $this->setPageTitle("Renewal Report");
        }

        $displayAccountsInfo = $this->hasPermissions(PHPLIB_PERM_RENEWALS);

        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);

        $this->template->set_var(
            'customerName',
            $dbeCustomer->getValue(DBECustomer::name)
        );

        $buRenewal = new BURenewal($this);

        $items = $buRenewal->getRenewalsAndExternalItemsByCustomer(
            $customerID,
            $displayAccountsInfo,
            $this
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

        foreach ($items as $item) {

            if ($item['itemTypeDescription'] != $lastItemTypeDescription) {
                $itemTypeHeader = '<tr><td colspan="7"><h3>' . $item['itemTypeDescription'] . '</h3></td></tr>';
            } else {
                $itemTypeHeader = '';
            }

            $this->template->set_var(
                array(
                    'itemTypeHeader' => $itemTypeHeader
                )
            );

            $lastItemTypeDescription = $item['itemTypeDescription'];

            $coveredItemsString = '';

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

            if (is_null($item['customerItemID'])) {
                $itemClass = 'externalItem';
                $salePrice = '';
                $costPrice = '';
            } else {
                $itemClass = '';

                $salePrice = Controller::formatNumber($item['salePrice']);

                $costPrice = Controller::formatNumber($item['costPrice']);

                $totalCostPrice += $item['costPrice'];

                $totalSalePrice += $item['salePrice'];
            }
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
            $dsResults = new DataSet($this);
            $BUStandardText->getStandardTextByTypeID(
                BUStandardText::SignableContractsEmailType,
                $dsResults
            );
            $this->template->set_block(
                'RenewalReport',
                'templateBlock',
                'templates'
            );

            while ($dsResults->fetchNext()) {
                $this->template->set_var(
                    array(
                        'templateID'   => $dsResults->getValue("stt_standardtextno"),
                        'templateDesc' => $dsResults->getValue("stt_desc")
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
                    'linkURL'             => $item['linkURL'],
                    'notes'               => $item['notes'],
                    'description'         => Controller::htmlDisplayText($item['description']),
                    'itemTypeDescription' => Controller::htmlDisplayText($item['itemTypeDescription']),
                    'expiryDate'          => Controller::htmlDisplayText($item['expiryDate']),
                    'salePrice'           => $salePrice,
                    'costPrice'           => $costPrice,
                    'customerItemID'      => $item['customerItemID'],
                    'coveredItemsString'  => $coveredItemsString,
                    'itemClass'           => $itemClass,
                    'customerID'          => $customerID,
                    'checkbox'            => $checkbox
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
            $this->buildLink(
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
            $urlCreateQuote = $this->buildLink(
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

            $options = new \Dompdf\Options();
            $options->set(
                'isRemoteEnabled',
                true
            );
            $dompdf = new \Dompdf\Dompdf($options);


            /* @todo: set template dir */
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

}// end of class
?>