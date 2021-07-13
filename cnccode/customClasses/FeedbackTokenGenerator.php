<?php


namespace CNCLTD;


use dbSweetcode;
use Exception;

class FeedbackTokenGenerator
{
    private $db;

    /**
     * FeedbackTokenGenerator constructor.
     * @param dbSweetcode $db
     */
    public function __construct(dbSweetcode $db)
    {

        $this->db = $db;
    }

    public function getTokenForServiceRequestId($serviceRequestId)
    {
        $uniqueValue = str_replace(".", "", uniqid("", true));
        $this->db->preparedQuery(
            'insert into feedbackToken(token,serviceRequestId) values (?,?)',
            [
                [
                    "type"  => "s",
                    "value" => $uniqueValue
                ],
                [
                    "type"  => "s",
                    "value" => $serviceRequestId
                ],
            ]
        );
        return $uniqueValue;
    }

    /**
     * @param $token
     * @return TokenData|null
     * @throws Exception
     */
    public function getTokenData($token): ?TokenData
    {
        $statement = $this->db->preparedQuery(
            'select * from feedbackToken where token = ?',
            [["type" => "s", "value" => $token]]
        );
        $row = $statement->fetch_array(MYSQLI_ASSOC);
        if (!$row) {
            return null;
        }
        return TokenData::fromDB($row);
    }

    public function invalidateToken($token)
    {
        $this->db->preparedQuery('delete from feedbackToken where token = ?', [["type" => "s", "value" => $token]]);
    }
}