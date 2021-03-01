<?php

namespace CNCLTD\Supplier\Domain\SupplierContact;

use CNCLTD\Exceptions\SupplierContactArchivedException;
use CNCLTD\Exceptions\SupplierContactMainInactiveException;

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
     * @var Main
     */
    private $main;

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
                                 Active $active,
                                 Main $main
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
        $this->main      = $main;
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
     * @param Main $main
     * @throws SupplierContactMainInactiveException
     */
    public static function create(SupplierContactId $id,
                                  Title $title,
                                  Position $position,
                                  FirstName $firstName,
                                  LastName $lastName,
                                  Phone $phone,
                                  Email $email,
                                  Active $active,
                                  Main $main
    ): SupplierContact
    {
        if ($main->value() && !$active->value()) {
            throw new SupplierContactMainInactiveException();
        }
        return new self(
            $id, $title, $position, $firstName, $lastName, $phone, $email, $active, $main
        );
    }

    public function id(): SupplierContactId
    {
        return $this->id;
    }

    /**
     * @throws SupplierContactArchivedException
     */
    public function demote()
    {
        $this->checkArchived();
        $this->main = new Main(false);
    }

    /**
     * @throws SupplierContactArchivedException
     */
    public function promote()
    {
        $this->checkArchived();
        $this->main = new Main(true);
    }

    /**
     * @throws SupplierContactArchivedException
     */
    private function checkArchived(): void
    {
        if (!$this->active->value()) {
            throw new SupplierContactArchivedException();
        }
    }

}