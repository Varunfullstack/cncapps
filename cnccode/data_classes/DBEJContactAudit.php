<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/07/2018
 * Time: 12:48
 */

require_once($cfg["path_dbe"] . "/DBEContactAudit.php");

class DBEJContactAudit extends DBEContactAudit
{
    const createdByUserName = "createdByUserName";
    const createdByContactName = "createdByContactName";
    const customerName = "customerName";

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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::createdByUserName,
            DA_STRING,
            DA_ALLOW_NULL,
            'cns_name'
        );

        $this->addColumn(
            self::createdByContactName,
            DA_STRING,
            DA_ALLOW_NULL,
            'concat(consultant.con_first_name," ",consultant.con_last_name'
        );

        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            'customer.cus_name'
        );

        $this->setAddColumnsOff();
    }

    function search($customerId = null,
                    $startDate = null,
                    $endDate = null,
                    $limit = 50
    )
    {
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . '
                LEFT JOIN contact 
    ON contactId = contact.`con_contno` 
    LEFT JOIN consultant ON userId = consultant.`cns_consno`
    LEFT JOIN customer ON customer.`cus_custno` = contactauditlog.con_custno
      where 1 = 1 ';

        if ($customerId) {
            $queryString .= " and " . $this->getTableName() . "." . $this->getDBColumnName(
                    self::customerID
                ) . " = $customerId";
        }


        if ($startDate) {
            $queryString .= " and date(" . $this->getTableName() . "." . $this->getDBColumnName(
                    self::createdAt
                ) . ") >= $startDate";
        }

        if ($endDate) {
            $queryString .= " and date(" . $this->getTableName() . "." . $this->getDBColumnName(
                    self::createdAt
                ) . ") <= $endDate";
        }

        if ($limit) {
            $queryString .= " limit $limit";
        }

        $this->setQueryString($queryString);
    }
}