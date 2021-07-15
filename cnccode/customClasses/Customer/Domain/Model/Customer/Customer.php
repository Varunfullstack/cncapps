<?php

namespace CNCLTD\Customer\Domain\Model\Customer;
use CNCLTD\Shared\Domain\Model\AggregateRoot;
use DateTimeImmutable;

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
    /** @var DateTimeImmutable */
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
    /** @var  CustomerReviewAction */
    protected $reviewAction;
    /** @var  CustomerReviewUserId */
    protected $reviewUserID;
    /** @var  CustomerSectorId */
    protected $sectorID;
    /** @var  CustomerBecameCustomerDate */
    protected $becameCustomerDate;
    /** @var  CustomerDroppedDate */
    protected $droppedCustomerDate;
    /** @var  CustomerLeadStatusId */
    protected $leadStatusId;
    /** @var  CustomerTechNotes */
    protected $techNotes;
    /** @var  CustomerIsSpecialAttention */
    protected $specialAttentionFlag;
    /** @var  CustomerSpecialAttentionUntilDate */
    protected $specialAttentionUntilDate;
    /** @var  CustomerHas24HourSupport */
    protected $has24HourSupport;
    /** @var  CustomerPriority1SLA */
    protected $slaP1;
    /** @var  CustomerPriority2SLA */
    protected $slaP2;
    /** @var  CustomerPriority3SLA */
    protected $slaP3;
    /** @var  CustomerPriority4SLA */
    protected $slaP4;
    /** @var  CustomerPriority5SLA */
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