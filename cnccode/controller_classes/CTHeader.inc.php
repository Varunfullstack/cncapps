<?php
/**
 * System header controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPortalDocument.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define(
    'CTHEADER_ACT_EDIT',
    'editHeader'
);
define(
    'CTHEADER_ACT_UPDATE',
    'updateHeader'
);

class CTHeader extends CTCNC
{
    const GET_PRIORITIES_DESCRIPTIONS = 'getPrioritiesDescriptions';
    const GET_NUMBER_ALLOWED_MISTAKES = "numberOfAllwoedMistaks";
    const KEYWORD_MATCHING_PERCENT    = "keywordMatchingPercent";
    /** @var DSForm */
    public $dsHeader;
    /** @var BUHeader */
    public $buHeader;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $this->setMenuId(901);
        $this->buHeader = new BUHeader($this);
        $this->dsHeader = new DSForm($this);
        $this->dsHeader->copyColumnsFrom($this->buHeader->dbeJHeader);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case CTHEADER_ACT_UPDATE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $roles = SENIOR_MANAGEMENT_PERMISSION;
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $this->update();
                break;
            case self::GET_PRIORITIES_DESCRIPTIONS:
            {
                $this->getPrioritiesDescriptions();
                exit;
            }
            case self::GET_NUMBER_ALLOWED_MISTAKES:
                echo json_encode($this->getNumberAllwoedMistakes());
                exit;
            case self::KEYWORD_MATCHING_PERCENT:
                echo json_encode($this->getKeywordMatchingPercent());
                exit;
            case CTHEADER_ACT_EDIT:
            default:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $roles = SENIOR_MANAGEMENT_PERMISSION;
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $this->edit();
                break;
        }
    }

    /**
     * Update
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsHeader->populateFromArray($this->getParam('header')));
        if ($this->formError) {
            $this->setAction(CTHEADER_ACT_EDIT);
            $this->edit();
            exit;
        }
        $this->buHeader->updateHeader($this->dsHeader);
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_VIEW
            )
        );
        header('Location: ' . $urlNext);
    }// end function editHeader()

    /**
     * Edit/Add Header
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsHeader = &$this->dsHeader; // ref to class var
        if (!$this->getFormError()) {
            $this->buHeader->getHeader($dsHeader);
        } else {                                                                        // form validation error
            $dsHeader->initialise();
            $dsHeader->fetchNext();
        }
        $urlUpdate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTHEADER_ACT_UPDATE
            )
        );
        $this->setPageTitle('Edit Header');
        $this->setTemplateFiles(
            array('HeaderEdit' => 'HeaderEdit.inc')
        );
        $urlItemPopup = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_DISP_ITEM_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->template->set_var(
            [
                'headerID'                                                           => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::headerID)
                ),
                'name'                                                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::name)
                ),
                'nameMessage'                                                        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::name)
                ),
                'add1'                                                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::add1)
                ),
                'add1Message'                                                        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::add1)
                ),
                'expensesNextProcessingDate'                                         => $dsHeader->getValue(
                    DBEHeader::expensesNextProcessingDate
                ),
                'add2'                                                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::add2)
                ),
                'add3'                                                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::add3)
                ),
                'town'                                                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::town)
                ),
                'townMessage'                                                        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::town)
                ),
                'county'                                                             => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::county)
                ),
                'postcode'                                                           => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::postcode)
                ),
                'postcodeMessage'                                                    => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::postcode)
                ),
                'phone'                                                              => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::phone)
                ),
                'phoneMessage'                                                       => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::phone)
                ),
                'fax'                                                                => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::fax)
                ),
                'faxMessage'                                                         => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::fax)
                ),
                'goodsContact'                                                       => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::goodsContact)
                ),
                'goodsContactMessage'                                                => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::goodsContact)
                ),
                'serviceDeskNotification24hBegin'                                    => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::serviceDeskNotification24hBegin)
                ),
                'serviceDeskNotification24hBeginMessage'                             => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::serviceDeskNotification24hBegin)
                ),
                'serviceDeskNotification24hEnd'                                      => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::serviceDeskNotification24hEnd)
                ),
                'serviceDeskNotification24hEndMessage'                               => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::serviceDeskNotification24hEnd)
                ),
                'billingStartTime'                                                   => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::billingStartTime)
                ),
                'billingStartTimeMessage'                                            => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::billingStartTime)
                ),
                'billingEndTime'                                                     => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::billingEndTime)
                ),
                'billingEndTimeMessage'                                              => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::billingEndTime)
                ),
                'overtimeStartTime'                                                  => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::overtimeStartTime)
                ),
                'overtimeStartTimeMessage'                                           => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::overtimeStartTime)
                ),
                'overtimeEndTime'                                                    => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::overtimeEndTime)
                ),
                'overtimeEndTimeMessage'                                             => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::overtimeEndTime)
                ),
                'fixSLABreachWarningHours'                                           => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::fixSLABreachWarningHours)
                ),
                'fixSLABreachWarningHoursMessage'                                    => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::fixSLABreachWarningHours)
                ),
                'hourlyLabourCost'                                                   => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::hourlyLabourCost)
                ),
                'hourlyLabourCostMessage'                                            => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::hourlyLabourCost)
                ),
                'portalPin'                                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::portalPin)
                ),
                'portalPinMessage'                                                   => Controller::htmlInputText(
                    $dsHeader->getMessage(DBEJHeader::portalPin)
                ),
                'portal24HourPin'                                                    => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::portal24HourPin)
                ),
                'portal24HourPinMessage'                                             => Controller::htmlInputText(
                    $dsHeader->getMessage(DBEJHeader::portal24HourPin)
                ),
                'gscItemID'                                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::gscItemID)
                ),
                'gscItemDescription'                                                 => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::gscItemDescription)
                ),
                'highActivityAlertCount'                                             => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::highActivityAlertCount)
                ),
                'highActivityAlertCountMessage'                                      => $dsHeader->getMessage(
                    DBEJHeader::highActivityAlertCount
                ),
                'mailshot2FlagDesc'                                                  => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::mailshot2FlagDesc)
                ),
                'mailshot2FlagDefChecked'                                            => $this->getChecked(
                    $dsHeader->getValue(DBEJHeader::mailshot2FlagDef)
                ),
                'mailshot3FlagDesc'                                                  => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::mailshot3FlagDesc)
                ),
                'mailshot3FlagDefChecked'                                            => $this->getChecked(
                    $dsHeader->getValue(DBEJHeader::mailshot3FlagDef)
                ),
                'mailshot4FlagDesc'                                                  => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::mailshot4FlagDesc)
                ),
                'mailshot4FlagDefChecked'                                            => $this->getChecked(
                    $dsHeader->getValue(DBEJHeader::mailshot4FlagDef)
                ),
                'mailshot8FlagDesc'                                                  => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::mailshot8FlagDesc)
                ),
                'mailshot8FlagDefChecked'                                            => $this->getChecked(
                    $dsHeader->getValue(DBEJHeader::mailshot8FlagDef)
                ),
                'mailshot9FlagDesc'                                                  => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::mailshot9FlagDesc)
                ),
                'mailshot9FlagDefChecked'                                            => $this->getChecked(
                    $dsHeader->getValue(DBEJHeader::mailshot9FlagDef)
                ),
                'mailshot11FlagDesc'                                                 => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::mailshot11FlagDesc)
                ),
                'mailshot11FlagDefChecked'                                           => $this->getChecked(
                    $dsHeader->getValue(DBEJHeader::mailshot11FlagDef)
                ),
                'priority1Desc'                                                      => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::priority1Desc)
                ),
                'priority1DescMessage'                                               => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::priority1Desc)
                ),
                'priority2Desc'                                                      => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::priority2Desc)
                ),
                'priority2DescMessage'                                               => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::priority2Desc)
                ),
                'priority3Desc'                                                      => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::priority3Desc)
                ),
                'priority3DescMessage'                                               => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::priority3Desc)
                ),
                'priority4Desc'                                                      => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::priority4Desc)
                ),
                'priority4DescMessage'                                               => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::priority4Desc)
                ),
                'priority5Desc'                                                      => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::priority5Desc)
                ),
                'priority5DescMessage'                                               => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::priority5Desc)
                ),
                'allowedClientIpPattern'                                             => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::allowedClientIpPattern)
                ),
                'allowedClientIpPatternMessage'                                      => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::allowedClientIpPattern)
                ),
                'hdTeamLimitMinutes'                                                 => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::hdTeamLimitMinutes)
                ),
                'hdTeamLimitMinutesMessage'                                          => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::hdTeamLimitMinutes)
                ),
                DBEHeader::hdTeamManagementTimeApprovalMinutes                       => $dsHeader->getValue(
                    DBEHeader::hdTeamManagementTimeApprovalMinutes
                ),
                'esTeamLimitMinutes'                                                 => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::esTeamLimitMinutes)
                ),
                'esTeamLimitMinutesMessage'                                          => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::esTeamLimitMinutes)
                ),
                DBEHeader::esTeamManagementTimeApprovalMinutes                       => $dsHeader->getValue(
                    DBEHeader::esTeamManagementTimeApprovalMinutes
                ),
                'smallProjectsTeamLimitMinutes'                                      => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::smallProjectsTeamLimitMinutes)
                ),
                'smallProjectsTeamLimitMinutesMessage'                               => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::smallProjectsTeamLimitMinutes)
                ),
                DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes            => $dsHeader->getValue(
                    DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                ),
                'projectTeamLimitMinutes'                                            => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::projectTeamLimitMinutes)
                ),
                'projectTeamLimitMinutesMessage'                                     => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::projectTeamLimitMinutes)
                ),
                'hdTeamTargetLogPercentage'                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::hdTeamTargetLogPercentage)
                ),
                'hdTeamTargetSlaPercentage'                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::hdTeamTargetSlaPercentage)
                ),
                'hdTeamTargetSlaPercentageMessage'                                   => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::hdTeamTargetSlaPercentage)
                ),
                'hdTeamTargetFixHours'                                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::hdTeamTargetFixHours)
                ),
                'hdTeamTargetFixHoursMessage'                                        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::hdTeamTargetFixHours)
                ),
                'hdTeamTargetFixQtyPerMonth'                                         => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::hdTeamTargetFixQtyPerMonth)
                ),
                'hdTeamTargetFixQtyPerMonthMessage'                                  => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::hdTeamTargetFixQtyPerMonth)
                ),
                'esTeamTargetLogPercentage'                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::esTeamTargetLogPercentage)
                ),
                'esTeamTargetSlaPercentage'                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::esTeamTargetSlaPercentage)
                ),
                'esTeamTargetSlaPercentageMessage'                                   => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::esTeamTargetSlaPercentage)
                ),
                'projectTeamTargetSlaPercentage'                                     => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::projectTeamTargetSlaPercentage)
                ),
                'projectTeamTargetSlaPercentageMessage'                              => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::projectTeamTargetSlaPercentage)
                ),
                'esTeamTargetFixHours'                                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::esTeamTargetFixHours)
                ),
                'esTeamTargetFixHoursMessage'                                        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::esTeamTargetFixHours)
                ),
                'esTeamTargetFixQtyPerMonth'                                         => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::esTeamTargetFixQtyPerMonth)
                ),
                'esTeamTargetFixQtyPerMonthMessage'                                  => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::esTeamTargetFixQtyPerMonth)
                ),
                'smallProjectsTeamTargetLogPercentage'                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::smallProjectsTeamTargetLogPercentage)
                ),
                'projectTeamTargetLogPercentage'                                     => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::projectTeamTargetLogPercentage)
                ),
                'smallProjectsTeamTargetSlaPercentage'                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::smallProjectsTeamTargetSlaPercentage)
                ),
                'smallProjectsTeamTargetSlaPercentageMessage'                        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::smallProjectsTeamTargetSlaPercentage)
                ),
                'smallProjectsTeamTargetFixHours'                                    => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::smallProjectsTeamTargetFixHours)
                ),
                'projectTeamTargetFixHours'                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::projectTeamTargetFixHours)
                ),
                'smallProjectsTeamMinutesInADay'                                     => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay)
                ),
                'projectTeamMinutesInADay'                                           => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::projectTeamMinutesInADay)
                ),
                'smallProjectsTeamTargetFixHoursMessage'                             => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::smallProjectsTeamTargetFixHours)
                ),
                'smallProjectsTeamTargetFixQtyPerMonth'                              => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::smallProjectsTeamTargetFixQtyPerMonth)
                ),
                'smallProjectsTeamTargetFixQtyPerMonthMessage'                       => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::smallProjectsTeamTargetFixQtyPerMonth)
                ),
                'projectTeamTargetFixQtyPerMonth'                                    => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::projectTeamTargetFixQtyPerMonth)
                ),
                'projectTeamTargetFixQtyPerMonthMessage'                             => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::projectTeamTargetFixQtyPerMonth)
                ),
                'srAutocompleteThresholdHours'                                       => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::srAutocompleteThresholdHours)
                ),
                'srAutocompleteThresholdHoursMessage'                                => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::srAutocompleteThresholdHours)
                ),
                'srStartersLeaversAutoCompleteThresholdHours'                        => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::srStartersLeaversAutoCompleteThresholdHours)
                ),
                'srStartersLeaversAutoCompleteThresholdHoursMessage'                 => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::srStartersLeaversAutoCompleteThresholdHours)
                ),
                'srPromptContractThresholdHours'                                     => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::srPromptContractThresholdHours)
                ),
                'srPromptContractThresholdHoursMessage'                              => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::srPromptContractThresholdHours)
                ),
                'customerContactWarnHours'                                           => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::customerContactWarnHours)
                ),
                'customerContactWarnHoursMessage'                                    => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::customerContactWarnHours)
                ),
                'remoteSupportWarnHours'                                             => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::remoteSupportWarnHours)
                ),
                'remoteSupportWarnHoursMessage'                                      => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEJHeader::remoteSupportWarnHours)
                ),
                DBEHeader::minimumOvertimeMinutesRequired                            => Controller::htmlInputText(
                    $dsHeader->getValue(DBEJHeader::minimumOvertimeMinutesRequired)
                ),
                DBEHeader::minimumOvertimeMinutesRequired . 'Message'                => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::minimumOvertimeMinutesRequired)
                ),
                'customerReviewMeetingText'                                          => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::customerReviewMeetingText)
                ),
                DBEHeader::computerLastSeenThresholdDays                             => $dsHeader->getValue(
                    DBEHeader::computerLastSeenThresholdDays
                ),
                DBEHeader::RemoteSupportMinWarnHours                                 => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::RemoteSupportMinWarnHours)
                ),
                DBEHeader::RemoteSupportMinWarnHours . 'Message'                     => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::RemoteSupportMinWarnHours)
                ),
                DBEHeader::backupTargetSuccessRate                                   => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::backupTargetSuccessRate)
                ),
                DBEHeader::backupReplicationTargetSuccessRate                        => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::backupReplicationTargetSuccessRate)
                ),
                DBEHeader::SDDashboardEngineersInSREngineersMaxCount                 => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::SDDashboardEngineersInSREngineersMaxCount)
                ),
                "SDDashboardEngineersInSRInPastHours"                                => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::SDDashboardEngineersInSRInPastHours)
                ),
                DBEHeader::SDDashboardEngineersInSREngineersMaxCount . "Message"     => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::SDDashboardEngineersInSREngineersMaxCount)
                ),
                "SDDashboardEngineersInSRInPastHoursMessage"                         => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::SDDashboardEngineersInSRInPastHours)
                ),
                DBEHeader::secondSiteReplicationAdditionalDelayAllowance             => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::secondSiteReplicationAdditionalDelayAllowance)
                ),
                DBEHeader::secondSiteReplicationAdditionalDelayAllowance . 'Message' => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::secondSiteReplicationAdditionalDelayAllowance)
                ),
                DBEHeader::projectCommenceNotification                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::projectCommenceNotification)
                ),
                DBEHeader::daysInAdvanceExpensesNextMonthAlert                       => $dsHeader->getValue(
                    DBEHeader::daysInAdvanceExpensesNextMonthAlert
                ),
                DBEHeader::projectCommenceNotification . 'Message'                   => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::projectCommenceNotification)
                ),
                DBEHeader::OSSupportDatesThresholdDays                               => Controller::htmlInputText(
                    $dsHeader->getValue(DBEHeader::OSSupportDatesThresholdDays)
                ),
                DBEHeader::OSSupportDatesThresholdDays . 'Message'                   => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::OSSupportDatesThresholdDays)
                ),
                DBEHeader::closingSRBufferMinutes                                    => $dsHeader->getValue(
                    DBEHeader::closingSRBufferMinutes
                ),
                DBEHeader::closingSRBufferMinutes . 'Message'                        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::closingSRBufferMinutes)
                ),
                DBEHeader::sevenDayerAmberDays                                       => $dsHeader->getValue(
                    DBEHeader::sevenDayerAmberDays
                ),
                DBEHeader::sevenDayerAmberDays . 'Message'                           => $dsHeader->getMessage(
                    DBEHeader::sevenDayerAmberDays
                ),
                DBEHeader::sevenDayerTarget                                          => $dsHeader->getValue(
                    DBEHeader::sevenDayerTarget
                ),
                DBEHeader::sevenDayerTarget . 'Message'                              => $dsHeader->getMessage(
                    DBEHeader::sevenDayerTarget
                ),
                DBEHeader::sevenDayerRedDays                                         => $dsHeader->getValue(
                    DBEHeader::sevenDayerRedDays
                ),
                DBEHeader::sevenDayerRedDays . 'Message'                             => $dsHeader->getMessage(
                    DBEHeader::sevenDayerRedDays
                ),
                DBEHeader::office365MailboxYellowWarningThreshold                    => $dsHeader->getValue(
                    DBEHeader::office365MailboxYellowWarningThreshold
                ),
                DBEHeader::office365MailboxYellowWarningThreshold . 'Message'        => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::office365MailboxYellowWarningThreshold)
                ),
                DBEHeader::office365MailboxRedWarningThreshold                       => $dsHeader->getValue(
                    DBEHeader::office365MailboxRedWarningThreshold
                ),
                DBEHeader::office365MailboxRedWarningThreshold . 'Message'           => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::office365MailboxRedWarningThreshold)
                ),
                DBEHeader::office365ActiveSyncWarnAfterXDays                         => $dsHeader->getValue(
                    DBEHeader::office365ActiveSyncWarnAfterXDays
                ),
                DBEHeader::office365ActiveSyncWarnAfterXDays . 'Message'             => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::office365ActiveSyncWarnAfterXDays)
                ),
                DBEHeader::autoCriticalP1Hours                                       => $dsHeader->getValue(
                    DBEHeader::autoCriticalP1Hours
                ),
                DBEHeader::autoCriticalP1Hours . 'Message'                           => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::autoCriticalP1Hours)
                ),
                DBEHeader::autoCriticalP2Hours                                       => $dsHeader->getValue(
                    DBEHeader::autoCriticalP2Hours
                ),
                DBEHeader::autoCriticalP2Hours . 'Message'                           => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::autoCriticalP2Hours)
                ),
                DBEHeader::autoCriticalP3Hours                                       => $dsHeader->getValue(
                    DBEHeader::autoCriticalP3Hours
                ),
                DBEHeader::autoCriticalP3Hours . 'Message'                           => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::autoCriticalP3Hours)
                ),
                DBEHeader::pendingTimeLimitActionThresholdMinutes                    => Controller::htmlDisplayText(
                    $dsHeader->getValue(DBEHeader::pendingTimeLimitActionThresholdMinutes)
                ),
                DBEHeader::closureReminderDays                                       => $dsHeader->getValue(
                    DBEHeader::closureReminderDays
                ),
                DBEHeader::closureReminderDays . 'Message'                           => Controller::htmlDisplayText(
                    $dsHeader->getMessage(DBEHeader::closureReminderDays)
                ),
                DBEHeader::solarwindsPartnerName                                     => $dsHeader->getValue(
                    DBEHeader::solarwindsPartnerName
                ),
                DBEHeader::solarwindsPartnerName . 'Message'                         => $dsHeader->getMessage(
                    DBEHeader::solarwindsPartnerName
                ),
                DBEHeader::solarwindsUsername                                        => $dsHeader->getValue(
                    DBEHeader::solarwindsUsername
                ),
                DBEHeader::solarwindsUsername . 'Message'                            => $dsHeader->getMessage(
                    DBEHeader::solarwindsUsername
                ),
                DBEHeader::solarwindsPassword                                        => $dsHeader->getValue(
                    DBEHeader::solarwindsPassword
                ),
                DBEHeader::solarwindsPassword . 'Message'                            => $dsHeader->getMessage(
                    DBEHeader::solarwindsPassword
                ),
                DBEHeader::cDriveFreeSpaceWarningPercentageThreshold                 => $dsHeader->getValue(
                    DBEHeader::cDriveFreeSpaceWarningPercentageThreshold
                ),
                DBEHeader::otherDriveFreeSpaceWarningPercentageThreshold             => $dsHeader->getValue(
                    DBEHeader::otherDriveFreeSpaceWarningPercentageThreshold
                ),
                DBEHeader::yearlySicknessThresholdWarning                            => $dsHeader->getValue(
                    DBEHeader::yearlySicknessThresholdWarning
                ),
                DBEHeader::antivirusOutOfDateThresholdDays                           => $dsHeader->getValue(
                    DBEHeader::antivirusOutOfDateThresholdDays
                ),
                DBEHeader::offlineAgentThresholdDays                                 => $dsHeader->getValue(
                    DBEHeader::offlineAgentThresholdDays
                ),
                'urlItemPopup'                                                       => $urlItemPopup,
                'urlUpdate'                                                          => $urlUpdate,
                DBEHeader::holdAllSOSmallProjectsP5sforQAReview                      => $dsHeader->getValue(
                    DBEHeader::holdAllSOSmallProjectsP5sforQAReview
                ) ? "checked" : null,
                DBEHeader::holdAllSOProjectsP5sforQAReview                           => $dsHeader->getValue(
                    DBEHeader::holdAllSOProjectsP5sforQAReview
                ) ? "checked" : null,
                DBEHeader::numberOfAllowedMistakes                                   => $dsHeader->getValue(
                    DBEHeader::numberOfAllowedMistakes
                ),
                'keywordMatchingPercent'                                             => $dsHeader->getValue(DBEHeader::keywordMatchingPercent),

            ]
        );
        // VAT code
        $this->template->set_block(
            'HeaderEdit',
            'vatCodeBlock',
            'vatCodes'
        );
        for ($i = 0; $i < 10; $i++) {
            $vatCode         = 'T' . $i;
            $vatCodeSelected = ($dsHeader->getValue(DBEJHeader::stdVATCode) == $vatCode) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'vatCodeSelected' => $vatCodeSelected,
                    'vatCode'         => $vatCode,
                )
            );
            $this->template->parse(
                'vatCodes',
                'vatCodeBlock',
                true
            );
        }
        $this->documents('HeaderEdit');
        $this->template->parse(
            'CONTENTS',
            'HeaderEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * @param $templateName
     * @throws Exception
     */
    function documents($templateName)
    {
        $this->template->set_block(
            $templateName,
            'portalDocumentBlock',
            'portalDocuments'
        );
        if ($this->getAction() != 'add') {
            $buPortalDocument = new BUPortalDocument($this);
            $dsPortalDocument = new DataSet($this);
            $buPortalDocument->getDocuments($dsPortalDocument);
            $urlAddDocument = Controller::buildLink(
                'PortalDocument.php',
                array(
                    'action' => 'add'
                )
            );
            $this->template->set_var(
                array(
                    'txtAddDocument' => 'Add document',
                    'urlAddDocument' => $urlAddDocument
                )
            );
            while ($dsPortalDocument->fetchNext()) {

                $urlEditDocument   = Controller::buildLink(
                    'PortalDocument.php',
                    array(
                        'action'           => 'edit',
                        'portalDocumentID' => $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)
                    )
                );
                $urlViewFile       = Controller::buildLink(
                    'PortalDocument.php',
                    array(
                        'action'           => 'viewFile',
                        'portalDocumentID' => $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)
                    )
                );
                $urlDeleteDocument = Controller::buildLink(
                    'PortalDocument.php',
                    array(
                        'action'           => 'delete',
                        'portalDocumentID' => $dsPortalDocument->getValue(DBEPortalDocument::portalDocumentID)
                    )
                );
                $this->template->set_var(
                    array(
                        'description'            => $dsPortalDocument->getValue(DBEPortalDocument::description),
                        'filename'               => $dsPortalDocument->getValue(DBEPortalDocument::filename),
                        'mainContactOnlyFlag'    => $dsPortalDocument->getValue(DBEPortalDocument::mainContactOnlyFlag),
                        'requiresAcceptanceFlag' => $dsPortalDocument->getValue(
                            DBEPortalDocument::requiresAcceptanceFlag
                        ),
                        'createDate'             => $dsPortalDocument->getValue(DBEPortalDocument::createdDate),
                        'urlViewFile'            => $urlViewFile,
                        'urlEditDocument'        => $urlEditDocument,
                        'urlDeleteDocument'      => $urlDeleteDocument
                    )
                );
                $this->template->parse(
                    'portalDocuments',
                    'portalDocumentBlock',
                    true
                );
            }
        }
    }

    private function getPrioritiesDescriptions()
    {
        $dbeHeader = new DBEHeader($this);
        $dbeHeader->getRow(1);
        echo json_encode(
            [
                "status" => "ok",
                "data"   => [
                    [
                        "id"          => "1",
                        "description" => $dbeHeader->getValue(DBEHeader::priority1Desc)
                    ],
                    [
                        "id"          => "2",
                        "description" => $dbeHeader->getValue(DBEHeader::priority2Desc)
                    ],
                    [
                        "id"          => "3",
                        "description" => $dbeHeader->getValue(DBEHeader::priority3Desc)
                    ],
                    [
                        "id"          => "4",
                        "description" => $dbeHeader->getValue(DBEHeader::priority4Desc)
                    ],
                    [
                        "id"          => "5",
                        "description" => $dbeHeader->getValue(DBEHeader::priority5Desc)
                    ],
                ]
            ]
        );

    }

    function getNumberAllwoedMistakes()
    {
        $dbeHeader = new DBEHeader($this);
        $dbeHeader->getRow(1);
        return ["value" => $dbeHeader->getValue(DBEHeader::numberOfAllowedMistakes)];
    }

    function getKeywordMatchingPercent(){
        $dbeHeader = new DBEHeader($this);
        $dbeHeader->getRow(1);
        return $dbeHeader->getValue(DBEHeader::keywordMatchingPercent);
    }
}
