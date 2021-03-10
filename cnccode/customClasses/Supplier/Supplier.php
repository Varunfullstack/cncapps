<?php

namespace CNCLTD\Supplier;

use CNCLTD\Exceptions\ContactAlreadyExistsException;
use CNCLTD\Exceptions\SupplierArchivedException;
use CNCLTD\Exceptions\SupplierContactAlreadyActiveException;
use CNCLTD\Exceptions\SupplierContactAlreadyArchivedException;
use CNCLTD\Exceptions\SupplierContactArchivedException;
use CNCLTD\Exceptions\SupplierContactCannotArchiveMain;
use CNCLTD\Exceptions\SupplierContactNotFoundException;
use CNCLTD\Supplier\Domain\SupplierContact\Active;
use CNCLTD\Supplier\Domain\SupplierContact\Email;
use CNCLTD\Supplier\Domain\SupplierContact\FirstName;
use CNCLTD\Supplier\Domain\SupplierContact\LastName;
use CNCLTD\Supplier\Domain\SupplierContact\Phone;
use CNCLTD\Supplier\Domain\SupplierContact\Position;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContact;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\Domain\SupplierContact\Title;

class Supplier
{
    /**
     * @var SupplierContact
     */
    private $mainContact;
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
     * @var SupplierIsActive
     */
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

