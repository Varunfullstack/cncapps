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

    public function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue('customerID', '');
    }

    public function getConversionData($fromDate, $toDate, $customerID)
    {
        $db = $GLOBALS['db'];

        if ($fromDate) {
            $fromDateSql = " `quote`.odh_quotation_create_date >= '$fromDate'";
        } else {
            $fromDateSql = ' 1=1';
        }

        if ($toDate) {
            $toDateSql = " AND `quote`.odh_quotation_create_date <= '$toDate'";
        } else {
            $toDateSql = '';
        }

        if ($customerID) {
            $customerIDSql = " AND odh_custno = $customerID";
        } else {
            $customerIDSql = "";

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

    private function getTotalQuotes($year, $month, $customerID)
    {
        $db = $GLOBALS['db'];

        $sql =
            "SELECT
        COUNT(*) as count
      FROM
        ordhead
      WHERE
        odh_type = 'Q'
        AND YEAR( odh_date ) = $year
        AND MONTH ( odh_date ) = $month";

        if ($customerID) {
            $sql .= " AND odh_custno = $customerID";
        }

        $db->query($sql);
        $db->next_record();
        return $db->Record['count'];
    }
} // End of class
?>