<?php


namespace CNCLTD;


use DBEJCallActivity;
use DBEUser;
use Iterator;

class TeamLeaderWithPendingTimeRequestsWithoutServiceRequest implements Iterator
{
    /**
     * @var PendingTimeRequestWithoutServiceRequestTwigDTO[]
     */
    private $pendingRequests = [];
    private $email;

    /**
     * TeamLeaderWithPendingTimeRequestsWithoutServiceRequest constructor.
     * @param $email
     */
    public function __construct($email) { $this->email = $email; }

    public function addPendingTimeRequest(DBEJCallActivity $DBEJCallActivity, DBEUser $requestingUser)
    {
        $this->pendingRequests[] = new PendingTimeRequestWithoutServiceRequestTwigDTO(
            $DBEJCallActivity->getValue(DBEJCallActivity::callActivityID),
            $this->getSubmittedDateTimeFromActivity($DBEJCallActivity),
            $DBEJCallActivity->getValue(DBEJCallActivity::reason),
            $requestingUser->getValue(DBEUser::name)
        );
    }

    private function getSubmittedDateTimeFromActivity(DBEJCallActivity $DBEJCallActivity)
    {
        return $DBEJCallActivity->getValue(DBEJCallActivity::date) . " " . $DBEJCallActivity->getValue(
                DBEJCallActivity::startTime
            ) . ":00";
    }

    public function getLeaderEmail()
    {
        return $this->email;
    }

    function rewind()
    {
        reset($this->pendingRequests);
    }

    function current()
    {
        return current($this->pendingRequests);
    }

    function key()
    {
        return key($this->pendingRequests);
    }

    function next()
    {
        next($this->pendingRequests);
    }

    function valid()
    {
        return key($this->pendingRequests) !== null;
    }
}