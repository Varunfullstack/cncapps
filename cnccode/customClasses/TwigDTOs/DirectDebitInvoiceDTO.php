<?php


namespace CNCLTD\TwigDTOs;


class DirectDebitInvoiceDTO
{
    /**
     * @var string
     */
    private $contactName;
    /**
     * @var array|bool|float|int|string|null
     */
    private $companyName;
    /**
     * @var array|bool|float|int|string|null
     */
    private $addressLine1;
    /**
     * @var array|bool|float|int|string|null
     */
    private $town;
    /**
     * @var array|bool|float|int|string|null
     */
    private $county;
    /**
     * @var array|bool|float|int|string|null
     */
    private $postCode;
    /**
     * @var string
     */
    private $date;
    /**
     * @var array|bool|float|int|string|null
     */
    private $invoiceNo;
    /**
     * @var string
     */
    private $paymentDate;
    /**
     * @var float|int|string
     */
    private $totalAmount;

    /**
     * DirectDebitInvoiceDTO constructor.
     * @param string $contactName
     * @param array|bool|float|int|string|null $companyName
     * @param array|bool|float|int|string|null $addressLine1
     * @param array|bool|float|int|string|null $town
     * @param array|bool|float|int|string|null $county
     * @param array|bool|float|int|string|null $postCode
     * @param string $date
     * @param array|bool|float|int|string|null $invoiceNo
     * @param string $paymentDate
     * @param float|int|string $totalAmount
     */
    public function __construct(string $contactName,
                                $companyName,
                                $addressLine1,
                                $town,
                                $county,
                                $postCode,
                                string $date,
                                $invoiceNo,
                                string $paymentDate,
                                $totalAmount
    )
    {
        $this->contactName = $contactName;
        $this->companyName = $companyName;
        $this->addressLine1 = $addressLine1;
        $this->town = $town;
        $this->county = $county;
        $this->postCode = $postCode;
        $this->date = $date;
        $this->invoiceNo = $invoiceNo;
        $this->paymentDate = $paymentDate;
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return string
     */
    public function getContactName(): string
    {
        return $this->contactName;
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }


    /**
     * @return array|bool|float|int|string|null
     */
    public function getInvoiceNo()
    {
        return $this->invoiceNo;
    }

    /**
     * @return string
     */
    public function getPaymentDate(): string
    {
        return $this->paymentDate;
    }

    /**
     * @return float|int|string
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }
}