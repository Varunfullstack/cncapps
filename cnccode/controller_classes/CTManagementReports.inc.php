<?php
/**
 * Sales Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global

use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierId;

$cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUManagementReports.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_dbe'] . '/DBESector.inc.php');
require_once("Mail.php");
require_once("Mail/mime.php");

class CTManagementReports extends CTCNC
{
    const GetSalesByCustomerDataAction = "GetSalesByCustomerDataAction";
    public $buManagementReports;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = REPORTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buManagementReports = new BUManagementReports($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'SalesByCustomer':
                $this->SalesByCustomer();
                break;
            case 'SpendBySupplier':
                $this->spendBySupplier();
                break;
            case 'SpendByCategory':
                $this->spendByCategory();
                break;
            case self::GetSalesByCustomerDataAction:
                $data = $this->salesByCustomerData(
                    $this->getParam('year'),
                    $this->getParam('customerID'),
                    $this->getParam('sector'),
                    $this->getParam('noOfPcs')
                );
                echo json_encode($data);
                break;
            case 'SpendByManufacturer':
            default:
                $this->spendByManufacturer();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function SalesByCustomer()
    {
        $this->setMenuId(505);
        $this->setMethodName('SalesByCustomer');
        $this->setTemplateFiles('ManagementReportsSalesCustomer', 'ManagementReportsSalesCustomer.inc');
        $this->setPageTitle("Sales By Customer Profile");
        // year selector
        $this->template->set_block('ManagementReportsSalesCustomer', 'yearBlock', 'years');
        $this->parseYearSelector($this->getParam('year'));
        // sector selector
        $this->template->set_block('ManagementReportsSalesCustomer', 'sectorBlock', 'sectors');
        $this->parseSectorSelector($this->getParam('sectorID'));
        // noOfPcs selector
        $this->parseNoOfPcs($this->template, $this->getParam('noOfPcs'));
        $customerPopupURL = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $fetchDataUrl     = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => self::GetSalesByCustomerDataAction
            )
        );
        $customerName     = null;
        if ($this->getParam('customerID')) {
            $dbeCustomer = new DBECustomer($this);
            $dbeCustomer->getRow($this->getParam('customerID'));
            $customerName = $dbeCustomer->getValue(DBECustomer::name);
        }
        $this->template->set_var(
            array(
                'customerPopupURL' => $customerPopupURL,
                'customerName'     => $customerName,
                'customerID'       => $this->getParam('customerID'),
                'sectorID'         => $this->getParam('sectorID'),
                'noOfPcs'          => $this->getParam('noOfPcs'),
                'fetchDataUrl'     => $fetchDataUrl
            )
        );
        $this->template->parse("CONTENTS", "ManagementReportsSalesCustomer");
        $this->parsePage();

    }

    /**
     * Get and parse year drop-down selector
     * @access private
     * @param $selectedYear
     */
    function parseYearSelector($selectedYear)
    {
        $thisYear = date('Y');
        for ($year = $thisYear; $year >= $thisYear - 3; $year--) {

            $yearSelected = ($selectedYear == $year) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'yearSelected' => $yearSelected,
                    'year'         => $year
                )
            );
            $this->template->parse('years', 'yearBlock', true);
        }
    } // end function spendBySupplier

    private function parseSectorSelector($selectedSectorID)
    {

        $buSector  = new BUSector($this);
        $dsResults = new DataSet($this);
        $buSector->getAll($dsResults);
        $this->template->set_var(
            array(
                'selectedSector'    => $selectedSectorID ? CT_SELECTED : null,
                'sectorID'          => null,
                'sectorDescription' => "Search All"
            )
        );
        $this->template->parse('sectors', 'sectorBlock', true);
        while ($dsResults->fetchNext()) {

            $sectorID          = $dsResults->getValue(DBESector::sectorID);
            $sectorDescription = $dsResults->getValue(DBESector::description);
            $selectedSector    = ($selectedSectorID == $sectorID) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'selectedSector'    => $selectedSector,
                    'sectorID'          => $sectorID,
                    'sectorDescription' => $sectorDescription
                )
            );
            $this->template->parse('sectors', 'sectorBlock', true);
        }
    }

    private function parseNoOfPcs(Template $template, $selectedNoOfPcs)
    {
        $template->set_block('ManagementReportsSalesCustomer', 'noOfPcsBlock', 'noOfPcsSelector');
        $options = [
            "Search All",
            "0",
            "1-5",
            "6-10",
            "11-25",
            "26-50",
            "51-99",
            "100+"
        ];
        foreach ($options as $option) {
            $isSelected = $selectedNoOfPcs ? ($selectedNoOfPcs == $option ? CT_SELECTED : null) : ($option === "Search All" ? CT_SELECTED : null);
            $value      = $option;
            if ($option === 'Search All') {
                $value = null;
            }
            $this->template->set_var(
                array(
                    'noOfPcsSelected'    => $isSelected ? CT_SELECTED : null,
                    'noOfPcsDescription' => $option,
                    'noOfPcsValue'       => $value
                )
            );
            $this->template->parse('noOfPcsSelector', 'noOfPcsBlock', true);
        }
    }

    /**
     * @throws Exception
     */
    function spendBySupplier()
    {
        $this->setMenuId(506);
        $this->setMethodName('spendBySupplier');
        $this->setTemplateFiles('ManagementReportsSpendSupplier', 'ManagementReportsSpendSupplier.inc');
        $this->setPageTitle("Spend By Supplier");
        // year selector
        $this->template->set_block('ManagementReportsSpendSupplier', 'yearBlock', 'years');
        $this->parseYearSelector($this->getParam('year'));
        $supplierPopupURL = Controller::buildLink(
            CTCNC_PAGE_SUPPLIER,
            array(
                'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $results          = $this->buManagementReports->getSpendBySupplier(
            $this->getParam('supplierID'),
            $this->getParam('year')
        );
        $supplierName     = null;
        if ($this->getParam('supplierID')) {
            $supplierRepo = new MySQLSupplierRepository();
            $supplier     = $supplierRepo->getById(new SupplierId($this->getParam('supplierID')));
            $supplierName = $supplier->name()->value();
        }
        $this->template->set_block('ManagementReportsSpendSupplier', 'resultsBlock', 'results');
        while ($row = $results->fetch_object()) {

            $suppliers[] = $row->supplier;
            $value[1][]  = $row->month1;
            $value[2][]  = $row->month2;
            $value[3][]  = $row->month3;
            $value[4][]  = $row->month4;
            $value[5][]  = $row->month5;
            $value[6][]  = $row->month6;
            $value[7][]  = $row->month7;
            $value[8][]  = $row->month8;
            $value[9][]  = $row->month9;
            $value[10][] = $row->month10;
            $value[11][] = $row->month11;
            $value[12][] = $row->month12;
            $this->template->set_var(
                array(
                    'supplier' => Controller::htmlDisplayText($row->supplier),
                    'month1'   => Controller::formatNumber($row->month1, 0),
                    'month2'   => Controller::formatNumber($row->month2, 0),
                    'month3'   => Controller::formatNumber($row->month3, 0),
                    'month4'   => Controller::formatNumber($row->month4, 0),
                    'month5'   => Controller::formatNumber($row->month5, 0),
                    'month6'   => Controller::formatNumber($row->month6, 0),
                    'month7'   => Controller::formatNumber($row->month7, 0),
                    'month8'   => Controller::formatNumber($row->month8, 0),
                    'month9'   => Controller::formatNumber($row->month9, 0),
                    'month10'  => Controller::formatNumber($row->month10, 0),
                    'month11'  => Controller::formatNumber($row->month11, 0),
                    'month12'  => Controller::formatNumber($row->month12, 0),
                    'total'    => Controller::formatNumber(
                        $row->month1 + $row->month2 + $row->month3 + $row->month4 + $row->month5 + $row->month6 + $row->month7 + $row->month8 + $row->month9 + $row->month10 + $row->month11 + $row->month12,
                        0
                    )
                )
            );
            $this->template->parse('results', 'resultsBlock', true);
        }
        $urlGenerateReport = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'SpendBySupplier'
            )
        );
        $this->template->set_var(
            array(
                'urlGenerateReport' => $urlGenerateReport,
                'urlSupplierPopup'  => $supplierPopupURL,
                'supplierName'      => $supplierName,
                'supplierID'        => $this->getParam('supplierID')
            )
        );
        $this->template->parse("CONTENTS", "ManagementReportsSpendSupplier");
        $this->parsePage();

    } // end function salesByCustomer

    /**
     * @throws Exception
     */
    function spendByCategory()
    {
        $this->setMethodName('spendByCategory');
        $this->setTemplateFiles('ManagementReportsSpendCategory', 'ManagementReportsSpendCategory.inc');
        $this->setPageTitle("Spend By Category");
        // year selector
        $this->template->set_block('ManagementReportsSpendCategory', 'yearBlock', 'years');
        $this->parseYearSelector($this->getParam('year'));
        $results = $this->buManagementReports->getSpendByCategory(
            $this->getParam('year')
        );
        $this->template->set_block('ManagementReportsSpendCategory', 'resultsBlock', 'results');
        while ($row = $results->fetch_object()) {

            $categories[] = $row->category;
            $value[1][]   = $row->month1;
            $value[2][]   = $row->month2;
            $value[3][]   = $row->month3;
            $value[4][]   = $row->month4;
            $value[5][]   = $row->month5;
            $value[6][]   = $row->month6;
            $value[7][]   = $row->month7;
            $value[8][]   = $row->month8;
            $value[9][]   = $row->month9;
            $value[10][]  = $row->month10;
            $value[11][]  = $row->month11;
            $value[12][]  = $row->month12;
            $this->template->set_var(
                array(
                    'category' => Controller::htmlDisplayText($row->category),
                    'month1'   => Controller::formatNumber($row->month1, 0),
                    'month2'   => Controller::formatNumber($row->month2, 0),
                    'month3'   => Controller::formatNumber($row->month3, 0),
                    'month4'   => Controller::formatNumber($row->month4, 0),
                    'month5'   => Controller::formatNumber($row->month5, 0),
                    'month6'   => Controller::formatNumber($row->month6, 0),
                    'month7'   => Controller::formatNumber($row->month7, 0),
                    'month8'   => Controller::formatNumber($row->month8, 0),
                    'month9'   => Controller::formatNumber($row->month9, 0),
                    'month10'  => Controller::formatNumber($row->month10, 0),
                    'month11'  => Controller::formatNumber($row->month11, 0),
                    'month12'  => Controller::formatNumber($row->month12, 0),
                    'total'    => Controller::formatNumber(
                        $row->month1 + $row->month2 + $row->month3 + $row->month4 + $row->month5 + $row->month6 + $row->month7 + $row->month8 + $row->month9 + $row->month10 + $row->month11 + $row->month12,
                        0
                    )
                )
            );
            $this->template->parse('results', 'resultsBlock', true);
        }
        $urlGenerateReport = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'SpendByCategory'
            )
        );
        $this->template->set_var(
            array(
                'urlGenerateReport' => $urlGenerateReport,
                'categoryID'        => $this->getParam('categoryID')
            )
        );
        $this->template->parse("CONTENTS", "ManagementReportsSpendCategory");
        $this->parsePage();

    }

    /**
     * @param $year
     * @param null|integer $customerId
     * @param null|integer $sector
     * @param null| $pcs
     * @return array
     */
    function salesByCustomerData($year,
                                 $customerId = null,
                                 $sector = null,
                                 $pcs = null
    )
    {

        $minPcs = null;
        $maxPcs = null;
        switch ($pcs) {
            case '0':
                $minPcs = 0;
                $maxPcs = 0;
                break;
            case '1-5':
                $minPcs = 1;
                $maxPcs = 5;
                break;
            case '6-10';
                $minPcs = 6;
                $maxPcs = 10;
                break;
            case '11-25';
                $minPcs = 11;
                $maxPcs = 25;
                break;
            case '26-50';
                $minPcs = 26;
                $maxPcs = 50;
                break;
            case '51-99';
                $minPcs = 51;
                $maxPcs = 99;
                break;
            case '100+':
                $minPcs = 100;
        }
        $results = $this->buManagementReports->getSalesByCustomer($customerId, $year, $sector, $minPcs, $maxPcs);
        $data    = [];
        while ($row = $results->fetch_object()) {

            $data[] = [
                'customer'           => Controller::htmlDisplayText($row->customer),
                'sector'             => Controller::htmlDisplayText($row->sector),
                'noOfPCs'            => Controller::htmlDisplayText($row->noOfPCs),
                'noOfServers'        => $row->noOfServers,
                'salesMonth1'        => number_format($row->salesMonth1, 2),
                'profitMonth1'       => round($row->profitMonth1, 2),
                'salesMonth2'        => round($row->salesMonth2, 2),
                'profitMonth2'       => round($row->profitMonth2, 2),
                'salesMonth3'        => round($row->salesMonth3, 2),
                'profitMonth3'       => round($row->profitMonth3, 2),
                'salesMonth4'        => round($row->salesMonth4, 2),
                'profitMonth4'       => round($row->profitMonth4, 2),
                'salesMonth5'        => round($row->salesMonth5, 2),
                'profitMonth5'       => round($row->profitMonth5, 2),
                'salesMonth6'        => round($row->salesMonth6, 2),
                'profitMonth6'       => round($row->profitMonth6, 2),
                'salesMonth7'        => round($row->salesMonth7, 2),
                'profitMonth7'       => round($row->profitMonth7, 2),
                'salesMonth8'        => round($row->salesMonth8, 2),
                'profitMonth8'       => round($row->profitMonth8, 2),
                'salesMonth9'        => round($row->salesMonth9, 2),
                'profitMonth9'       => round($row->profitMonth9, 2),
                'salesMonth10'       => round($row->salesMonth10, 2),
                'profitMonth10'      => round($row->profitMonth10, 2),
                'salesMonth11'       => round($row->salesMonth11, 2),
                'profitMonth11'      => round($row->profitMonth11, 2),
                'salesMonth12'       => round($row->salesMonth12, 2),
                'profitMonth12'      => round($row->profitMonth12, 2),
                'totalSales'         => round($row->salesTotal, 2),
                'totalProfit'        => round($row->profitTotal, 2),
                'becameCustomerDate' => $this->formatDateIfCorrect($row->becameCustomerDate, 'd/m/Y')
            ];
        }
        return $data;
    }

    private function formatDateIfCorrect($dateString, $format)
    {
        if (!$dateString) {
            return null;
        }
        $date = DateTime::createFromFormat(DATE_MYSQL_DATE, $dateString);
        if (!$date) {
            return null;
        }
        return $date->format($format);
    }

    /**
     * @throws Exception
     */
    function spendByManufacturer()
    {
        $this->setMenuId(507);
        $this->setMethodName('spendByManufacturer');
        $this->setTemplateFiles('ManagementReportsSpendManufacturer', 'ManagementReportsSpendManufacturer.inc');
        $this->setPageTitle("Spend By Manufacturer");
        // year selector
        $this->template->set_block('ManagementReportsSpendManufacturer', 'yearBlock', 'years');
        $this->parseYearSelector($this->getParam('year'));
        $results = $this->buManagementReports->getSpendByManufacturer(
            $this->getParam('manufacturerName'),
            $this->getParam('year')
        );
        $this->template->set_block('ManagementReportsSpendManufacturer', 'resultsBlock', 'results');
        $grandTotal = 0;
        while ($row = $results->fetch_object()) {
            $total      = $row->month1 + $row->month2 + $row->month3 + $row->month4 + $row->month5 + $row->month6 + $row->month7 + $row->month8 + $row->month9 + $row->month10 + $row->month11 + $row->month12;
            $grandTotal += $total;
            $this->template->set_var(
                array(
                    'manufacturer' => Controller::htmlDisplayText($row->manufacturer),
                    'month1'       => Controller::formatNumber($row->month1, 0),
                    'month2'       => Controller::formatNumber($row->month2, 0),
                    'month3'       => Controller::formatNumber($row->month3, 0),
                    'month4'       => Controller::formatNumber($row->month4, 0),
                    'month5'       => Controller::formatNumber($row->month5, 0),
                    'month6'       => Controller::formatNumber($row->month6, 0),
                    'month7'       => Controller::formatNumber($row->month7, 0),
                    'month8'       => Controller::formatNumber($row->month8, 0),
                    'month9'       => Controller::formatNumber($row->month9, 0),
                    'month10'      => Controller::formatNumber($row->month10, 0),
                    'month11'      => Controller::formatNumber($row->month11, 0),
                    'month12'      => Controller::formatNumber($row->month12, 0),
                    'total'        => Controller::formatNumber($total, 0)
                )
            );
            $this->template->parse('results', 'resultsBlock', true);
        }
        $urlGenerateReport = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'spendByManufacturer'
            )
        );
        $this->template->set_var(
            array(
                'urlGenerateReport' => $urlGenerateReport,
                'manufacturerName'  => $this->getParam('manufacturerName'),
                'grandTotal'        => Controller::formatNumber($grandTotal, 0)
            )
        );
        $this->template->parse("CONTENTS", "ManagementReportsSpendManufacturer");
        $this->parsePage();

    }
}