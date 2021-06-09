<?php /*
* Ordhead table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOrdhead extends DBEntity
{
    const INITIAL_TYPE           = 'I';
    const ordheadID              = "ordheadID";
    const customerID             = "customerID";
    const type                   = "type";
    const partInvoice            = "partInvoice";
    const date                   = "date";
    const requestedDate          = "requestedDate";
    const promisedDate           = "promisedDate";
    const expectedDate           = "expectedDate";
    const quotationOrdheadID     = "quotationOrdheadID";
    const custPORef              = "custPORef";
    const vatCode                = "vatCode";
    const vatRate                = "vatRate";
    const invSiteNo              = "invSiteNo";
    const invAdd1                = "invAdd1";
    const invAdd2                = "invAdd2";
    const invAdd3                = "invAdd3";
    const invTown                = "invTown";
    const invCounty              = "invCounty";
    const invPostcode            = "invPostcode";
    const invContactID           = "invContactID";
    const invContactName         = "invContactName";
    const invContactSalutation   = "invContactSalutation";
    const invContactPhone        = "invContactPhone";
    const invSitePhone           = "invSitePhone";
    const invContactFax          = "invContactFax";
    const invContactEmail        = "invContactEmail";
    const delSiteNo              = "delSiteNo";
    const delAdd1                = "delAdd1";
    const delAdd2                = "delAdd2";
    const delAdd3                = "delAdd3";
    const delTown                = "delTown";
    const delCounty              = "delCounty";
    const delPostcode            = "delPostcode";
    const delContactID           = "delContactID";
    const delContactName         = "delContactName";
    const delContactSalutation   = "delContactSalutation";
    const delContactPhone        = "delContactPhone";
    const delSitePhone           = "delSitePhone";
    const delContactFax          = "delContactFax";
    const delContactEmail        = "delContactEmail";
    const debtorCode             = "debtorCode";
    const wip                    = "wip";
    const consultantID           = "consultantID";
    const paymentTermsID         = "paymentTermsID";
    const addItem                = "addItem";
    const callID                 = "callID";
    const quotationSubject       = "quotationSubject";
    const quotationIntroduction  = "quotationIntroduction";
    const updatedTime            = "updatedTime";
    const quotationCreateDate    = "quotationCreateDate";
    const directDebitFlag        = "ordhead.directDebitFlag";
    const transactionType        = 'ordhead.transactionType';
    const serviceRequestTaskList = "serviceRequestTaskList";

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
        $this->setTableName("Ordhead");
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_NOT_NULL,
            "odh_ordno"
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL,
            "odh_custno"
        );
        $this->addColumn(
            self::type,
            DA_STRING,
            DA_NOT_NULL,
            "odh_type"
        );
        $this->addColumn(
            self::partInvoice,
            DA_YN,
            DA_NOT_NULL,
            "odh_part_invoice"
        );
        $this->addColumn(
            self::date,
            DA_DATE,
            DA_NOT_NULL,
            "odh_date"
        );
        $this->addColumn(
            self::requestedDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "odh_req_date"
        );
        $this->addColumn(
            self::promisedDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "odh_prom_date"
        );
        $this->addColumn(
            self::expectedDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "odh_expect_date"
        );
        $this->addColumn(
            self::quotationOrdheadID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "odh_quotation_ordno"
        );
        $this->addColumn(
            self::custPORef,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_ref_cust"
        );
        $this->addColumn(
            self::vatCode,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_vat_code"
        );
        $this->addColumn(
            self::vatRate,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_vat_rate"
        );
        $this->addColumn(
            self::invSiteNo,
            DA_ID,
            DA_NOT_NULL,
            "odh_inv_siteno"
        );
        $this->addColumn(
            self::invAdd1,
            DA_STRING,
            DA_NOT_NULL,
            "odh_inv_add1"
        );
        $this->addColumn(
            self::invAdd2,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_add2"
        );
        $this->addColumn(
            self::invAdd3,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_add3"
        );
        $this->addColumn(
            self::invTown,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_town"
        );
        $this->addColumn(
            self::invCounty,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_county"
        );
        $this->addColumn(
            self::invPostcode,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_postcode"
        );
        $this->addColumn(
            self::invContactID,
            DA_ID,
            DA_NOT_NULL,
            "odh_inv_contno"
        );
        $this->addColumn(
            self::invContactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_contact"
        );
        $this->addColumn(
            self::invContactSalutation,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_salutation"
        );
        $this->addColumn(
            self::invContactPhone,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_phone"
        );
        $this->addColumn(
            self::invSitePhone,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_sphone"
        );
        $this->addColumn(
            self::invContactFax,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_fax"
        );
        $this->addColumn(
            self::invContactEmail,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_inv_email"
        );
        $this->addColumn(
            self::delSiteNo,
            DA_ID,
            DA_NOT_NULL,
            "odh_del_siteno"
        );
        $this->addColumn(
            self::delAdd1,
            DA_STRING,
            DA_NOT_NULL,
            "odh_del_add1"
        );
        $this->addColumn(
            self::delAdd2,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_add2"
        );
        $this->addColumn(
            self::delAdd3,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_add3"
        );
        $this->addColumn(
            self::delTown,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_town"
        );
        $this->addColumn(
            self::delCounty,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_county"
        );
        $this->addColumn(
            self::delPostcode,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_postcode"
        );
        $this->addColumn(
            self::delContactID,
            DA_ID,
            DA_NOT_NULL,
            "odh_del_contno"
        );
        $this->addColumn(
            self::delContactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_contact"
        );
        $this->addColumn(
            self::delContactSalutation,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_salutation"
        );
        $this->addColumn(
            self::delContactPhone,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_phone"
        );
        $this->addColumn(
            self::delSitePhone,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_sphone"
        );
        $this->addColumn(
            self::delContactFax,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_fax"
        );
        $this->addColumn(
            self::delContactEmail,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_del_email"
        );
        $this->addColumn(
            self::debtorCode,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_debtor_code"
        );
        $this->addColumn(
            self::wip,
            DA_YN,
            DA_ALLOW_NULL,
            "odh_wip"
        );
        $this->addColumn(
            self::consultantID,
            DA_ID,
            DA_ALLOW_NULL,
            "odh_consno"
        );
        $this->addColumn(
            self::paymentTermsID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::addItem,
            DA_YN,
            DA_ALLOW_NULL,
            "odh_add_item"
        );
        $this->addColumn(
            self::callID,
            DA_ID,
            DA_ALLOW_NULL,
            "odh_callno"
        );
        $this->addColumn(
            self::quotationSubject,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_quotation_subject"
        );
        $this->addColumn(
            self::quotationIntroduction,
            DA_STRING,
            DA_ALLOW_NULL,
            "odh_quotation_introduction"
        );
        $this->addColumn(
            self::updatedTime,
            DA_DATETIME,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::quotationCreateDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "odh_quotation_create_date"
        );
        $this->addColumn(
            self::directDebitFlag,
            DA_YN_FLAG,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::transactionType,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function insertRow()
    {
        $this->setValue(
            self::updatedTime,
            date('Y-m-d H:i:s')
        );
        parent::insertRow();
    }

    function updateRow()
    {
        $this->setValue(
            self::updatedTime,
            date('Y-m-d H:i:s')
        );
        parent::updateRow();
    }

    function setUpdatedTime()
    {
        if ($this->getPKValue() == '') {
            $this->raiseError('ordheadID not set');
        }
        $newTime = date('Y-m-d H:i:s');
        $this->setQueryString(
            "UPDATE {$this->getTableName()} SET {$this->getDBColumnName(self::updatedTime)}='{$newTime}' WHERE {$this->getPKDBName()}={$this->getPKValue()}"
        );
        $this->runQuery();
        $this->resetQueryString();
        return $newTime;
    }

    function setStatusCompleted()
    {
        if ($this->getPKValue() == '') {
            $this->raiseError('ordheadID not set');
        }
        $this->setQueryString(
            "UPDATE " . $this->getTableName() . " SET " . $this->getDBColumnName(
                self::type
            ) . "='C'" . " WHERE " . $this->getPKDBName() . "=" . $this->getPKValue()
        );
        $this->runQuery();
        $this->resetQueryString();
        return TRUE;
    }

    function countRowsByCustomerSiteNo($customerID,
                                       $siteNo
    )
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() . " WHERE " . $this->getDBColumnName(
                self::customerID
            ) . "=" . $customerID . " AND (" . $this->getDBColumnName(
                self::delSiteNo
            ) . "=" . $siteNo . " OR " . $this->getDBColumnName(self::invSiteNo) . "=" . $siteNo . ")"
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return false;
    }

    function countRowsByContactID($contactID)
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() . " WHERE " . $this->getDBColumnName(
                self::delContactID
            ) . "=" . $contactID . " OR " . $this->getDBColumnName(self::invContactID) . "=" . $contactID
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return false;
    }
}
