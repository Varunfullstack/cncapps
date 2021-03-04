<?php

namespace CNCLTD\ServiceRequestInternalNote;
class ServiceRequestInternalNotePDODTO
{
    private $id;
    private $serviceRequestId;
    private $createdBy;
    private $createdAt;
    private $updatedBy;
    private $updatedAt;
    private $content;

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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

}