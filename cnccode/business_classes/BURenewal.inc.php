<?php /**
 * Renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Business\BURenContract;
use CNCLTD\Exceptions\ContactNotFoundException;


require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BURenBroadband.inc.php");
require_once($cfg["path_bu"] . "/Burencontract.php");
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

class BURenewal extends Business
{
    private $dbeJContract;
    private $dbeCustomer;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJContract = new DBEJContract($this);
        $this->dbeCustomer = new DBECustomer($this);
    }

    /**
     * Given the itemID of an item this function returns an instance of
     * the appropriate business class
     *
     * @param $renewalTypeID
     * @param string $page the controller page
     * @return object The appropriate renewal business object for this itemID
     */
    function getRenewalBusinessObject($renewalTypeID,
                                      &$page
    )
    {

        $this->setMethodName('getRenewalBusinessObject');

        if (!$renewalTypeID) {
            $this->raiseError('$renewalTypeID not passed');
        }

        switch ($renewalTypeID) {

            case CONFIG_CONTRACT_RENEWAL_TYPE_ID:
                $buRenewal = new BURenContract($this);
                $page = 'RenContract.php';
                break;

            case CONFIG_QUOTATION_RENEWAL_TYPE_ID:
                $buRenewal = new BURenQuotation($this);
                $page = 'RenQuotation.php';
                break;

            case CONFIG_DOMAIN_RENEWAL_TYPE_ID:
                $buRenewal = new BURenDomain($this);
                $page = 'RenDomain.php';
                break;

            case CONFIG_HOSTING_RENEWAL_TYPE_ID:
                $buRenewal = new BURenHosting($this);
                $page = 'RenHosting.php';
                break;

            case CONFIG_BROADBAND_RENEWAL_TYPE_ID:
            default:
                $buRenewal = new BURenBroadband($this);
                $page = 'RenBroadband.php';
                break;
        }

        return $buRenewal;

    }

    /**
     * Process any requests for renewal schedule emails from portal
     *
     */
    function processRenewalEmailRequests()
    {
        $this->dbeCustomer->getRenewalRequests();
        $dsCustomer = new DataSet($this);
        $this->getData(
            $this->dbeCustomer,
            $dsCustomer
        );
        while ($dsCustomer->fetchNext()) {
            $buCustomer = new BUCustomer($this);
            $primaryContact = $buCustomer->getPrimaryContact($dsCustomer->getValue(DBECustomer::customerID));
            if(!$primaryContact){
                throw new Exception("Customer {$dsCustomer->getValue(DBECustomer::customerID)} does not have a valid primary main contact");
            }
            $this->sendRenewalEmailToContact($primaryContact->getValue(DBEContact::contactID));
            $this->dbeCustomer->getRow($dsCustomer->getValue(DBECustomer::customerID));
            $this->dbeCustomer->setValue(
                DBECustomer::sendContractEmail,
                null
            );
            $this->dbeCustomer->updateRow();
        }
    }

    /**
     * @param $contactId
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    function sendRenewalEmailToContact($contactId)
    {
        $dbeContact = new DBEContact($this);
        if (!$dbeContact->getRow($contactId)) {
            throw new ContactNotFoundException();
        }
        $toEmail = $dbeContact->getValue(DBEContact::email);
        $contactFirstName = $dbeContact->getValue(DBEContact::firstName);
        $subject = 'CNC Renewal Contracts';

        global $twig;

        $this->dbeJContract->getRowsByCustomerID($dbeContact->getValue(DBEContact::customerID), null);
        $dsRenewal = new DataSet($this);
        $this->getData(
            $this->dbeJContract,
            $dsRenewal
        );

        $renewalCount = 0;
        $attachments = new \CNCLTD\Email\AttachmentCollection();
        while ($dsRenewal->fetchNext()) {
            if ($dsRenewal->getValue(DBEJContract::renewalTypeID) != CONFIG_QUOTATION_RENEWAL_TYPE_ID) {
                $pdfFile = $this->getRenewalAsPdfString($dsRenewal->getValue(DBEJContract::customerItemID));
                $attachments->add(
                    $pdfFile,
                    'Application/pdf',
                    $dsRenewal->getValue(DBEJContract::itemDescription) . '.pdf',
                    false
                );
                $renewalCount++;
            }
        }

        $body = $twig->render(
            '@customerFacing/RenewalSchedule/RenewalSchedule.html.twig',
            ["contactFirstName" => $contactFirstName, "hasRenewals" => $renewalCount > 0]
        );
        $buMail = new BUMail($this);
        $buMail->sendEmailWithAttachments($body, $subject, $toEmail, $attachments, CONFIG_SALES_EMAIL);
    }

    function getRenewalAsPdfString($customerItemID)
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract = new DataSet($this);
        $buCustomerItem->getCustomerItemByID(
            $customerItemID,
            $dsContract
        );
        $buCustomerItem->getCustomerItemsByContractID(
            $customerItemID,
            $dsCustomerItem
        );
        $buSite = new BUSite($this);
        $buActivity = new BUActivity($this);
        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID(
            $dsContract->getValue(DBECustomerItem::customerID),
            $dsCustomer
        );
        $buSite->getSiteByID(
            $dsContract->getValue(DBECustomerItem::customerID),
            $dsContract->getValue(DBECustomerItem::siteNo),
            $dsSite
        );
        $customerHasServiceDeskContract =
            $buCustomerItem->customerHasServiceDeskContract($dsContract->getValue(DBECustomerItem::customerID));

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

        return $buPDFSupportContract->generateFile();
    }

    function processTandcEmailRequests()
    {
        $this->dbeCustomer->getTandcRequests();
        $dsCustomer = new DataSet($this);
        $this->getData(
            $this->dbeCustomer,
            $dsCustomer
        );

        while ($dsCustomer->fetchNext()) {
            $this->sendTermsAndConditionsEmailToContact($dsCustomer);

            $this->dbeCustomer->getRow($dsCustomer->getValue(DBECustomer::customerID));
            $this->dbeCustomer->setValue(
                DBECustomer::sendTandcEmail,
                null
            );
            $this->dbeCustomer->updateRow();
        }
    }

    /**
     * @param $contactId
     * @throws ContactNotFoundException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    function sendTermsAndConditionsEmailToContact($contactId)
    {

        $dbeContact = new DBEContact($this);
        if (!$dbeContact->getRow($contactId)) {
            throw new ContactNotFoundException();
        }
        $recipient = $dbeContact->getValue(DBEContact::email);
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($dbeContact->getValue(DBEContact::customerID));

        $subject = "Accepted Terms & Conditions - {$dbeCustomer->getValue(DBECustomer::name)}";
        $attachments = new \CNCLTD\Email\AttachmentCollection();
        $attachments->add(
            PDF_RESOURCE_DIR . '/Terms & Conditions.pdf',
            'Application/pdf',
            'Terms & Conditions.pdf',
            true
        );
        global $twig;
        $body = $twig->render(
            '@customerFacing/TermsAndConditions/TermsAndConditions.html.twig',
            ["contactFirstName" => $dbeContact->getValue(DBEContact::firstName)]
        );
        $buMail = new BUMail($this);
        $buMail->sendEmailWithAttachments($body, $subject, $recipient, $attachments, CONFIG_SALES_EMAIL);
    }

    function addCustomerAcceptedDocumentsToEmail($customerID,
                                                 &$buMail
    )
    {
        global $db;

        /* Add customer accepted contracts */
        $statement =
            "SELECT
        pd.description,
        pd.file,
        pd.fileMimeType,
        pd.filename
      FROM
        portal_document pd
        JOIN portal_document_acceptance pdo ON pdo.portalDocumentID = pd.portalDocumentID
      WHERE
        pdo.customerID = " . $customerID;

        $db->query($statement);

        while ($db->next_record()) {
            $buMail->mime->addAttachment(
                $db->Record['file'],
                $db->Record['fileMimeType'],
                $db->Record['filename'],
                false                           // file is a string
            );
        }

    }

    /**
     * create an array of renewals items and external items
     *
     * @param mixed $customerID
     * @param Controller $controller
     * @param bool $displayAccountsInfo
     * @return array
     * @throws Exception
     */
    function getRenewalsAndExternalItemsByCustomer($customerID,
                                                   $controller,
                                                   $displayAccountsInfo = true
    )
    {
        $returnArray = array();

        $buCustomerItem = new BUCustomerItem($this);

        // Start contracts
        $dbeJRenContract = new DBEJRenContract($this);
        $dbeJRenContract->getRowsByCustomerID($customerID);

        while ($dbeJRenContract->fetchNext()) {

            $row = array();

            $row['linkURL'] =
                $controller->buildLink(
                    'RenContract.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dbeJRenContract->getValue(DBEJRenContract::customerItemID)
                    )
                );

            $row['salePrice'] = null;
            $row['costPrice'] = null;
            $row['units'] = $dbeJRenContract->getValue(DBEJRenContract::users);


            $row['directDebit'] = $dbeJRenContract->getValue(DBEJRenContract::directDebitFlag) == 'Y';
            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenContract->getValue(DBEJRenContract::curUnitSale);
                $row['costPrice'] = $dbeJRenContract->getValue(DBEJRenContract::curUnitCost);
            }
            $row['description'] = $dbeJRenContract->getValue(DBEJRenContract::itemDescription);
            $row['customerItemID'] = $dbeJRenContract->getValue(DBEJRenContract::customerItemID);
            $row['itemTypeDescription'] = $dbeJRenContract->getValue(DBEJRenContract::itemTypeDescription);
            $row['notes'] = $dbeJRenContract->getValue(DBEJRenContract::notes);
            $row['expiryDate'] = $dbeJRenContract->getValue(DBEJRenContract::invoiceFromDate);
            $row['itemID'] = $dbeJRenContract->getValue(DBEJRenContract::itemID);
            $row['renewalTypeID'] = 2;
            $row['itemTypeId'] = $dbeJRenContract->getValue(DBEJRenContract::itemTypeId);


            $expiryDate = null;

            if ($installationDate = DateTime::createFromFormat(
                'Y-m-d',
                $dbeJRenContract->getValue(DBECustomerItem::installationDate)
            )) {
                $expiryDate = getExpiryDate(
                    $installationDate,
                    $dbeJRenContract->getValue(
                        DBECustomerItem::initialContractLength
                    )
                )->format('d/m/Y');
            }

            $row['calculatedExpiryDate'] = $expiryDate;

            /*
            Build list of covered items
            */
            $dsLinkedItems = new DataSet($this);
            $buCustomerItem->getCustomerItemsByContractID(
                $dbeJRenContract->getValue(DBEJRenContract::customerItemID),
                $dsLinkedItems
            );

            $row['coveredItems'] = array();

            while ($dsLinkedItems->fetchNext()) {

                $description = $dsLinkedItems->getValue(DBEJCustomerItem::itemDescription);
                if ($dsLinkedItems->getValue(DBEJCustomerItem::serverName)) {
                    $description .= ' (' . $dsLinkedItems->getValue(DBEJCustomerItem::serverName) . ')';
                }
                if ($dsLinkedItems->getValue(DBEJCustomerItem::serialNo)) {
                    $description .= ' ' . $dsLinkedItems->getValue(DBEJCustomerItem::serialNo);
                }

                $row['coveredItems'][] = $description;
            }

            $returnArray[] = $row;
        } // end contracts

        // Domains
        $dbeJRenDomain = new DBEJRenDomain($this);
        $dbeJRenDomain->getRowsByCustomerID($customerID);

        while ($dbeJRenDomain->fetchNext()) {

            $row = array();

            $row['linkURL'] =
                $controller->buildLink(
                    'RenDomain.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dbeJRenDomain->getValue(DBEJRenDomain::customerItemID)
                    )
                );
            $row['salePrice'] = null;
            $row['costPrice'] = null;
            $row['units'] = $dbeJRenDomain->getValue(DBEJRenContract::users);
            $row['directDebit'] = $dbeJRenDomain->getValue(DBEJRenContract::directDebitFlag) == 'Y';
            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenDomain->getValue(DBEJRenDomain::salePrice);
                $row['costPrice'] = $dbeJRenDomain->getValue(DBEJRenDomain::costPrice);
            }
            $row['description'] = $dbeJRenDomain->getValue(DBEJRenDomain::itemDescription);
            $row['customerItemID'] = $dbeJRenDomain->getValue(DBEJRenDomain::customerItemID);
            $row['itemTypeDescription'] = $dbeJRenDomain->getValue(DBEJRenDomain::itemTypeDescription);
            $row['notes'] = $dbeJRenDomain->getValue(DBEJRenDomain::notes);
            $row['expiryDate'] = $dbeJRenDomain->getValue(DBEJRenDomain::invoiceFromDate);
            $row['itemID'] = $dbeJRenDomain->getValue(DBEJRenDomain::itemID);
            $row['renewalTypeID'] = 4;
            $row['coveredItems'] = [];
            $row['itemTypeId'] = $dbeJRenDomain->getValue(DBEJRenDomain::itemTypeId);

            $installationDate = DateTime::createFromFormat(
                'Y-m-d',
                $dbeJRenContract->getValue(DBECustomerItem::installationDate)
            );
            $calculatedExpiryDate = null;
            if ($installationDate) {
                $calculatedExpiryDate = getExpiryDate(
                    $installationDate,
                    $dbeJRenContract->getValue(
                        DBECustomerItem::initialContractLength
                    )
                )->format('d/m/Y');
            }
            $row['calculatedExpiryDate'] = $calculatedExpiryDate;


            $returnArray[] = $row;
        }
        // end domains

        //start broadband
        $dbeJRenBroadband = new DBEJRenBroadband($this);
        $dbeJRenBroadband->getRowsByCustomerID($customerID);

        while ($dbeJRenBroadband->fetchNext()) {

            $row = array();

            $row['linkURL'] =
                $controller->buildLink(
                    'RenBroadband.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dbeJRenBroadband->getValue(DBEJRenBroadband::customerItemID)
                    )
                );

            $row['salePrice'] = null;
            $row['costPrice'] = null;
            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::salePricePerMonth) * 12;
                $row['costPrice'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::costPricePerMonth) * 12;
            }
            $row['units'] = $dbeJRenBroadband->getValue(DBEJRenContract::users);
            $row['directDebit'] = $dbeJRenBroadband->getValue(DBEJRenContract::directDebitFlag) == 'Y';
            $row['description'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::itemDescription);
            $row['customerItemID'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::customerItemID);
            $row['itemTypeDescription'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::itemTypeDescription);
            $row['notes'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::adslPhone);
            $row['expiryDate'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::invoiceFromDate);
            $row['itemID'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::itemID);
            $row['renewalTypeID'] = 1;
            $row['coveredItems'] = [];
            $row['itemTypeId'] = $dbeJRenBroadband->getValue(DBEJRenBroadband::itemTypeId);
            $installationDate = DateTime::createFromFormat(
                'Y-m-d',
                $dbeJRenBroadband->getValue(DBECustomerItem::installationDate)
            );

            $calculatedExpiryDate = null;
            if ($installationDate) {
                $calculatedExpiryDate = getExpiryDate(
                    $installationDate,
                    $dbeJRenBroadband->getValue(
                        DBECustomerItem::initialContractLength
                    )
                )->format('d/m/Y');
            }
            $row['calculatedExpiryDate'] = $calculatedExpiryDate;

            $returnArray[] = $row;
        }
        // Hosting
        $dbeJRenHosting = new DBEJRenHosting($this);
        $dbeJRenHosting->getRowsByCustomerID($customerID);

        while ($dbeJRenHosting->fetchNext()) {

            $row = array();

            $row['linkURL'] =
                $controller->buildLink(
                    'RenHosting.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dbeJRenHosting->getValue(DBEJRenHosting::customerItemID)
                    )
                );

            $row['salePrice'] = null;
            $row['costPrice'] = null;
            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenHosting->getValue(DBEJRenHosting::curUnitSale);
                $row['costPrice'] = $dbeJRenHosting->getValue(DBEJRenHosting::curUnitCost);
            }
            $row['units'] = $dbeJRenHosting->getValue(DBEJRenContract::users);
            $row['directDebit'] = $dbeJRenHosting->getValue(DBEJRenContract::directDebitFlag) == 'Y';
            $row['description'] = $dbeJRenHosting->getValue(DBEJRenHosting::itemDescription);
            $row['customerItemID'] = $dbeJRenHosting->getValue(DBEJRenHosting::customerItemID);
            $row['itemTypeDescription'] = $dbeJRenHosting->getValue(DBEJRenHosting::itemTypeDescription);
            $row['notes'] = $dbeJRenHosting->getValue(DBEJRenHosting::notes);
            $row['expiryDate'] = $dbeJRenHosting->getValue(DBEJRenHosting::invoiceFromDate);
            $row['itemID'] = $dbeJRenHosting->getValue(DBEJRenHosting::itemID);
            $row['itemTypeId'] = $dbeJRenHosting->getValue(DBEJRenHosting::itemTypeId);
            $row['renewalTypeID'] = 5;
            $row['coveredItems'] = [];
            $installationDate = DateTime::createFromFormat(
                'Y-m-d',
                $dbeJRenHosting->getValue(DBECustomerItem::installationDate)
            );

            $calculatedExpiryDate = null;
            if ($installationDate) {
                $calculatedExpiryDate = getExpiryDate(
                    $installationDate,
                    $dbeJRenHosting->getValue(
                        DBECustomerItem::initialContractLength
                    )
                )->format('d/m/Y');
            }
            $row['calculatedExpiryDate'] = $calculatedExpiryDate;
            $returnArray[] = $row;

        }// end hosting


        $dbeJRenQuotation = new DBEJRenQuotation($this);
        $dbeJRenQuotation->getRowsByCustomerID($customerID);

        while ($dbeJRenQuotation->fetchNext()) {

            $row = array();

            $row['linkURL'] =
                $controller->buildLink(
                    'RenQuotation.php',
                    array(
                        'action' => 'edit',
                        'ID'     => $dbeJRenQuotation->getValue(DBEJRenQuotation::customerItemID)
                    )
                );

            $row['salePrice'] = null;
            $row['costPrice'] = null;
            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenQuotation->getValue(
                        DBEJRenQuotation::salePrice
                    ) * $dbeJRenQuotation->getValue(DBEJRenQuotation::qty);
                $row['costPrice'] = $dbeJRenQuotation->getValue(
                        DBEJRenQuotation::costPrice
                    ) * $dbeJRenQuotation->getValue(DBEJRenQuotation::qty);
            }

            $row['description'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::itemDescription);
            $row['customerItemID'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::customerItemID);
            $row['itemTypeDescription'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::itemTypeDescription);
            $row['notes'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::notes);
            $row['expiryDate'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::nextPeriodStartDate);
            $row['units'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::users);
            $row['directDebit'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::directDebitFlag) == 'Y';
            $row['itemID'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::itemID);
            $row['itemTypeId'] = $dbeJRenQuotation->getValue(DBEJRenQuotation::itemTypeId);
            $row['renewalTypeID'] = 3;
            $row['coveredItems'] = [];
            $row['calculatedExpiryDate'] = (
            DateTime::createFromFormat(
                'Y-m-d',
                $dbeJRenQuotation->getValue(DBECustomerItem::startDate)
            )
            )->add(new DateInterval('P1Y'))->format('d/m/Y');
            $returnArray[] = $row;

        }

        $buExternalItem = new BUExternalItem($this);
        $dsExternalItem = new DataSet($this);
        $buExternalItem->getExternalItemsByCustomerID(
            $customerID,
            $dsExternalItem
        );

        while ($dsExternalItem->fetchNext()) {

            $row['linkURL'] =
                $controller->buildLink(
                    'ExternalItem.php',
                    array(
                        'action'         => 'edit',
                        'externalItemID' => $dsExternalItem->getValue(DBEJExternalItem::externalItemID)
                    )
                );

            $row['expiryDate'] = null;
            if ($dsExternalItem->getValue(DBEJExternalItem::licenceRenewalDate) > 0) {
                $row['expiryDate'] = strftime(
                    "%d/%m/%Y",
                    strtotime($dsExternalItem->getValue(DBEJExternalItem::licenceRenewalDate))
                );
            }

            $row['description'] = $dsExternalItem->getValue(DBEJExternalItem::description);
            $row['customerItemID'] = null;
            $row['itemTypeDescription'] = $dsExternalItem->getValue(DBEJExternalItem::itemTypeDescription);
            $row['notes'] = $dsExternalItem->getValue(DBEJExternalItem::notes);
            $row['itemTypeId'] = $dsExternalItem->getValue(DBEJExternalItem::itemTypeId);
            $row['renewalTypeID'] = 0;
            $row['coveredItems'] = [];
            $returnArray[] = $row;
        }
        return $returnArray;
    }
}
