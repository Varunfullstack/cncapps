<?php


namespace CNCLTD\CustomerValidation;


use DBEContact;

class ContactValidation
{

    private $contactId;
    /**
     * @var DBEContact
     */
    private $dbeContact;
    private $validationErrors;

    public function __construct($contactId)
    {

        $this->contactId = $contactId;
        $this->dbeContact = new DBEContact($this);
        $this->dbeContact->getRow($contactId);
        $this->validationErrors = $this->runValidation();
    }

    /**
     * @return ContactValidationErrorCollection
     */
    private function runValidation()
    {
        $errorCollection = new ContactValidationErrorCollection($this->getContactName(), $this->contactId);
        if (!$this->dbeContact->getValue(DBEContact::firstName)) {
            $errorCollection->addError("First Name Required");
        }

        if (!$this->dbeContact->getValue(DBEContact::lastName)) {
            $errorCollection->addError("Last Name Required");
        }

        if (!$this->dbeContact->getValue(DBEContact::title)) {
            $errorCollection->addError("Title Required");
        }

        if ($email = $this->dbeContact->getValue(DBEContact::email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorCollection->addError("Invalid Email");
            } else {
                $testContact = new DBEContact($this);
                if (!$testContact->validateUniqueEmail($email, $this->contactId)) {
                    $errorCollection->addError("Duplicated Email");
                }
            }
        }

        if ($this->dbeContact->getValue(DBEContact::phone) && !preg_match(
                "/^\d+$/",
                $this->dbeContact->getValue(DBEContact::phone)
            )) {
            $errorCollection->addError("Invalid Phone Number: " . $this->dbeContact->getValue(DBEContact::phone));
        }

        if ($this->dbeContact->getValue(DBEContact::mobilePhone) && !preg_match(
                "/^\d+$/",
                $this->dbeContact->getValue(DBEContact::mobilePhone)
            )) {
            $errorCollection->addError(
                "Invalid Mobile Phone Number: " . $this->dbeContact->getValue(DBEContact::mobilePhone)
            );
        }
        return $errorCollection;
    }

    public function getContactName()
    {
        return $this->dbeContact->getValue(DBEContact::firstName) . " " . $this->dbeContact->getValue(
                DBEContact::lastName
            );
    }

    /**
     * @return ValidationError[]
     */
    public function getValidationErrors()
    {
        return $this->validationErrors->getErrors();
    }

    public function hasErrors()
    {
        return $this->validationErrors->hasErrors();
    }
}