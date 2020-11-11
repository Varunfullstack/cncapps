<?php


namespace CNCLTD\TwigDTOs;


class InvoiceItemDTO
{

    private $id;
    private $amount;

    /**
     * InvoiceItemDTO constructor.
     * @param $id
     * @param $amount
     */
    public function __construct($id, $amount)
    {

        $this->id = $id;
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

}