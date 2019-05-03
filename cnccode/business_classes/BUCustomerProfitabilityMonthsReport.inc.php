<?php
/**
 * Customer Review Meetings business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 *
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerAnalysisReport.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerProfitabilityMonthsReport extends Business
{

    const searchFormCustomerID = "customerID";
    const searchFormStartYearMonth = "startYearMonth";
    const searchFormEndYearMonth = "endYearMonth";

    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    public function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormCustomerID, DA_STRING, DA_NOT_NULL);
        $dsData->addColumn(self::searchFormStartYearMonth, DA_STRING, DA_NOT_NULL);
        $dsData->addColumn(self::searchFormEndYearMonth, DA_STRING, DA_NOT_NULL);
    }

    function getPeriodsBetween($startYearMonth, $endYearMonth)
    {
        $periods = array();
        $d1 = (DateTime::createFromFormat('m/Y', $startYearMonth))->modify('first day of this month');
        $d2 = DateTime::createFromFormat('m/Y', $endYearMonth)->modify('last day of this month');

        while ($d1 <= $d2) {
            $periods[] = $d1->format('m/Y');
            $d1->add(new DateInterval('P1M'));
        }

        return $periods;
    }


    /**
     * Create a PDF file of customer profit figures and save to documentation
     * folder
     * @param $customerID
     * @param $startYearMonth
     * @param $endYearMonth
     * @return array
     */
    public function getReportData($customerID, $startYearMonth, $endYearMonth)
    {
        $buCustomer = new BUCustomer($this);

        $buCustomer->getCustomerByID($customerID, $dsCustomer);

        $buCustomerAnalysisReport = new BUCustomerAnalysisReport($this);

        $dsSearchForm = new DSForm($this);
        $buCustomerAnalysisReport->initialiseSearchForm($dsSearchForm);
        $periods = $this->getPeriodsBetween($startYearMonth, $endYearMonth);

        $dsSearchForm->setValue(self::searchFormCustomerID, $customerID);

        $profit = array();

        foreach ($periods as $period) {
            $dsSearchForm->setValue(self::searchFormStartYearMonth, $period);
            $dsSearchForm->setValue(self::searchFormEndYearMonth, $period);
            $results = $buCustomerAnalysisReport->getResults($dsSearchForm);
            $profitPeriodTotal = 0;

            foreach ($results as $contractName => $row) {

                if ($contractName != 'Other Sales' AND $contractName != 'Pre-Pay Contract') {

                    $profitPeriodTotal += $row['profit'];
                }
            }
            $profit[$period] = $profitPeriodTotal;
        }
        return $profit;
    }
}
