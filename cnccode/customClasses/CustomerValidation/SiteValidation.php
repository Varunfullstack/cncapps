<?php


namespace CNCLTD\CustomerValidation;


use BUSite;
use DataSet;
use DBESite;

class SiteValidation
{
    private $customerId;
    private $siteId;
    /**
     * @var DataSet|DBESite
     */
    private $site;
    private $validationErrors;

    /**
     * SiteValidation constructor.
     * @param $customerId
     * @param $siteId
     */
    public function __construct($customerId, $siteId)
    {
        $this->customerId = $customerId;
        $this->siteId = $siteId;
        $buSite = new BUSite($this);
        $this->site = new DataSet($this);
        $buSite->getSiteByID($customerId, $siteId, $this->site);
        $this->validationErrors = $this->runValidation();
    }

    private function runValidation()
    {
        $errors = new SiteValidationErrorCollection($this->getPostCode());
        if (!$this->site->getValue(DBESite::maxTravelHours)) {
            $errors->addError("Max Travel hours must be greater than 0");
        }

        if ($this->site->getValue(DBESite::phone) && !preg_match(
                "/^\d+$/",
                $this->site->getValue(DBESite::phone)
            )) {
            $errors->addError("Invalid Phone Number: " . $this->site->getValue(DBESite::phone));
        }
        if (!$this->site->getValue(DBESite::what3Words)) {
            $errors->addError("What3Words value is missing");
        }

        if (!$this->site->getValue(DBESite::deliverContactID)) {
            $errors->addError('Deliver contact is missing');
        }

        if (!$this->site->getValue(DBESite::invoiceContactID)) {
            $errors->addError('Invoice contact is missing');
        }

        return $errors;
    }

    public function getPostCode()
    {
        return $this->site->getValue(DBESite::postcode);
    }

    /**
     * @return ValidationError[]
     */
    public function getValidationErrors()
    {
        return $this->validationErrors->getErrors();
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->validationErrors->hasErrors();
    }

}