<?php

namespace CNCLTD\ServiceRequestInternalNote;
class ServiceRequestInternalNote
{
    private $id;
    private $serviceRequestId;
    private $createdBy;
    private $createdAt;
    private $updatedBy;
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
    private function __construct($id, $serviceRequestId, $createdBy, $createdAt, $updatedBy, $updatedAt, $content)
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
                                  \DateTimeImmutable $createdAt,
                                  $updatedBy,
                                  \DateTimeImmutable $updatedAt,
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
     * @return mixed
     */
    public function getCreatedAt()
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
     * @return mixed
     */
    public function getUpdatedAt()
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

}