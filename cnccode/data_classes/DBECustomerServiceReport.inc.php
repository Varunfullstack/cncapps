<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerServiceReport extends DBEntity
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("callactivity");
        $this->addColumn("CustomerID", DA_ID, DA_NOT_NULL);
        $this->addColumn("CustomerName", DA_STRING, DA_NOT_NULL);
        $this->addColumn("Activities", DA_INTEGER, DA_ALLOW_NULL);
        $this->addColumn("OnSite", DA_INTEGER, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsBySearchCriteria(
        $fromDate,
        $toDate
    )
    {

        $this->setMethodName('getRowsBySearchCriteria');

        $query =
            "SELECT
      CustomerID,
      CustomerName,
      SUM(Activities) AS Activities,
      SUM(OnSite) AS OnSite
    FROM(
    SELECT
      cus_custno AS CustomerID,
      cus_name   AS CustomerName,
      0 AS 'OnSite',
      COUNT(*) AS 'Activities'
    FROM problem
      JOIN customer ON pro_custno = cus_custno
    WHERE
      pro_date_raised BETWEEN '$fromDate' AND '$toDate'
      AND (
        SELECT COUNT(*)
        FROM
        custitem
        WHERE cui_custno = cus_custno
        AND renewalStatus = 'R'
      ) > 0
      AND (    # has at least one telephone/remote suport
        SELECT
          COUNT(*)
        FROM
          callactivity
        WHERE
          caa_problemno = pro_problemno
          AND caa_callacttypeno = " . CONFIG_REMOTE_TELEPHONE_ACTIVITY_TYPE_ID . "
      ) > 0
    GROUP BY CustomerID
    UNION
    SELECT
      cus_custno AS CustomerID,
      cus_name   AS CustomerName,
      COUNT(*) 'OnSite',
      0 AS 'Activities'
    FROM problem
      JOIN customer ON pro_custno = cus_custno
    WHERE
      pro_date_raised BETWEEN '$fromDate' AND '$toDate'
      AND (
        SELECT COUNT(*)
        FROM
        custitem
        WHERE cui_custno = cus_custno
        AND renewalStatus = 'R'
      ) > 0 
        
      AND (    # has at least one on-site visit
        SELECT
          COUNT(*)
        FROM
          callactivity
          JOIN callacttype
              ON callactivity.caa_callacttypeno = callacttype.cat_callacttypeno
        WHERE
          caa_problemno = pro_problemno
          AND allowSCRFlag = 'Y'
      ) > 0
    GROUP BY CustomerID
    ) AS temp
    GROUP BY CustomerID
    ORDER BY CustomerName;";

        $this->setQueryString($query);

        $ret = (parent::getRows());
        return $ret;
    }

}

?>