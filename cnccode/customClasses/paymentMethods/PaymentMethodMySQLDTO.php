<?php

namespace CNCLTD\paymentMethods;
use JsonSerializable;

class PaymentMethodMySQLDTO implements JsonSerializable
{
    private $pay_payno;
    private $pay_desc;
    private $pay_card;
    private $pay_cardno;
    private $pay_exp_date;
    private $pay_consno;
    private $automaticInvoiceFlag;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->pay_payno;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->pay_desc;
    }

    /**
     * @return mixed
     */
    public function getCardType()
    {
        return $this->pay_card;
    }

    /**
     * @return mixed
     */
    public function getCardNumber()
    {
        return $this->pay_cardno;
    }

    /**
     * @return mixed
     */
    public function getCardExpiryDate()
    {
        return $this->pay_exp_date;
    }

    /**
     * @return mixed
     */
    public function getConsultant()
    {
        return $this->pay_consno;
    }

    /**
     * @return mixed
     */
    public function generateAutomaticInvoice(): bool
    {
        return $this->automaticInvoiceFlag === 'Y';
    }


    public function jsonSerialize(): array
    {
        return [
            "id"                       => $this->getId(),
            "description"              => $this->getDescription(),
            "cardType"                 => $this->getCardType(),
            "cardNumber"               => $this->getCardNumber(),
            "cardExpiryDate"           => $this->getCardExpiryDate(),
            "consultantId"             => $this->getConsultant(),
            "generateAutomaticInvoice" => $this->generateAutomaticInvoice(),
        ];
    }
}