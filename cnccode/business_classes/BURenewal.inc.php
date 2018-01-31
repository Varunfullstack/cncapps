<?php /**
 * Renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
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


class BURenewal extends Business
{
    private $dbeJContract;
    private $dbeCustomer;

    /**
     * Constructor
     * @access Public
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
     * @param integer $itemID the itemID
     * @param string $page the controller page
     * @return object The appropriate renewal business object for this itemID
     */
    function getRenewalBusinessObject($renewalTypeID, &$page)
    {

        $this->setMethodName('getRenewalBusinessObject');

        if ($renewalTypeID == '') {
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
     * Process any requests for renwal shedule emails from portal
     *
     */
    function processRenewalEmailRequests()
    {
        $this->dbeCustomer->getRenewalRequests();
        $this->getData($this->dbeCustomer, $dsCustomer);
        while ($dsCustomer->fetchNext()) {
            $this->sendRenewalEmailToCustomer($dsCustomer);

            $this->dbeCustomer->getRow($dsCustomer->getValue('customerID'));
            $this->dbeCustomer->setValue('sendContractEmail', '');
            $this->dbeCustomer->updateRow();
        }
    }

    function sendRenewalEmailToCustomer($dsCustomer)
    {
        /*
        Start new email
        */
        $buMail = new BUMail($this);

        $toEmail = $dsCustomer->getValue('sendContractEmail');

        $senderEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales';
        $subject = 'Renewal Contracts';


        $hdrs = array(
            'From' => $senderName . " <" . $senderEmail . ">",
            'To' => $toEmail,
            'Subject' => $subject,
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
        $template->set_file('page', 'RenewalScheduleEmail.inc.html');

        $this->dbeJContract->getRowsByCustomerID($dsCustomer->getValue('customerID'));
        $this->getData($this->dbeJContract, $dsRenewal);

        $renewalCount = 0;

        while ($dsRenewal->fetchNext()) {
            if ($dsRenewal->getValue('renewalTypeID') != CONFIG_QUOTATION_RENEWAL_TYPE_ID) {
                $pdfFile = $this->getRenewalAsPdfString($dsRenewal->getValue('customerItemID'));
                $buMail->mime->addAttachment(
                    $pdfFile,
                    'Application/pdf',
                    $dsRenewal->getValue('itemDescription') . '.pdf'
                );
                $renewalCount++;
            }
        }

        if ($renewalCount > 0) {
            $this->addCustomerAcceptedDocumentsToEmail($dsCustomer->getValue('customerID'), $buMail);

            $template->parse('output', 'page', true);
            $buMail->mime->setHTMLBody($template->get_var('output'));
            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset' => 'UTF-8',
                'html_charset' => 'UTF-8',
                'head_charset' => 'UTF-8'
            );
            $body = $buMail->mime->get($mime_params);
            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
        }

    }

    function getRenewalAsPdfString($customerItemID)
    {
        $buCustomerItem = new BUCustomerItem($this);
        $buCustomerItem->getCustomerItemByID($customerItemID, $dsContract);
        $buCustomerItem->getCustomerItemsByContractID($customerItemID, $dsCustomerItem);
        $buSite = new BUSite($this);
        $buActivity = new BUActivity($this);
        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID($dsContract->getValue('customerID'), $dsCustomer);
        $buSite->getSiteByID($dsContract->getValue('customerID'), $dsContract->getValue('siteNo'), $dsSite);
        $customerHasServiceDeskContract =
            $buCustomerItem->customerHasServiceDeskContract($dsContract->getValue('customerID'));

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
        $this->getData($this->dbeCustomer, $dsCustomer);

        while ($dsCustomer->fetchNext()) {

            $this->sendTandcEmailToCustomer($dsCustomer);

            $this->dbeCustomer->getRow($dsCustomer->getValue('customerID'));
            $this->dbeCustomer->setValue('sendTandcEmail', '');
            $this->dbeCustomer->updateRow();

        }
    }

    function sendTandcEmailToCustomer($dsCustomer)
    {
        global $db;
        /*
        Start new email
        */
        $buMail = new BUMail($this);

        $toEmail = $dsCustomer->getValue('sendTandcEmail');

        $senderEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales';
        $subject = 'Accepted Terms & Conditions - ' . $dsCustomer->getValue('name');


        $hdrs = array(
            'From' => $senderName . " <" . $senderEmail . ">",
            'To' => $toEmail,
            'Subject' => $subject,
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
        $template->set_file('page', 'TermsAndConditionsEmail.inc.html');

        $this->addCustomerAcceptedDocumentsToEmail($dsCustomer->getValue('customerID'), $buMail);

        $template->parse('output', 'page', true);
        $buMail->mime->setHTMLBody($template->get_var('output'));
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset' => 'UTF-8',
            'html_charset' => 'UTF-8',
            'head_charset' => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);
        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );


    }

    function addCustomerAcceptedDocumentsToEmail($customerID, &$buMail)
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
     * @param bool $displayAccountsInfo
     * @param Controller $controller
     * @return array
     */
    function getRenewalsAndExternalItemsByCustomer($customerID, $displayAccountsInfo = true, $controller)
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
                        'ID' => $dbeJRenContract->getValue('customerItemID')
                    )
                );

            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenContract->getValue('curUnitSale');
                $row['costPrice'] = $dbeJRenContract->getValue('curUnitCost');
            } else {
                $row['salePrice'] = '';
                $row['costPrice'] = '';
            }
            $row['description'] = $dbeJRenContract->getValue('itemDescription');
            $row['customerItemID'] = $dbeJRenContract->getValue('customerItemID');
            $row['itemTypeDescription'] = $dbeJRenContract->getValue('itemTypeDescription');
            $row['notes'] = $dbeJRenContract->getValue('notes');
            $row['expiryDate'] = $dbeJRenContract->getValue('invoiceFromDate');
            /*
            Build list of covered items
            */
            $buCustomerItem->getCustomerItemsByContractID(
                $dbeJRenContract->getValue('customerItemID'),
                $dsLinkedItems
            );

            $row['coveredItems'] = array();

            while ($dsLinkedItems->fetchNext()) {

                $description = $dsLinkedItems->getValue('itemDescription');
                if ($dsLinkedItems->getValue('serverName')) {
                    $description .= ' (' . $dsLinkedItems->getValue('serverName') . ')';
                }
                if ($dsLinkedItems->getValue('serialNo')) {
                    $description .= ' ' . $dsLinkedItems->getValue('serialNo');
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
                        'ID' => $dbeJRenDomain->getValue('customerItemID')
                    )
                );
            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenDomain->getValue('salePrice');
                $row['costPrice'] = $dbeJRenDomain->getValue('costPrice');
            } else {
                $row['salePrice'] = '';
                $row['costPrice'] = '';
            }
            $row['description'] = $dbeJRenDomain->getValue('itemDescription');
            $row['customerItemID'] = $dbeJRenDomain->getValue('customerItemID');
            $row['itemTypeDescription'] = $dbeJRenDomain->getValue('itemTypeDescription');
            $row['notes'] = $dbeJRenDomain->getValue('notes');
            $row['expiryDate'] = $dbeJRenDomain->getValue('invoiceFromDate');

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
                        'ID' => $dbeJRenBroadband->getValue('customerItemID')
                    )
                );

            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenBroadband->getValue('salePricePerMonth') * 12;
                $row['costPrice'] = $dbeJRenBroadband->getValue('costPricePerMonth') * 12;
            } else {
                $row['salePrice'] = '';
                $row['costPrice'] = '';
            }

            $row['description'] = $dbeJRenBroadband->getValue('itemDescription');
            $row['customerItemID'] = $dbeJRenBroadband->getValue('customerItemID');
            $row['itemTypeDescription'] = $dbeJRenBroadband->getValue('itemTypeDescription');
            $row['notes'] = $dbeJRenBroadband->getValue('adslPhone');
            $row['expiryDate'] = $dbeJRenBroadband->getValue('invoiceFromDate');

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
                        'ID' => $dbeJRenHosting->getValue('customerItemID')
                    )
                );

            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenHosting->getValue('curUnitSale');
                $row['costPrice'] = $dbeJRenHosting->getValue('curUnitCost');
            } else {
                $row['salePrice'] = '';
                $row['costPrice'] = '';
            }

            $row['description'] = $dbeJRenHosting->getValue('itemDescription');
            $row['customerItemID'] = $dbeJRenHosting->getValue('customerItemID');
            $row['itemTypeDescription'] = $dbeJRenHosting->getValue('itemTypeDescription');
            $row['notes'] = $dbeJRenHosting->getValue('notes');
            $row['expiryDate'] = $dbeJRenHosting->getValue('invoiceFromDate');

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
                        'ID' => $dbeJRenQuotation->getValue('customerItemID')
                    )
                );

            if ($displayAccountsInfo) {
                $row['salePrice'] = $dbeJRenQuotation->getValue('salePrice') * $dbeJRenQuotation->getValue('qty');
                $row['costPrice'] = $dbeJRenQuotation->getValue('costPrice') * $dbeJRenQuotation->getValue('qty');
            } else {
                $row['salePrice'] = '';
                $row['costPrice'] = '';
            }

            $row['description'] = $dbeJRenQuotation->getValue('itemDescription');
            $row['customerItemID'] = $dbeJRenQuotation->getValue('customerItemID');
            $row['itemTypeDescription'] = $dbeJRenQuotation->getValue('itemTypeDescription');
            $row['notes'] = $dbeJRenQuotation->getValue('notes');
            $row['expiryDate'] = $dbeJRenQuotation->getValue('nextPeriodStartDate');

            $returnArray[] = $row;

        }

        $buExternalItem = new BUExternalItem($this);
        $buExternalItem->getExternalItemsByCustomerID($customerID, $dsExternalItem);

        while ($dsExternalItem->fetchNext()) {

            $row['linkURL'] =
                $controller->buildLink(
                    'ExternalItem.php',
                    array(
                        'action' => 'edit',
                        'externalItemID' => $dsExternalItem->getValue('externalItemID')
                    )
                );

            if ($dsExternalItem->getValue('licenceRenewalDate') > 0) {
                $row['expiryDate'] = strftime("%d/%m/%Y", strtotime($dsExternalItem->getValue('licenceRenewalDate')));
            } else {
                $row['expiryDate'] = '';
            }

            $row['description'] = $dsExternalItem->getValue('description');
            $row['customerItemID'] = null;
            $row['itemTypeDescription'] = $dsExternalItem->getValue('itemTypeDescription');
            $row['notes'] = $dsExternalItem->getValue('notes');

            $returnArray[] = $row;

        }
        // End external items
        return $returnArray;

    }
}// End of class
?>