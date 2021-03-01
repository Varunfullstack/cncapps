<?php

namespace CNCLTD\Exceptions\infra;
class SupplierWithMainContactMysqlDTO implements \JsonSerializable
{
    private $id;
    private $name;
    private $address1;
    private $address2;
    private $town;
    private $county;
    private $postcode;
    private $cncAccountCode;
    private $websiteURL;
    private $mainContactTitle;
    private $mainContactPosition;
    private $mainContactName;
    private $mainContactEmail;
    private $mainContactPhone;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getMainContactName()
    {
        return $this->mainContactName;
    }

    /**
     * @return mixed
     */
    public function getMainContactEmail()
    {
        return $this->mainContactEmail;
    }

    /**
     * @return mixed
     */
    public function getMainContactPhone()
    {
        return $this->mainContactPhone;
    }

    /**
     * @return mixed
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return mixed
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return mixed
     */
    public function getCncAccountCode()
    {
        return $this->cncAccountCode;
    }

    /**
     * @return mixed
     */
    public function getWebsiteURL()
    {
        return $this->websiteURL;
    }

    /**
     * @return mixed
     */
    public function getMainContactTitle()
    {
        return $this->mainContactTitle;
    }

    /**
     * @return mixed
     */
    public function getMainContactPosition()
    {
        return $this->mainContactPosition;
    }

    /**
     * @return mixed
     */
    public function getMainContactMobile()
    {
        return $this->mainContactMobile;
    }
}