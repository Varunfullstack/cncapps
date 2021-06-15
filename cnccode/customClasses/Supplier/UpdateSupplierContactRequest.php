<?php

namespace CNCLTD\Supplier;

use CNCLTD\Supplier\Domain\SupplierContact\Active;
use CNCLTD\Supplier\Domain\SupplierContact\Email;
use CNCLTD\Supplier\Domain\SupplierContact\FirstName;
use CNCLTD\Supplier\Domain\SupplierContact\LastName;
use CNCLTD\Supplier\Domain\SupplierContact\Phone;
use CNCLTD\Supplier\Domain\SupplierContact\Position;
use CNCLTD\Supplier\Domain\SupplierContact\SupplierContactId;
use CNCLTD\Supplier\Domain\SupplierContact\Title;

class UpdateSupplierContactRequest
{
    /**
     * @var SupplierId
     */
    private $supplierId;
    /**
     * @var SupplierContactId
     */
    private $contactId;
    /**
     * @var Email
     */
    private $email;
    /**
     * @var FirstName
     */
    private $firstName;
    /**
     * @var LastName
     */
    private $lastName;
    /**
     * @var Phone
     */
    private $phone;
    /**
     * @var Position
     */
    private $position;
    /**
     * @var Title
     */
    private $title;
    /**
     * @var Active
     */
    private $active;


    /**
     * CreateSupplierRequest constructor.
     * @param SupplierId $supplierId
     * @param SupplierContactId $contactId
     * @param Email $email
     * @param FirstName $firstName
     * @param LastName $lastName
     * @param Phone $phone
     * @param Position $position
     * @param Title $title
     * @param Active $active
     */
    public function __construct(SupplierId $supplierId,
                                SupplierContactId $contactId,
                                Email $email,
                                FirstName $firstName,
                                LastName $lastName,
                                Phone $phone,
                                Position $position,
                                Title $title,
                                Active $active
    )
    {

        $this->supplierId = $supplierId;
        $this->contactId  = $contactId;
        $this->email      = $email;
        $this->firstName  = $firstName;
        $this->lastName   = $lastName;
        $this->phone      = $phone;
        $this->position   = $position;
        $this->title      = $title;
        $this->active     = $active;
    }

    public static function fromJSONArray($array): self
    {

        $supplierId   = new SupplierId(@$array['supplierId']);
        $contactId    = new SupplierContactId(@$array['id']);
        $email        = new Email(@$array['email']);
        $firstName    = new FirstName(@$array['firstName']);
        $lastName     = new LastName(@$array['lastName']);
        $contactPhone = new Phone(@$array['phone']);
        $position     = new Position(@$array['position']);
        $title        = new Title(@$array['title']);
        $active       = new Active(@$array['active']);
        return new self(
            $supplierId, $contactId, $email, $firstName, $lastName, $contactPhone, $position, $title, $active
        );

    }

    /**
     * @return SupplierId
     */
    public function getSupplierId(): SupplierId
    {
        return $this->supplierId;
    }

    /**
     * @return SupplierContactId
     */
    public function getContactId(): SupplierContactId
    {
        return $this->contactId;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return FirstName
     */
    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }

    /**
     * @return LastName
     */
    public function getLastName(): LastName
    {
        return $this->lastName;
    }

    /**
     * @return Phone
     */
    public function getPhone(): Phone
    {
        return $this->phone;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @return Title
     */
    public function getTitle(): Title
    {
        return $this->title;
    }

    /**
     * @return Active
     */
    public function getActive(): Active
    {
        return $this->active;
    }
}
