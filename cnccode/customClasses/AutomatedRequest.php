<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 11/12/2018
 * Time: 11:49
 */


namespace CNCLTD;


class AutomatedRequest
{
    protected $automatedRequestID;
    protected $customerID;
    protected $serviceRequestID;
    protected $postcode;
    protected $senderEmailAddress;
    protected $textBody;
    protected $htmlBody;
    protected $priority;
    protected $sendEmail;
    protected $serverGuardFlag;
    protected $importedFlag;
    protected $attachment;
    protected $attachmentFilename;
    protected $attachmentMimeType;
    protected $rootCauseID;
    protected $contractCustomerItemID;
    protected $activityCategoryID;
    protected $monitorName;
    protected $monitorAgentName;
    protected $monitorStatus;
    protected $importErrorFound;
    protected $importDateTime;
    protected $createDateTime;
    protected $subjectLine;
    protected $queueNo;

    /**
     * @return mixed
     */
    public function getAutomatedRequestID()
    {
        return $this->automatedRequestID;
    }

    /**
     * @return mixed
     */
    public function getCustomerID()
    {
        if (!$this->customerID) {
            if ($this->serviceRequestID) {
                $dbeProblem = new \DBEProblem($this);

                $dbeProblem->getRow($this->serviceRequestID);
                if ($dbeProblem->rowCount()) {
                    $this->customerID = $dbeProblem->getValue(\DBEProblem::customerID);
                    return $this->customerID;
                }
            }

            if ($this->senderEmailAddress) {
                $dbeContact = new \DBEContact($this);
                $dbeContact->setValue(
                    \DBEContact::email,
                    $this->senderEmailAddress
                );
                $dbeContact->getRowsByColumn(\DBEContact::email);
                if ($dbeContact->rowCount()) {
                    $dbeContact->fetchNext();
                    $this->customerID = $dbeContact->getValue(\DBEContact::customerID);
                    return $this->customerID;
                }
            }

            if ($this->contractCustomerItemID) {
                $dbeItem = new \DBECustomerItem($this);

                $dbeItem->getRow($this->contractCustomerItemID);
                if ($dbeItem->rowCount()) {
                    $this->customerID = $dbeItem->getValue(\DBECustomerItem::customerID);
                    return $this->customerID;
                }

            }

        }

        return $this->customerID;
    }

    /**
     * @return mixed
     */
    public function getServiceRequestID()
    {
        return $this->serviceRequestID;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return mixed
     */
    public function getSenderEmailAddress()
    {
        return $this->senderEmailAddress;
    }

    /**
     * @return mixed
     */
    public function getTextBody()
    {
        return $this->textBody;
    }

    /**
     * @return mixed
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return mixed
     */
    public function getSendEmail()
    {
        return $this->sendEmail;
    }

    /**
     * @return mixed
     */
    public function getServerGuardFlag()
    {
        return $this->serverGuardFlag;
    }

    /**
     * @return mixed
     */
    public function getImportedFlag()
    {
        return $this->importedFlag;
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @return mixed
     */
    public function getAttachmentFilename()
    {
        return $this->attachmentFilename;
    }

    /**
     * @return mixed
     */
    public function getAttachmentMimeType()
    {
        return $this->attachmentMimeType;
    }

    /**
     * @return mixed
     */
    public function getRootCauseID()
    {
        return $this->rootCauseID;
    }

    /**
     * @return mixed
     */
    public function getContractCustomerItemID()
    {
        return $this->contractCustomerItemID;
    }

    /**
     * @return mixed
     */
    public function getActivityCategoryID()
    {
        return $this->activityCategoryID;
    }

    /**
     * @return mixed
     */
    public function getMonitorName()
    {
        return $this->monitorName;
    }

    /**
     * @return mixed
     */
    public function getMonitorAgentName()
    {
        return $this->monitorAgentName;
    }

    /**
     * @return mixed
     */
    public function getMonitorStatus()
    {
        return $this->monitorStatus;
    }

    /**
     * @return mixed
     */
    public function getImportErrorFound()
    {
        return $this->importErrorFound;
    }

    /**
     * @return mixed
     */
    public function getImportDateTime()
    {
        return $this->importDateTime;
    }

    /**
     * @return mixed
     */
    public function getCreateDateTime()
    {
        return $this->createDateTime;
    }

    /**
     * @return mixed
     */
    public function getSubjectLine()
    {
        return $this->subjectLine;
    }

    /**
     * @return mixed
     */
    public function getQueueNo()
    {
        return $this->queueNo;
    }

}