    private $supplierDirty = false;
    private $contactDirty  = [];


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
                                 SupplierWebsiteURL $websiteURL,
                                 SupplierPaymentMethodId $paymentMethodId,
                                 SupplierAccountCode $accountCode,
                                 SupplierIsActive $isActive,
                                 SupplierContact $mainContact
    )
    {
        $this->mainContact                              = $mainContact;
        $this->id                                       = $id;
        $this->name                                     = $name;
        $this->address1                                 = $address1;
        $this->address2                                 = $address2;
        $this->town                                     = $town;
        $this->postcode                                 = $postcode;
        $this->websiteURL                               = $websiteURL;
        $this->isActive                                 = $isActive;
        $this->paymentMethodId                          = $paymentMethodId;
        $this->accountCode                              = $accountCode;
        $this->county                                   = $county;
        $this->contacts[$mainContact->getId()->value()] = $mainContact;
    }

    public static function create(SupplierId $id,
                                  SupplierName $name,
                                  SupplierAddress1 $address1,
                                  SupplierAddress2 $address2,
                                  SupplierTown $town,
                                  SupplierCounty $county,
                                  SupplierPostcode $postcode,
                                  SupplierWebsiteURL $websiteURL,
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
    ): Supplier
    {

        $mainContact = SupplierContact::create(
            $supplierContactId,
            $supplierContactTitle,
            $supplierContactPosition,
            $supplierContactFirstName,
            $supplierContactLastName,
            $supplierContactPhone,
            $supplierContactEmail,
            new Active(true)
        );
        return new self(
            $id,
            $name,
            $address1,
            $address2,
            $town,
            $county,
            $postcode,
            $websiteURL,
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
        if (!$contactToPromote->getActive()) {
            throw new SupplierContactArchivedException();
        }
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
        );
        if (key_exists($id->value(), $this->contacts)) {
            throw new ContactAlreadyExistsException();
        }
        $this->contacts[$id->value()] = $contact;
    }

    public function mainContact(): SupplierContact
    {
        return $this->mainContact;
    }

    public function town(): SupplierTown
    {
        return $this->town;
    }

    /**
     * @return SupplierCounty
     */
    public function county(): SupplierCounty
    {
        return $this->county;
    }

    /**
     * @return SupplierPostcode
     */
    public function postcode(): SupplierPostcode
    {
        return $this->postcode;
    }

    /**
     * @return mixed
     */
    public function isActive(): SupplierIsActive
    {
        return $this->isActive;
    }

    /**
     * @return SupplierId
     */
    public function id(): SupplierId
    {
        return $this->id;
    }

    /**
     * @return SupplierName
     */
    public function name(): SupplierName
    {
        return $this->name;
    }

    /**
     * @return SupplierAddress1
     */
    public function address1(): SupplierAddress1
    {
        return $this->address1;
    }

    /**
     * @return SupplierAddress2
     */
    public function address2(): SupplierAddress2
    {
        return $this->address2;
    }

    /**
     * @return SupplierWebsiteURL
     */
    public function websiteURL(): SupplierWebsiteURL
    {
        return $this->websiteURL;
    }

    /**
     * @return SupplierPaymentMethodId
     */
    public function paymentMethodId(): SupplierPaymentMethodId
    {
        return $this->paymentMethodId;
    }

    /**
     * @return SupplierAccountCode
     */
    public function accountCode(): SupplierAccountCode
    {
        return $this->accountCode;
    }

    /**
     * @return SupplierContact[]
     */
    public function getContacts(): array
    {
        return array_values($this->contacts);
    }

    /**
     * @param UpdateSupplierRequest $request
     * @throws SupplierArchivedException
     * @throws SupplierContactArchivedException
     * @throws SupplierContactNotFoundException
     */
    public function updateFromRequest(UpdateSupplierRequest $request)
    {
        $this->name            = $request->getName();
        $this->address1        = $request->getAddress1();
        $this->address2        = $request->getAddress2();
        $this->town            = $request->getTown();
        $this->postcode        = $request->getPostcode();
        $this->websiteURL      = $request->getWebsiteURL();
        $this->paymentMethodId = $request->getPaymentMethodId();
        $this->accountCode     = $request->getAccountCode();
        $this->county          = $request->getCounty();
        if ($request->getMainSupplierContactId()->value() !== $this->mainContact->id()->value()) {
            $this->changeMainContact($request->getMainSupplierContactId());
        }
    }

    /**
     * @param UpdateSupplierContactRequest $request
     * @throws SupplierContactCannotArchiveMain
     * @throws SupplierContactNotFoundException
     */
    public function updateSupplierContactFromRequest(UpdateSupplierContactRequest $request)
    {
        $foundContact = @$this->contacts[$request->getContactId()->value()];
        $newContact   = SupplierContact::create(
            $request->getContactId(),
            $request->getTitle(),
            $request->getPosition(),
            $request->getFirstName(),
            $request->getLastName(),
            $request->getPhone(),
            $request->getEmail(),
            $request->getActive()
        );
        if (!$foundContact) {
            throw new SupplierContactNotFoundException();
        }
        if (!$this->hasChanges($this->contacts[$request->getContactId()->value()], $newContact)) {
            return;
        }
        if (!$this->mainContact->id()->isSame($request->getContactId())) {
            // we are updating the main supplier contact
            if ($request->getActive()->value() === false) {
                throw new SupplierContactCannotArchiveMain();
            }
            $this->mainContact = $newContact;
        }
        $this->contacts[$request->getContactId()->value()]     = $newContact;
        $this->contactDirty[$request->getContactId()->value()] = true;

    }

    public function archive()
    {
        $this->isActive = new SupplierIsActive(false);
    }

    public function reactivate()
    {
        $this->isActive = new SupplierIsActive(true);
    }

    public function reactivateSupplierContact(SupplierContactId $supplierContactId)
    {
        $contact = $this->contacts[$supplierContactId->value()];
        if (!$contact) {
            throw new SupplierContactNotFoundException();
        }
        if ($contact->getActive()->value()) {
            throw new SupplierContactAlreadyActiveException();
        }
        $contact->reactivate();
        $this->contactDirty[$supplierContactId->value()] = true;
    }

    public function archiveSupplierContact(SupplierContactId $supplierContactId)
    {
        $contact = $this->contacts[$supplierContactId->value()];
        if (!$contact) {
            throw new SupplierContactNotFoundException();
        }
        if (!$contact->getActive()->value()) {
            throw new SupplierContactAlreadyArchivedException();
        }
        $this->contactDirty[$supplierContactId->value()] = true;
    }

    /**
     * @return array
     */
    public function getContactDirty(): array
    {
        return $this->contactDirty;
    }

    private function hasChanges(SupplierContact $supplierContact, SupplierContact $newContact): bool
    {
        $hasChangeTitle     = !$supplierContact->getTitle()->isSame($newContact->getTitle());
        $hasChangePosition  = !$supplierContact->getPosition()->isSame($newContact->getPosition());
        $hasChangeFirstName = !$supplierContact->getFirstName()->isSame($newContact->getFirstName());
        $hasChangeLastName  = !$supplierContact->getLastName()->isSame($newContact->getLastName());
        $hasChangePhone     = !$supplierContact->getPhone()->isSame($newContact->getPhone());
        $hasChangeEmail     = !$supplierContact->getEmail()->isSame($newContact->getEmail());
        $hasChangeActive    = !$supplierContact->getActive()->isSame($newContact->getActive());
        return $hasChangeTitle || $hasChangePosition || $hasChangeFirstName || $hasChangeLastName || $hasChangePhone || $hasChangeEmail || $hasChangeActive;
    }

}