<?php

namespace CNCLTD\Supplier;

use CNCLTD\Supplier\Domain\SupplierContact\Email;
use CNCLTD\Supplier\Domain\SupplierContact\FirstName;
use CNCLTD\Supplier\Domain\SupplierContact\LastName;
use CNCLTD\Supplier\Domain\SupplierContact\Phone;
use CNCLTD\Supplier\Domain\SupplierContact\Position;
use CNCLTD\Supplier\Domain\SupplierContact\Title;

class CreateSupplierRequest
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
     * @var Title
     */
    private $mainContactTitle;
    /**
     * @var Position
     */
    private $mainContactPosition;
    /**
     * @var FirstName
     */
    private $mainContactFirstName;
    /**
     * @var LastName
     */
    private $mainContactLastName;
    /**
     * @var Email
     */
    private $mainContactEmail;
    /**
     * @var Phone
     */
    private $mainContactPhone;

    /**
     * CreateSupplierRequest constructor.
     * @param SupplierTown $town
     * @param SupplierCounty $county
     * @param SupplierPostcode $postcode
     * @param SupplierName $name
     * @param SupplierAddress1 $address1
     * @param SupplierAddress2 $address2
     * @param SupplierWebsiteURL $websiteURL
     * @param SupplierPaymentMethodId $paymentMethodId
     * @param SupplierAccountCode $accountCode
     * @param Title $mainContactTitle
     * @param Position $mainContactPosition
     * @param FirstName $mainContactFirstName
     * @param LastName $mainContactLastName
     * @param Email $mainContactEmail
     * @param Phone $mainContactPhone
     */
    public function __construct(SupplierTown $town,
                                SupplierCounty $county,
                                SupplierPostcode $postcode,
                                SupplierName $name,
                                SupplierAddress1 $address1,
                                SupplierAddress2 $address2,
                                SupplierWebsiteURL $websiteURL,
                                SupplierPaymentMethodId $paymentMethodId,
                                SupplierAccountCode $accountCode,
                                Title $mainContactTitle,
                                Position $mainContactPosition,
                                FirstName $mainContactFirstName,
                                LastName $mainContactLastName,
                                Email $mainContactEmail,
                                Phone $mainContactPhone
    )
    {
        $this->town                 = $town;
        $this->county               = $county;
        $this->postcode             = $postcode;
        $this->name                 = $name;
        $this->address1             = $address1;
        $this->address2             = $address2;
        $this->websiteURL           = $websiteURL;
        $this->paymentMethodId      = $paymentMethodId;
        $this->accountCode          = $accountCode;
        $this->mainContactTitle     = $mainContactTitle;
        $this->mainContactPosition  = $mainContactPosition;
        $this->mainContactFirstName = $mainContactFirstName;
        $this->mainContactLastName  = $mainContactLastName;
        $this->mainContactEmail     = $mainContactEmail;
        $this->mainContactPhone     = $mainContactPhone;
    }

    public static function fromJSONArray($array): self
    {
        $town                 = new SupplierTown(@$array['town']);
        $county               = new SupplierCounty(@$array['county']);
        $postcode             = new SupplierPostcode(@$array['postcode']);
        $name                 = new SupplierName(@$array['name']);
        $address1             = new SupplierAddress1(@$array['address1']);
        $address2             = new SupplierAddress2(@$array['address2']);
        $websiteURL           = new SupplierWebsiteURL(@$array['websiteURL']);
        $paymentMethodId      = new SupplierPaymentMethodId(@$array['paymentMethodId']);
        $accountCode          = new SupplierAccountCode(@$array['accountCode']);
        $mainContactTitle     = new Title(@$array['mainContactTitle']);
        $mainContactPosition  = new Position(@$array['mainContactPosition']);
        $mainContactFirstName = new FirstName(@$array['mainContactFirstName']);
        $mainContactLastName  = new LastName(@$array['mainContactLastName']);
        $mainContactEmail     = new Email(@$array['mainContactEmail']);
        $mainContactPhone     = new Phone(@$array['mainContactPhone']);
        return new self(
            $town,
            $county,
            $postcode,
            $name,
            $address1,
            $address2,
            $websiteURL,
            $paymentMethodId,
            $accountCode,
            $mainContactTitle,
            $mainContactPosition,
            $mainContactFirstName,
            $mainContactLastName,
            $mainContactEmail,
            $mainContactPhone
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
     * @return Title
     */
    public function getMainContactTitle(): Title
    {
        return $this->mainContactTitle;
    }

    /**
     * @return Position
     */
    public function getMainContactPosition(): Position
    {
        return $this->mainContactPosition;
    }

    /**
     * @return FirstName
     */
    public function getMainContactFirstName(): FirstName
    {
        return $this->mainContactFirstName;
    }

    /**
     * @return LastName
     */
    public function getMainContactLastName(): LastName
    {
        return $this->mainContactLastName;
    }

    /**
     * @return Email
     */
    public function getMainContactEmail(): Email
    {
        return $this->mainContactEmail;
    }

    /**
     * @return Phone
     */
    public function getMainContactPhone(): Phone
    {
        return $this->mainContactPhone;
    }
}
