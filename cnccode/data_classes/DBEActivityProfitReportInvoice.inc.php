<?php
/*
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEActivityProfitReportInvoice extends DBEntity
{
    const InvoiceID = "InvoiceID";
    const Date = "Date";
    const Cost = "Cost";
    const Sale = "Sale";
    const Profit = "Profit";

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("callactivity");
        $this->addColumn(
            self::InvoiceID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::Date,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::Cost,
            DA_FLOAT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::Sale,
            DA_FLOAT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::Profit,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsBySearchCriteria(
        $customerID,
        $fromDate,
        $toDate
    )
    {

        $this->setMethodName('getRowsBySearchCriteria');

        $query =
            "SELECT
			inh_invno AS InvoiceID,
			inh_date_printed AS Date,
			SUM( invline.inl_qty * invline.inl_cost_price ) AS Cost,
			SUM( invline.inl_qty * invline.inl_unit_price ) AS Sale,
			SUM( 
				( invline.inl_qty * invline.inl_unit_price )
				- ( invline.inl_qty * invline.inl_cost_price ) ) AS Profit
			
		FROM
				invline
				JOIN invhead 
						ON (invline.inl_invno = invhead.inh_invno)
		WHERE (invhead.inh_custno = '" . $customerID . "')" .
            " AND inl_line_type = 'I' " .
            " AND inh_date_printed BETWEEN '" . $fromDate . "' AND '" . $toDate .
            "' GROUP BY inl_invno";

        $this->setQueryString($query);

        $ret = (parent::getRows());
        return $ret;
    }

}

?>