<?php


namespace CNCLTD;


use DBEJCallActivity;
use DBEUser;
use Iterator;

class PendingTimeRequestsWithoutServiceRequestCollection implements Iterator
{

    private $leadersMap = [];

    public function add(DBEUser $leader, DBEJCallActivity $timeRequestWithoutSR, DBEUser $requestingUser)
    {
        if (!isset($this->leadersMap[$leader->getValue(DBEUser::userID)])) {
            $this->leadersMap[$leader->getValue(
                DBEUser::userID
            )] = new TeamLeaderWithPendingTimeRequestsWithoutServiceRequest(
                $this->getEmailFromUser($leader)
            );
        }
        $this->leadersMap[$leader->getValue(
            DBEUser::userID
        )]->addPendingTimeRequest($timeRequestWithoutSR, $requestingUser);
    }

    private function getEmailFromUser(DBEUser $DBEUser)
    {
        return $DBEUser->getValue(DBEUser::username) . "@" . CONFIG_PUBLIC_DOMAIN;
    }

    function rewind()
    {
        reset($this->leadersMap);
    }

    /**
     * @return TeamLeaderWithPendingTimeRequestsWithoutServiceRequest
     */
    function current()
    {
        return current($this->leadersMap);
    }

    /**
     * @return int
     */
    function key()
    {
        return key($this->leadersMap);
    }

    function next()
    {
        next($this->leadersMap);
    }

    function valid()
    {
        return key($this->leadersMap) !== null;
    }
}