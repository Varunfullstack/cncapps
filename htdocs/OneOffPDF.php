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
require_once($cfg['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUPDFSupportContract.inc.php');
require_once($cfg['path_dbe'] . '/DBEJContract.inc.php');
require_once($cfg['path_ct'] . '/CTCustomerItem.inc.php');
require_once __DIR__ . '/../vendor/autoload.php';
$returnArray = array();


class OneOffPDF
{
    function runIt($customerID)
    {
        $mainPDF = new \setasign\Fpdi\Fpdi();
        $buCustomerItem = new BUCustomerItem($this);

// Start contracts
        $dbeJRenContract = new DBEJRenContract($this);
        $dbeJRenContract->getRowsByCustomerID($customerID);


        $this->addPages($mainPDF, $dbeJRenContract);


//http://cncapps/CustomerItem.php?action=printContract&customerItemID=36745

// Domains
        $dbeJRenDomain = new DBEJRenDomain($this);
        $dbeJRenDomain->getRowsByCustomerID($customerID);

        $this->addPages($mainPDF, $dbeJRenDomain);
// end domains

//start broadband
        $dbeJRenBroadband = new DBEJRenBroadband($this);
        $dbeJRenBroadband->getRowsByCustomerID($customerID);
        $this->addPages($mainPDF, $dbeJRenBroadband);

// Hosting
        $dbeJRenHosting = new DBEJRenHosting($this);
        $dbeJRenHosting->getRowsByCustomerID($customerID);
        $this->addPages($mainPDF, $dbeJRenHosting);

//$buExternalItem = new BUExternalItem($this);
//$buExternalItem->getExternalItemsByCustomerID($customerID, $dsExternalItem);
//addPages($mainPDF, $buExternalItem);


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

        $mainPDF->Output('F', 'test.pdf');

        $this->generateEnvelope("test.pdf");

//header('Pragma: public');
////header('Expires: 0');
////header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
////header('Content-Type: application/pdf');
////header('Content-Disposition: attachment; filename=contract.pdf;');
////header('Content-Transfer-Encoding: binary');
////header('Content-Length: ' . filesize('test.pdf'));
////readfile('test.pdf');
////unlink('test.pdf');
        ?>
        <a href="/test.pdf">Link</a>
        <?php
    }


    function addPages(\setasign\Fpdi\Fpdi $mainPDF, $contracts)
    {

        $validItems = [
            "2nd Site",
            "Internet Services",
            "Managed Service",
            "ServerCare",
            "ServiceDesk",
            "Telecom Services"
        ];

        while ($contracts->fetchNext()) {

            $continue = true;

//        if ($contracts->getValue('renewalTypeID') === 3) {
//            //this is a renewal ignore
//            continue;
//        }

            foreach ($validItems as $item) {
                if (strpos($item, $contracts->getValue('itemTypeDescription')) !== false) {
                    $continue = false;
                }
            }

            if ($continue) {
                continue;
            }


            echo '<br>contract: ' . $contracts->getValue("customerItemID") . " of type " . $contracts->getValue('itemTypeDescription');

            // Validation and setting of variables

            $buCustomerItem = new BUCustomerItem($this);
            $buCustomerItem->getCustomerItemByID($contracts->getValue("customerItemID"), $dsContract);
            $buCustomerItem->getCustomerItemsByContractID($contracts->getValue("customerItemID"), $dsCustomerItem);

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


    function generateEnvelope($fileName)
    {
        \Signable\ApiClient::setApiKey("fc2d9ba05f3f3d9f2e9de4d831e8fed9");

        $envDocs = [];

        $envelopeDocument = new DocumentWithoutTemplate(
            'GDPR document',
            null,
            base64_encode(file_get_contents($fileName)),
            "contracts.pdf"
        );

        $envDocs[] = $envelopeDocument;

        $envelopeParties = [];

        $envelopeParty = new Party(
            'client name',
            'AdrianC@cnc-ltd.co.uk',
            'signer1',
            'Please sign here',
            'no',
            false
        );
        $envelopeParties[] = $envelopeParty;


        $response = Envelopes::createNewWithoutTemplate(
            "Document ##" . uniqid(),
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
            echo 'Creation of evenlop successful';
        } else {
            echo 'creation of envelope failed';
        }
    }
}

$test = new OneOffPDF();
$customerID = 2554;
$test->runIt($customerID);



