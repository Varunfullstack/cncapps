<?php

namespace CNCLTD\Supplier;

use CNCLTD\Exceptions\ContactAlreadyExistsException;
use CNCLTD\Exceptions\SupplierArchivedException;
use CNCLTD\Exceptions\SupplierContactArchivedException;
use CNCLTD\Exceptions\SupplierContactMainInactiveException;
use CNCLTD\Exceptions\SupplierContactNotFoundException;
use CNCLTD\Supplier\Domain\SupplierContact\Active;
use CNCLTD\Supplier\Domain\SupplierContact\Email;
use CNCLTD\Supplier\Domain\SupplierContact\FirstName;
use CNCLTD\Supplier\Domain\SupplierContact\LastName;
use CNCLTD\Supplier\Domain\SupplierContact\Main;
use CNCLTD\Supplier\Domain\SupplierContact\Phone;
use CNCLTD\Supplier\Domain\SupplierContact\Position;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContact;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\Domain\SupplierContact\Title;

class Supplier
{
    private $mainContact;

    private $town;
    private $county;
    private $postcode;
    private $phone;
    private $fax;


    private $isActive;
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
     * @var SupplierIsActive
     */
    private $active;
    /**
     * @var SupplierPaymentMethodId
     */
    private $paymentMethodId;
    /**
     * @var SupplierAccountCode
     */
    private $accountCode;
    /**
     * @var SupplierContact[]
     */
    private $contacts = [];

    /**
     * Supplier constructor.
     * @param $mainContact
     */
    private function __construct(SupplierId $id,
                                 SupplierName $name,
                                 SupplierAddress1 $address1,
                                 SupplierAddress2 $address2,
                                 SupplierTown $town,
                                 SupplierCounty $county,
                                 SupplierPostcode $postcode,
                                 SupplierPhone $phone,
                                 SupplierWebsiteURL $websiteURL,
                                 SupplierFax $fax,
                                 SupplierPaymentMethodId $paymentMethodId,
                                 SupplierAccountCode $accountCode,
                                 SupplierIsActive $active,
                                 SupplierContact $mainContact
    )
    {
        $this->mainContact     = $mainContact;
        $this->id              = $id;
        $this->name            = $name;
        $this->address1        = $address1;
        $this->address2        = $address2;
        $this->town            = $town;
        $this->postcode        = $postcode;
        $this->phone           = $phone;
        $this->websiteURL      = $websiteURL;
        $this->active          = $active;
        $this->fax             = $fax;
        $this->paymentMethodId = $paymentMethodId;
        $this->accountCode     = $accountCode;
        $this->county          = $county;
    }

    public static function create(SupplierId $id,
                                  SupplierName $name,
                                  SupplierAddress1 $address1,
                                  SupplierAddress2 $address2,
                                  SupplierTown $town,
                                  SupplierCounty $county,
                                  SupplierPostcode $postcode,
                                  SupplierPhone $phone,
                                  SupplierWebsiteURL $websiteURL,
                                  SupplierFax $fax,
                                  SupplierPaymentMethodId $paymentMethodId,
                                  SupplierAccountCode $accountCode,
                                  SupplierIsActive $active,
                                  SupplierContactId $supplierContactId,
                                  Position $supplierContactPosition,
                                  Title $supplierContactTitle,
                                  FirstName $supplierContactFirstName,
                                  LastName $supplierContactLastName,
                                  Email $supplierContactEmail,
                                  Phone $supplierContactPhone
    )
    {

        $mainContact = SupplierContact::create(
            $supplierContactId,
            $supplierContactTitle,
            $supplierContactPosition,
            $supplierContactFirstName,
            $supplierContactLastName,
            $supplierContactPhone,
            $supplierContactEmail,
            new Active(true),
            new Main(true)
        );
        return new self(
            $id,
            $name,
            $address1,
            $address2,
            $town,
            $county,
            $postcode,
            $phone,
            $websiteURL,
            $fax,
            $paymentMethodId,
            $accountCode,
            $active,
            $mainContact
        );
    }

    /**
     * @param SupplierContactId $supplierContactId
     * @throws SupplierArchivedException
     * @throws SupplierContactArchivedException
     * @throws SupplierContactNotFoundException
     */
    public function changeMainContact(SupplierContactId $supplierContactId): void
    {
        if (!$this->isActive) {
            throw new SupplierArchivedException();
        }
        if (!key_exists($supplierContactId->value(), $this->contacts)) {
            throw new SupplierContactNotFoundException();
        }
        $contactToPromote = $this->contacts[$supplierContactId->value()];
        $contactToPromote->promote();
        $this->contacts[$this->mainContact->id()->value()] = $this->mainContact;
        $this->mainContact->demote();
        $this->mainContact = $contactToPromote;
    }

    /**
     * @param SupplierContactId $id
     * @param Title $title
     * @param Position $position
     * @param FirstName $firstName
     * @param LastName $lastName
     * @param Phone $phone
     * @param Email $email
     * @param Active $active
     * @throws ContactAlreadyExistsException
     * @throws SupplierContactMainInactiveException
     */
    public function addContact(SupplierContactId $id,
                               Title $title,
                               Position $position,
                               FirstName $firstName,
                               LastName $lastName,
                               Phone $phone,
                               Email $email,
                               Active $active
    )
    {
        $contact = SupplierContact::create(
            $id,
            $title,
            $position,
            $firstName,
            $lastName,
            $phone,
            $email,
            $active,
            new Main(false)
        );
        if (key_exists($id->value(), $this->contacts)) {
            throw new ContactAlreadyExistsException();
        }
        $this->contacts[$id->value()] = $contact;
    }


}