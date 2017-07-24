<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_dbe"] . "/DBEActivityProfitReport.inc.php");
require_once($cfg["path_dbe"] . "/DBEActivityProfitReportInvoice.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");

class BUActivityProfitReport extends Business
{
    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeActivityProfitReport = new DBEActivityProfitReport($this);
        $this->dbeActivityProfitReportInvoice = new DBEActivityProfitReportInvoice($this);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue('customerID', '');
    }

    function search(&$dsSearchForm)
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $this->dbeActivityProfitReport->getRowsBySearchCriteria(
            trim($dsSearchForm->getValue('customerID')),
            trim($dsSearchForm->getValue('fromDate')),
            trim($dsSearchForm->getValue('toDate')),
            $dsHeader->getValue('billingStartTime'),
            $dsHeader->getValue('billingEndTime'),
            false
        );

        return $this->dbeActivityProfitReport;
    }

    function searchDrill(
        $customerID,
        $fromDate,
        $toDate
    )
    {
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $this->dbeActivityProfitReport->getRowsBySearchCriteria(
            trim($customerID),
            trim($fromDate),
            trim($toDate),
            $dsHeader->getValue('billingStartTime'),
            $dsHeader->getValue('billingEndTime'),
            true            // drill down mode
        );

        return $this->dbeActivityProfitReport;
    }

    function searchDrillInvoices($customerID, $fromDate, $toDate)
    {

        $result = $this->getResults($customerID, $fromDate, $toDate, "'C', 'S', 'H', 'D', 'F', 'E', 'U'");

        $result_array['ProductSalesTurnover'] = $result['Turnover'];
        $result_array['ProductSalesProfit'] = $result['Profit'];

        $result = $this->getResults($customerID, $fromDate, $toDate, "'B'");

        $result_array['InternetRevenueTurnover'] = $result['Turnover'];
        $result_array['InternetRevenueProfit'] = $result['Profit'];

        $result = $this->getResults($customerID, $fromDate, $toDate, "'G'");

        $result_array['ManagedServiceRevenueTurnover'] = $result['Turnover'];
        $result_array['ManagedServiceRevenueProfit'] = $result['Profit'];

        $result = $this->getResults($customerID, $fromDate, $toDate, "'M', 'R'");

        $result_array['MaintAndGenSupportTurnover'] = $result['Turnover'];
        $result_array['MaintAndGenSupportProfit'] = $result['Profit'];

        return $result_array;
    }

    function getResults($customerID, $fromDate, $toDate, $stockcat_list)
    {

        if (!$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
            echo 'Could not connect to mysql host ' . DB_HOST;
            exit;
        }

        mysqli_select_db(DB_NAME, $db);

        $query =
            "
			SELECT
				SUM( invline.inl_qty * invline.inl_unit_price ) as Turnover, 
				SUM( 
				( invline.inl_qty * invline.inl_unit_price )
				- ( invline.inl_qty * invline.inl_cost_price ) ) as Profit
			FROM
				invline
				JOIN invhead ON inl_invno = inh_invno
				JOIN item ON itm_itemno = inl_itemno
				JOIN itemtype ON itm_itemtypeno = ity_itemtypeno
			WHERE
						inh_custno = $customerID
				AND ity_stockcat IN( $stockcat_list )
				AND inl_line_type = 'I'
				AND inh_date_printed BETWEEN '$fromDate' AND '$toDate';
			";

        $result = mysqli_query($db, $query);

        $row = mysqli_fetch_assoc($result);

        return $row;
    }
}// End of class
?>