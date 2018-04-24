<?php
/**
 * Sales Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUManagementReports.inc.php');
require_once($cfg['path_dbe'] . '/DBESupplier.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerNew.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
require_once("Mail.php");
require_once("Mail/mime.php");

class CTManagementReports extends CTCNC
{
    public $buManagementReports;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "reports",
        ];
        if (!self::canAccess($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buManagementReports = new BUManagementReports($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case 'SalesByCustomer':
                $this->SalesByCustomer();
                break;

            case 'SpendBySupplier':
                $this->spendBySupplier();
                break;

            case 'SpendByCategory':
                $this->spendByCategory();
                break;

            case 'SpendByManufacturer':
            default:
                $this->spendByManufacturer();
                break;
        }
    }

    function spendByManufacturer()
    {
        $this->setMethodName('spendByManufacturer');

        $this->setTemplateFiles('ManagementReportsSpendManufacturer', 'ManagementReportsSpendManufacturer.inc');

        $this->setPageTitle("Spend By Manufacturer");

        // year selector
        $this->template->set_block('ManagementReportsSpendManufacturer', 'yearBlock', 'years');
        $this->parseYearSelector($_REQUEST['year']);

        $results =
            $this->buManagementReports->getSpendByManufacturer(
                $_REQUEST['manufacturerName'],
                $_REQUEST['year']
            );

        $this->template->set_block('ManagementReportsSpendManufacturer', 'resultsBlock', 'results');

        $minValue = 99999;
        $maxValue = 0;
        $grandTotal = 0;

        while ($row = $results->fetch_object()) {
            /*
                        $manufacturers[] = $row->manufacturer;
                        $value[1][] = $row->month1;
                        $value[2][] = $row->month2;
                        $value[3][] = $row->month3;
                        $value[4][] = $row->month4;
                        $value[5][] = $row->month5;
                        $value[6][] = $row->month6;
                        $value[7][] = $row->month7;
                        $value[8][] = $row->month8;
                        $value[9][] = $row->month9;
                        $value[10][] = $row->month10;
                        $value[11][] = $row->month11;
                        $value[12][] = $row->month12;
            */
            $total =
                $row->month1 +
                $row->month2 +
                $row->month3 +
                $row->month4 +
                $row->month5 +
                $row->month6 +
                $row->month7 +
                $row->month8 +
                $row->month9 +
                $row->month10 +
                $row->month11 +
                $row->month12;

            $grandTotal += $total;

            $this->template->set_var(
                array(
                    'manufacturer' => Controller::htmlDisplayText($row->manufacturer),
                    'month1' => Controller::formatNumber($row->month1, 0),
                    'month2' => Controller::formatNumber($row->month2, 0),
                    'month3' => Controller::formatNumber($row->month3, 0),
                    'month4' => Controller::formatNumber($row->month4, 0),
                    'month5' => Controller::formatNumber($row->month5, 0),
                    'month6' => Controller::formatNumber($row->month6, 0),
                    'month7' => Controller::formatNumber($row->month7, 0),
                    'month8' => Controller::formatNumber($row->month8, 0),
                    'month9' => Controller::formatNumber($row->month9, 0),
                    'month10' => Controller::formatNumber($row->month10, 0),
                    'month11' => Controller::formatNumber($row->month11, 0),
                    'month12' => Controller::formatNumber($row->month12, 0),
                    'total' => Controller::formatNumber($total, 0)
                )
            );

            $this->template->parse('results', 'resultsBlock', true);
        }

        $urlGenerateReport =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'spendByManufacturer'
                )
            );

        $this->template->set_var(
            array(
                'urlTheChart' => $urlTheChart,
                'urlGenerateReport' => $urlGenerateReport,
                'manufacturerName' => $_REQUEST['manufacturerName'],
                'grandTotal' => Controller::formatNumber($grandTotal, 0)
            )
        );

        $this->template->parse("CONTENTS", "ManagementReportsSpendManufacturer");

        $this->parsePage();

    } // end function sepndByManufacturer

    function spendBySupplier()
    {
        $this->setMethodName('spendBySupplier');

        $this->setTemplateFiles('ManagementReportsSpendSupplier', 'ManagementReportsSpendSupplier.inc');

        $this->setPageTitle("Spend By Supplier");

        // year selector
        $this->template->set_block('ManagementReportsSpendSupplier', 'yearBlock', 'years');
        $this->parseYearSelector($_REQUEST['year']);

        $supplierPopupURL =
            $this->buildLink(
                CTCNC_PAGE_SUPPLIER,
                array(
                    'action' => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $results = $this->buManagementReports->getSpendBySupplier($_REQUEST['supplierID'], $_REQUEST['year']);

        if ($_REQUEST['supplierID']) {
            $dbeSupplier = new DBESupplier($this);
            $dbeSupplier->getRow($_REQUEST['supplierID']);
            $supplierName = $dbeSupplier->getValue('name');
        }

        $this->template->set_block('ManagementReportsSpendSupplier', 'resultsBlock', 'results');

        $minValue = 99999;
        $maxValue = 0;

        while ($row = $results->fetch_object()) {

            $suppliers[] = $row->supplier;
            $value[1][] = $row->month1;
            $value[2][] = $row->month2;
            $value[3][] = $row->month3;
            $value[4][] = $row->month4;
            $value[5][] = $row->month5;
            $value[6][] = $row->month6;
            $value[7][] = $row->month7;
            $value[8][] = $row->month8;
            $value[9][] = $row->month9;
            $value[10][] = $row->month10;
            $value[11][] = $row->month11;
            $value[12][] = $row->month12;


            $this->template->set_var(
                array(
                    'supplier' => Controller::htmlDisplayText($row->supplier),
                    'month1' => Controller::formatNumber($row->month1, 0),
                    'month2' => Controller::formatNumber($row->month2, 0),
                    'month3' => Controller::formatNumber($row->month3, 0),
                    'month4' => Controller::formatNumber($row->month4, 0),
                    'month5' => Controller::formatNumber($row->month5, 0),
                    'month6' => Controller::formatNumber($row->month6, 0),
                    'month7' => Controller::formatNumber($row->month7, 0),
                    'month8' => Controller::formatNumber($row->month8, 0),
                    'month9' => Controller::formatNumber($row->month9, 0),
                    'month10' => Controller::formatNumber($row->month10, 0),
                    'month11' => Controller::formatNumber($row->month11, 0),
                    'month12' => Controller::formatNumber($row->month12, 0),
                    'total' => Controller::formatNumber(
                        $row->month1 +
                        $row->month2 +
                        $row->month3 +
                        $row->month4 +
                        $row->month5 +
                        $row->month6 +
                        $row->month7 +
                        $row->month8 +
                        $row->month9 +
                        $row->month10 +
                        $row->month11 +
                        $row->month12,
                        0)
                )
            );

            $this->template->parse('results', 'resultsBlock', true);
        }
        $urlGenerateReport =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'SpendBySupplier'
                )
            );
        $this->template->set_var(
            array(
                'urlGenerateReport' => $urlGenerateReport,
                'urlSupplierPopup' => $supplierPopupURL,
                'supplierName' => $supplierName,
                'supplierID' => $_REQUEST['supplierID']
            )
        );

        $this->template->parse("CONTENTS", "ManagementReportsSpendSupplier");

        $this->parsePage();

    } // end function spendBySupplier

    function spendByCategory()
    {
        $this->setMethodName('spendByCategory');

        $this->setTemplateFiles('ManagementReportsSpendCategory', 'ManagementReportsSpendCategory.inc');

        $this->setPageTitle("Spend By Category");

        // year selector
        $this->template->set_block('ManagementReportsSpendCategory', 'yearBlock', 'years');
        $this->parseYearSelector($_REQUEST['year']);

        $results =
            $this->buManagementReports->getSpendByCategory(
                $_REQUEST['year']
            );

        $this->template->set_block('ManagementReportsSpendCategory', 'resultsBlock', 'results');

        $minValue = 99999;
        $maxValue = 0;

        while ($row = $results->fetch_object()) {

            $categorys[] = $row->category;
            $value[1][] = $row->month1;
            $value[2][] = $row->month2;
            $value[3][] = $row->month3;
            $value[4][] = $row->month4;
            $value[5][] = $row->month5;
            $value[6][] = $row->month6;
            $value[7][] = $row->month7;
            $value[8][] = $row->month8;
            $value[9][] = $row->month9;
            $value[10][] = $row->month10;
            $value[11][] = $row->month11;
            $value[12][] = $row->month12;


            $this->template->set_var(
                array(
                    'category' => Controller::htmlDisplayText($row->category),
                    'month1' => Controller::formatNumber($row->month1, 0),
                    'month2' => Controller::formatNumber($row->month2, 0),
                    'month3' => Controller::formatNumber($row->month3, 0),
                    'month4' => Controller::formatNumber($row->month4, 0),
                    'month5' => Controller::formatNumber($row->month5, 0),
                    'month6' => Controller::formatNumber($row->month6, 0),
                    'month7' => Controller::formatNumber($row->month7, 0),
                    'month8' => Controller::formatNumber($row->month8, 0),
                    'month9' => Controller::formatNumber($row->month9, 0),
                    'month10' => Controller::formatNumber($row->month10, 0),
                    'month11' => Controller::formatNumber($row->month11, 0),
                    'month12' => Controller::formatNumber($row->month12, 0),
                    'total' => Controller::formatNumber(
                        $row->month1 +
                        $row->month2 +
                        $row->month3 +
                        $row->month4 +
                        $row->month5 +
                        $row->month6 +
                        $row->month7 +
                        $row->month8 +
                        $row->month9 +
                        $row->month10 +
                        $row->month11 +
                        $row->month12,
                        0)
                )
            );

            $this->template->parse('results', 'resultsBlock', true);
        }

        $urlGenerateReport =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'SpendByCategory'
                )
            );

        $this->template->set_var(
            array(
                'urlGenerateReport' => $urlGenerateReport,
                'urlCategoryPopup' => $categoryPopupURL,
                'categoryName' => $categoryName,
                'categoryID' => $_REQUEST['categoryID']
            )
        );

        $this->template->parse("CONTENTS", "ManagementReportsSpendCategory");

        $this->parsePage();

    } // end function sepndBycategory

    function SalesByCustomer()
    {
        $this->setMethodName('SalesByCustomer');

        $this->setTemplateFiles('ManagementReportsSalesCustomer', 'ManagementReportsSalesCustomer.inc');

        $this->setPageTitle("Sales By Customer");

        // year selector
        $this->template->set_block('ManagementReportsSalesCustomer', 'yearBlock', 'years');
        $this->parseYearSelector($_REQUEST['year']);

        $customerPopupURL =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $results = $this->buManagementReports->getSalesByCustomer($_REQUEST['customerID'], $_REQUEST['year']);

        if ($_REQUEST['customerID']) {
            $dbeCustomer = new DBECustomer($this);
            $dbeCustomer->getRow($_REQUEST['customerID']);
            $customerName = $dbeCustomer->getValue('name');
        }

        $this->template->set_block('ManagementReportsSalesCustomer', 'resultsBlock', 'results');

        $minValue = 99999;
        $maxValue = 0;

        while ($row = $results->fetch_object()) {

            $this->template->set_var(
                array(
                    'customer' => Controller::htmlDisplayText($row->customer),
                    'sector' => Controller::htmlDisplayText($row->sector),
                    'noOfPCs' => Controller::htmlDisplayText($row->noOfPCs),
                    'noOfServers' => Controller::htmlDisplayText($row->noOfServers),
                    'salesMonth1' => Controller::formatNumber($row->salesMonth1, 0),
                    'profitMonth1' => Controller::formatNumber($row->profitMonth1, 0),
                    'salesMonth2' => Controller::formatNumber($row->salesMonth2, 0),
                    'profitMonth2' => Controller::formatNumber($row->profitMonth2, 0),
                    'salesMonth3' => Controller::formatNumber($row->salesMonth3, 0),
                    'profitMonth3' => Controller::formatNumber($row->profitMonth3, 0),
                    'salesMonth4' => Controller::formatNumber($row->salesMonth4, 0),
                    'profitMonth4' => Controller::formatNumber($row->profitMonth4, 0),
                    'salesMonth5' => Controller::formatNumber($row->salesMonth5, 0),
                    'profitMonth5' => Controller::formatNumber($row->profitMonth5, 0),
                    'salesMonth6' => Controller::formatNumber($row->salesMonth6, 0),
                    'profitMonth6' => Controller::formatNumber($row->profitMonth6, 0),
                    'salesMonth7' => Controller::formatNumber($row->salesMonth7, 0),
                    'profitMonth7' => Controller::formatNumber($row->profitMonth7, 0),
                    'salesMonth8' => Controller::formatNumber($row->salesMonth8, 0),
                    'profitMonth8' => Controller::formatNumber($row->profitMonth8, 0),
                    'salesMonth9' => Controller::formatNumber($row->salesMonth9, 0),
                    'profitMonth9' => Controller::formatNumber($row->profitMonth9, 0),
                    'salesMonth10' => Controller::formatNumber($row->salesMonth10, 0),
                    'profitMonth10' => Controller::formatNumber($row->profitMonth10, 0),
                    'salesMonth11' => Controller::formatNumber($row->salesMonth11, 0),
                    'profitMonth11' => Controller::formatNumber($row->profitMonth11, 0),
                    'salesMonth12' => Controller::formatNumber($row->salesMonth12, 0),
                    'profitMonth12' => Controller::formatNumber($row->profitMonth12, 0),
                    'totalSales' => Controller::formatNumber($row->salesTotal, 0),
                    'totalProfit' => Controller::formatNumber($row->profitTotal, 0)

                )
            );

            $this->template->parse('results', 'resultsBlock', true);
        }
        $this->template->set_var(
            array(
                'urlGenerateReport' => $urlGenerateReport,
                'customerPopupURL' => $customerPopupURL,
                'customerName' => $customerName,
                'customerID' => $_REQUEST['customerID']
            )
        );

        $this->template->parse("CONTENTS", "ManagementReportsSalesCustomer");

        $this->parsePage();

    } // end function salesByCustomer

    /**
     * Get and parse year drop-down selector
     * @access private
     */
    function parseYearSelector($selectedYear)
    {
        $thisYear = date('Y');

        for ($year = $thisYear; $year >= $thisYear - 3; $year--) {

            $yearSelected = ($selectedYear == $year) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'yearSelected' => $yearSelected,
                    'year' => $year
                )
            );

            $this->template->parse('years', 'yearBlock', true);
        }
    }

}// end of class
?>