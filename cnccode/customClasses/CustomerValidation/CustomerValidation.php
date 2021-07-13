<?php


namespace CNCLTD\CustomerValidation;


use BUCustomer;
use DataSet;
use DBEContact;
use DBESite;

class CustomerValidation
{
    private $customerName;
    private $customerId;
    private $contactValidationErrors = [];
    private $siteValidationErrors = [];
    private $globalValidationErrors = [];

    public function __construct($customerName, $customerId)
    {
        $this->customerName = $customerName;
        $this->customerId = $customerId;
        $this->runValidation();
    }

    private function runValidation()
    {
        $dsContacts = new DataSet($this);
        $buCustomer = new BUCustomer($this);
        $buCustomer->getContactsByCustomerID($this->customerId, $dsContacts);
        $atLeastOneAccount = false;
        $atLeastOneInvoice = false;
        $atLeastOneAtMostOneStatement = false;
        $atLeastOneMain = false;
        $atLeastOneReview = false;
        $atLeastOneTopUp = !$buCustomer->hasPrepayContract($this->customerId);
        $atLeastOneReport = false;

        while ($dsContacts->fetchNext()) {
            $contactValidation = new ContactValidation($dsContacts->getValue(DBEContact::contactID));
            if ($dsContacts->getValue(DBEContact::accountsFlag) == 'Y' && !$atLeastOneAccount) {
                $atLeastOneAccount = true;
            }

            if ($dsContacts->getValue(DBEContact::mailshot2Flag) == 'Y' && !$atLeastOneInvoice) {
                $atLeastOneInvoice = true;
            }

            if ($dsContacts->getValue(
                    DBEContact::supportLevel
                ) == DBEContact::supportLevelMain && !$atLeastOneMain) {
                $atLeastOneMain = true;
            }

            if ($dsContacts->getValue(DBEContact::reviewUser) == 'Y' && !$atLeastOneReview) {
                $atLeastOneReview = true;
            }

            if ($dsContacts->getValue(DBEContact::mailshot8Flag) == 'Y' && !$atLeastOneTopUp) {
                $atLeastOneTopUp = true;
            }

            if ($dsContacts->getValue(DBEContact::mailshot9Flag) == 'Y' && !$atLeastOneReport) {
                $atLeastOneReport = true;
            }
            if ($contactValidation->hasErrors()) {
                $this->contactValidationErrors[] = $contactValidation;
            }
        }
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(DBESite::customerID, $this->customerId);
        $dbeSite->getRowsByCustomerID();
        while ($dbeSite->fetchNext()) {
            $siteValidation = new SiteValidation($this->customerId, $dbeSite->getValue(DBESite::siteNo));
            if ($siteValidation->hasErrors()) {
                $this->siteValidationErrors[] = $siteValidation;
            }

        }

        if (!$atLeastOneAccount) {
            $this->globalValidationErrors[] = new ValidationError(
                "At least one contact must have Account flag checked"
            );
        }

        if (!$atLeastOneInvoice) {
            $this->globalValidationErrors[] = new ValidationError(
                "At least one contact must have Invoice flag checked"
            );
        }

        if (!$atLeastOneAtMostOneStatement) {
            $this->globalValidationErrors[] = new ValidationError(
                "At most and at least one contact must have Statement flag checked"
            );
        }

        if (!$atLeastOneMain) {
            $this->globalValidationErrors[] = new ValidationError(
                "At least one contact must have Main as Support Level"
            );
        }

        if (!$atLeastOneReview) {
            $this->globalValidationErrors[] = new ValidationError("At least one contact must have Review flag checked");
        }

        if (!$atLeastOneTopUp) {
            $this->globalValidationErrors[] = new ValidationError("At least one contact must have TopUp flag checked");
        }

        if (!$atLeastOneReport) {
            $this->globalValidationErrors[] = new ValidationError("At least one contact must have Report flag checked");
        }


    }

    public function getCustomerURL()
    {
        return "$_SERVER[HTTP_HOST]/Customer.php?action=dispEdit&customerID={$this->customerId}";
    }

    public function hasErrors()
    {
        return count($this->globalValidationErrors) || count($this->contactValidationErrors) || count(
                $this->siteValidationErrors
            );
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @return ContactValidation[]
     */
    public function getContactValidationErrors(): array
    {
        return $this->contactValidationErrors;
    }

    /**
     * @return SiteValidation[]
     */
    public function getSiteValidationErrors(): array
    {
        return $this->siteValidationErrors;
    }

    /**
     * @return ValidationError[]
     */
    public function getGlobalValidationErrors(): array
    {
        return $this->globalValidationErrors;
    }

}