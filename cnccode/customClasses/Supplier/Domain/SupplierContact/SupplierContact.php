<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;
class SupplierContact
{

    /**
     * @var SupplierContactId
     */
    private $id;
    /**
     * @var Title
     */
    private $title;
    /**
     * @var Position
     */
    private $position;
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
     * @var Email
     */
    private $email;
    /**
     * @var Active
     */
    private $active;

    /**
     * SupplierContact constructor.
     * @param $id
     * @param $title
     * @param $position
     * @param $firstName
     * @param $lastName
     * @param $phone
     * @param $email
     */
    private function __construct(SupplierContactId $id,
                                 Title $title,
                                 Position $position,
                                 FirstName $firstName,
                                 LastName $lastName,
                                 Phone $phone,
                                 Email $email,
                                 Active $active
    )
    {
        $this->id        = $id;
        $this->title     = $title;
        $this->position  = $position;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->phone     = $phone;
        $this->email     = $email;
        $this->active    = $active;
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
     */
    public static function create(SupplierContactId $id,
                                  Title $title,
                                  Position $position,
                                  FirstName $firstName,
                                  LastName $lastName,
                                  Phone $phone,
                                  Email $email,
                                  Active $active
    ): SupplierContact
    {
        return new self(
            $id, $title, $position, $firstName, $lastName, $phone, $email, $active
        );
    }

    public function id(): SupplierContactId
    {
        return $this->id;
    }

    /**
     * @return SupplierContactId
     */
    public function getId(): SupplierContactId
    {
        return $this->id;
    }

    /**
     * @return Title
     */
    public function getTitle(): Title
    {
        return $this->title;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
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
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return Active
     */
    public function getActive(): Active
    {
        return $this->active;
    }

    public function reactivate()
    {
        $this->active = new Active(true);
    }

    public function archive()
    {
        $this->active = new Active(false);
    }

    public function fullName(): string
    {
        return "{$this->firstName->value()} {$this->lastName->value()}";
    }
}