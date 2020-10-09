<?php


namespace CNCLTD;


class CustomerOOHCallOutsDB
{

    static function getCustomerOOHCallOutsForCurrentMonth($customerId)
    {
        global $db;
        $statement = $db->preparedQuery(
            "select sum(OOHCallOuts) as result from CustomerOOHCallOuts where customerId = ? and `date` between DATE_FORMAT(NOW(),'%Y-%m-01') and LAST_DAY(NOW()) group by customerId",
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

    static function incrementCustomerOOHCallOut($customerId)
    {
        $dateTime = new \DateTime();
        global $db;
        $statement = $db->preparedQuery(
            'insert into CustomerOOHCallOuts values (?,?,1) on duplicate key update OOHCallOuts = OOHCallOuts + 1',
            [
                [
                    "type"  => "i",
                    "value" => $customerId
                ],
                [
                    "type"  => "s",
                    "value" => $dateTime->format(DATE_MYSQL_DATE)
                ]
            ]
        );
    }
}