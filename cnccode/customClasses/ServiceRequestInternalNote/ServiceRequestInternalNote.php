<?php

namespace CNCLTD\ServiceRequestInternalNote;

use DateTimeImmutable;

class ServiceRequestInternalNote
{
    private $id;
    private $serviceRequestId;
    private $createdBy;
    /** @var DateTimeImmutable $createdAt */
    private $createdAt;
    private $updatedBy;
    /** @var DateTimeImmutable $updatedAt */
    private $updatedAt;
    private $content;

    /**
     * ServiceRequestInternalNote constructor.
     * @param $id
     * @param $serviceRequestId
     * @param $createdBy
     * @param $createdAt
     * @param $updatedBy
     * @param $updatedAt
     * @param $content
     */
    private function __construct($id,
                                 $serviceRequestId,
                                 $createdBy,
                                 DateTimeImmutable $createdAt,
                                 $updatedBy,
                                 DateTimeImmutable $updatedAt,
                                 $content
    )
    {

        $this->id               = $id;
        $this->serviceRequestId = $serviceRequestId;
        $this->createdBy        = $createdBy;
        $this->createdAt        = $createdAt;
        $this->updatedBy        = $updatedBy;
        $this->updatedAt        = $updatedAt;
        $this->content          = $content;
    }

    public static function create($id,
                                  $serviceRequestId,
                                  $createdBy,
                                  DateTimeImmutable $createdAt,
                                  $updatedBy,
                                  DateTimeImmutable $updatedAt,
                                  $comment
    ): ServiceRequestInternalNote
    {
        return new self($id, $serviceRequestId, $createdBy, $createdAt, $updatedBy, $updatedAt, $comment);
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
    public function getServiceRequestId()
    {
        return $this->serviceRequestId;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    public function updateContent($content, \DBEUser $currentUser)
    {
        $this->content   = $content;
        $this->updatedBy = $currentUser->getValue(\DBEUser::userID);
        $this->updatedAt = new DateTimeImmutable();
    }

}