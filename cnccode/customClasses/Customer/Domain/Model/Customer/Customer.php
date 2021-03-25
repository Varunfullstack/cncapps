<?php

namespace CNCLTD\Customer\Domain\Model\Customer;
use CNCLTD\Shared\Domain\Model\AggregateRoot;

class Customer implements AggregateRoot
{
    /** @var CustomerId */
    protected $id;
    /** @var CustomerName */
    protected $name;

    /** @var CustomerRegistrationCode */
    protected $registrationCode;
    /** @var CustomerInvoiceSiteId */
    protected $invoiceSiteId;
    /** @var CustomerInvoiceSiteId */
    protected $deliverSiteId;
    /** @var CustomerMailshotAllowed */
    protected $mailshotAllowed;
    /** @var \DateTimeImmutable */
    protected $createDate;
    /** @var CustomerIsReferred */
    protected $isReferred;
    /** @var CustomerTypeId */
    protected $customerTypeID;
    /** @var  CustomerTopUpAmount */
    protected $topUpAmount;
    /** @var CustomerLastModifiedDateTime */
    protected $lastModifiedDateTime;
    /** @var EngineerId */
    protected $modifyUserID;
    /** @var CustomerNumberOfPCs */
    protected $noOfPCs;
    /** @var CustomerNumberOfServers */
    protected $noOfServers;
    /** @var CustomerComments */
    protected $comments;
    /** @var CustomerReviewDate */
    protected $reviewDate;
    /** @var CustomerReviewTime */
    protected $reviewTime;
    /** @var  */
    protected $reviewAction;
    protected $reviewUserID;
    protected $sectorID;
    protected $becameCustomerDate;
    protected $droppedCustomerDate;
    protected $leadStatusId;
    protected $techNotes;
    protected $specialAttentionFlag;
    protected $specialAttentionEndDate;
    protected $support24HourFlag;
    protected $slaP1;
    protected $slaP2;
    protected $slaP3;
    protected $slaP4;
    protected $slaP5;
    protected $sendContractEmail;
    protected $sendTandcEmail;
    protected $lastReviewMeetingDate;
    protected $reviewMeetingFrequencyMonths;
    protected $accountManagerUserID;
    protected $reviewMeetingEmailSentFlag;
    protected $dateMeetingConfirmed;
    protected $meetingDateTime;
    protected $inviteSent;
    protected $reportProcessed;
    protected $reportSent;
    protected $crmComments;
    protected $companyBackground;
    protected $decisionMakerBackground;
    protected $opportunityDeal;
    protected $rating;
    protected $lastContractSent;
    protected $primaryMainContactID;
    protected $sortCode;
    protected $accountName;
    protected $accountNumber;
    protected $activeDirectoryName;
    protected $reviewMeetingBooked;
    protected $licensedOffice365Users;
    protected $websiteURL;
    protected $slaFixHoursP1;
    protected $slaFixHoursP2;
    protected $slaFixHoursP3;
    protected $slaFixHoursP4;
    protected $slaP1PenaltiesAgreed;
    protected $slaP2PenaltiesAgreed;
    protected $slaP3PenaltiesAgreed;
    protected $streamOneEmail;
    protected $lastUpdatedDateTime;
    protected $inclusiveOOHCallOuts;
    protected $eligiblePatchManagement;

    protected $statementContactId;

}