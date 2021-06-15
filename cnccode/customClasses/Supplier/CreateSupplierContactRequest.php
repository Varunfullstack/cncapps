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

class CreateSupplierContactRequest
{
    /**
     * @var SupplierId
     */
    private $supplierId;
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
                                Email $email,
                                FirstName $firstName,
                                LastName $lastName,
                                Phone $phone,
                                Position $position,
                                Title $title
    )
    {

        $this->supplierId = $supplierId;
        $this->email      = $email;
        $this->firstName  = $firstName;
        $this->lastName   = $lastName;
        $this->phone      = $phone;
        $this->position   = $position;
        $this->title      = $title;
    }

    public static function fromJSONArray($array): self
    {

        $supplierId   = new SupplierId(@$array['supplierId']);
        $email        = new Email(@$array['email']);
        $firstName    = new FirstName(@$array['firstName']);
        $lastName     = new LastName(@$array['lastName']);
        $contactPhone = new Phone(@$array['phone']);
        $position     = new Position(@$array['position']);
        $title        = new Title(@$array['title']);
        return new self($supplierId, $email, $firstName, $lastName, $contactPhone, $position, $title);

    }

    /**
     * @return SupplierId
     */
    public function getSupplierId(): SupplierId
    {
        return $this->supplierId;
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
}
