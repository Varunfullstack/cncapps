<?php
/**
 * Daily Helpdesk Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEJContract.inc.php');
require_once($cfg['path_dbe'] . '/DBERenContract.inc.php');
require_once($cfg['path_dbe'] . '/DBERenDomain.inc.php');
require_once($cfg['path_dbe'] . '/DBERenBroadband.inc.php');
require_once($cfg['path_dbe'] . '/DBERenQuotation.inc.php');
require_once($cfg['path_dbe'] . '/DBERenHosting.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUExternalItem.inc.php');
require_once($cfg ['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_bu'] . '/BURenewal.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
require_once($cfg ['path_bu'] . '/BUSalesOrder.inc.php');

class CTRenewalReport extends CTCNC
{

    var $dsActivtyEngineer = '';
    var $dsSearchForm = '';
    var $page = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $this->dsSearchForm->setValue('customerID', '');

    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            case 'produceReport':
                $this->page = $this->produceReport(false, false);
                break;

            case 'producePdfReport':
                $this->page = $this->produceReport(false, true);
                break;

            case 'createQuote':
                $this->createQuote();
                break;

            case 'Search':
            default:
                $this->search();
                break;

        }
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

                    $urlProducePdf =
                        $this->buildLink(
                            $_SERVER ['PHP_SELF'],
                            array(
                                'action' => 'producePdfReport',
                                'customerID' => $customerID
                            )
                        );

                    $producePdfLink = '<a href="' . $urlProducePdf . '">Download PDF</a>';
                }

            }

        }

        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'RenewalReportSearch' => 'RenewalReportSearch.inc'
            )
        );

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));


        $this->setPageTitle('Renewal Report');

        if ($this->dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($this->dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue('name');
        }
        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER, array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $this->dsSearchForm->getValue('customerID'),
                'customerIDMessage' => $this->dsSearchForm->getMessage('customerID'),
                'customerString' => $customerString,
                'urlCustomerPopup' => $urlCustomerPopup,
                'producePdfLink' => $producePdfLink,
                'urlSubmit' => $urlSubmit,
                'report' => $report
            )
        );

        $this->template->parse('CONTENTS', 'RenewalReportSearch', true);

        $this->parsePage();

    } // end function displaySearchForm

    /**
     * @access private
     */
    function produceReport($customerID = false, $createPdf = false)
    {
        $this->setMethodName('produceReport');

        if ($createPdf) {
            $this->setHTMLFmt(CT_HTML_FMT_PDF);
        }

        $this->setTemplateFiles('RenewalReport', 'RenewalReport.inc');

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

        $this->template->set_var('customerName', $dbeCustomer->getValue('name'));

        $buRenewal = new BURenewal($this);

        $items = $buRenewal->getRenewalsAndExternalItemsByCustomer($customerID, $displayAccountsInfo, $this);

        usort($items, function ($a, $b) {
            return $a['itemTypeDescription'] <=> $b['itemTypeDescription'];
        });

        $lastItemTypeDescription = false;

        $this->template->set_block('RenewalReport', 'itemBlock', 'items');

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


            $this->template->set_var(
                array(
                    'linkURL' => $item['linkURL'],
                    'notes' => $item['notes'],
                    'description' => Controller::htmlDisplayText($item['description']),
                    'itemTypeDescription' => Controller::htmlDisplayText($item['itemTypeDescription']),
                    'expiryDate' => Controller::htmlDisplayText($item['expiryDate']),
                    'salePrice' => $salePrice,
                    'costPrice' => $costPrice,
                    'customerItemID' => $item['customerItemID'],
                    'coveredItemsString' => $coveredItemsString,
                    'itemClass' => $itemClass
                )
            );

            $this->template->parse('items', 'itemBlock', true);
        }

        /*
        External Items
        */
        $addExternalItemURL =
            $this->buildLink(
                'ExternalItem.php',
                array(
                    'action' => 'add',
                    'customerID' => $customerID
                )
            );


        $this->template->set_var(
            array(
                'addExternalItemURL' => $addExternalItemURL
            )
        );

        if ($displayAccountsInfo) {
            $urlCreateQuote = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => 'createQuote'));


            $this->template->set_var(
                array(
                    'totalSalePrice' => Controller::formatNumber($totalSalePrice),
                    'totalCostPrice' => Controller::formatNumber($totalCostPrice),
                    'urlCreateQuote' => $urlCreateQuote,
                    'buttonCreateQuote' => '<input type="submit" value="Quote">'
                )
            );
        }
        if ($createPdf) {
            $this->template->parse('CONTENTS', 'RenewalReport', true);

            $this->template->parse("CONTENTS", "page");

            $output = $this->template->get("CONTENTS");

            require_once BASE_DRIVE . '/vendor/autoload.php';

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);


            /* @todo: set template dir */
            $dompdf->setBasePath(BASE_DRIVE . '/htdocs');   // so we can get the images and css

            $dompdf->loadHtml($output);

            set_time_limit(120);                           // it may take some time!

            $dompdf->setPaper('a4', 'landscape');

            $dompdf->render();

            $dompdf->add_info('Title', 'Renewal Report - ' . $dbeCustomer->getValue('name'));

            $dompdf->add_info('Author', 'CNC Ltd');

            $dompdf->add_info('Subject', 'Renewal Report');

            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename='downloaded.pdf'");
            echo $dompdf->output();
            exit;
        } elseif ($calledFromSearch) {
            $this->template->parse('output', 'RenewalReport', true);

            return $this->template->get_var('output');

        } else {
            $this->template->parse('CONTENTS', 'RenewalReport', true);

            $this->parsePage();
        }


    }

    function createQuote()
    {
        /*
        Go trhough diferent types of renewal creating a quote for each
        */
        if ($_REQUEST['selectedRenewal']['domain']) {
            $buRenDomain = new BURenDomain($this);
            $ordheadIDs[] = $buRenDomain->createRenewalsSalesOrders($_REQUEST['selectedRenewal']['domain']);
        }

        if ($_REQUEST['selectedRenewal']['contract']) {
            $buRenContract = new BURenContract($this);
            $ordheadIDs[] = $buRenContract->createRenewalsSalesOrders($_REQUEST['selectedRenewal']['contract']);
        }

        if ($_REQUEST['selectedRenewal']['broadband']) {
            $buRenBroadband = new BURenBroadband($this);
            $ordheadIDs[] = $buRenBroadband->createRenewalsSalesOrders($_REQUEST['selectedRenewal']['broadband']);
        }

        if ($_REQUEST['selectedRenewal']['quotation']) {
            $buRenQuotation = new BURenQuotation($this);
            $ordheadIDs[] = $buRenQuotation->createRenewalsQuotations($_REQUEST['selectedRenewal']['quotation']);
        }
        /*
        Join the quotes together
        */
        if ($ordheadIDs) {

            $buSalesOrder = new BUSalesOrder($this);

            foreach ($ordheadIDs as $key => $ordheadID) {
                if ($key == 0) {
                    $toOrdheadID = $ordheadID; // apend to first order
                    continue;
                }
                $buSalesOrder->pasteLinesFromOrder($ordheadID, $toOrdheadID, true);
                // delete copied order
                $buSalesOrder->deleteOrder($ordheadID);
            }


            $nextURL =
                $this->buildLink(
                    'SalesOrder.php',
                    array(
                        'action' => 'displaySalesOrder',
                        'ordheadID' => $toOrdheadID
                    )
                );
        } else {
            // nothing checked so back to display
            $nextURL =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array()
                );
        }

        header('Location: ' . $nextURL);
        exit;
    }

}// end of class
?>