<?php


namespace CNCLTD\CustomerValidation;


use Countable;

class ContactValidationErrorCollection implements Countable
{
    private $contactName;
    private $contactId;
    private $errors = [];

    /**
     * ContactValidationErrorCollection constructor.
     * @param $contactName
     * @param $contactId
     */
    public function __construct($contactName, $contactId)
    {
        $this->contactName = $contactName;
        $this->contactId = $contactId;
    }

    /**
     * @param string $error
     */
    public function addError(string $error)
    {
        $this->errors[] = new ValidationError($error);
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !!count($this->errors);
    }

    /**
     * @return mixed
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * @return mixed
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function count()
    {
        return count($this->errors);
    }
}