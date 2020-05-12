<?php
/**
 * Quotation Conversion report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");

class BUQuotationConversionReport extends Business
{

    const searchFormCustomerID = "customerID";
    const searchFormFromDate = "fromDate";
    const searchFormToDate = "toDate";


    public function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormCustomerID, DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormFromDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormToDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormCustomerID, null);
    }

    public function getConversionData($fromDate, $toDate, $customerID)
    {
        $db = $GLOBALS['db'];

        $fromDateSql = ' 1=1';
        if ($fromDate) {
            $fromDateSql = " `quote`.odh_quotation_create_date >= '$fromDate'";
        }
        $toDateSql = null;
        if ($toDate) {
            $toDateSql = " AND `quote`.odh_quotation_create_date <= '$toDate'";
        }
        $customerIDSql = null;
        if ($customerID) {
            $customerIDSql = " AND odh_custno = $customerID";
        }
        $sql =
            "SELECT 
        year,
        month,
        SUM(quoteCount) AS quoteCount,
        SUM(conversionCount) AS conversionCount
      FROM
        (
        SELECT 
          YEAR(`quote`.odh_quotation_create_date) AS YEAR,
          MONTH(`quote`.odh_quotation_create_date) AS MONTH,
          0 AS quoteCount,
          COUNT(*) AS conversionCount 
        FROM
          ordhead AS `quote` 
        WHERE
          $fromDateSql
          $toDateSql
          $customerIDSql
          AND ( SELECT COUNT(*) FROM ordhead AS `order` WHERE `order`.odh_quotation_ordno = `quote`.odh_ordno ) > 0
        GROUP BY
        YEAR(odh_quotation_create_date),
        MONTH(odh_quotation_create_date)
        UNION ALL
        SELECT 
          YEAR(`quote`.odh_quotation_create_date) AS YEAR,
          MONTH(`quote`.odh_quotation_create_date) AS MONTH,
          COUNT(*) AS quoteCount,
          0 AS conversionCount 
        FROM
          ordhead as `quote`
        WHERE
          $fromDateSql
          $toDateSql
          $customerIDSql
        GROUP BY
        YEAR(`quote`.odh_quotation_create_date),
        MONTH(`quote`.odh_quotation_create_date)
      ) AS totals 
      GROUP BY
        YEAR, MONTH";
        $db->query($sql);

        $ret = array();
        while ($db->next_record()) {
            $ret[] = $db->Record;
        }
        return $ret;
    }
}
