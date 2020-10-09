<?php


namespace CNCLTD;


class CustomerCallOutsDB
{

    static function getCustomerOutOfHoursUsedCallOutsForCurrentMonth($customerId)
    {
        global $db;
        $statement = $db->preparedQuery(
            "select count(*) as result from CustomerCallOuts where customerId = ? and createdAt between DATE_FORMAT(NOW(),'%Y-%m-01') and LAST_DAY(NOW()) and chargeable group by customerId",
            [
                [
                    "type"  => "i",
                    "value" => $customerId
                ]
            ]
        );
        $row = $statement->fetch_array(MYSQLI_ASSOC);
        if (!$row) {
            return 0;
        }
        return $row['result'];
    }

    static function recordCallOut($customerId, $chargeable, $salesOrderId)
    {
        global $db;
        $statement = $db->preparedQuery(
            'insert into CustomerCallOuts(customerId,chargeable, salesOrderHeaderId) values (?,?,?)',
            [
                [
                    "type"  => "i",
                    "value" => $customerId
                ],
                [
                    "type"  => "i",
                    "value" => $chargeable
                ],
                [
                    "type"  => "i",
                    "value" => $salesOrderId
                ],
            ]
        );
    }
}