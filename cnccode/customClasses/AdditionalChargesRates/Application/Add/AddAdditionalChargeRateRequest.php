<?php

namespace CNCLTD\AdditionalChargesRates\Application\Add;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class AddAdditionalChargeRateRequest
{
    private $jsonData;

    /**
     * AddAdditionalChargeRateRequest constructor.
     */
    public function __construct($jsonData)
    {
        $this->jsonData = $jsonData;
    }

    public function validate(): ConstraintViolationListInterface
    {

        $constraint = new Assert\Collection(
            [
                'description'            => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 100])],
                'salePrice'              => [new Assert\Type('float')],
                'specificCustomerPrices' => new Assert\Optional(
                    [
                        new Assert\Type('array'),
                        new Assert\All(
                            [
                                new Assert\Collection(
                                    [
                                        'customerId' => [
                                            new Assert\NotBlank(),
                                            new Assert\Type('integer')
                                        ],
                                        'salePrice'  => [new Assert\Type('float'),],
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ]
        );
        $validator  = Validation::createValidator();
        return $validator->validate($this->jsonData, $constraint);
    }

    public function description()
    {
        return $this->getDataField('description');
    }

    public function notes()
    {
        return $this->getDataField('notes');
    }

    private function getDataField($field)
    {
        return @$this->jsonData[$field];
    }

    public function salePrice()
    {
        return $this->getDataField('salePrice');
    }

    public function specificCustomerPrices()
    {
        return $this->getDataField('specificCustomerPrices');
    }
}