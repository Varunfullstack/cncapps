<?php


namespace CNCLTD;


class PendingTimeRequestWithoutServiceRequestTwigDTO
{
    private $activityId;
    private $submittedDateTime;
    private $reason;
    private $requestingUserName;

    /**
     * PendingTimeRequestWithoutServiceRequestTwigDTO constructor.
     * @param $activityId
     * @param $submittedDateTime
     * @param $reason
     * @param $requestingUserName
     */
    public function __construct($activityId, $submittedDateTime, $reason, $requestingUserName)
    {
        $this->activityId = $activityId;
        $this->submittedDateTime = $submittedDateTime;
        $this->reason = $reason;
        $this->requestingUserName = $requestingUserName;
    }

    /**
     * @return mixed
     */
    public function getActivityId()
    {
        return $this->activityId;
    }

    /**
     * @return mixed
     */
    public function getSubmittedDateTime()
    {
        return $this->submittedDateTime;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return mixed
     */
    public function getRequestingUserName()
    {
        return $this->requestingUserName;
    }
}