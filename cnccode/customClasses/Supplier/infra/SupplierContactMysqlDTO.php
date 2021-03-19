<?php

namespace CNCLTD\Supplier\infra;
class SupplierContactMysqlDTO
{
    private $id;
    private $supplierId;
    private $title;
    private $position;
    private $firstName;
    private $lastName;
    private $email;
    private $phone;
    private $active;
    private $isMain;

    /**
     * @return mixed
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSupplierId(): int
    {
        return $this->supplierId;
    }

    /**
     * @return mixed
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getPosition(): ?string
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return (bool)$this->active;
    }

    /**
     * @return bool
     */
    public function getIsMain(): bool
    {
        return (bool)$this->isMain;
    }

}