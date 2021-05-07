<?php
/**
 * System header controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

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
    const CONST_HEADER="header";
    const CONST_PORTAL_DOCUMENTS="portalDocument";
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
            case self::CONST_HEADER:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getHeader(),JSON_NUMERIC_CHECK);
                        break;
                    // case 'POST':
                    //     echo  json_encode($this->addItemType(),JSON_NUMERIC_CHECK);
                    //     break;
                     case 'PUT':
                         echo  json_encode($this->updateHeader(),JSON_NUMERIC_CHECK);
                    //     break;
                    // case 'DELETE':
                    //     echo  json_encode($this->deleteItemType(),JSON_NUMERIC_CHECK);
                    //     break;
                    default:
                        # code...
                        break;
                }
                exit;                
            case self::CONST_PORTAL_DOCUMENTS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getDocuuments(),JSON_NUMERIC_CHECK);
                        break;
                    // case 'POST':
                    //     echo  json_encode($this->addItemType(),JSON_NUMERIC_CHECK);
                    //     break;
                        case 'PUT':
                            echo  json_encode($this->updateHeader(),JSON_NUMERIC_CHECK);
                    //     break;
                    // case 'DELETE':
                    //     echo  json_encode($this->deleteItemType(),JSON_NUMERIC_CHECK);
                    //     break;
                    default:
                        # code...
                        break;
                }
                exit;         
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
                $this->initTemplate();
                break;
        }
    }

    function initTemplate(){
        $this->setPageTitle('Edit Header');
        $this->setTemplateFiles(
            array('HeaderEdit' => 'HeaderEdit.inc')
        );        
        $this->template->parse(
            'CONTENTS',
            'HeaderEdit',
            true
        );        
        $this->loadReactScript('HeaderComponent.js');
        $this->loadReactCSS('HeaderComponent.css');
        $this->parsePage();
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
    // -------------new code
    function getHeader(){
        $dbeHeader = new DBEJHeader($this);
        $dbeHeader->getRow(1);
        $header=[
            "headerID"=>$dbeHeader->getValue(DBEHeader::headerID),
            "name"=>$dbeHeader->getValue(DBEHeader::name),
            "add1"=>$dbeHeader->getValue(DBEHeader::add1),
            "add2"=>$dbeHeader->getValue(DBEHeader::add2),
            "add3"=>$dbeHeader->getValue(DBEHeader::add3),
            "town"=>$dbeHeader->getValue(DBEHeader::town),
            "county"=>$dbeHeader->getValue(DBEHeader::county),
            "postcode"=>$dbeHeader->getValue(DBEHeader::postcode),
            "phone"=>$dbeHeader->getValue(DBEHeader::phone),
            "fax"=>$dbeHeader->getValue(DBEHeader::fax),
            "goodsContact"=>$dbeHeader->getValue(DBEHeader::goodsContact),
            "gscItemID"=>$dbeHeader->getValue(DBEHeader::gscItemID),
            'gscItemDescription'=> $dbeHeader->getValue(DBEJHeader::gscItemDescription),
            "yearlySicknessThresholdWarning"=>$dbeHeader->getValue(DBEHeader::yearlySicknessThresholdWarning),
            "stdVATCode"=>$dbeHeader->getValue(DBEHeader::stdVATCode),
            "billingStartTime"=>$dbeHeader->getValue(DBEHeader::billingStartTime),
            "billingEndTime"=>$dbeHeader->getValue(DBEHeader::billingEndTime),
            "overtimeStartTime"=>$dbeHeader->getValue(DBEHeader::overtimeStartTime),
            "overtimeEndTime"=>$dbeHeader->getValue(DBEHeader::overtimeEndTime),
            "hourlyLabourCost"=>$dbeHeader->getValue(DBEHeader::hourlyLabourCost),
            "minimumOvertimeMinutesRequired"=>$dbeHeader->getValue(DBEHeader::minimumOvertimeMinutesRequired),
            "daysInAdvanceExpensesNextMonthAlert"=>$dbeHeader->getValue(DBEHeader::daysInAdvanceExpensesNextMonthAlert),
            "fixSLABreachWarningHours"=>$dbeHeader->getValue(DBEHeader::fixSLABreachWarningHours),
            "srAutocompleteThresholdHours"=>$dbeHeader->getValue(DBEHeader::srAutocompleteThresholdHours),
            "srStartersLeaversAutoCompleteThresholdHours"=>$dbeHeader->getValue(DBEHeader::srStartersLeaversAutoCompleteThresholdHours),
            "srPromptContractThresholdHours"=>$dbeHeader->getValue(DBEHeader::srPromptContractThresholdHours),
            "fixSLABreachWarningHours"=>$dbeHeader->getValue(DBEHeader::fixSLABreachWarningHours),
            "fixSLABreachWarningHours"=>$dbeHeader->getValue(DBEHeader::fixSLABreachWarningHours),
            "fixSLABreachWarningHours"=>$dbeHeader->getValue(DBEHeader::fixSLABreachWarningHours),
            "fixSLABreachWarningHours"=>$dbeHeader->getValue(DBEHeader::fixSLABreachWarningHours),
            "fixSLABreachWarningHours"=>$dbeHeader->getValue(DBEHeader::fixSLABreachWarningHours),
            "fixSLABreachWarningHours"=>$dbeHeader->getValue(DBEHeader::fixSLABreachWarningHours),
            "closureReminderDays"=>$dbeHeader->getValue(DBEHeader::closureReminderDays),
            "closingSRBufferMinutes"=>$dbeHeader->getValue(DBEHeader::closingSRBufferMinutes),
            "hdTeamLimitMinutes"=>$dbeHeader->getValue(DBEHeader::hdTeamLimitMinutes),
            "hdTeamTargetLogPercentage"=>$dbeHeader->getValue(DBEHeader::hdTeamTargetLogPercentage),
            "hdTeamTargetSlaPercentage"=>$dbeHeader->getValue(DBEHeader::hdTeamTargetSlaPercentage),
            "hdTeamTargetFixHours"=>$dbeHeader->getValue(DBEHeader::hdTeamTargetFixHours),
            "hdTeamTargetFixQtyPerMonth"=>$dbeHeader->getValue(DBEHeader::hdTeamTargetFixQtyPerMonth),
            "esTeamTargetLogPercentage"=>$dbeHeader->getValue(DBEHeader::esTeamTargetLogPercentage),
            "esTeamLimitMinutes"=>$dbeHeader->getValue(DBEHeader::esTeamLimitMinutes),
            "esTeamTargetSlaPercentage"=>$dbeHeader->getValue(DBEHeader::esTeamTargetSlaPercentage),
            "esTeamTargetFixHours"=>$dbeHeader->getValue(DBEHeader::esTeamTargetFixHours),
            "esTeamTargetFixQtyPerMonth"=>$dbeHeader->getValue(DBEHeader::esTeamTargetFixQtyPerMonth),
            "smallProjectsTeamTargetLogPercentage"=>$dbeHeader->getValue(DBEHeader::smallProjectsTeamTargetLogPercentage),
            "smallProjectsTeamLimitMinutes"=>$dbeHeader->getValue(DBEHeader::smallProjectsTeamLimitMinutes),
            "smallProjectsTeamMinutesInADay"=>$dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay),
            "smallProjectsTeamTargetSlaPercentage"=>$dbeHeader->getValue(DBEHeader::smallProjectsTeamTargetSlaPercentage),
            "smallProjectsTeamTargetFixHours"=>$dbeHeader->getValue(DBEHeader::smallProjectsTeamTargetFixHours),
            "smallProjectsTeamTargetFixQtyPerMonth"=>$dbeHeader->getValue(DBEHeader::smallProjectsTeamTargetFixQtyPerMonth),
            "projectTeamTargetLogPercentage"=>$dbeHeader->getValue(DBEHeader::projectTeamTargetLogPercentage),
            "projectTeamLimitMinutes"=>$dbeHeader->getValue(DBEHeader::projectTeamLimitMinutes),
            "projectTeamMinutesInADay"=>$dbeHeader->getValue(DBEHeader::projectTeamMinutesInADay),
            "projectTeamTargetSlaPercentage"=>$dbeHeader->getValue(DBEHeader::projectTeamTargetSlaPercentage),
            "projectTeamTargetFixHours"=>$dbeHeader->getValue(DBEHeader::projectTeamTargetFixHours),
            "projectTeamTargetFixQtyPerMonth"=>$dbeHeader->getValue(DBEHeader::projectTeamTargetFixQtyPerMonth),
            "SDDashboardEngineersInSREngineersMaxCount"=>$dbeHeader->getValue(DBEHeader::SDDashboardEngineersInSREngineersMaxCount),
            "SDDashboardEngineersInSRInPastHours"=>$dbeHeader->getValue(DBEHeader::SDDashboardEngineersInSRInPastHours),
            "serviceDeskNotification24hBegin"=>$dbeHeader->getValue(DBEHeader::serviceDeskNotification24hBegin),
            "serviceDeskNotification24hEnd"=>$dbeHeader->getValue(DBEHeader::serviceDeskNotification24hEnd),
            "highActivityAlertCount"=>$dbeHeader->getValue(DBEHeader::highActivityAlertCount),
            "customerContactWarnHours"=>$dbeHeader->getValue(DBEHeader::customerContactWarnHours),
            "remoteSupportWarnHours"=>$dbeHeader->getValue(DBEHeader::remoteSupportWarnHours),
            "RemoteSupportMinWarnHours"=>$dbeHeader->getValue(DBEHeader::RemoteSupportMinWarnHours),
            "autoCriticalP1Hours"=>$dbeHeader->getValue(DBEHeader::autoCriticalP1Hours),
            "autoCriticalP2Hours"=>$dbeHeader->getValue(DBEHeader::autoCriticalP2Hours),
            "autoCriticalP3Hours"=>$dbeHeader->getValue(DBEHeader::autoCriticalP3Hours),
            "hdTeamManagementTimeApprovalMinutes"=>$dbeHeader->getValue(DBEHeader::hdTeamManagementTimeApprovalMinutes),
            "esTeamManagementTimeApprovalMinutes"=>$dbeHeader->getValue(DBEHeader::esTeamManagementTimeApprovalMinutes),
            "smallProjectsTeamManagementTimeApprovalMinutes"=>$dbeHeader->getValue(DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes),
            "sevenDayerTarget"=>$dbeHeader->getValue(DBEHeader::sevenDayerTarget),
            "sevenDayerAmberDays"=>$dbeHeader->getValue(DBEHeader::sevenDayerAmberDays),
            "sevenDayerRedDays"=>$dbeHeader->getValue(DBEHeader::sevenDayerRedDays),
            "pendingTimeLimitActionThresholdMinutes"=>$dbeHeader->getValue(DBEHeader::pendingTimeLimitActionThresholdMinutes),
            "numberOfAllowedMistakes"=>$dbeHeader->getValue(DBEHeader::numberOfAllowedMistakes),
            "keywordMatchingPercent"=>$dbeHeader->getValue(DBEHeader::keywordMatchingPercent),
            "portalPin"=>$dbeHeader->getValue(DBEHeader::portalPin),
            "portal24HourPin"=>$dbeHeader->getValue(DBEHeader::portal24HourPin),
            "backupTargetSuccessRate"=>$dbeHeader->getValue(DBEHeader::backupTargetSuccessRate),
            "backupReplicationTargetSuccessRate"=>$dbeHeader->getValue(DBEHeader::backupReplicationTargetSuccessRate),
            "secondSiteReplicationAdditionalDelayAllowance"=>$dbeHeader->getValue(DBEHeader::secondSiteReplicationAdditionalDelayAllowance),
            "projectCommenceNotification"=>$dbeHeader->getValue(DBEHeader::projectCommenceNotification),
            "holdAllSOSmallProjectsP5sforQAReview"=>$dbeHeader->getValue(DBEHeader::holdAllSOSmallProjectsP5sforQAReview),
            "holdAllSOProjectsP5sforQAReview"=>$dbeHeader->getValue(DBEHeader::holdAllSOProjectsP5sforQAReview)?true:false,
            "OSSupportDatesThresholdDays"=>$dbeHeader->getValue(DBEHeader::OSSupportDatesThresholdDays)?true:false,
            "antivirusOutOfDateThresholdDays"=>$dbeHeader->getValue(DBEHeader::antivirusOutOfDateThresholdDays),
            "offlineAgentThresholdDays"=>$dbeHeader->getValue(DBEHeader::offlineAgentThresholdDays),
            "office365MailboxYellowWarningThreshold"=>$dbeHeader->getValue(DBEHeader::office365MailboxYellowWarningThreshold),
            "office365MailboxRedWarningThreshold"=>$dbeHeader->getValue(DBEHeader::office365MailboxRedWarningThreshold),
            "office365ActiveSyncWarnAfterXDays"=>$dbeHeader->getValue(DBEHeader::office365ActiveSyncWarnAfterXDays),
            "cDriveFreeSpaceWarningPercentageThreshold"=>$dbeHeader->getValue(DBEHeader::cDriveFreeSpaceWarningPercentageThreshold),
            "otherDriveFreeSpaceWarningPercentageThreshold"=>$dbeHeader->getValue(DBEHeader::otherDriveFreeSpaceWarningPercentageThreshold),
            "computerLastSeenThresholdDays"=>$dbeHeader->getValue(DBEHeader::computerLastSeenThresholdDays),
            "allowedClientIpPattern"=>$dbeHeader->getValue(DBEHeader::allowedClientIpPattern),
            "solarwindsPartnerName"=>$dbeHeader->getValue(DBEHeader::solarwindsPartnerName),
            "solarwindsUsername"=>$dbeHeader->getValue(DBEHeader::solarwindsUsername),
            "solarwindsPassword"=>$dbeHeader->getValue(DBEHeader::solarwindsPassword),
            "customerReviewMeetingText"=>$dbeHeader->getValue(DBEHeader::customerReviewMeetingText),
            "mailshot2FlagDesc"=>$dbeHeader->getValue(DBEHeader::mailshot2FlagDesc),
            "mailshot2FlagDef"=>$dbeHeader->getValue(DBEHeader::mailshot2FlagDef)=='Y'?true:false,
            "mailshot3FlagDesc"=>$dbeHeader->getValue(DBEHeader::mailshot3FlagDesc),
            "mailshot3FlagDef"=>$dbeHeader->getValue(DBEHeader::mailshot3FlagDef)=='Y'?true:false,
            "mailshot4FlagDesc"=>$dbeHeader->getValue(DBEHeader::mailshot4FlagDesc),
            "mailshot4FlagDef"=>$dbeHeader->getValue(DBEHeader::mailshot4FlagDef)=='Y'?true:false,
            "mailshot8FlagDesc"=>$dbeHeader->getValue(DBEHeader::mailshot8FlagDesc),
            "mailshot8FlagDef"=>$dbeHeader->getValue(DBEHeader::mailshot8FlagDef)=='Y'?true:false,
            "mailshot9FlagDesc"=>$dbeHeader->getValue(DBEHeader::mailshot9FlagDesc),
            "mailshot9FlagDef"=>$dbeHeader->getValue(DBEHeader::mailshot9FlagDef)=='Y'?true:false,
            "mailshot11FlagDesc"=>$dbeHeader->getValue(DBEHeader::mailshot11FlagDesc),
            "mailshot11FlagDef"=>$dbeHeader->getValue(DBEHeader::mailshot11FlagDef)=='Y'?true:false,            
            "priority1Desc"=>$dbeHeader->getValue(DBEHeader::priority1Desc),
            "priority2Desc"=>$dbeHeader->getValue(DBEHeader::priority2Desc),
            "priority3Desc"=>$dbeHeader->getValue(DBEHeader::priority3Desc),
            "priority4Desc"=>$dbeHeader->getValue(DBEHeader::priority4Desc),
            "priority5Desc"=>$dbeHeader->getValue(DBEHeader::priority5Desc),

        ];
        return $this->success($header);
    }
    function updateHeader(){
        try {
            $body = $this->getBody(true);
            if (!$body) {
                return $this->fail(APIException::badRequest, "Bad Request2");
            }
            //$this->dsHeader->debug=true;
            if (!$this->dsHeader->populateFromArray([ $body])) {
                $this->setFormErrorOn();
                $this->setAction(CTCNC_ACT_ITEM_EDIT);                
                return $this->fail(APIException::badRequest, $this->getFormErrorMessage());
            }
            $this->setAction(CTCNC_ACT_ITEM_EDIT);
            $this->buHeader->updateHeader($this->dsHeader);
            return $this->success();
        } catch (Exception $ex) {
            return $this->fail($ex->getMessage());
        }
    }
    function getDocuuments(){
        $data=[];
        if ($this->getAction() != 'add') {
            $buPortalDocument = new BUPortalDocument($this);
            $dsPortalDocument = new DataSet($this);
            $buPortalDocument->getDocuments($dsPortalDocument);
            
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
                $data []=
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
                    );
            }
        }
        return $this->success($data);
    }
}
