<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 07/05/2018
 * Time: 15:01
 */

use Signable\DocumentWithoutTemplate;
use Signable\Envelopes;
use Signable\Party;

require_once('config.inc.php');
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BURenBroadband.inc.php");
require_once($cfg["path_bu"] . "/BURenContract.inc.php");
require_once($cfg["path_bu"] . "/BURenQuotation.inc.php");
require_once($cfg["path_bu"] . "/BURenDomain.inc.php");
require_once($cfg["path_bu"] . "/BURenHosting.inc.php");
require_once($cfg["path_bu"] . "/BUExternalItem.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerItem.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');
require_once($cfg['path_dbe'] . '/DBEJContract.inc.php');
require_once($cfg['path_ct'] . '/CTCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once __DIR__ . '/../vendor/autoload.php';
$returnArray = array();

$sendToSignable = isset($_GET['sendToSignable']);

class OneOffPDF
{

    function extractValidContracts($something)
    {

        $contracts = [];
        $validItems = [
            "2nd Site",
            "Internet Services",
            "Managed Service",
            "ServerCare",
            "ServiceDesk",
            "Telecom Services",
            "PrePay"
        ];

        while ($something->fetchNext()) {

            $continue = true;

            foreach ($validItems as $item) {
                if (strpos($something->getValue('itemTypeDescription'), $item) !== false) {
                    $continue = false;
                }
            }

            if ($continue) {
                continue;
            }
            $contracts[] = [
                'itemTypeDescription' => $something->getValue("itemTypeDescription"),
                'customerItemID' => $something->getValue("customerItemID"),
                'itemDescription' => $something->getValue('itemDescription')
            ];
        }

        return $contracts;

    }

    function runIt($customerID, $firstName, $lastName, $emailAddress, $sendToSignable = false)
    {
        $mainPDF = new \setasign\Fpdi\Fpdi();
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


        uasort($contracts,
            function ($a, $b) {
                if (strcmp($a['itemTypeDescription'], $b['itemTypeDescription']) === 0) {
                    return strcmp($a['itemDescription'], $b['itemDescription']);
                }
                return strcmp($a['itemTypeDescription'], $b['itemTypeDescription']);
            });

        $this->addPages($mainPDF, $contracts);

        $pageCount = $mainPDF->setSourceFile(__DIR__ . '/PDF-resources/Terms & Conditions April 2018 branded.pdf');
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pageId = $mainPDF->importPage($pageNo);
            $s = $mainPDF->getTemplatesize($pageId);
            $mainPDF->AddPage($s['orientation'], $s);
            $mainPDF->useImportedPage($pageId);
        }

        $pageCount = $mainPDF->setSourceFile(__DIR__ . '/PDF-resources/lastPage.pdf');
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pageId = $mainPDF->importPage($pageNo);
            $s = $mainPDF->getTemplatesize($pageId);
            $mainPDF->AddPage($s['orientation'], $s);
            $mainPDF->useImportedPage($pageId);
        }

        $fileName = 'GDPR Documents/' . $customerID . '-GDPR.pdf';

        $mainPDF->Output('F', $fileName);

        if ($sendToSignable) {

            $buMail = new BUMail($this);

            $toEmail = $emailAddress;

            $hdrs = array(
                'From' => 'support@cnc-ltd.co.uk',
                'To' => $toEmail,
                'Subject' => "General Data Protection Regulations - Action Required",
                'Date' => date("r"),
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
                'text_charset' => 'UTF-8',
                'html_charset' => 'UTF-8',
                'head_charset' => 'UTF-8'
            );

            $thisBody = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->send(
                $emailAddress,
                $hdrs,
                $thisBody
            );

            $this->generateEnvelope($fileName, $firstName, $lastName, $emailAddress, $customerID);
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


    function addPages(\setasign\Fpdi\Fpdi $mainPDF, $contracts)
    {

        foreach ($contracts as $contract) {
            // Validation and setting of variables

            $buCustomerItem = new BUCustomerItem($this);
            $buCustomerItem->getCustomerItemByID($contract["customerItemID"], $dsContract);
            $buCustomerItem->getCustomerItemsByContractID($contract["customerItemID"], $dsCustomerItem);

            $buSite = new BUSite($this);
            $buActivity = new BUActivity($this);
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID($dsContract->getValue('customerID'), $dsCustomer);
            $buSite->getSiteByID($dsContract->getValue('customerID'), $dsContract->getValue('siteNo'), $dsSite);
            $customerHasServiceDeskContract = $buCustomerItem->customerHasServiceDeskContract($dsContract->getValue('customerID'));

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
                $mainPDF->AddPage($s['orientation'], $s);
                $mainPDF->useImportedPage($pageId);
            }
        }

    }


    function generateEnvelope($fileName, $firstName, $lastName, $email, $customerID)
    {
        \Signable\ApiClient::setApiKey("fc2d9ba05f3f3d9f2e9de4d831e8fed9");

        $envDocs = [];

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
            0,
            json_encode(["reportId" => 'test'])
        );

        if ($response && $response->http == 202) {
            //all went alright!! store the envelope fingerprint
            echo 'Creation of envelope successful';
        } else {
            echo 'creation of envelope failed';
        }
    }
}

$test = new OneOffPDF();
$csv = fopen('c:/Temp/gdpr-data.csv', 'r');

$firstLine = fgetcsv($csv);

while ($row = fgetcsv($csv)) {
    $test->runIt($row[0], $row[2], $row[3], $row[11], $sendToSignable);
}




