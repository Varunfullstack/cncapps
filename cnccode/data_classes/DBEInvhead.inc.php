<?php /*
* Invhead table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEInvhead extends DBEntity
{

    const invheadID = "invheadID";
    const customerID = "customerID";
    const siteNo = "siteNo";
    const ordheadID = "ordheadID";
    const type = "type";
    const add1 = "add1";
    const add2 = "add2";
    const add3 = "add3";
    const town = "town";
    const county = "county";
    const postcode = "postcode";
    const contactID = "contactID";
    const contactName = "contactName";
    const salutation = "salutation";
    const payMethod = "payMethod";
    const paymentTermsID = "paymentTermsID";
    const vatCode = "vatCode";
    const vatRate = "vatRate";
    const intPORef = "intPORef";
    const custPORef = "custPORef";
    const debtorCode = "debtorCode";
    const source = "source";
    const vatOnly = "vatOnly";
    const datePrinted = "datePrinted";
    const pdfFile = "pdfFile";
    const directDebit = "directDebit";
    const transactionType = "transactionType";

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
        $this->setTableName("invhead");
        $this->addColumn(
            self::invheadID,
            DA_ID,
            DA_NOT_NULL,
            "inh_invno"
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL,
            "inh_custno"
        );
        $this->addColumn(
            self::siteNo,
            DA_ID,
            DA_ALLOW_NULL,
            "inh_siteno"
        );
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_ALLOW_NULL,
            "inh_ordno"
        );
        $this->addColumn(
            self::type,
            DA_STRING,
            DA_NOT_NULL,
            "inh_type"
        );
        $this->addColumn(
            self::add1,
            DA_STRING,
            DA_NOT_NULL,
            "inh_add1"
        );
        $this->addColumn(
            self::add2,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_add2"
        );
        $this->addColumn(
            self::add3,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_add3"
        );
        $this->addColumn(
            self::town,
            DA_STRING,
            DA_NOT_NULL,
            "inh_town"
        );
        $this->addColumn(
            self::county,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_county"
        );
        $this->addColumn(
            self::postcode,
            DA_STRING,
            DA_NOT_NULL,
            "inh_postcode"
        );
        $this->addColumn(
            self::contactID,
            DA_ID,
            DA_NOT_NULL,
            "inh_contno"
        );
        $this->addColumn(
            self::contactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_contact"
        );
        $this->addColumn(
            self::salutation,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_salutation"
        );
        $this->addColumn(
            self::payMethod,
            DA_STRING,
            DA_NOT_NULL,
            "inh_pay_method"
        );
        $this->addColumn(
            self::paymentTermsID,
            DA_ID,
            DA_NOT_NULL,
            'invhead.paymentTermsID'
        );
        $this->addColumn(
            self::vatCode,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_vat_code"
        );
        $this->addColumn(
            self::vatRate,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "inh_vat_rate"
        );
        $this->addColumn(
            self::intPORef,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_ref_ecc"
        );
        $this->addColumn(
            self::custPORef,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_ref_cust"
        );
        $this->addColumn(
            self::debtorCode,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_debtor_code"
        );
        $this->addColumn(
            self::source,
            DA_STRING,
            DA_ALLOW_NULL,
            "inh_source"
        );
        $this->addColumn(
            self::vatOnly,
            DA_YN,
            DA_ALLOW_NULL,
            "inh_vat_only"
        );
        $this->addColumn(
            self::datePrinted,
            DA_DATE,
            DA_ALLOW_NULL,
            "inh_date_printed"
        );
        $this->addColumn(
            self::pdfFile,
            DA_BLOB,
            DA_ALLOW_NULL,
            "inh_pdf_file"
        );
        $this->addColumn(
            self::directDebit,
            DA_BOOLEAN,
            DA_NOT_NULL,
            'directDebit'
        );

        $this->addColumn(
            self::transactionType,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Set the date printed to passed date for all unprinted invoices in the
     * database
     * @parameter Date $dateToUse Date to use for printed date
     * @param $date
     * @param bool $directDebit
     * @return bool &$dsResults results
     * @access public
     */
    function setUnprintedInvoicesDate($date,
                                      $directDebit = false
    )
    {
        if ($date == '') {
            $this->raiseError('date not passed');
        }
        $queryString =
            "UPDATE " . $this->getTableName() .
            " SET " . $this->getDBColumnName(self::datePrinted) . "='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $date
            ) . "'" .
            " WHERE " . $this->getDBColumnName(self::datePrinted) . "='0000-00-00'";

        if (!$directDebit) {
            $queryString .= " and " . $this->getDBColumnName(self::directDebit) . ' = false ';
        } else {
            $queryString .= " and " . $this->getDBColumnName(self::directDebit) . ' = true ';
        }

        $this->setQueryString($queryString);
        $ret = (parent::runQuery());
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Count number of unprinted credit notes or invoices
     * @param string $type I=Invoices C=Credit Notes
     * @param bool $directDebit
     * @return Integer Count
     * @access public
     */
    function countUnprinted($type = 'I',
                            $directDebit = false
    )
    {
        $queryString = "SELECT COUNT(*)" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::type) . "='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $type
            ) . "'" .
            " AND " . $this->getDBColumnName(self::datePrinted) . "='0000-00-00'";
        if (!$directDebit) {
            $queryString .= " and " . $this->getDBColumnName(self::directDebit) . ' = false ';
        } else {
            $queryString .= " and " . $this->getDBColumnName(self::directDebit) . ' = true ';
        }

        $this->setQueryString($queryString);

        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }

    /**
     * Value of unprinted credit notes or invoices
     * @param string $type I=Invoices C=Credit Notes
     * @param bool $directDebit
     * @return Integer Count
     * @access public
     */
    function valueUnprinted($type = 'I',
                            $directDebit = false
    )
    {
        $queryString = "SELECT SUM(inl_unit_price)" .
            " FROM " . $this->getTableName() .
            " JOIN invline ON " . $this->getDBColumnName(self::invheadID) . "=inl_invno" .
            " WHERE " . $this->getDBColumnName(self::type) . "='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $type
            ) . "'" .
            " AND " . $this->getDBColumnName(self::datePrinted) . "='0000-00-00'" .
            " AND inl_unit_price IS NOT NULL";

        if (!$directDebit) {
            $queryString .= " and " . $this->getDBColumnName(self::directDebit) . ' = false ';
        } else {
            $queryString .= " and " . $this->getDBColumnName(self::directDebit) . ' = true ';
        }
        $this->setQueryString($queryString);
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return 0;
    }

    function countRowsByCustomerSiteNo($customerID,
                                       $siteNo
    )
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . "=" . $customerID .
            " AND " . $this->getDBColumnName(self::siteNo) . "=" . $siteNo
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }
}

?>