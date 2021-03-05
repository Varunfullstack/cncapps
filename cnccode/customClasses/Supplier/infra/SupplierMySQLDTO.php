<?php

namespace CNCLTD\Supplier\infra;
class SupplierMySQLDTO
{
    private $sup_suppno;
    private $sup_name;
    private $sup_add1;
    private $sup_add2;
    private $sup_town;
    private $sup_county;
    private $sup_postcode;
    private $sup_phone;
    private $sup_fax;
    private $sup_web_site_url;
    private $sup_contno;
    private $sup_cnc_accno;
    private $sup_payno;
    private $active;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->sup_suppno;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->sup_name;
    }

    /**
     * @return mixed
     */
    public function getAddress1()
    {
        return $this->sup_add1;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->sup_add2;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->sup_town;
    }

    /**
     * @return mixed
     */
    public function getCounty()
    {
        return $this->sup_county;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->sup_postcode;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->sup_phone;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->sup_fax;
    }

    /**
     * @return mixed
     */
    public function getWebsiteUrl()
    {
        return $this->sup_web_site_url;
    }


    /**
     * @return mixed
     */
    public function getPayMethodId()
    {
        return $this->sup_payno;
    }

    /**
     * @return int
     */
    public function getMainSupplierContactId(): int
    {
        return $this->sup_contno;
    }

    /**
     * @return string
     */
    public function getCNCAccountCode(): ?string
    {
        return $this->sup_cnc_accno;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return (bool)$this->active;
    }

}