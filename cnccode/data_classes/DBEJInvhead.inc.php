<?php /*
* Invhead join to customer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEInvhead.inc.php");

class DBEJInvhead extends DBEInvhead
{
    const customerName = "customerName";
    const firstName = "firstName";
    const lastName = "lastName";
    const title = "title";
    const paymentTerms = "paymentTerms";

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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::firstName,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_first_name"
        );
        $this->addColumn(
            self::lastName,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_last_name"
        );
        $this->addColumn(
            self::title,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_title"
        );
        $this->addColumn(
            self::paymentTerms,
            DA_STRING,
            DA_ALLOW_NULL,
            "description"
        );
        $this->setAddColumnsOff();
    }

    function getPrintedRowsByRange($customerID,
                                   $startDate,
                                   $endDate,
                                   $startID,
                                   $endID
    )
    {
        $this->setMethodName('getPrintedRowsByRange');

        $queryString =
            'SELECT ' . $this->getDBColumnNamesAsString() . ' FROM ' . $this->getTableName() .
            ' LEFT JOIN customer ON inh_custno = cus_custno' .
            ' LEFT JOIN contact ON inh_contno = con_contno' .
            ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID ' .
            ' WHERE 1=1';

        if ($startDate != '') {
            $queryString .=
                ' AND ' . $this->getDBColumnName(self::datePrinted) . ' >= \'' . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $startDate
                ) . '\'';
        }

        if ($endDate != '') {
            $queryString .=
                ' AND ' . $this->getDBColumnName(self::datePrinted) . ' <= \'' . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $endDate
                ) . '\'';
        }

        if ($customerID != '') {
            $queryString .=
                ' AND ' . $this->getDBColumnName(self::customerID) . ' = \'' . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $customerID
                ) . '\'';
        }

        if ($startID != '') {
            $queryString .=
                ' AND ' . $this->getDBColumnName(self::invheadID) . ' >= \'' . $startID . '\'';
        }

        if ($endID != '') {
            $queryString .=
                ' AND ' . $this->getDBColumnName(self::invheadID) . ' <= \'' . $endID . '\'';
        }

        $queryString .= ' AND ' . $this->getDBColumnName(self::datePrinted) . ' is not null ';

        $queryString .= ' ORDER BY ' . $this->getDBColumnName(self::invheadID);

        $this->setQueryString($queryString);
        return ($this->getRows());
    }

    function getUnprintedRows($directDebit = false)
    {
        $this->setMethodName('getUnprintedRows');
        $queryString =
            'SELECT ' . $this->getDBColumnNamesAsString() . ' FROM ' . $this->getTableName() .
            ' LEFT JOIN customer ON inh_custno = cus_custno' .
            ' LEFT JOIN contact ON inh_contno = con_contno' .
            ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID ' .
            ' WHERE ' . $this->getDBColumnName(self::datePrinted) . ' is null';

        $queryString .= " and " . $this->getDBColumnName(
                self::directDebitFlag
            ) . ($directDebit ? ' = "Y" ' : ' <> "Y" ');

        $queryString .= ' ORDER BY ' . $this->getDBColumnName(self::customerID);

        $this->setQueryString($queryString);
        return ($this->getRows());
    }

    function getRowsBySearchCriteria(
        $customerID,
        $ordheadID,
        $printedFlag,
        $fromDate,
        $toDate,
        $invoiceType
    )
    {
        $this->setMethodName('getRowsBySearchCriteria');
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN customer ON " . $this->getTableName() . "." . $this->getDBColumnName(self::customerID) .
            "= customer.cus_custno" .
            " LEFT JOIN contact ON inh_contno = con_contno" .
            ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID ';
        $statement = $statement . " WHERE 1=1";
        if ($ordheadID != '') {                // if passed an ordheadID then only use this
            $statement = $statement .
                " AND " . $this->getDBColumnName(self::ordheadID) . "=" . $ordheadID;
        } else {
            if ($customerID != '') {
                $statement = $statement .
                    " AND " . $this->getDBColumnName(self::customerID) . "=" . $customerID;
            }
            if ($invoiceType != '') {
                $statement = $statement .
                    " AND " . $this->getDBColumnName(self::type) . "='" . $invoiceType . "'";
            }
            if ($printedFlag == 'Y') {
                if ($fromDate != '') {
                    $statement = $statement .
                        " AND " . $this->getDBColumnName(self::datePrinted) . ">='" . mysqli_real_escape_string(
                            $this->db->link_id(),
                            $fromDate
                        ) . "'";
                }
                if ($toDate != '') {
                    $statement = $statement .
                        " AND " . $this->getDBColumnName(self::datePrinted) . "<='" . mysqli_real_escape_string(
                            $this->db->link_id(),
                            $toDate
                        ) . "'";
                }
            } else {
                $statement = $statement .
                    " AND " . $this->getDBColumnName(self::datePrinted) . " is null ";
            }
        }
        $statement .= " ORDER BY " . $this->getDBColumnName(self::ordheadID) . " DESC";
        $statement .= " LIMIT 0, 200";
        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    } // no ordheadID

    function getRow($invheadID = null)
    {
        $this->setMethodName('getRow');
        $queryString =
            'SELECT ' . $this->getDBColumnNamesAsString() . ' FROM ' . $this->getTableName() .
            ' LEFT JOIN customer ON inh_custno = cus_custno' .
            ' LEFT JOIN contact ON inh_contno = con_contno' .
            ' JOIN paymentterms ON invhead.paymentTermsID = paymentterms.paymentTermsID ' .
            ' WHERE ' . $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID);
        $this->setQueryString($queryString);
        return (parent::getRow());
    }
}