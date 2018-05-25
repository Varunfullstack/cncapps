<?php
/**
 * CustomerAnalysis Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerAnalysisReport extends CTCNC
{

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomerAnalysisReport = new BUCustomerAnalysisReport ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->search();
    }

    function search()
    {
        global $cfg;

        $this->setMethodName('search');

        $dsSearchForm = new DSForm ($this);
        $dsResults = new DataSet ($this);

        $this->buCustomerAnalysisReport->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('CustomerAnalysisReport' => 'CustomerAnalysisReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);

                $results = $this->buCustomerAnalysisReport->getResults($dsSearchForm);

                if ($_REQUEST['Search'] == 'Generate CSV') {

                    $template = new Template ($cfg["path_templates"], "remove");

                    $template->set_file('page', 'CustomerAnalysisReport.inc.csv');

                    $template->set_block('page', 'contractsBlock', 'contracts');
                    foreach ($results as $contractName => $row) {
                        $template->set_var(
                            array(
                                'contract' => $contractName,
                                'sales' => $row['sales'],
                                'cost' => $row['cost'],
                                'labour' => $row['labourCost'],
                                'labourHours' => $row['labourHours'],
                            )
                        );
                        $template->parse('contracts', 'contractsBlock', true);
                    }
                    $template->parse('output', 'page', true);

                    $output = $template->get_var('output');

                    Header('Content-type: text/plain');
                    Header('Content-Disposition: attachment; filename=CustomerAnalysisReport.csv');
                    echo $output;
                    exit;
                } else { // Screen Report

                    $totalSales = 0;
                    $totalCost = 0;
                    $totalLabour = 0;
                    $totalLabourHours = 0;
                    $grandTotalSales = 0;
                    $grandTotalCost = 0;
                    $grandTotalLabour = 0;
                    $grandTotalLabourHours = 0;

                    if (isset($_REQUEST['orderBy'])) {
                        $columns = [
                            "sales" => [],
                            "cost" => [],
                            "labourCost" => [],
                            "labourHours" => [],
                            "profitPercent" => [],
                            "profit" => [],
                        ];
                        foreach ($results as $key => $row) {
                            $columns['sales'][$key] = $row['sales'];
                            $columns['cost'][$key] = $row['cost'];
                            $columns['labourCost'][$key] = $row['labourCost'];
                            $columns['labourHours'][$key] = $row['labourHours'];
                            $columns['profitPercent'][$key] = $row['profitPercent'];
                            $columns['profit'][$key] = $row['profit'];
                        }
                        array_multisort($columns[$_REQUEST['orderBy']], SORT_ASC, $results);
                    }

                    $this->template->set_block('CustomerAnalysisReport', 'contractsBlock', 'contracts');

                    $reportUrl =
                        $this->buildLink(
                            'CustomerAnalysisReport.php',
                            array(
                                'searchForm[1][customerID]' => $_REQUEST ['searchForm'][1]['customerID'],
                                'searchForm[1][startYearMonth]' => $_REQUEST ['searchForm'][1]['startYearMonth'],
                                'searchForm[1][endYearMonth]' => $_REQUEST ['searchForm'][1]['endYearMonth'],
                            )
                        );
                    foreach ($results as $contractName => $row) {

                        if ($row['profit'] <= 0) {
                            $profitAlertClass = 'profitAlert';
                        } else {
                            $profitAlertClass = '';
                        }

                        $target = 'contracts';
                        $handle = 'contractsBlock';

                        if ($contractName == 'Other Sales') {
                            $this->template->setBlock('CustomerAnalysisReport', 'otherSalesBlock', 'otherSales');
                            $target = 'otherSales';
                            $handle = 'otherSalesBlock';

                        } else {
                            $totalSales += $row['sales'];
                            $totalCost += $row['cost'];
                            $totalLabour += $row['labourCost'];
                            $totalLabourHours += $row['labourHours'];
                        }

                        $grandTotalSales += $row['sales'];
                        $grandTotalCost += $row['cost'];
                        $grandTotalLabour += $row['labourCost'];
                        $grandTotalLabourHours += $row['labourHours'];

                        $this->template->set_var(
                            array(
                                'contract' => $contractName,
                                'sales' => number_format($row['sales'], 2),
                                'cost' => number_format($row['cost'], 2),
                                'labour' => number_format($row['labourCost'], 2),
                                'profit' => number_format($row['profit'], 2),
                                'profitPercent' => $row['profitPercent'],
                                'labourHours' => number_format($row['labourHours'], 0),
                                'profitAlertClass' => $profitAlertClass,
                                'reportUrl' => $reportUrl
                            )
                        );
                        $this->template->parse($target, $handle, true);


                    }
                    $this->template->set_var(
                        array(
                            'totalSales' => number_format($totalSales, 2),
                            'totalCost' => number_format($totalCost, 2),
                            'totalLabour' => number_format($totalLabour, 2),
                            'totalProfit' => number_format($totalSales - $totalCost - $totalLabour, 2),
                            'totalProfitPercent' => number_format($totalSales > 0 ? 100 - (($totalCost + $totalLabour) / $totalSales) * 100 : 0,
                                                                  2),
                            'totalLabourHours' => number_format($totalLabourHours, 2),
                        )
                    );
                    $this->template->set_var(
                        array(
                            'grandTotalSales' => number_format($grandTotalSales, 2),
                            'grandTotalCost' => number_format($grandTotalCost, 2),
                            'grandTotalLabour' => number_format($grandTotalLabour, 2),
                            'grandTotalProfit' => number_format($grandTotalSales - $grandTotalCost - $grandTotalLabour,
                                                                2),
                            'grandTotalProfitPercent' => number_format($grandTotalSales > 0 ? 100 - (($grandTotalCost + $grandTotalLabour) / $grandTotalSales) * 100 : 0,
                                                                       2),
                            'grandTotalLabourHours' => number_format($grandTotalLabourHours, 2),
                        )
                    );

                }

            }

        }

        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER,
                                             array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('CustomerAnalysis Report');

        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::Name);
        }

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerString' => $customerString,
                'startYearMonth' => $dsSearchForm->getValue('startYearMonth'),
                'endYearMonth' => $dsSearchForm->getValue('endYearMonth'),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit' => $urlSubmit,
            )
        );

        $this->template->parse('CONTENTS', 'CustomerAnalysisReport', true);
        $this->parsePage();
    }
    /**
     * Display search form
     * @access private
     */
    /*
     function displaySearchForm() {
     $this->setMethodName ( 'displaySearchForm' );


     $this->buCustomerAnalysisReport->initialiseSearchForm ( $dsSearchForm );

     if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {

       if (! $dsSearchForm->populateFromArray ( $_REQUEST ['searchForm'] )) {

         $this->setFormErrorOn ();

       }

     }

         $this->setTemplateFiles ( array ('CustomerAnalysisReport' => 'CustomerAnalysisReport.inc' ) );

         $urlCustomerPopup = $this->buildLink ( CTCNC_PAGE_CUSTOMER, array ('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP ) );

         $urlSubmit = $this->buildLink ( $_SERVER ['PHP_SELF'], array ('action' => CTCNC_ACT_SEARCH ) );

         $this->setPageTitle ( 'CustomerAnalysis Report' );

         if ($dsSearchForm->rowCount () == 0) {
             $this->buCustomerAnalysisReport->initialiseSearchForm ( $dsSearchForm );
         }

         if ($dsSearchForm->getValue ( 'customerID' ) != 0) {
             $buCustomer = new BUCustomer ( $this );
             $buCustomer->getCustomerByID ( $dsSearchForm->getValue ( 'customerID' ), $dsCustomer );
             $customerString = $dsCustomer->getValue ( 'name' );
         }

     if ( $this->results ){

       if( $_REQUEST[ ] == 'Screen Report' ){


       }else{

         Header('Content-type: text/plain');
         Header('Content-Disposition: attachment; filename=CustomerAnalysisReport.csv');

         echo "Category,Sales(GBP),Cost(GBP),Labour(GBP),Labour(hours)\r\n";

         foreach ( $this->results as $contractName => $row ){

           echo "\"" . $contractName . "\",";
           foreach ( $row as $value ){
             echo $value . ",";
           }
           echo "\r\n";
         }
         exit;
       }
     }

         $this->template->set_var (
       array (
         'formError'       => $this->formError,
         'customerID'      => $dsSearchForm->getValue ( 'customerID' ),
         'customerString'  => $customerString,
         'startYearMonth'  => $dsSearchForm->getValue('startYearMonth'),
         'startYearMonthMessage'   => $dsSearchForm->getMessage('startYearMonth'),
         'endYearMonth'    => $dsSearchForm->getValue('endYearMonth'),
         'endYearMonthMessage'   => $dsSearchForm->getMessage('endYearMonth'),
         'urlCustomerPopup'=> $urlCustomerPopup,
         'urlSubmit'       => $urlSubmit,
         )
       );

         $this->template->parse ( 'CONTENTS', 'CustomerAnalysisReport', true );
         $this->parsePage ();
     } // end function displaySearchForm
 */

} // end of class
?>