<?php


namespace CNCLTD\CustomerValidation;


use Countable;

class SiteValidationErrorCollection implements Countable
{

    private $postCode;
    private $contactId;
    private $errors = [];

    /**
     * ContactValidationErrorCollection constructor.
     * @param $postCode
     */
    public function __construct($postCode)
    {
        $this->postCode = $postCode;
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
    public function getPostCode()
    {
        return $this->postCode;
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