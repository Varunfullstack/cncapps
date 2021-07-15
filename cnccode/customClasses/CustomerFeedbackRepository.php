<?php


namespace CNCLTD;


use dbSweetcode;

class CustomerFeedbackRepository
{
    /**
     * @var dbSweetcode
     */
    private $db;

    public function __construct(dbSweetcode $db)
    {

        $this->db = $db;
    }

    public function persistCustomerFeedback(CustomerFeedback $customerFeedback)
    {
        $this->db->preparedQuery(
            "insert into customerFeedback(serviceRequestId, contactId, `value`, comments ) values (?,?,?,?)",
            [
                [
                    "type"  => "i",
                    "value" => $customerFeedback->serviceRequestId
                ],
                [
                    "type"  => "i",
                    "value" => $customerFeedback->contactId
                ],
                [
                    "type"  => "i",
                    "value" => $customerFeedback->value
                ],
                [
                    "type"  => "s",
                    "value" => $customerFeedback->comments
                ],
            ]
        );
    }
}