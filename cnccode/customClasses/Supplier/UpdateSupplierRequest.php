<?php

namespace CNCLTD\Supplier;

use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;

class UpdateSupplierRequest
{
    /**
     * @var SupplierTown
     */
    private $town;
    /**
     * @var SupplierCounty
     */
    private $county;
    /**
     * @var SupplierPostcode
     */
    private $postcode;

    /**
     * @var SupplierId
     */
    private $id;
    /**
     * @var SupplierName
     */
    private $name;
    /**
     * @var SupplierAddress1
     */
    private $address1;
    /**
     * @var SupplierAddress2
     */
    private $address2;
    /**
     * @var SupplierWebsiteURL
     */
    private $websiteURL;
    /**
     * @var SupplierPaymentMethodId
     */
    private $paymentMethodId;
    /**
     * @var SupplierAccountCode
     */
    private $accountCode;

    /**
     * @var SupplierContactId
     */
    private $mainSupplierContactId;

    /**
     * UpdateSupplierRequest constructor.
     * @param SupplierId $id
     * @param SupplierTown $town
     * @param SupplierCounty $county
     * @param SupplierPostcode $postcode
     * @param SupplierName $name
     * @param SupplierAddress1 $address1
     * @param SupplierAddress2 $address2
     * @param SupplierWebsiteURL $websiteURL
     * @param SupplierPaymentMethodId $paymentMethodId
     * @param SupplierAccountCode $accountCode
     * @param SupplierContactId $mainSupplierContactId
     */
    public function __construct(SupplierId $id,
                                SupplierTown $town,
                                SupplierCounty $county,
                                SupplierPostcode $postcode,
                                SupplierName $name,
                                SupplierAddress1 $address1,
                                SupplierAddress2 $address2,
                                SupplierWebsiteURL $websiteURL,
                                SupplierPaymentMethodId $paymentMethodId,
                                SupplierAccountCode $accountCode,
                                SupplierContactId $mainSupplierContactId
    )
    {
        $this->town                  = $town;
        $this->county                = $county;
        $this->postcode              = $postcode;
        $this->id                    = $id;
        $this->name                  = $name;
        $this->address1              = $address1;
        $this->address2              = $address2;
        $this->websiteURL            = $websiteURL;
        $this->paymentMethodId       = $paymentMethodId;
        $this->accountCode           = $accountCode;
        $this->mainSupplierContactId = $mainSupplierContactId;
    }

    public static function fromJSONArray($array): UpdateSupplierRequest
    {
        $supplierId            = new SupplierId(@$array['id']);
        $town                  = new SupplierTown(@$array['town']);
        $county                = new SupplierCounty(@$array['county']);
        $postcode              = new SupplierPostcode(@$array['postcode']);
        $name                  = new SupplierName(@$array['name']);
        $address1              = new SupplierAddress1(@$array['address1']);
        $address2              = new SupplierAddress2(@$array['address2']);
        $websiteURL            = new SupplierWebsiteURL(@$array['websiteURL']);
        $paymentMethodId       = new SupplierPaymentMethodId(@$array['paymentMethodId']);
        $accountCode           = new SupplierAccountCode(@$array['accountCode']);
        $mainSupplierContactId = new SupplierContactId(@$array['mainSupplierContactId']);
        return new self(
            $supplierId,
            $town,
            $county,
            $postcode,
            $name,
            $address1,
            $address2,
            $websiteURL,
            $paymentMethodId,
            $accountCode,
            $mainSupplierContactId
        );

    }

    /**
     * @return SupplierTown
     */
    public function getTown(): SupplierTown
    {
        return $this->town;
    }

    /**
     * @return SupplierCounty
     */
    public function getCounty(): SupplierCounty
    {
        return $this->county;
    }

    /**
     * @return SupplierPostcode
     */
    public function getPostcode(): SupplierPostcode
    {
        return $this->postcode;
    }

    /**
     * @return SupplierId
     */
    public function getId(): SupplierId
    {
        return $this->id;
    }

    /**
     * @return SupplierName
     */
    public function getName(): SupplierName
    {
        return $this->name;
    }

    /**
     * @return SupplierAddress1
     */
    public function getAddress1(): SupplierAddress1
    {
        return $this->address1;
    }

    /**
     * @return SupplierAddress2
     */
    public function getAddress2(): SupplierAddress2
    {
        return $this->address2;
    }

    /**
     * @return SupplierWebsiteURL
     */
    public function getWebsiteURL(): SupplierWebsiteURL
    {
        return $this->websiteURL;
    }

    /**
     * @return SupplierPaymentMethodId
     */
    public function getPaymentMethodId(): SupplierPaymentMethodId
    {
        return $this->paymentMethodId;
    }

    /**
     * @return SupplierAccountCode
     */
    public function getAccountCode(): SupplierAccountCode
    {
        return $this->accountCode;
    }

    /**
     * @return SupplierContactId
     */
    public function getMainSupplierContactId(): SupplierContactId
    {
        return $this->mainSupplierContactId;
    }


}
