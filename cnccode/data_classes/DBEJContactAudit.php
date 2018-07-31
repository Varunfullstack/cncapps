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
            '(SELECT 
    CONCAT(
      contact.con_first_name,
      " ",
      contact.con_last_name
    )
  FROM
    contact 
  WHERE contact.con_contno = contactId)'
        );

        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            'customer.cus_name'
        );
        $this->setAddColumnsOff();


    }

    static function getConstants()
    {
        try {
            $oClass = new ReflectionClass(__CLASS__);
            return $oClass->getConstants();
        } catch (ReflectionException $e) {
            return [];
        }
    }

    function search($customerId = null,
                    DateTime $startDate = null,
                    DateTime $endDate = null,
                    $limit = 50
    )
    {
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . ' 
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
                ) . ") >= '" . $startDate->format('Y-m-d') . "'";
        }

        if ($endDate) {
            $queryString .= " and date(" . $this->getTableName() . "." . $this->getDBColumnName(
                    self::createdAt
                ) . ") <= '" . $endDate->format('Y-m-d') . "'";
        }

        if ($limit) {
            $queryString .= " limit $limit";
        }

        $this->setQueryString($queryString);

        $this->getRows();
    }
}